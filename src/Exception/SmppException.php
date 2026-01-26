<?php

declare(strict_types=1);

namespace Larc\SMPPClient\Exception;

/**
 * SmppException
 * Una excepción personalizada para errores relacionados con SMPP.
 * @package Larc\SMPPClient\Exception
 */
class SmppException extends \Exception
{
    /**
     * Mensaje de error personalizado para la conexión del socket.
     *
     * @param string $message El mensaje de error.
     * @param int $code El código de error (opcional).
     */
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * Obtiene una representación en cadena de la excepción.
     *
     * @return string Una cadena que representa la excepción.
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
