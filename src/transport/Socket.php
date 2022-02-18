<?php
declare(strict_types = 1);

namespace Larc\smpp\transport;

use Larc\smpp\{SMPP, PDU};
use Larc\smpp\debugger\Logs;

class Socket
{
	private $host;
	private $port;
	private $timeout;
	private $debug;
	private $socket;

	/**
	 * @param string      $host    IP o dirección
	 * @param int|integer $port    Puerto
	 * @param int|integer $timeout Timeout en segundo
	 */
	function __construct( string $host, int $port, int $timeout = 2, bool $debug = false )
	{
		$this->host 	= $host;
		$this->port 	= $port;
		$this->timeout  = $timeout;
		$this->debug 	= $debug;
	}

	/**
	 * Abre la conexión por socket
	 * @return mixed        Socket
	 */
	public function open()
	{
		//Abre la conexión
		$this->socket = @fsockopen( $this->host, $this->port, $errno, $errstr, $this->timeout );

		if( $this->socket ) {
			if( function_exists('stream_set_timeout') ) stream_set_timeout( $this->socket, $this->timeout );

			if( $this->debug ) Logs::write('>>> Connected');

			return $this->socket;
		}

		if( $this->debug ) Logs::write('--- Connection not established!');

		return null;
	}

	/**
	 * Enviar el PDU
	 * @param  string $pdu Data a enviar
	 * @return void
	 */
	public function write( $pdu ): void
	{
		$writed = @fwrite($this->socket, $pdu );
	
		if(!$writed) {
			if($this->debug) Logs::write('--- PDU send error');
		}

		if($this->debug) Logs::write('>>> PDU Sent');
	}

	/**
	 * Lee el PDU
	 * @param  int $length Length
	 * @return string      Datos
	 */
	public function read( int $length )
	{
		$readed = @fread($this->socket, $length);

		if(!$readed) {
			if($this->debug) Logs::write('--- PDU reception error');
			return false;
		}

		if($this->debug) Logs::write('<<< PDU received');
		return $readed;
	}

	/**
	 * Cierra le conexión socket
	 * @return bool 	true | false
	 */
	public function close()
	{
		if(!is_null( $this->socket )) {
			if( $this->socket ) @fclose( $this->socket );
			if( $this->debug ) Logs::write('--- Disconnected');
			return true;
		}

		if( $this->debug ) Logs::write('--- Socket was disconnected!');
		return false;
	}
}
?>