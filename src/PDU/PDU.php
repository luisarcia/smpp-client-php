<?php

declare(strict_types=1);

namespace Larc\SMPPClient\PDU;

use Larc\SMPPClient\SMPP;
use Larc\SMPPClient\debugger\TraceLogger;
use Larc\SMPPClient\Protocol\Sequence\SequenceNumber;

/**
 * Class PDU
 * Responsible for building and parsing SMPP PDUs.
 *
 * @package Larc\SMPPClient\PDU
 */
final class PDU
{
    // PDU header length in bytes
    private const HEADER_LENGTH = 16;

    // Trace logger instance
    private TraceLogger $trace;

    private string $buffer = '';

    public function __construct(?TraceLogger $trace = null)
    {
        $this->trace = $trace ?? new TraceLogger();
    }

    /**
     * Method build
     * Builds a raw PDU binary string from command ID, body, and sequence number
     *
     * @param int $commandId Command ID of the PDU
     * @param string $body Binary body of the PDU
     * @param SequenceNumber $sequenceNumber Sequence number for the PDU
     *
     * @return string Raw binary PDU
     */
    public function build(int $commandId, string $body, SequenceNumber $sequenceNumber): string
    {
        $length = self::HEADER_LENGTH + strlen($body);

        $header = pack(
            'NNNN',
            $length,
            $commandId,
            SMPP::ESME_ROK,
            $sequenceNumber->value()
        );

        $this->trace->write(
            ">>> Building PDU [commandId: {$commandId}, status: 0, sequenceNumber: {$sequenceNumber->value()}, body: " .
                bin2hex($body) . "]"
        );

        return $header . $body;
    }

    /**
     * Method feed
     * Feeds raw data into the PDU parser and extracts complete PDUs
     *
     * @param string $data Raw binary data
     *
     * @return array Array of PDUResponse objects
     */
    public function feed(string $data): array
    {
        $this->buffer .= $data;
        $pdus = [];

        while (strlen($this->buffer) >= self::HEADER_LENGTH) {

            // leer longitud SIN consumir
            $header = substr($this->buffer, 0, self::HEADER_LENGTH);
            $unpacked = unpack('Nlength', $header);

            if (!$unpacked || $unpacked['length'] < self::HEADER_LENGTH) {
                $this->trace->write('--- Error: invalid PDU length');
                $this->buffer = '';
                break;
            }

            $length = $unpacked['length'];

            if ($length > 65536) {
                $this->trace->write('--- Error: PDU length too large');
                $this->buffer = '';
                break;
            }

            // aún no llegó el PDU completo
            if (strlen($this->buffer) < $length) {
                break;
            }

            // extraer PDU completo
            $pduBinary = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);

            $pdus[] = $this->parsePdu($pduBinary);
        }

        return $pdus;
    }

    /**
     * Method parsePdu
     * Parses a raw PDU binary string into a PDUResponse object
     *
     * @param string $pdu Binary PDU string
     *
     * @return PDUResponse
     */
    private function parsePdu(string $pdu): PDUResponse
    {
        if (strlen($pdu) < self::HEADER_LENGTH) {
            $this->trace->write('--- Error: PDU header too short');
            return new PDUResponse(0, SMPP::ESME_RUNKNOWNERR, 0, '', []);
        }

        $header = unpack(
            'Nlength/NcommandId/NcommandStatus/NsequenceNumber',
            substr($pdu, 0, self::HEADER_LENGTH)
        );

        $length = $header['length'];
        $commandId = $header['commandId'];
        $commandStatus = $header['commandStatus'];
        $sequenceNumber = $header['sequenceNumber'];

        $body = '';
        if ($length > self::HEADER_LENGTH) {
            $body = substr($pdu, self::HEADER_LENGTH, $length - self::HEADER_LENGTH);
        }

        $this->trace->write(
            "<<< PDU response [commandId: {$commandId}, status: {$commandStatus}, sequenceNumber: {$sequenceNumber}, body: " .
                bin2hex($body) . "]"
        );

        return new PDUResponse(
            $commandId,
            $commandStatus,
            $sequenceNumber,
            $body,
            $this->parseBody($commandId, $body)
        );
    }

    /**
     * Method parseBody
     * Parses the body of a PDU based on its command ID
     *
     * @param int $commandId Command ID of the PDU
     * @param string $body Binary body of the PDU
     *
     * @return array Parsed body data
     */
    private function parseBody(int $commandId, string $body): array
    {
        $data = [];

        switch ($commandId) {
            case SMPP::SUBMIT_SM_RESP:
                // Extrae solo hasta el primer byte nulo (C-string)
                $data['message_id'] = explode("\0", $body, 2)[0];
                break;
            case SMPP::DELIVER_SM_RESP:
                // No body parsing implemented for this command
                break;
            default:
                // No body parsing implemented for this command
                break;
        }

        return $data;
    }
}
