<?php
declare(strict_types = 1);

namespace larc\smpp\debugger;

date_default_timezone_set ( 'America/Panama' );

class Logs
{
	/**
     * Inserta el error en un archivo log
     * @param  string $error Texto del error
     * @return void
     */
	static public function write( string $error ): void
	{
		$file = fopen('logs.log', 'a');
		fwrite($file, sprintf('%s | %s', date('Y-m-d H:i:s'), $error) . PHP_EOL);
		fclose($file);
	}
}
?>