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
    private int $bindType = SMPP::BIND_TRANSCEIVER;
    private ?string $systemType = 'SMPP';
    private int $interfaceVersion = SMPP::SMPP_3_4;
    private ?string $addressRange = null;
    private int $addrTon = SMPP::TON_INTERNATIONAL;
    private int $addrNpi = SMPP::NPI_E164;
    private int $timeout = 5;

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
     * Method withAddrTon
     *
     * @param int $ton address TON
     *
     * @return self
     */
    public function withAddrTon(int $ton): self
    {
        $this->addrTon = $ton;
        return $this;
    }

    /**
     * Method withAddrNpi
     *
     * @param int $npi address NPI
     *
     * @return self
     */
    public function withAddrNpi(int $npi): self
    {
        $this->addrNpi = $npi;
        return $this;
    }

    /**
     * Method withTimeout
     *
     * @param int $timeout timeout in seconds
     *
     * @return self
     */
    public function withTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
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
            'bindType' => $this->bindType,
            'systemType' => $this->systemType,
            'interfaceVersion' => $this->interfaceVersion,
            'addressRange' => $this->addressRange,
            'addrTon' => $this->addrTon,
            'addrNpi' => $this->addrNpi,
            'timeout' => $this->timeout,
        ]);
    }
}
