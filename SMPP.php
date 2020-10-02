<?php
/**
 * SMPP Client
 * Description: Permite realizar envío de SMS con el protocolo SMPP v3.4
 * @version 1.1
 * @author Luis Arcia
 * @since 02-10-2020
 * Fork https://github.com/rayed/smpp_php
 */

class SMPP
{
	private $host; //IP del servidor
	private $port; //Puerto
	private $system_id; //Usuario
	private $password; //Password
	private $ton; //Tipo de número (Númerico o alfanumérico)
	private $npi; //Identificación del plan de numeración
	private $command_id; //Identifica el tipo de mensaje que representa la PDU SMPP. Se identifica como un "transmitter", "receiver", or "transceiver".

	private $socket       = 0;
	private $seq          = 0;
	private $data_coding  = 0;
	private $timeout      = 2;

	public function __construct( array $param )
	{
		$this->host 		= $param['host'];
		$this->port 		= $param['port'];
		$this->system_id 	= $param['system_id'];
		$this->password		= $param['password'];
		$this->ton 			= $param['ton'];
		$this->npi 			= $param['npi'];
		$this->command_id	= $param['command_id'];
	}

	/**
	 * Realiza el envío
	 * @param  array $param Datos y parametros de envío
	 * @return array        Respuesta
	 */
	public function send( array $param )
	{
		$openSocket = $this->open();

		if( !$openSocket['success'] ) {
			$this->close();

			return $openSocket;
		}

		$bind = $this->sendBind( $this->system_id, $this->password );

		if( !$bind['success'] ) {
			$this->close();

			return $bind;
		}

		$res = $this->send_long(
			$param['from'],
			$param['to'],
			$param['message'],
			$param['utf'],
			$param['flash']
		);

		$this->close();
		
		return $res;
	}

	/**
	 * Intercambio de información
	 * @param  int $command_id Identifica el tipo de mensaje que representa la PDU SMPP. Se identifica como transmisor, receptor o transceptor.
	 * @param  string $data       Datos a enviar
	 * @return array             Respuesta
	 */
	private function sendPDU( int $command_id, string $data )
	{
		//increment sequence
		$this->seq += 1;
		//PDU = PDU_header + PDU_content
		$pdu = @pack('NNNN', strlen($data)+16, $command_id, 0, $this->seq) . $data; //
		// send PDU
		@fputs( $this->socket, $pdu );

		// Get response length
		$data 			= @fread($this->socket, 4);
		//if($data==false) die("\nSend PDU: Connection closed!");
		if( !$data ) return false;
		$tmp 			= @unpack('Nlength', $data);
		$command_length = $tmp['length'];
		if( $command_length < 12 ) return;

		// Get response 
		$data 			= @fread($this->socket, $command_length-4);
		$pdu 			= @unpack('Nid/Nstatus/Nseq', $data);

		return $pdu;
	}

	/**
	 * Abre la conexión para establecer la conexión
	 * @return array    Resultado del proceso
	 */
	private function open()
	{
		// Open the socket
		$socketO = @fsockopen( $this->host, $this->port, $errno, $errstr, $this->timeout );

		if( !$socketO ) {
			return [
				'success' 		=> false,
				'code'			=> 504,//Gateway Timeout
				'description'	=> 'Communication has been lost'
			];
		}

		$this->socket = $socketO;

		return [
			'success'		=> true,
			'code'			=> 200,
			'description'	=> 'Established connection'
		];
	}

	/**
	 * Permite enviar los parametros de conexión con el SMS Gateway
	 * @param  int $system_id   Usuario
	 * @param  int $password    Contraseña
	 * @return array            Resultado del proceso
	 */
	private function sendBind( int $system_id, int $password )
	{
		if( function_exists('stream_set_timeout') ) {
			stream_set_timeout($this->socket, $this->timeout);
		}

		$data  = sprintf("%s\0%s\0", $system_id, $password); // system_id, password 
		$data .= sprintf("%s\0%c", 'SMPP', 0x34);  // system_type, interface_version
		$data .= sprintf("%c%c%s\0", 1, 1, ""); // addr_ton, addr_npi, address_range 

		$ret = $this->sendPDU( $this->command_id, $data ); //2

		if( $ret['status'] === 0 ) {
			return [
				'success' 		=> true,
				'code'			=> 200,
				'description' 	=> 'Bind done'
			];
		}

		return [
			'success' 		=> false,
			'code'			=> 504,
			'description' 	=> 'Bind error: ' . $ret['status']
		];
	}

	/**
	 * Envía el SMS
	 * @param  string / int $source_addr 	Nombre o número de origen
	 * @param  int $destintation_addr 		Número de destino
	 * @param  string $short_message     	Texto
	 * @param  string $optional          	null
	 * @return array                    	Resultado del proceso
	 */
	private function submit_sm( string $source_addr, int $destintation_addr, string $short_message, $optional = '' )
	{
		$data  = sprintf("%s\0", ""); // service_type
		$data .= sprintf("%c%c%s\0", $this->ton, $this->npi, $source_addr); // source_addr_ton, source_addr_npi, source_addr
		$data .= sprintf("%c%c%s\0", 1, 1, $destintation_addr); // dest_addr_ton, dest_addr_npi, destintation_addr
		$data .= sprintf("%c%c%c", 0, 0, 0); // esm_class, protocol_id, priority_flag
		$data .= sprintf("%s\0%s\0", "",""); // schedule_delivery_time, validity_period
		$data .= sprintf("%c%c", 0,0); // registered_delivery, replace_if_present_flag
		$data .= sprintf("%c%c", $this->data_coding, 0); // data_coding, sm_default_msg_id
		$data .= sprintf("%c%s", strlen( $short_message ), $short_message); // sm_length, short_message
		$data .= $optional;

		$ret = $this->sendPDU( 4, $data );

		if( $ret['status'] === 0 ) {
			return [
				'success' 		=> true,
				'code'			=> 200,
				'description' 	=> 'Submit done!'
			];
		}

		return [
			'success' 		=> false,
			'code'			=> 504,
			'description' 	=> 'Submit error'
		];
	}

	/**
	 * Cierra la conexión
	 * @return bool true o false
	 */
	private function close() {
		$ret = $this->sendPDU(6, '');

		@fclose( $this->socket );

		return true;
	}

	/**
	 * Configura el SMS y verifica si es multiparte o no
	 * @param  string  $source_addr      Nombre o número de origen
	 * @param  int  $destintation_addr 	Número de destino
	 * @param  string  $short_message   Texto
	 * @param  int $utf               	Si es compatible con UTF
	 * @param  int $flash             	Si es un mensaje clase 0
	 * @return array                     	Resultado del proceso
	 */
	private function send_long( string $source_addr, int $destintation_addr, string $short_message, $utf = 0, $flash = 0 ) {
		if( $utf ) $this->data_coding = 0x08;

		if( $flash ) $this->data_coding = $this->data_coding | 0x10;

		$size = strlen($short_message);

		if( $utf ) $size += 20;

		//SMS individual
		if ( $size < 160 ) {
			$submit = $this->submit_sm( $source_addr, $destintation_addr, $short_message );

			if( !$submit['success'] ) {
				return $submit;
			}

		//SMS multiparte
		} else {
			$sar_msg_ref_num 		= rand(1,255);
			$sar_total_segments 	= ceil( strlen( $short_message) / 130 );

			for( $sar_segment_seqnum = 1; $sar_segment_seqnum <= $sar_total_segments; $sar_segment_seqnum++ ) {
				$part 			= substr( $short_message, 0 ,130);
				$short_message 	= substr($short_message, 130);

				$optional  		= @pack('nnn', 0x020C, 2, $sar_msg_ref_num);
				$optional 		.= @pack('nnc', 0x020E, 1, $sar_total_segments);
				$optional 		.= @pack('nnc', 0x020F, 1, $sar_segment_seqnum);

				$submit 		= $this->submit_sm( $source_addr, $destintation_addr, $part, $optional );

				if( !$submit['success'] ) {
					return $submit;
				}
			}
		}

		return [
			'success' 		=> true,
			'code'			=> 200,
			'description' 	=> 'SMS sent'
		];
	}
}
?>