<?php
declare(strict_types = 1);

namespace Larc\SMPPClient;

/**
 * Class Code
 * Code error of SMPPClient.
 *
 * @package Larc\SMPPClient
 */
class Code
{
    /**
     * Success.
     *
     * @var int
    */
    const OK = 0;

    /**
     * Error de conexión del socket.
     * 
     * @var int
    */
    const CONNECTION_ERROR = 1;

    /**
     * Error de binding.
     *
     * @var int
    */
    const BINDING_ERROR = 2;
}
