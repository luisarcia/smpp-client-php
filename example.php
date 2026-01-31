<?php

require_once 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Larc\SMPPClient\Client\SMPPClient;
use Larc\SMPPClient\Config\ServerConfigBuilder;
use Larc\SMPPClient\Exception\ProtocolException;
use Larc\SMPPClient\SMPP;

$config = ServerConfigBuilder::transceiver()
    ->withHost('127.0.0.1')
    ->withPort(2776)
    ->withCredentials('CLIENT1', '00000000')
    ->withAddrTon(SMPP::TON_INTERNATIONAL) // Optional, default is TON_INTERNATIONAL
    ->withAddrNpi(SMPP::NPI_E164) // Optional, default is NPI_E164

    ->build();

$smppClient = new SMPPClient($config);
$smppClient->enableTrace();

if (!$smppClient->login()) {
    die("Login failed!");
}

$smppClient->from('Weblarc')
    ->fromTon(SMPP::TON_ALPHANUMERIC)
    ->fromNpi(SMPP::NPI_UNKNOWN)
    ->to('50760001000')
    ->message('Test message')
    ->send();

try {
    $smppClient->logout();
} catch (ProtocolException $e) {
    echo "Warning: UNBIND failed\n";
}

echo '📩 MessageId: ' . $smppClient->getLastMessageId() . "\n";
