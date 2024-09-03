# SMPP Client

Permite enviar SMS utilizando el protocolo SMPP v3.4 (https://smpp.org/SMPP_v3_4_Issue1_2.pdf)

**Soporta:**

- Unicode
- SMS Multi-part
- SMS Flash (tipo 0)



## Changelog

* 2.1.0
  * Se renombra algunas clases para que sea más claro lo que hace.
  * Se mejora la separación de funcionlidad por clase.
  * Se elimina la función de Bulk.



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

use Larc\SMPPClient\entities\{ServerConfig, SMS};
use Larc\SMPPClient\{SMSBuilder, SMPP, Code};

$config = new ServerConfig();
$config->setHost('127.0.0.1')
    ->setPort(1234)
    ->setSystemId('0000')
    ->setPassword('00000000')
    ->setCommandId(SMPP::BIND_TRANSCEIVER)
    ->setTon(SMPP::TON_ALPHANUMERIC)
    ->setNpi(SMPP::NPI_PRIVATE);
```



## Uso

#### Envío de SMS onDemand (uno a uno)

```php
$sms = new SMS();
$sms->setSender('Name')
    ->setRecipient('50760001000')
    ->setMessage('Text message')
    ->setFlash(false)
    ->setUtf(false);

$SMSBuilder = new SMSBuilder($config, $timeout, $trace);
$res = $SMSBuilder->send($sms);
```



#### Enviar SMS Tipo 0 o Flash

```php
$sms->setFlash(true);
```



#### Enviar mensaje con caracteres latinos (tildes, ñ, ¿?, !¡)

Coming Soon