<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Encoding;

use Larc\SMPPClient\Interfaces\Encoding\EncodingStrategy;
use Larc\SMPPClient\SMPP;

final class Ucs2Encoding implements EncodingStrategy
{
    public function encode(string $message): string
    {
        return mb_convert_encoding($message, "UCS-2BE", "UTF-8");
    }

    public function singleLimit(): int
    {
        return 70;
    }

    public function segmentLimit(): int
    {
        return 67;
    }

    public function esmClass(): int
    {
        return 0x40; // UDHI
    }

    public function dataCoding(): int
    {
        return SMPP::DATA_CODING_UCS2;
    }
}
