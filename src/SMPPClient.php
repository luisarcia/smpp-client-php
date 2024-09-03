<?php

declare(strict_types=1);

namespace Larc\SMPPClient;

use Larc\SMPPClient\{SMPP, PDU};
use Larc\SMPPClient\debugger\TraceLogger;

/**
 * Class SMPPClient
 * SMPP client
 *
 * @package Larc\SMPPClient
 */
class SMPPClient
{
    private $socket;
    private $ton;
    private $npi;
    private $dataCoding;
    private $sequenceNumber = 0;
    private $trace;

    public function __construct($socket, $ton, $npi, $dataCoding = SMPP::DATA_CODING_DEFAULT, bool $trace = false)
    {
        $this->socket = $socket;
        $this->ton = $ton;
        $this->npi = $npi;
        $this->dataCoding = $dataCoding;

        $this->trace = new TraceLogger($trace);
    }

    /**
     * Method login
     * Login to SMPP server
     *
     * @param $commandId $commandId Command ID for the PDU
     * @param string $systemId User ID
     * @param string $password Password
     *
     * @return bool
     */
    public function login($commandId, string $systemId, string $password): bool
    {
        $data  = sprintf("%s\0%s\0", $systemId, $password); // system_id, password
        $data .= sprintf("%s\0%c", 'SMPP', SMPP::SMPP_3_4);  // system_type, interface_version
        $data .= sprintf("%c%c%s\0", SMPP::TON_INTERNATIONAL, SMPP::NPI_E164, ''); // addr_ton, addr_npi, address_range

        $response = $this->sendCommand($commandId, $data);

        if ($response) {
            $this->trace->write('>>> Bind done!');
            return true;
        } else {
            $this->trace->write('--- Binding error!');
            return false;
        }
    }

    /**
     * Method submitSm
     * Submit short message
     *
     * @param $source $source Sender
     * @param $destination $destination Recipient
     * @param string $message Message to send
     * @param $optional $optional Optional parameters
     *
     * @return bool
     */
    protected function submitSm($source, $destination, string $message, $optional = ''): bool
    {
        $data  = sprintf("%s\0", ""); // service_type
        $data .= sprintf("%c%c%s\0", $this->ton, $this->npi, $source); // source_addr_ton, source_addr_npi, source_addr
        $data .= sprintf("%c%c%s\0", 1, 1, $destination); // dest_addr_ton, dest_addr_npi, destination_addr
        $data .= sprintf("%c%c%c", 0, 0, 1); // esm_class, protocol_id, priority_flag
        $data .= sprintf("%s\0%s\0", "", ""); // schedule_delivery_time, validity_period
        $data .= sprintf("%c%c", 0, 0); // registered_delivery, replace_if_present_flag
        $data .= sprintf("%c%c", $this->dataCoding, 0); // data_coding, sm_default_msg_id
        $data .= sprintf("%c%s", strlen($message), $message); // sm_length, short_message
        $data .= $optional;

        return $this->sendCommand(SMPP::SUBMIT_SM, $data);
    }

    /**
     * Method logout
     * Logout from SMPP server. Unbind.
     *
     * @return bool
     */
    public function logout(): bool
    {
        $response = $this->sendCommand(SMPP::UNBIND, '');

        if ($response) {
            $this->trace->write('>>> Unbind done!');
            return true;
        } else {
            $this->trace->write('--- Unbinding error!');
            return false;
        }
    }

    /**
     * Method sendCommand
     * Send command to SMPP server
     *
     * @param mixed $commandId Command ID for the PDU
     * @param mixed $data Data to be included in the PDU body
     *
     * @return bool
     */
    protected function sendCommand($commandId, $data): bool
    {
        $sequenceNumber = $this->sequenceNumber += 1;

        $pdu = new PDU($this->trace->getState());
        $pduPacket = $pdu->buildPduPacket($commandId, $data, $sequenceNumber);

        if (!$pduPacket) {
            return false;
        }

        $this->socket->send($pduPacket);

        $pduHeader = $this->socket->receive(12);

        if (!$pduHeader) {
            $this->trace->write('--- Send PDU: Connection closed!. Dont read socket.');
            return false;
        }

        $response = $pdu->read($pduHeader);

        return $response->commandStatus == SMPP::ESME_ROK;
    }

    /**
     * Method sendSMS
     * Send SMS
     *
     * @param string $source Sender
     * @param string $destination Recipient
     * @param string $message Message to send
     * @param bool $utf Support UTF-8
     * @param bool $flash Send as flash message
     *
     * @return void
     */
    public function sendSMS(string $source, string $destination, string $message, bool $flash = false, bool $utf = false): void
    {
        // Set data coding according to parameters
        $this->dataCoding = $utf ? SMPP::DATA_CODING_UCS2 : SMPP::DATA_CODING_DEFAULT;
        
        if ($flash) {
            $this->dataCoding |= 0x10; // Compose the data coding with the flash flag
        }

        // Calculate message size with optional UTF-16 encoding overhead (20 bytes) if needed
        $messageSize = strlen($message);
        if ($utf) {
            $messageSize += 20;
        }

        // Send single-part SMS. If the message size is less than or equal to 160 characters, send it as is.
        if ($messageSize <= 160) {
            $this->submitSm($source, $destination, $message);
            return;
        }

        // Send multi-part SMS. If the message size is greater than 160 characters, split it into parts. Each part will have a maximum of 153 characters.
        $sarMsgRefNum = rand(1, 255);
        $segmentSize = 130;
        $totalSegments = ceil(strlen($message) / $segmentSize);

        for ($segmentSeqNum = 1; $segmentSeqNum <= $totalSegments; $segmentSeqNum++) {
            // Extract the next part of the message
            $part = substr($message, 0, $segmentSize);
            $message = substr($message, $segmentSize);

            // Prepare the optional parameters for the segment
            $optional = pack('nnn', 0x020C, 2, $sarMsgRefNum);
            $optional .= pack('nnc', 0x020E, 1, $totalSegments);
            $optional .= pack('nnc', 0x020F, 1, $segmentSeqNum);

            // Send the message segment
            $this->submitSm($source, $destination, $part, $optional);
        }
    }
}
