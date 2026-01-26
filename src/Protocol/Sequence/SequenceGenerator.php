<?php

namespace Larc\SMPPClient\Protocol\Sequence;

final class SequenceGenerator
{
    private int $current = 0;

    public function next(): SequenceNumber
    {
        if ($this->current >= 0x7FFFFFFF) {
            $this->current = 1;
        } else {
            $this->current++;
        }

        return new SequenceNumber($this->current);
    }

    public function reset(): void
    {
        $this->current = 0;
    }
}
