<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Transport;

use Larc\SMPPClient\debugger\TraceLogger;
use Larc\SMPPClient\Exception\SocketException;
use Larc\SMPPClient\Interfaces\ConnectionInterface;

/**
 * Class SocketClient
 * Implementations of the ConnectionInterface interface for socket connections.
 *
 * @package Larc\SMPPClient\transport
 */
class SocketClient implements ConnectionInterface
{
    private string $host;
    private int $port;
    private int $timeout;
    private $socket = null;
    private ?TraceLogger $trace;

    /**
     * Method __construct
     *
     * @param string $host Dirección IP o DNS del servidor SMPP
     * @param int $port Puerto del servidor SMPP
     * @param int $timeout Tiempo de espera en segundos
     * @param bool $trace Habilitar traza
     *
     * @return void
     */
    public function __construct(string $host, int $port, int $timeout = 5, bool $trace = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->trace = $trace ? new TraceLogger(true) : null;
    }

    /**
     * Method connect
     * Establishes a socket connection to the SMPP server
     *
     * @return void
     */
    public function connect(): void
    {
        if ($this->socket !== null) {
            return; // Already connected
        }

        $socket = null;

        set_error_handler(function ($message) {
            throw new SocketException("PHP socket error: $message");
        });

        try {
            $socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            if ($socket === false) {
                throw new SocketException(
                    "Unable to establish socket connection: $errstr",
                    $errno
                );
            }

            stream_set_timeout($socket, $this->timeout);
        } catch (\Throwable $e) {
            throw new SocketException(
                "Socket connection failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        } finally {
            restore_error_handler();
        }

        $this->socket = $socket;
        $this->trace?->write('>>> Connected Socket');
    }

    /**
     * Method send
     * Sends a PDU to the SMPP server
     *
     * @param string $pdu The PDU to be sent
     *
     * @return void
     */
    public function send(string $pdu): void
    {
        if ($this->socket === null) {
            throw new SocketException('Socket is not connected.');
        }

        $length = strlen($pdu);
        $written = 0;

        while ($written < $length) {
            $result = fwrite($this->socket, substr($pdu, $written));

            if ($result === false) {
                $this->trace?->write('--- PDU send error');
                throw new SocketException('Failed to send PDU.');
            }

            $written += $result;
        }

        $this->trace?->write('>>> PDU Sent (' . $length . ' bytes)');
    }

    /**
     * Method receive
     * Receives a PDU from the SMPP server
     *
     * @param int $length The length of the PDU to receive
     *
     * @return string
     */
public function receive(): string
{
    if ($this->socket === null) {
        throw new SocketException('Socket is not connected.');
    }

    $data = '';

    // leer lo que haya disponible (TCP stream)
    while (true) {

        if (feof($this->socket)) {
            throw new SocketException('Socket closed by remote host.');
        }

        $chunk = fread($this->socket, 8192);

        if ($chunk === false) {
            throw new SocketException('Failed to receive data from socket.');
        }

        if ($chunk === '') {
            $meta = stream_get_meta_data($this->socket);

            if ($meta['timed_out']) {
                break; // no más datos por ahora
            }

            usleep(1000);
            continue;
        }

        $data .= $chunk;

        // si llegó algo, salimos (PDU::feed decide si está completo)
        // if (strlen($data) > 0) {
        //     break;
        // }

        if (strlen($data) > 1024 * 1024) {
            throw new SocketException('Too much data without valid PDU');
        }

        break;
    }

    $this->trace?->write('<<< TCP data received (' . strlen($data) . ' bytes)');

    return $data;
}


    /**
     * Method disconnect
     * Disconnects from the SMPP server
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
            $this->trace?->write('>>> Disconnected Socket');
        }
    }

    /**
     * Method reconnect
     * Reconnects to the SMPP server
     *
     * @return void
     */
    public function reconnect(): void
    {
        if ($this->socket === null) {
            $this->connect();
            return;
        }

        $this->trace?->write('>>> Reconnecting Socket');
        $this->disconnect();
        $this->connect();
    }
}
