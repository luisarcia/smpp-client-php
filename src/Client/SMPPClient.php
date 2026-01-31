<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Client;

use Larc\SMPPClient\Config\ServerConfig;
use Larc\SMPPClient\SMPP;
use Larc\SMPPClient\PDU\PDU;
use Larc\SMPPClient\Interfaces\ConnectionInterface;
use Larc\SMPPClient\Debugger\TraceLogger;
use Larc\SMPPClient\Protocol\Encoding\Ucs2Encoding;
use Larc\SMPPClient\Protocol\Encoding\Gsm7Encoding;
use Larc\SMPPClient\Protocol\Sequence\SequenceGenerator;
use Larc\SMPPClient\Exception\SmppException;
use Larc\SMPPClient\PDU\PDUResponse;
use Larc\SMPPClient\Protocol\Message\MessageSplitter;
use Larc\SMPPClient\Protocol\SubmitSm;
use Larc\SMPPClient\Protocol\SubmitSmParams;
use Larc\SMPPClient\Transport\SocketClient;

final class SMPPClient
{
    private ConnectionInterface $socket;
    private string $systemId;
    private string $password;
    private int $addrTon;
    private int $addrNpi;
    private int $commandId;
    private SequenceGenerator $sequence;
    private TraceLogger $trace;

    private ?string $currentSender = null;
    private ?string $currentRecipient = null;
    private ?string $messageText = null;
    private bool $flash = false;
    private bool $utf8 = false;

    private ?string $lastMessageId = null;

    public function __construct(ServerConfig $config)
    {
        $this->socket = new SocketClient(
            $config->host(),
            $config->port(),
            $config->timeout(),
            false
        );

        $this->systemId = $config->systemId();
        $this->password = $config->password();
        $this->addrTon = $config->addrTon();
        $this->addrNpi = $config->addrNpi();
        $this->commandId = $config->bindType();
        $this->sequence = new SequenceGenerator();
        $this->trace = new TraceLogger();
    }

    /**
     * Method login
     * Establishes the SMPP connection by sending a BIND request
     *
     * @return bool
     */
    public function login(): bool
    {
        $this->socket->connect();

        // Build BIND PDU body
        $data  = sprintf("%s\0%s\0", $this->systemId, $this->password); // system_id, password
        $data .= sprintf("%s\0%c", 'SMPP', SMPP::SMPP_3_4); // system_type, interface_version
        $data .= sprintf("%c%c%s\0", $this->addrTon, $this->addrNpi, ''); // addr_ton, addr_npi, address_range

        $response = $this->sendCommand($this->commandId, $data);

        if ($response->commandStatus !== SMPP::ESME_ROK) {
            return false;
        }

        $this->trace->write('>>> Bind OK');
        return true;
    }

    /**
     * Method logout
     * Closes the SMPP connection by sending an UNBIND request
     *
     * @return void
     */
    public function logout(): void
    {
        try {
            $this->sendCommand(SMPP::UNBIND, '');
        } catch (\Throwable) {
            // Ignore errors in UNBIND
        } finally {
            $this->socket->disconnect();
        }
    }

    /**
     * Method from
     * Sets the sender ID for the message
     *
     * @param string $sender Sender ID
     *
     * @return self
     */
    public function from(string $sender): self
    {
        $this->currentSender = $sender;
        return $this;
    }

    /**
     * Method fromTon
     * Sets the TON (Type of Number) for the sender
     *
     * @param int $ton Type of Number
     *
     * @return self
     */
    public function fromTon(int $ton): self
    {
        $this->addrTon = $ton;
        return $this;
    }

    /**
     * Method fromNpi
     * Sets the NPI (Numbering Plan Indicator) for the sender
     *
     * @param int $npi Numbering Plan Indicator
     *
     * @return self
     */
    public function fromNpi(int $npi): self
    {
        $this->addrNpi = $npi;
        return $this;
    }

    /**
     * Method to
     * Sets the recipient number for the message
     *
     * @param string $recipient Recipient number
     *
     * @return self
     */
    public function to(string $recipient): self
    {
        $this->currentRecipient = $recipient;
        return $this;
    }

    /**
     * Method message
     * Sets the message text to be sent
     *
     * @param string $text Message text
     *
     * @return self
     */
    public function message(string $text): self
    {
        $this->messageText = $text;
        return $this;
    }

    /**
     * Method asFlash
     * Marks the message as a flash SMS
     *
     * @return self
     */
    public function asFlash(): self
    {
        $this->flash = true;
        return $this;
    }

    /**
     * Method asUtf8
     * Sets the message encoding to UTF-8 (UCS2)
     *
     * @param bool $utf8 Whether to use UTF-8 encoding
     *
     * @return self
     */
    public function asUtf8(): self
    {
        $this->utf8 = true;
        return $this;
    }

    /**
     * Method send
     * Sends the prepared SMS message
     *
     * @return void
     *
     * @throws SmppException
     */
    public function send(): void
    {
        if (!$this->currentSender || !$this->currentRecipient || !$this->messageText) {
            throw new SmppException('Sender, recipient and message are required');
        }

        $encoding = $this->utf8 ? new Ucs2Encoding() : new Gsm7Encoding();
        $dataCoding = $encoding->dataCoding();

        if ($this->flash) {
            $dataCoding |= 0x10;
        }

        $payload = $encoding->encode($this->messageText);
        $splitter = new MessageSplitter();
        $segments = $splitter->split($payload, $encoding);

        foreach ($segments as $segment) {
            $response = $this->submitSm(
                $this->currentSender,
                $this->currentRecipient,
                $segment->body(),
                $segment->optionalParams(),
                $segment->esmClass(),
                $dataCoding
            );

            $this->lastMessageId = $response->bodyData['message_id'] ?? null;
        }

        $this->resetState();
    }

    /**
     * Method getLastMessageId
     * Retrieves the message ID of the last sent message
     *
     * @return string|null
     */
    public function getLastMessageId(): ?string
    {
        return $this->lastMessageId;
    }

    /**
     * Method submitSm
     * Sends a SUBMIT_SM PDU to the SMPP server
     *
     * @param string $source Source address
     * @param string $destination Destination address
     * @param string $message Message body
     * @param string $optional Optional parameters
     * @param int $esmClass ESM class
     * @param int $dataCoding Data coding scheme
     *
     * @return PDUResponse
     */
    protected function submitSm(
        string $source,
        string $destination,
        string $message,
        string $optional = '',
        int $esmClass = 0,
        int $dataCoding = SMPP::DATA_CODING_DEFAULT
    ): PDUResponse {
        $submitSm = new SubmitSm();

        $data = $submitSm->build(
            new SubmitSmParams(
                $source,
                $destination,
                $message,
                SMPP::TON_ALPHANUMERIC,
                SMPP::NPI_UNKNOWN,
                SMPP::TON_INTERNATIONAL,
                SMPP::NPI_E164,
                $esmClass,
                $dataCoding,
                $optional
            )
        );

        return $this->sendCommand(SMPP::SUBMIT_SM, $data);
    }

    /**
     * Method sendCommand
     * Sends a command PDU and waits for the corresponding response
     *
     * @param int $commandId Command ID of the PDU
     * @param string $data Binary body of the PDU
     *
     * @return PDUResponse
     *
     * @throws SmppException
     */
    protected function sendCommand(int $commandId, string $data): PDUResponse
    {
        $sequence = $this->sequence->next();
        $pdu = new PDU($this->trace);

        $packet = $pdu->build($commandId, $data, $sequence);
        $this->socket->send($packet);

        $start = time();
        $timeout = $this->socket->getTimeout();

        while (true) {
            if ((time() - $start) > $timeout) {
                throw new SmppException('Timeout waiting for SMPP response');
            }

            $raw = $this->socket->receive();

            if ($raw === false || $raw === '') {
                throw new SmppException('Socket closed or empty response');
            }

            $responses = $pdu->feed($raw);

            foreach ($responses as $response) {
                if ($response->sequenceNumber === $sequence->value()) {
                    return $response;
                }
            }
        }
    }

    /**
     * Method resetState
     * Resets the internal state after sending a message
     *
     * @return void
     */
    private function resetState(): void
    {
        $this->currentSender = null;
        $this->currentRecipient = null;
        $this->messageText = null;
        $this->flash = false;
        $this->utf8 = false;
    }

    /**
     * Method enableTrace
     * Enables tracing of SMPP operations
     *
     * @return void
     */
    public function enableTrace(): void
    {
        $this->trace->enableTrace();
    }

    /**
     * Method disableTrace
     * Disables tracing of SMPP operations
     *
     * @return void
     */
    public function disableTrace(): void
    {
        $this->trace->disableTrace();
    }

    /**
     * Method getTraceMessages
     * Retrieves the trace messages
     *
     * @return array
     */
    public function getTraceMessages(): array
    {
        return $this->trace->getMessages();
    }
}
