<?php

require_once 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Larc\SMPPClient\entities\{ServerConfig, SMS};
use Larc\SMPPClient\{SMSBuilder, SMPP, Code};

//Configuración del servidor
$config = new ServerConfig();
$config->setHost('127.0.0.1')
    ->setPort(1234)
    ->setSystemId('0000')
    ->setPassword('00000000')
    ->setCommandId(SMPP::BIND_TRANSCEIVER)
    ->setTon(SMPP::TON_ALPHANUMERIC)
    ->setNpi(SMPP::NPI_PRIVATE);

//Envío de SMS uno a uno
$sms = new SMS();
$sms->setSender('Name')
    ->setRecipient('50760001000')
    ->setMessage('Text message')
    ->setFlash(false)
    ->setUtf(false);

$timeout = 5;
$trace = true;

$SMSBuilder = new SMSBuilder($config, $timeout, $trace);
$res = $SMSBuilder->send($sms);

switch ($res) {
    case Code::OK:
        echo "SMS Sent";
        break;
    case Code::CONNECTION_ERROR:
        echo "Connection error";
        break;
    case Code::BINDING_ERROR:
        echo "Binding error";
        break;
    default:
        echo "Unknown error";
        break;
}
