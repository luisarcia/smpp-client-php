<?php
declare(strict_types = 1);

namespace Larc\smpp;

use Larc\smpp\transport\{Socket};
use Larc\smpp\{PDU, Client, Debug, Code};
use Larc\smpp\entities\{Bulk, SMS};

class SMSBuilder
{
	private $host;
	private $port;
	private $systemId;
	private $password;
	private $commandId;
	private $ton;
	private $npi;
	private $timeout 	= 2; //segundo
	private $debug 		= false;
	private $socket;
	private $sleep = 1 * 1000;

	/**
	 * @param object $configServer Configuración del Servidor
	 */
	public function __construct( object $configServer, Debug $debug = null )
	{
		$this->host 		= $configServer->getHost();
		$this->port 		= $configServer->getPort();
		$this->systemId 	= $configServer->getSystemId();
		$this->password 	= $configServer->getPassword();
		$this->commandId 	= $configServer->getCommandId();
		$this->ton 			= $configServer->getTon();
		$this->npi 			= $configServer->getNpi();
		$this->debug 		= is_null( $debug ) ? false : $debug->state;
	}

	/**
	 * Envia el SMS OnDemand
	 * @param  SMS 	$sms 		SMS
	 * @return array      		Response
	 */
	public function send( SMS $sms )
	{
		$socket 	= new Socket( $this->host, $this->port, $this->timeout, $this->debug );
		$socketO	= $socket->open();

		//Falla la conexión
		if( !$socketO ) {
			$socket->close();

			return [
				'code'			=> Code::CONNECTION_ERROR,
				'description'	=> 'Connection Error'
			];
		}

		$client 	= new Client( $socket, $this->ton, $this->npi, SMPP::DATA_CODING_DEFAULT, $this->debug );
		$response 	= $client->bind( $this->commandId, $this->systemId, $this->password );

		//Falla el binding
		if( !$response ) {
			$client->unbind();
			$socket->close();

			return [
				'code'			=> Code::BINDING_ERROR,
				'description'	=> 'Binding Error'
			];
		}

		$client->sendSMS(
			$sms->getSender(),
			$sms->getRecipient(),
			$sms->getMessage(),
			$sms->getFlash(),
			$sms->getUtf()
		);

		$client->unbind();
		$socket->close();

		return [
			'code'			=> Code::OK,
			'description'	=> 'OK'
		];
	}

	/**
	 * Envia el SMS Bulk. El texto es el mismo para todos los destinatarios.
	 * @param  object $sms  SMS
	 * @return array 		Response
	 */
	public function sendBulk( Bulk $sms )
	{
		$socket 	= new Socket( $this->host, $this->port, $this->timeout, $this->debug );
		$socketO	= $socket->open();

		//Falla la conexión
		if( !$socketO ) {
			$socket->close( $socketO );

			return [
				'code'			=> Code::CONNECTION_ERROR,
				'description'	=> 'Connection Error'
			];
		}

		$client 	= new Client( $socketO, $this->ton, $this->npi, SMPP::DATA_CODING_DEFAULT, $this->debug );
		$response 	= $client->bind( $this->commandId, $this->systemId, $this->password );

		//Falla el binding
		if( !$response ) {
			$client->unbind();
			$socket->close( $socketO );

			return [
				'code'			=> Code::BINDING_ERROR,
				'description'	=> 'Binding Error'
			];
		}

		for ($i=0; $i < count($sms->getRecipient()); $i++) { 
			$client->sendSMS(
				$sms->getSender(),
				$sms->getRecipient()[$i],
				$sms->getMessage(),
				$sms->getFlash(),
				$sms->getUtf()
			);
		}

		$client->unbind();
		$socket->close( $socketO );

		return [
			'code'			=> Code::OK,
			'description'	=> 'OK'
		];
	}
}
?>