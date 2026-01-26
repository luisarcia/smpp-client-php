<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Response;

use Larc\SMPPClient\SMPP;

/**
 * Class SubmitSmResponse
 * Represents a response to a SubmitSm request.
 *
 * @package Larc\SMPPClient\Protocol\Response
 */
final class SubmitSmResponse
{
    public function __construct(
        public readonly int $commandStatus,
        public readonly ?string $messageId
    ) {}

        
    /**
     * Method isOk
     * Checks if the command status indicates a successful operation.
     *
     * @return bool True if the command status is ESME_ROK, false otherwise.
     */
    public function isOk(): bool
    {
        return $this->commandStatus === SMPP::ESME_ROK;
    }
}
