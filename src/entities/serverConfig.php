<?php

declare(strict_types = 1);

namespace Larc\SMPPClient\entities;

use Larc\SMPPClient\interfaces\ServerConfigInterface;

/**
 * ServerConfig
 * Model of ServerConfig.
 */
class ServerConfig implements ServerConfigInterface
{
    private $host; //IP del servidor
    private $port; //Puerto
    private $systemId; //Usuario
    private $password; //Password
    private $commandId; //transmitter (TX), receiver (RX) o transceiver (TRX)
    private $ton; //TON
    private $npi; //NPI

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     *
     * @return self
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     *
     * @return self
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @param mixed $systemId
     *
     * @return self
     */
    public function setSystemId(string $systemId)
    {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     *
     * @return self
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommandId()
    {
        return $this->commandId;
    }

    /**
     * @param mixed $commandId
     *
     * @return self
     */
    public function setCommandId($commandId)
    {
        $this->commandId = $commandId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTon()
    {
        return $this->ton;
    }

    /**
     * @param mixed $ton
     *
     * @return self
     */
    public function setTon($ton)
    {
        $this->ton = $ton;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNpi()
    {
        return $this->npi;
    }

    /**
     * @param mixed $npi
     *
     * @return self
     */
    public function setNpi($npi)
    {
        $this->npi = $npi;

        return $this;
    }
}
