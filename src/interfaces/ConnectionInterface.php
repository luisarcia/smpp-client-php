<?php

declare(strict_types = 1);

namespace Larc\SMPPClient\interfaces;

/**
 * ConnectionInterface
 */
interface ConnectionInterface
{
    public function connect();
    public function disconnect();
    public function send(string $pdu);
    public function receive(int $length);
}
