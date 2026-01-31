<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol;

/**
 * Clase de parámetros para SubmitSm
 */
class SubmitSmParams
{
    public function __construct(
        public string $source,
        public string $destination,
        public string $message,
        public int $sourceAddrTon,
        public int $sourceAddrNpi,
        public int $destAddrTon,
        public int $destAddrNpi,
        public int $esmClass,
        public int $dataCoding,
        public string $optional = ''
    ) {}
}
