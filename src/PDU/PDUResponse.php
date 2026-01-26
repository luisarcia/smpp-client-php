<?php

declare(strict_types=1);

namespace Larc\SMPPClient\PDU;

/**
 * Class PDUResponse
 * Represents a parsed PDU response.
 *
 * @package Larc\SMPPClient\PDU
 */
final class PDUResponse
{
    public $commandId;
    public $commandStatus;
    public $sequenceNumber;
    public $body;
    public $bodyData;

    public function __construct(
        int $commandId,
        int $commandStatus,
        int $sequenceNumber,
        string $body,
        array $bodyData
    ) {
        $this->commandId = $commandId;
        $this->commandStatus = $commandStatus;
        $this->sequenceNumber = $sequenceNumber;
        $this->body = $body;
        $this->bodyData = $bodyData;
    }
}
