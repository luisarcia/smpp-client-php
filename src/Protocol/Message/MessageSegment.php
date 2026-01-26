<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Message;

final class MessageSegment
{
    public function __construct(
        private string $body,
        private string $optional,
        private int $esmClass
    ) {}

    public function body(): string
    {
        return $this->body;
    }

    public function optionalParams(): string
    {
        return $this->optional;
    }

    public function esmClass(): int
    {
        return $this->esmClass;
    }
}
