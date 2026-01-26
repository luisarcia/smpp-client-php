<?php

namespace Larc\SMPPClient\Config;

use Larc\SMPPClient\Exception\InvalidConfigException;

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
    private ?string $systemType;
    private int $interfaceVersion;
    private int $ton;
    private int $npi;
    private ?string $addressRange;
    private int $bindType;

    public function __construct(array $config)
    {
        $this->validateConfig($config);

        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->systemId = $config['systemId'];
        $this->password = $config['password'];
        $this->systemType = $config['systemType'];
        $this->interfaceVersion = $config['interfaceVersion'];
        $this->ton = $config['ton'];
        $this->npi = $config['npi'];
        $this->addressRange = $config['addressRange'];
        $this->bindType = $config['bindType'];
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

    public function systemType(): ?string
    {
        return $this->systemType;
    }

    public function interfaceVersion(): int
    {
        return $this->interfaceVersion;
    }

    public function ton(): int
    {
        return $this->ton;
    }

    public function npi(): int
    {
        return $this->npi;
    }

    public function addressRange(): ?string
    {
        return $this->addressRange;
    }

    public function bindType(): int
    {
        return $this->bindType;
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
    }
}
