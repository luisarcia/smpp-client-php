<?php
declare(strict_types = 1);

namespace Larc\smpp;

use Larc\smpp\{SMPP, PDU};
use Larc\smpp\debugger\Logs;

class Client
{
	private $socket;
	private $ton;
	private $npi;
	private $dataCoding;
	private $sequenceNumber = 0;
	private $debug = false;

	/**
	 * @param Ocject $socket     Socket establecido
	 * @param [type] $ton        TON
	 * @param [type] $npi        NPI
	 * @param [type] $dataCoding Codificación de la data. Default: SMPP::DATA_CODING_DEFAULT
	 */
	public function __construct( $socket, $ton, $npi, $dataCoding = SMPP::DATA_CODING_DEFAULT, bool $debug = false )
	{
		$this->socket 		= $socket;
		$this->ton 			= $ton;
		$this->npi 			= $npi;
		$this->dataCoding 	= $dataCoding;
		$this->debug 		= $debug;
	}
	
	/**
	 * SUBMIT_SMS
	 * @param  string $source      	Remitente. Número o texto
	 * @param  string $destination 	Destinatario
	 * @param  string $message     	Mensaje de texto
	 * @param  string $optional
	 * @return bool
	 */
	protected function submit_sm($source, $destination, string $message, $optional = '')
	{
		$data  = sprintf("%s\0", ""); // service_type
		$data .= sprintf("%c%c%s\0", $this->ton, $this->npi, $source ); // source_addr_ton, source_addr_npi, source_addr
		$data .= sprintf("%c%c%s\0", 1, 1, $destination ); // dest_addr_ton, dest_addr_npi, destination_addr
		$data .= sprintf("%c%c%c", 0, 0, 1); // esm_class, protocol_id, priority_flag
		$data .= sprintf("%s\0%s\0", "",""); // schedule_delivery_time, validity_period
		$data .= sprintf("%c%c", 0, 0); // registered_delivery, replace_if_present_flag
		$data .= sprintf("%c%c", $this->dataCoding, 0); // data_coding, sm_default_msg_id
		$data .= sprintf("%c%s", strlen( $message ), $message); // sm_length, short_message
		$data .= $optional;

		return $this->sendCommand( SMPP::SUBMIT_SM, $data );
	}

	/**
	 * BINDING
	 * @param  string $systemId  	Usuario
	 * @param  string $password  	Contraseña
	 * @param  [type] $commandId 	Commad ID. Ver documentación
	 * @return bool            		true / false
	 */
	public function bind( $commandId, string $systemId, string $password )
	{
		$data  = sprintf("%s\0%s\0", $systemId, $password); // system_id, password 
		$data .= sprintf("%s\0%c", 'SMPP', SMPP::SMPP_3_4);  // system_type, interface_version
		$data .= sprintf("%c%c%s\0", SMPP::TON_INTERNATIONAL, SMPP::NPI_E164, ''); // addr_ton, addr_npi, address_range

		$response = $this->sendCommand( $commandId, $data );

		if($response) {
			if( $this->debug ) Logs::write('>>> Bind done!');
			return true;
		} else {
			if( $this->debug ) Logs::write('--- Binding error!');
			return false;
		}
	}

	/**
	 * UNBINDING
	 * @return void
	 */
	public function unbind()
	{
		$response = $this->sendCommand( SMPP::UNBIND, '' );

		if($response) {
			if( $this->debug ) Logs::write('>>> Unbind done!');
			return true;
		}

		if( $this->debug ) Logs::write('--- Unbinding error!');
		return false;
	}

	/**
	 * Envía los comandos
	 * @param  [type] $commandId	Command ID
	 * @param  [type] $data     	Data que se quiere enviar
	 * @return bool 				true / false
	 */
	protected function sendCommand( $commandId, $data )
	{
		$sequenceNumber = $this->sequenceNumber += 1;

		$PDU 			= new PDU( $this->socket, 0, true );
		$PDU->send( $commandId, $data, $sequenceNumber );
		$response = $PDU->read();

		if( !$response ) return false;

		return $response['command_status'] == SMPP::ESME_ROK ? true : false;
	}

	/**
	 * Envía el SMS
	 * @param  string  $source       Remitente. Número o texto
	 * @param  string  $destination  Destinatario
	 * @param  string  $message      Mensaje de texto
	 * @param  integer $utf          Si soporta o no UTF8
	 * @param  integer $flash        Si es un SMS tipo 0
	 * @return [type]
	 */
	public function sendSMS( string $source, string $destination, string $message, bool $flash = false, bool $utf = false )
	{
		if( $utf ) $this->dataCoding 	= SMPP::DATA_CODING_UCS2;
		if( $flash ) $this->dataCoding 	= $this->dataCoding | 0x10;

		$size = strlen( $message );

		if( $utf ) $size += 20;

		//SMS individual
		if ( $size < 160 ) {
			$response = $this->submit_sm( $source, $destination, $message );

			/*if( !$response ) {
				return $response;
			}*/

		//SMS multiparte
		} else {
			$sar_msg_ref_num 	= rand(1,255);
			$sar_total_segments = ceil( strlen( $message) / 130 );

			for( $sar_segment_seqnum = 1; $sar_segment_seqnum <= $sar_total_segments; $sar_segment_seqnum++ ) {
				$part 		= substr( $message, 0, 130);
				$message 	= substr($message, 130);

				$optional  = pack('nnn', 0x020C, 2, $sar_msg_ref_num);
				$optional .= pack('nnc', 0x020E, 1, $sar_total_segments);
				$optional .= pack('nnc', 0x020F, 1, $sar_segment_seqnum);

				$response	= $this->submit_sm( $source, $destination, $part, $optional );

				/*if( !$response ) {
					return $res;
				}*/
			}
		}
	}
}
?>