<?php
declare(strict_types = 1);

namespace Larc\smpp;

class Debug
{
	public $state;

	/**
	 * Debug
	 * @param bool|boolean $state Activa / desactiva el debug
	 * @param string       $dir   Directorio donde se quiere guardar el log
	 */
	public function __construct( bool $state = false )
	{
		$this->state 	= $state;
	}
}
?>