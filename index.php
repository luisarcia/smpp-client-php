<?php
require 'SMPP.php';

$config = [
	'host' 			=> '127.0.0.1',
	'port'			=> 0000,
	'system_id'		=> 12345,
	'password'		=> 1234567890,
	'ton'			=> 5,
	'npi'			=> 9,
	'command_id'	=> 9
];

$param = [
	'from'		=> 'EXAMPLE',
	'to'		=> '50760000000',
	'message'	=> 'Message text',
	'utf'		=> 0,
	'flash'		=> 0
];

$smpp = new SMPP( $config );
$smpp->send( $param );