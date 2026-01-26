<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Interfaces;

use Larc\SMPPClient\Exception\SocketException;

/**
 * Defines a low-level SMPP connection.
 */
interface ConnectionInterface
{
    /**
     * Establishes the connection.
     *
     * @throws SocketException
     */
    public function connect(): void;

    /**
     * Closes the connection if open.
     */
    public function disconnect(): void;

    /**
     * Sends raw PDU data.
     *
     * @param string $pdu Binary PDU
     * @throws SocketException
     */
    public function send(string $pdu): void;

    /**
     * Receives raw data from the connection.
     *
     * @return string Binary data
     * @throws SocketException
     */
    public function receive(): string;

    /**
     * Re-establishes the connection.
     *
     * @throws SocketException
     */
    public function reconnect(): void;
}
