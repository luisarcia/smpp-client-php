<?php
require 'vendor/autoload.php';

use Larc\smpp\entities\{serverConfig, SMS, Bulk};
use Larc\smpp\{SMSBuilder, SMPP, Debug};


//Configuración del servidor
$config = new serverConfig();
$config->setHost('127.0.0.1');
$config->setPort('1234');
$config->setSystemId('12345');
$config->setPassword('54321');
$config->setCommandId(SMPP::BIND_TRANSCEIVER);
$config->setTon(SMPP::TON_ALPHANUMERIC);
$config->setNpi(SMPP::NPI_PRIVATE);


//Envío de SMS uno a uno
$sms = new SMS();
$sms->setSender('Example Sender');
$sms->setRecipient('50760001000');
$sms->setMessage('Este es un sms de prueba ondemand');
$sms->setFlash(false);
$sms->setUtf(false);

$SMSBuilder = new SMSBuilder($config, new Debug(false));
$res1 		= $SMSBuilder->send($sms);

var_dump( $res1 );


exit;


//Envío de SMS Masivo
$msisdn = [
	'50760001000',
	'50760002000'
];

$sms = new Bulk();
$sms->setSender('Example Sender');
$sms->setRecipient($msisdn);
$sms->setMessage('Este es un sms de prueba masivo');
$sms->setFlash(false);
$sms->setUtf(false);

$SMSBuilder = new SMSBuilder($config, new Debug(false));
$res2 		= $SMSBuilder->sendBulk($sms);

var_dump( $res2 );

?>