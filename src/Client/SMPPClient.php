<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Client;

use Larc\SMPPClient\Config\ServerConfig;
use Larc\SMPPClient\SMPP;
use Larc\SMPPClient\PDU\PDU;
use Larc\SMPPClient\Interfaces\ConnectionInterface;
use Larc\SMPPClient\debugger\TraceLogger;
use Larc\SMPPClient\Protocol\Encoding\Ucs2Encoding;
use Larc\SMPPClient\Protocol\Encoding\Gsm7Encoding;
use Larc\SMPPClient\Protocol\Sequence\SequenceGenerator;
use Larc\SMPPClient\Exception\ProtocolException;
use Larc\SMPPClient\Exception\SmppException;
use Larc\SMPPClient\PDU\PDUResponse;
use Larc\SMPPClient\Protocol\Message\MessageSplitter;
use Larc\SMPPClient\Protocol\SubmitSm;
use Larc\SMPPClient\transport\SocketClient;

final class SMPPClient
{
    private ConnectionInterface $socket;
    private string $systemId;
    private string $password;
    private int $ton;
    private int $npi;
    private int $commandId;
    private SequenceGenerator $sequence;
    private ?TraceLogger $trace = null;

    // Por mensaje
    private ?string $currentSender = null;
    private ?string $currentRecipient = null;
    private ?string $messageText = null;
    private bool $flash = false;
    private bool $utf8 = false;

    private ?string $lastMessageId = null;

    public function __construct(ServerConfig $config)
    {
        $this->socket = new SocketClient($config->host(), $config->port(), 5, false);
        $this->systemId = $config->systemId();
        $this->password = $config->password();
        $this->ton = $config->ton();
        $this->npi = $config->npi();
        $this->commandId = $config->bindType();
        $this->sequence = new SequenceGenerator();

        $this->trace = new TraceLogger();
    }

    public function login(): bool
    {
        $this->socket->connect();

        $data  = sprintf("%s\0%s\0", $this->systemId, $this->password);
        $data .= sprintf("%s\0%c", 'SMPP', SMPP::SMPP_3_4);
        $data .= sprintf("%c%c%s\0", SMPP::TON_INTERNATIONAL, SMPP::NPI_E164, '');

        $response = $this->sendCommand($this->commandId, $data);

        if ($response && $response->commandStatus === SMPP::ESME_ROK) {
            $this->trace?->write('>>> Bind done!');
            return true;
        }

        $this->trace?->write('--- Binding error!');
        return false;
    }

    public function logout(): void
    {
        try {
            $response = $this->sendCommand(SMPP::UNBIND, '');
            if ($response->commandStatus === SMPP::ESME_ROK) {
                $this->trace?->write('>>> Unbind done');
            } else {
                $this->trace?->write('--- Unbind returned non-OK, SMPPSim probablemente cerró la sesión');
            }
        } catch (\Exception $e) {
            // Atrapa cualquier excepción (socket cerrado, timeout, etc.)
            $this->trace?->write('--- Unbind failed: ' . $e->getMessage());
        } finally {
            // Siempre desconectamos el socket
            $this->socket->disconnect();
        }
    }

    public function from(string $sender): self
    {
        $this->currentSender = $sender;
        return $this;
    }

    public function to(string $recipient): self
    {
        $this->currentRecipient = $recipient;
        return $this;
    }

    public function message(string $text): self
    {
        $this->messageText = $text;
        return $this;
    }

    public function asFlash(bool $flash = true): self
    {
        $this->flash = $flash;
        return $this;
    }

    public function asUtf8(bool $utf = true): self
    {
        $this->utf8 = $utf;
        return $this;
    }

    public function send(): void
    {
        if (!$this->currentSender || !$this->currentRecipient || !$this->messageText) {
            throw new SmppException('Sender, Recipient and Message must be set before sending.');
        }

        // Codificación
        $encoding = $this->utf8 ? new Ucs2Encoding() : new Gsm7Encoding();

        // DataCoding por mensaje
        $dataCoding = $encoding->dataCoding();
        if ($this->flash) {
            $dataCoding |= 0x10; // bit flash
        }

        // Codificar mensaje
        $payload = $encoding->encode($this->messageText);

        $splitter = new MessageSplitter();
        $segments = $splitter->split($payload, $encoding);

        $lastMessageId = null;
        foreach ($segments as $segment) {
            $response = $this->submitSm(
                $this->currentSender,
                $this->currentRecipient,
                $segment->body(),
                $segment->optionalParams(),
                $segment->esmClass(),
                $dataCoding
            );
        }

        $this->lastMessageId = $response->message_id ?? null;

        // Limpiar estado para permitir otro mensaje
        $this->messageText = null;
        $this->currentRecipient = null;
        $this->flash = false;
        $this->utf8 = false;
    }

    public function getLastMessageId(): ?string
    {
        return $this->lastMessageId;
    }

    // --- Internals ---
    protected function submitSm(string $source, string $destination, string $message, string $optional = '', int $esmClass = 0, int $dataCoding = SMPP::DATA_CODING_DEFAULT): PDUResponse
    {
        $submitSm = new SubmitSm($this->ton, $this->npi);

        $data = $submitSm->build(
            $source,
            $destination,
            $message,
            $dataCoding,
            $optional,
            $esmClass
        );

        return $this->sendCommand(SMPP::SUBMIT_SM, $data);
    }

    protected function sendCommand(int $commandId, string $data): PDUResponse
    {
        $sequenceNumber = $this->sequence->next();
        $pdu = new PDU($this->trace?->getState());
        $pduPacket = $pdu->build($commandId, $data, $sequenceNumber);

        if (!$pduPacket) {
            throw new SmppException('Failed to build PDU');
        }

        $this->socket->send($pduPacket);
        $rawPdu = $this->socket->receive();
        return $pdu->read($rawPdu);
    }

    public function enableTrace(): void
    {
        $this->trace->enableTrace();
    }

    public function disableTrace(): void
    {
        $this->trace->disableTrace();
    }

    public function getTraceMessages(): array
    {
        return $this->trace->getMessages();
    }
}
