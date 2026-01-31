# SMPP Client PHP v3.0.0

![PHP](https://img.shields.io/badge/PHP-5.6%2B-blue)
![SMPP](https://img.shields.io/badge/SMPP-3.4-green)
![License](https://img.shields.io/github/license/larc/smpp-client-php)
![Status](https://img.shields.io/badge/status-stable-success)

Cliente **SMPP v3.4** en PHP para el envío de SMS.
Soporta **Unicode (UCS2)**, **mensajes largos (SAR)** y **SMS Flash (Class 0)**, con una API fluida y simple.

[Documentación oficial SMPP v3.4](https://smpp.org/SMPP_v3_4_Issue1_2.pdf)

---

## Tabla de Contenidos

- [Características](#características)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Envío de SMS Ejemplo](#envío-de-sms-ejemplo)
- [SMS Flash Class 0](#sms-flash-class-0)
- [Unicode UCS2](#unicode-ucs2)
- [Mensajes largos](#mensajes-largos)
- [Notas importantes](#notas-importantes)
- [Debug y Trace](#debug-y-trace)
- [Changelog](#changelog)
- [Licencia](#licencia)

---

## Características

- ✅ Compatible con **SMPP v3.4**
- 🌍 Soporte para **GSM 7-bit** y **Unicode (UCS2)**
- 📦 Envío automático de **SMS largos** (segmentación y concatenación SAR)
- ⚡ Soporte para **SMS Flash (Class 0)**
- 🔧 Configuración flexible de **TON / NPI**
- 🧩 Arquitectura desacoplada (ideal para mocks y testing)
- 🐘 Compatible con **PHP 5.6+**

---

## Instalación

```bash
composer require larc/smpp-client-php
```

## Configuración

```php
use Larc\SMPPClient\Config\ServerConfigBuilder;
use Larc\SMPPClient\Protocol\SMPP;

$config = ServerConfigBuilder::transceiver()
    ->withHost('127.0.0.1')
    ->withPort(2775)
    ->withCredentials('system_id', 'password')
    ->withAddrTon(SMPP::TON_ALPHANUMERIC)
    ->withAddrNpi(SMPP::NPI_PRIVATE)
    ->build();
```

## Envío de SMS Ejemplo

```php

use Larc\SMPPClient\Client\SMPPClient;

$client = new SMPPClient($config);

$client->from('Weblarc')
    ->to('50760001000')
    ->message('Texto de prueba')
    ->send();

```

## SMS Flash Class 0

```php
$client->from('Weblarc')
    ->to('50760001000')
    ->message('Mensaje Flash')
    ->asFlash(true)
    ->send();
```

## Unicode UCS2

```php
$client->from('Weblarc')
    ->to('50760001000')
    ->message('¡Hola, cómo estás? ñáéíóú')
    ->asUtf8(true)
    ->send();
```

## Mensajes largos

```php
$client->from('Weblarc')
    ->to('50760001000')
    ->message(str_repeat('Mensaje largo ', 50))
    ->send();
```

## Notas importantes

- **send()** maneja automáticamente:
  - Codificación
  - Segmentación
  - Concatenación SAR
- Último message_id recibido:

```php
$client->getLastMessageId();
```

## Debug y Trace

```php
$client->enableTrace();
```

```bash
@MacBook-Pro-de-Luis smpp-client-php % php example.php
2026-01-31 21:01:28 - >>> Building PDU [commandId: 9, status: 0, sequenceNumber: 1, body: 434c49454e543100303030303030303000534d50500034010100]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483657, status: 0, sequenceNumber: 1, body: 4d4f434b5f534d534300]
2026-01-31 21:01:28 - >>> Bind OK
2026-01-31 21:01:28 - >>> Building PDU [commandId: 4, status: 0, sequenceNumber: 2, body: 0005005765626c6172630001013530373630303031303030000000010000000000000c54657374206d657373616765]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483652, status: 0, sequenceNumber: 2, body: 6d73675f3137363938393332383836343000]
2026-01-31 21:01:28 - >>> Building PDU [commandId: 6, status: 0, sequenceNumber: 3, body: ]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483654, status: 0, sequenceNumber: 3, body: ]
📩 MessageId: msg_1769893288640
2026-01-31 21:01:28 - >>> Building PDU [commandId: 9, status: 0, sequenceNumber: 1, body: 434c49454e543100303030303030303000534d50500034010100]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483657, status: 0, sequenceNumber: 1, body: 4d4f434b5f534d534300]
2026-01-31 21:01:28 - >>> Bind OK
2026-01-31 21:01:28 - >>> Building PDU [commandId: 4, status: 0, sequenceNumber: 2, body: 0005005765626c6172630001013530373630303031303030000000010000000000000c54657374206d657373616765]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483652, status: 0, sequenceNumber: 2, body: 6d73675f3137363938393332383836343000]
2026-01-31 21:01:28 - >>> Building PDU [commandId: 6, status: 0, sequenceNumber: 3, body: ]
2026-01-31 21:01:28 - <<< PDU response [commandId: 2147483654, status: 0, sequenceNumber: 3, body: ]
```

## Changelog

### 3.0.0

- Retorno del message_id
- API fluida simplificada
- Nuevo ServerConfigBuilder
- Soporte completo SAR
- Unicode y GSM7 robusto
- SMS Flash (Class 0)
- Refactorización interna
- Documentación mejorada

## Licencia

MIT © Weblarc 2026. Develop by Luis Arcia.
