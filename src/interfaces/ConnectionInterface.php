<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Interfaces;

use Larc\SMPPClient\Exception\ConnectionException;

/**
 * ConnectionInterface
 * Interface for SMPP connection handling.
 * @package Larc\SMPPClient\Interfaces
 */
interface ConnectionInterface
{
    /**
     * Establishes the connection.
     *
     * @throws ConnectionException
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
     * @throws ConnectionException
     */
    public function send(string $pdu): void;

    /**
     * Receives raw data from the connection.
     *
     * @return string Binary data
     * @throws ConnectionException
     */
    public function receive(): string;

    /**
     * Re-establishes the connection.
     *
     * @throws ConnectionException
     */
    public function reconnect(): void;

    /**
     * Checks if the connection is currently established.
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Gets the connection timeout in seconds.
     *
     * @return int
     */
    public function getTimeout(): int;
}
