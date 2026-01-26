<?php

namespace Larc\SMPPClient\Interfaces\Encoding;

interface EncodingStrategy
{
    public function encode(string $message): string;

    public function singleLimit(): int;

    public function segmentLimit(): int;

    public function esmClass(): int;

    public function dataCoding(): int;
}
