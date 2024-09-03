<?php

declare(strict_types=1);

namespace Larc\SMPPClient;

use Larc\SMPPClient\transport\SocketClient;
use Larc\SMPPClient\{SMPPClient, Code};
use Larc\SMPPClient\entities\{SMS, ServerConfig};
use Larc\SMPPClient\debugger\TraceLogger;

/**
 * Class SMSBuilder
 * Build SMS
 *
 * @package Larc\SMPPClient
 */
class SMSBuilder
{
    private $host;
    private $port;
    private $systemId;
    private $password;
    private $commandId;
    private $ton;
    private $npi;
    private $timeout;
    private $trace;

    /**
     * SMSBuilder constructor.
     *
     * @param ServerConfig $configServer
     * @param int          $timeout. Default 2. Timeout in seconds
     * @param bool         $trace
     */
    public function __construct(ServerConfig $configServer, int $timeout = 2, bool $trace = false)
    {
        $this->host = $configServer->getHost();
        $this->port = $configServer->getPort();
        $this->systemId = $configServer->getSystemId();
        $this->password = $configServer->getPassword();
        $this->commandId = $configServer->getCommandId();
        $this->ton = $configServer->getTon();
        $this->npi = $configServer->getNpi();
        $this->timeout = $timeout;
        $this->trace = filter_var($trace, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Method send
     * Send SMS
     *
     * @param SMS $sms
     *
     * @return int
     */
    public function send(SMS $sms): int
    {
        $socket = new SocketClient($this->host, $this->port, $this->timeout, $this->trace);
        $socketO = $socket->connect();

        // If the connection fails, the error code is returned.
        if (!$socketO) {
            $socket->disconnect();
            return Code::CONNECTION_ERROR;
        }

        // Create a new SMPP client
        $client = new SMPPClient($socket, $this->ton, $this->npi, SMPP::DATA_CODING_DEFAULT, $this->trace);
        // Login to the SMPP server
        $response = $client->login($this->commandId, $this->systemId, $this->password);

        $tracelogger = new TraceLogger();
        $tracelogger->display();

        // If the login fails, the connection is closed. The error code is returned.
        if (!$response) {
            $client->logout();
            $socket->disconnect();

            return Code::BINDING_ERROR;
        }

        // Send SMS
        $client->sendSMS(
            $sms->getSender(),
            $sms->getRecipient(),
            $sms->getMessage(),
            $sms->getFlash(),
            $sms->getUtf()
        );

        // Logout and close connection
        $client->logout();
        $socket->disconnect();

        return Code::OK;
    }
}
