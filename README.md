# SMPP Client

Permite enviar SMS utilizando el protocolo SMPP v3.4 (https://smpp.org/SMPP_v3_4_Issue1_2.pdf)

**Soporta:**

- Unicode
- SMS Multi-part
- SMS Flash (tipo 0)



## Requerimientos

PHP 5.6 o superior



## Instalación

Instalar via [Composer](http://getcomposer.org/):

```bash
composer require larc/smpp-client-php
```

Ten en cuenta:

- Ejecutar `composer install` para agregar las dependencias en el directorio **vendor**
- Añade el autoloader en tu aplicación con la línea: `require("vendor/autoload.php")`



## Configuración

```php
require 'vendor/autoload.php';

use Larc\smpp\entities\{serverConfig, SMS, Bulk};
use Larc\smpp\{SMSBuilder, SMPP, Debug};

$config = new serverConfig();
$config->setHost('127.0.0.1');
$config->setPort('1234');
$config->setSystemId('12345');
$config->setPassword('54321');
$config->setCommandId(SMPP::BIND_TRANSCEIVER);
$config->setTon(SMPP::TON_ALPHANUMERIC);
$config->setNpi(SMPP::NPI_PRIVATE);
```



## Uso

#### Envío de SMS onDemand (uno a uno)

```php
$sms = new SMS();
$sms->setSender('Example Sender');
$sms->setRecipient('50760001000');
$sms->setMessage('Este es un sms de prueba ondemand');
$sms->setFlash(false);
$sms->setUtf(false);

$SMSBuilder = new SMSBuilder($config, new Debug(false));
$res1 		= $SMSBuilder->send($sms);
```



#### Envío de SMS masivo

Nota: es importante declarar el tiempo de ejecución lo más alto posible.

```php
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
```



#### Enviar SMS Tipo 0 o Flash

```php
$sms->setFlash(true);
```