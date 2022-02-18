<?php
declare(strict_types = 1);

namespace Larc\smpp;

use Larc\smpp\SMPP;
use Larc\smpp\debugger\Logs;

final class PDU
{
	private $socket;
	private $commandStatus;
	private $debug;

	/**
	 * @param [type]      $socket        Socket establecido
	 * @param int|integer $commandStatus Status del CommandStatus. Default: 0
	 */
	function __construct( $socket, int $commandStatus = 0, bool $debug = false )
	{
		$this->socket 			= $socket;
		$this->commandStatus 	= $commandStatus;
		$this->debug 			= $debug;
	}

	/**
	 * Envia los comandos
	 * @param  [type] $commandId     	Comando ID
	 * @param  string $data          	Data que se quiere enviar
	 * @param  int    $sequenceNumber	Número sequencial para correlacionar
	 * @return [type] 
	 */
	public function send( $commandId, string $data, int $sequenceNumber)
	{
		if(!is_null($this->socket)) {
			//PDU Header P: 39
			$length 	= strlen($data) + 16;
			$PDUHeader 	= pack('NNNN', $length, $commandId, $this->commandStatus, $sequenceNumber  );
			$PDUBody 	= $data;

			$PDU 		= $PDUHeader . $PDUBody;

			//Send PDU
			$this->socket->write($PDU);

			return true;
		}

		return null;
	}


	public function read()
	{
		// Read PDU length
		$pduLength = $this->socket->read(4);

		if( !$pduLength ) {
			if( $debug ) Logs::write('--- Send PDU: Connection closed!. Dont read socket.');

			return false;
		}

		 /**
		 * extraction define next variables:
		 * @var $length
		 * @var $command_id
		 * @var $command_status
		 * @var $sequence_number
		 */

		extract(unpack('Nlength', $pduLength));

		// Read PDU headers
		$pduHeader = $this->socket->read(12);

		if( !$pduHeader ) {
			if( $debug ) Logs::write('--- Send PDU: Connection closed!. Dont read socket.');

			return false;
		}

		extract(unpack('Ncommand_id/Ncommand_status/Nsequence_number', $pduHeader));

		


		$command_id			= $command_id;
		$command_status 	= SMPP::getStatusMessage( $command_status);
		$sequence_number	= $sequence_number;

		//Read PDU body
		if( $length - 16 > 0 ) {
			$pduBody = $this->socket->read( $length - 16 );
			if( !$pduBody ) $pduBody = '';
		}else{
			$pduBody = '';
		}

		/*if( dechex($command_id) == 80000004 ) {
			var_dump( $pduBody );
		}*/

		if( $this->debug ) Logs::write( "<<< PDU response [command_id: {$command_id}, status: {$command_status}, sequence_number: {$sequence_number}, body: {$pduBody}]");

		return [
			'command_id'		=> $command_id,
			'command_status'	=> $command_status,
			'sequence_number'	=> $sequence_number,
			'body'				=> $pduBody
		];
	}

	/**
	 * Muestra valor hexagesimal
	 * @param  string $pdu PDU
	 * @return void
	 */
	private function printHex( $pdu ): void
	{
		$ar=unpack("C*",$pdu);

		foreach($ar as $v){
			$s=dechex($v);
			if(strlen($s)<2)$s="0$s";
			print "$s ";
		}
		print "\n";
	}
}
?>