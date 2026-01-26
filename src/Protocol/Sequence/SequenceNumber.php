<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Protocol\Sequence;

final class SequenceNumber
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException(
                'Sequence number must be greater than zero'
            );
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
