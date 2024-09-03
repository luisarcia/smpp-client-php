<?php

declare(strict_types=1);

namespace Larc\SMPPClient\exceptions;

use \Exception;

/**
 * SocketException
 */
class SocketException extends Exception
{
    /**
     * Mensaje de error personalizado para la conexión del socket.
     *
     * @param string $message El mensaje de error.
     * @param int $code El código de error (opcional).
     * @param Exception|null $previous Excepción anterior usada para encadenamiento (opcional).
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null)
    {
        // Llamar al constructor de la clase base.
        parent::__construct($message, $code, $previous);
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
