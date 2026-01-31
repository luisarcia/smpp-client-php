<?php

require_once 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Larc\SMPPClient\Client\SMPPClient;
use Larc\SMPPClient\Config\ServerConfigBuilder;
use Larc\SMPPClient\SMPP;

$config = ServerConfigBuilder::transceiver()
    ->withHost('127.0.0.1')
    ->withPort(2776)
    ->withCredentials('CLIENT1', '00000000')
    ->withTon(SMPP::TON_INTERNATIONAL)
    ->withNpi(SMPP::NPI_E164)
    ->build();

$smppClient = new SMPPClient($config);
$smppClient->enableTrace();

if (!$smppClient->login()) {
    die("Login failed!");
}

// $smppClient->from('Weblarc')
//            ->to('50766207383')
//            ->asUtf8()
//            ->asFlash()
//            ->message('Hello, this is a test message!')
//            ->send();

$smppClient->from('50766207384')
           ->to('50766207383')
           ->message('Second test message')
           ->send();

var_dump($smppClient->getLastMessageId());

try {
    $smppClient->logout();
} catch (\Larc\SMPPClient\Exception\ProtocolException $e) {
    echo "Warning: UNBIND failed\n";
}