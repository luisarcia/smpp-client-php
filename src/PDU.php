<?php

declare(strict_types=1);

namespace Larc\SMPPClient;

use Larc\SMPPClient\SMPP;
use Larc\SMPPClient\debugger\TraceLogger;

/**
 * Class PDU
 * Builds and reads PDU packets
 *
 * @package Larc\SMPPClient
 */
final class PDU
{
    private $commandStatus = 0;
    private $trace;

    /**
     * @param bool $trace Enable tracing
     */
    public function __construct(bool $trace = false)
    {
        $this->trace = new TraceLogger($trace);
    }

    /**
     * Builds a PDU packet with header and body
     *
     * @param int $commandId Command ID for the PDU
     * @param string $data Data to be included in the PDU body
     * @param int $sequenceNumber Sequence number for the PDU
     *
     * @return string|null The constructed PDU packet or null on failure
     */
    public function buildPduPacket(int $commandId, string $data, int $sequenceNumber): ?string
    {
        // PDU Header length is 16 bytes
        $length = strlen($data) + 16;
        $pduHeader = pack('NNNN', $length, $commandId, $this->commandStatus, $sequenceNumber);
        $pduBody = $data;

        $pduPacket = $pduHeader . $pduBody;

        $this->trace->write('--- Constructed PDU packet: ' . bin2hex($pduPacket));

        return $pduPacket;
    }

    /**
     * Reads and parses the PDU header
     * 
     * @param string $pduHeader The PDU header
     * 
     * @return object The parsed PDU response
     */
    public function read(string $pduHeader): object
    {
        // Validates that the PDU header length is at least 16 bytes long (4 integers)
        $headerLength = strlen($pduHeader);

        if ($headerLength < 16) {
            $this->trace->write('--- Error: PDU header too short. Length: ' . $headerLength);

            return (object)[
                'commandId' => 0,
                'commandStatus' => 'Error',
                'sequenceNumber' => 0,
                'body' => ''
            ];
        }

        // parse the PDU header
        $headerData = unpack('Nlength/Ncommand_id/Ncommand_status/Nsequence_number', substr($pduHeader, 0, 16));

        if ($headerData === false) {
            $this->trace->write('--- Error unpacking PDU header.');
            return (object)[
                'commandId' => 0,
                'commandStatus' => 'Error',
                'sequenceNumber' => 0,
                'body' => ''
            ];
        }

        $length = $headerData['length'];
        $commandId = $headerData['command_id'];
        $commandStatus = $headerData['command_status'];
        $sequenceNumber = $headerData['sequence_number'];

        // Read the PDU body from the remaining part of the PDU packet
        $pduBody = '';

        if ($length > 16) {
            $pduBody = substr($pduHeader, 16, $length - 16);
        }

        $this->trace->write("<<< PDU response [command_id: {$commandId}, status: {$commandStatus}, sequence_number: {$sequenceNumber}, body: " . bin2hex($pduBody) . "]");

        return (object)[
            'commandId' => $commandId,
            'commandStatus' => SMPP::getStatusMessage($commandStatus),
            'sequenceNumber' => $sequenceNumber,
            'body' => $pduBody
        ];
    }
}
