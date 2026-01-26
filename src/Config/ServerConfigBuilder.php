<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Config;

use Larc\SMPPClient\SMPP;

/**
 * ServerConfigBuilder
 * A builder class for constructing ServerConfig objects with a fluent interface.
 * @package Larc\SMPPClient\Config
 */
final class ServerConfigBuilder
{
    private string $host = 'localhost';
    private int $port = 2775;
    private ?string $systemId = null;
    private ?string $password = null;
    private ?string $systemType = null;
    private int $interfaceVersion = 0x34;
    private int $ton = 0;
    private int $npi = 0;
    private ?string $addressRange = null;
    private int $bindType = SMPP::BIND_TRANSCEIVER;

    private function __construct(int $bindType = SMPP::BIND_TRANSCEIVER)
    {
        $this->bindType = $bindType;
    }

    /**
     * Method transceiver
     *
     * @return self
     */
    public static function transceiver(): self
    {
        return new self(SMPP::BIND_TRANSCEIVER);
    }

    /**
     * Method transmitter
     *
     * @return self
     */
    public static function transmitter(): self
    {
        return new self(SMPP::BIND_TRANSMITTER);
    }

    /**
     * Method receiver
     *
     * @return self
     */
    public static function receiver(): self
    {
        return new self(SMPP::BIND_RECEIVER);
    }

    /**
     * Method withHost
     *
     * @param string $host hostname or IP address of the SMPP server
     *
     * @return self
     */
    public function withHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Method withPort
     *
     * @param int $port port number of the SMPP server
     *
     * @return self
     */
    public function withPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Method withCredentials
     *
     * @param string $systemId system identifier
     * @param string $password password for authentication
     *
     * @return self
     */
    public function withCredentials(string $systemId, string $password): self
    {
        $this->systemId = $systemId;
        $this->password = $password;
        return $this;
    }

    /**
     * Method withSystemType
     *
     * @param string $systemType system type
     *
     * @return self
     */
    public function withSystemType(string $systemType): self
    {
        $this->systemType = $systemType;
        return $this;
    }

    /**
     * Method withInterfaceVersion
     *
     * @param int $version interface version
     *
     * @return self
     */
    public function withInterfaceVersion(int $version): self
    {
        $this->interfaceVersion = $version;
        return $this;
    }

    /**
     * Method withton
     *
     * @param int $ton type of number
     *
     * @return self
     */
    public function withTon(int $ton): self
    {
        $this->ton = $ton;
        return $this;
    }

    /**
     * Method withNpi
     *
     * @param int $npi numbering plan indicator
     *
     * @return self
     */
    public function withNpi(int $npi): self
    {
        $this->npi = $npi;
        return $this;
    }

    /**
     * Method withAddressRange
     *
     * @param string|null $range address range
     *
     * @return self
     */
    public function withAddressRange(?string $range): self
    {
        $this->addressRange = $range;
        return $this;
    }

    /**
     * Method build
     *
     * @return ServerConfig
     */
    public function build(): ServerConfig
    {
        return new ServerConfig([
            'host' => $this->host,
            'port' => $this->port,
            'systemId' => $this->systemId,
            'password' => $this->password,
            'systemType' => $this->systemType,
            'interfaceVersion' => $this->interfaceVersion,
            'ton' => $this->ton,
            'npi' => $this->npi,
            'addressRange' => $this->addressRange,
            'bindType' => $this->bindType
        ]);
    }
}
