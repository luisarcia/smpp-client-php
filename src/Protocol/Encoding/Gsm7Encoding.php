<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Encoding;

use Larc\SMPPClient\Interfaces\Encoding\EncodingStrategy;
use Larc\SMPPClient\SMPP;

final class Gsm7Encoding implements EncodingStrategy
{
    public function encode(string $message): string
    {
        return $message;
    }

    public function singleLimit(): int
    {
        return 160;
    }

    public function segmentLimit(): int
    {
        return 153;
    }

    public function esmClass(): int
    {
        return 0x00;
    }

    public function dataCoding(): int
    {
        return SMPP::DATA_CODING_DEFAULT;
    }
}
