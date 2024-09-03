<?php

declare(strict_types=1);

namespace Larc\SMPPClient\interfaces;

/**
 * ServerConfigInterface
 */
interface ServerConfigInterface
{
    public function getHost();

    public function setHost(string $host);

    public function getPort();

    public function setPort(int $port);

    public function getSystemId();

    public function setSystemId(string $systemId);

    public function getPassword();

    public function setPassword(string $password);

    public function getCommandId();

    public function setCommandId($commandId);

    public function getTon();

    public function setTon($ton);

    public function getNpi();

    public function setNpi($npi);
}
