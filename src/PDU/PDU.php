<?php

declare(strict_types=1);

namespace Larc\SMPPClient\PDU;

use Larc\SMPPClient\SMPP;
use Larc\SMPPClient\debugger\TraceLogger;
use Larc\SMPPClient\Protocol\Sequence\SequenceNumber;

/**
 * Class PDU
 * Builds and reads PDU packets for SMPP communication.
 * @package Larc\SMPPClient\PDU
 */
final class PDU
{
    private const HEADER_LENGTH = 16;
    private $trace;

    /**
     * @param bool $trace Enable tracing
     */
    public function __construct(bool $trace = false)
    {
        $this->trace = new TraceLogger($trace);
    }

    /**
     * Build Method
     * Builds a PDU packet with header and body
     *
     * @param int $commandId Command ID for the PDU
     * @param string $body PDU body data
     * @param SequenceNumber $sequenceNumber Sequence number for the PDU
     *
     * @return string The constructed PDU packet
     */
    public function build(int $commandId, string $body, SequenceNumber $sequenceNumber): string
    {
        // PDU Header length is 16 bytes
        $length = self::HEADER_LENGTH + strlen($body);
        $header = pack('NNNN', $length, $commandId, SMPP::ESME_ROK, $sequenceNumber->value());
        $this->trace->write(">>> Building PDU [commandId: {$commandId}, status: " . SMPP::ESME_ROK . ", sequenceNumber: {$sequenceNumber->value()}, body: " . bin2hex($body) . "]");

        // Return the complete PDU packet
        return $header . $body;
    }

    /**
     * Read Method
     * Reads and parses the PDU header
     * @param string $header The PDU header to be read
     * @return PDUResponse The parsed PDU response
     */
    public function read(string $pdu): PDUResponse
    {
        // Validates that the PDU header length is at least 16 bytes long (4 integers)
        $headerLength = strlen($pdu);

        if ($headerLength < self::HEADER_LENGTH) {
            $this->trace->write('--- Error: PDU header too short. Length: ' . $headerLength);

            return new PDUResponse(0, SMPP::ESME_RUNKNOWNERR, 0, '', []);
        }

        // parse the PDU header
        $headerData = unpack('Nlength/NcommandId/NcommandStatus/NsequenceNumber', substr($pdu, 0, self::HEADER_LENGTH));

        if ($headerData === false) {
            $this->trace->write('--- Error unpacking PDU header.');

            return new PDUResponse(0, SMPP::ESME_RUNKNOWNERR, 0, '', []);
        }

        $length = $headerData['length'];
        $commandId = $headerData['commandId'];
        $commandStatus = $headerData['commandStatus'];
        $sequenceNumber = $headerData['sequenceNumber'];

        // Read the PDU body from the remaining part of the PDU packet
        $body = '';

        if ($length > self::HEADER_LENGTH) {
            $body = substr($pdu, self::HEADER_LENGTH, $length - self::HEADER_LENGTH);
        }

        $this->trace->write("<<< PDU response [commandId: {$commandId}, status: {$commandStatus}, sequenceNumber: {$sequenceNumber}, body: " . bin2hex($body) . "]");

        $bodyData = $this->parseBody($commandId, $body);

        return new PDUResponse(
            $commandId,
            $commandStatus,
            $sequenceNumber,
            $body,
            $bodyData
        );
    }
    
    /**
     * Method parseBody
     * Parses the PDU body based on the command ID
     *
     * @param int $commandId Command ID of the PDU
     * @param string $body PDU body data
     *
     * @return array
     */
    private function parseBody(int $commandId, string $body): array
    {
        $data = [];

        switch ($commandId) {
            case SMPP::SUBMIT_SM_RESP:
                // message_id es un string C (NULL-terminated)
                $data['message_id'] = rtrim($body, "\0");
                break;

            // Otros PDU que tengan body
            case SMPP::DELIVER_SM:
                // Aquí podrías parsear source_addr, short_message, etc.
                break;
            default:
                // PDU sin body o no reconocido
                break;
        }

        return $data;
    }
}
