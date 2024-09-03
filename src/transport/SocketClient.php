<?php

declare(strict_types=1);

namespace Larc\SMPPClient\transport;

use Larc\SMPPClient\debugger\TraceLogger;
use Larc\SMPPClient\exceptions\SocketException;
use Larc\SMPPClient\interfaces\ConnectionInterface;

/**
 * Class SocketClient
 * Implementations of the ConnectionInterface interface for socket connections.
 *
 * @package Larc\SMPPClient\transport
 */
class SocketClient implements ConnectionInterface
{
    private $host;
    private $port;
    private $timeout;
    private $socket;
    private $trace;

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
        $this->trace = new TraceLogger($trace);
    }
    
    /**
     * Method connect
     * Conecta al servidor SMPP
     *
     * @return Resource Socket
     */
    public function connect()
    {
        try {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            if ($this->socket === false) {
                throw new SocketException("Unable to establish socket connection: $errstr", $errno);
            }

            if (function_exists('stream_set_timeout')) {
                stream_set_timeout($this->socket, $this->timeout);
            }

            $this->trace->write('>>> Connected Socket');

            return $this->socket;

        } catch (\Throwable $th) {
            throw new SocketException("An error occurred while opening the socket: " . $th->getMessage(), $th->getCode(), $th);
        }
    }
    
    /**
     * Method send
     * Envia un PDU al servidor SMPP
     *
     * @param string $pdu PDU a enviar
     *
     * @return void
     */
    public function send(string $pdu): void
    {
        $writed = @fwrite($this->socket, $pdu);

        if ($writed === false) {
            $this->trace->write('--- PDU send error');
            throw new SocketException('Failed to send PDU.');
        }

        $this->trace->write('>>> PDU Sent');
    }

    /**
     * Method receive
     * Recibe un PDU del servidor SMPP
     *
     * @param int $length Longitud del PDU a recibir
     *
     * @return string
     */
    public function receive(int $length)
    {
        $readed = @fread($this->socket, $length);

        if ($readed === false) {
            $this->trace->write('--- PDU receive error');
            throw new SocketException('Failed to receive PDU.');
        }

        $this->trace->write('<<< PDU received');

        return $readed;
    }
    
    /**
     * Method disconnect
     * Desconecta del servidor SMPP
     *
     * @return bool
     */
    public function disconnect(): bool
    {
        if ($this->socket !== null) {
            if ($this->socket) {
                @fclose($this->socket);
                $this->socket = null;
            }

            $this->trace->write('>>> Disconnected Socket');

            return true;
        }

        $this->trace->write('--- Socket was already disconnected!');

        return false;
    }

    /**
     * Method reconnect
     * Reconecta al servidor SMPP
     *
     * @return void
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }
}
