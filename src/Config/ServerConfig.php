<?php

namespace Larc\SMPPClient\Config;

use Larc\SMPPClient\Exception\InvalidConfigException;
use Larc\SMPPClient\SMPP;

/**
 * ServerConfig
 * A class representing the configuration for connecting to an SMPP server.
 * @package Larc\SMPPClient\Config
 */
final class ServerConfig
{
    private string $host;
    private int $port;
    private string $systemId;
    private string $password;
    private int $bindType;
    private ?string $systemType;
    private int $interfaceVersion;
    private ?string $addressRange;
    private int $addrTon;
    private int $addrNpi;
    private int $timeout;

    public function __construct(array $config)
    {
        $this->validateConfig($config);

        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->systemId = $config['systemId'];
        $this->password = $config['password'];
        $this->bindType = $config['bindType'];
        $this->systemType = $config['systemType'];
        $this->interfaceVersion = $config['interfaceVersion'];
        $this->addressRange = $config['addressRange'];
        $this->addrTon = $config['addrTon'] ?? \Larc\SMPPClient\SMPP::TON_INTERNATIONAL;
        $this->addrNpi = $config['addrNpi'] ?? \Larc\SMPPClient\SMPP::NPI_E164;
        $this->timeout = $config['timeout'] ?? 5;
    }
    public function addrTon(): int
    {
        return $this->addrTon;
    }

    public function addrNpi(): int
    {
        return $this->addrNpi;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function systemId(): string
    {
        return $this->systemId;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function bindType(): int
    {
        return $this->bindType;
    }

    public function systemType(): ?string
    {
        return $this->systemType;
    }

    public function interfaceVersion(): int
    {
        return $this->interfaceVersion;
    }

    public function addressRange(): ?string
    {
        return $this->addressRange;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    private function validateConfig(array $config): void
    {
        if (empty($config['host'])) {
            throw new InvalidConfigException('Host is required in ServerConfig');
        }

        if ($config['port'] <= 0 || $config['port'] > 65535) {
            throw new InvalidConfigException('Port must be between 1 and 65535 in ServerConfig');
        }

        if (empty($config['systemId'])) {
            throw new InvalidConfigException('System ID is required in ServerConfig');
        }

        if (empty($config['password'])) {
            throw new InvalidConfigException('Password is required in ServerConfig');
        }

        if (!isset($config['bindType']) || !in_array($config['bindType'], [
            SMPP::BIND_TRANSMITTER,
            SMPP::BIND_RECEIVER,
            SMPP::BIND_TRANSCEIVER
        ], true)) {
            throw new InvalidConfigException('Invalid bind type in ServerConfig');
        }

        if (!isset($config['interfaceVersion']) || !in_array($config['interfaceVersion'], [
            SMPP::SMPP_3_4
        ], true)) {
            throw new InvalidConfigException('Invalid interface version in ServerConfig');
        }
    }
}
