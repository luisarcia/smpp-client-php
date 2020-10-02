# SMPP Client PHP
Permite enviar SMS utilizando el protocolo SMPP v3.4.

Soporta:

- Unicode
- SMS Multi-part
- SMS Flash ( tipo 0 )



------

### Configuración del cliente

```php
$config = [
  'host' 		=> '127.0.0.1',
	'port'		=> 0000,
	'system_id'	=> 12345,
	'password'	=> 1234567890,
	'ton'		=> 5,
	'npi'		=> 9,
	'command_id'	=> 9
];
```



##### Parámentros

| Key        | Value      | Tipo   | Descripción                                                  |
| ---------- | ---------- | ------ | ------------------------------------------------------------ |
| host       | 127.0.0.1  | String | IP del servidor                                              |
| port       | 0000       | Int    | Puerto                                                       |
| system_id  | 12345      | Int    | Usuario                                                      |
| password   | 1234567890 | Int    | Contraseña                                                   |
| ton        | 5          | Int    | Tipo de número                                               |
| dpi        | 9          | Int    | Identificación del plan de numeración                        |
| command_id | 9          | Int    | Identifica el tipo de mensaje que representa la PDU SMPP. Se identifica como un "transmitter", "receiver", or "transceiver" |

##### Tipos de TON

| Código | Descripción       |
| ------ | ----------------- |
| 0      | Unknown           |
| 1      | International     |
| 2      | National          |
| 3      | Network Specific  |
| 4      | Subscriber Number |
| 5      | Alphanumeric      |
| 6      | Abbreviated       |

##### Tipos de NPI

| Código | Descripción                                |
| ------ | ------------------------------------------ |
| 0      | Unknown                                    |
| 1      | ISDN/telephone numbering plan (E163/E164)  |
| 3      | Data numbering plan (X.121)                |
| 4      | Telex numbering plan (F.69)                |
| 6      | Land Mobile (E.212)                        |
| 8      | National numbering plan                    |
| 9      | Private numbering plan                     |
| 10     | ERMES numbering plan (ETSI DE/PS 3 01-3)   |
| 13     | Internet (IP)                              |
| 18     | WAP Client Id (to be defined by WAP Forum) |

##### Tipos de comando

| Código | Descripción      |
| ------ | ---------------- |
| 1      | Bind receiver    |
| 3      | Bind transmitter |
| 9      | Bind transceiver |

------

### Configuración del mensaje

```php
$param = [
	'from'		=> 'EXAMPLE',
	'to'		=> '50760000000',
	'message'	=> 'Message text',
	'utf'		=> false,
	'flash'		=> false
];
```



##### Parámetros

| Key     | Value        | Tipo    | Descripción                                                  |
| ------- | ------------ | ------- | ------------------------------------------------------------ |
| from    | Example      | String  | El emisor, dependiente de TON puede ser Numérico o Alfanumérico |
| to      | 50760000000  | String  | Número de destino. Formato: cod pais + cod area + numero.    |
| message | Message Text | String  | Mensaje                                                      |
| utf     | 0            | Boolean | Si quiere activar UTF-8 o no                                 |
| flash   | 0            | Boolean | Si es un SMS tipo 0                                          |



------

### Uso

```php
require 'SMPP.php';

$config = [
	'host' 				=> '127.0.0.1',
	'port'				=> 0000,
	'system_id'		=> 12345,
	'password'		=> 1234567890,
	'ton'					=> 5,
	'npi'					=> 9,
	'command_id'	=> 9
];

$param = [
	'from'		=> 'EXAMPLE',
	'to'			=> '50760000000',
	'message'	=> 'Message text',
	'utf'			=> 0,
	'flash'		=> 0
];

$smpp = new SMPP( $config );
$smpp->send( $param );

```



------

### Status Codes

| Error Number in Hexadecimal | Error Number in Decimal | Error Name              | Error Description                                  |
| --------------------------- | ----------------------- | ----------------------- | -------------------------------------------------- |
| 0x00000000                  | 0                       | ESME_ROK                | No Error                                           |
| 0x00000001                  | 1                       | ESME_RINVMSGLEN         | Message too long                                   |
| 0x00000002                  | 2                       | ESME_RINVCMDLEN         | Command length is invalid                          |
| 0x00000003                  | 3                       | ESME_RINVCMDID          | Command ID is invalid or not supported             |
| 0x00000004                  | 4                       | ESME_RINVBNDSTS         | Incorrect bind status for given command            |
| 0x00000005                  | 5                       | ESME_RALYBND            | Already bound                                      |
| 0x00000006                  | 6                       | ESME_RINVPRTFLG         | Invalid Priority Flag                              |
| 0x00000007                  | 7                       | ESME_RINVREGDLVFLG      | Invalid registered delivery flag                   |
| 0x00000008                  | 8                       | ESME_RSYSERR            | System error                                       |
| 0x00000009                  | 9                       | Reserved                |                                                    |
| 0x0000000A                  | 10                      | ESME_RINVSRCADR         | Invalid source address                             |
| 0x0000000B                  | 11                      | ESME_RINVDSTADR         | Invalid destination address                        |
| 0x0000000C                  | 12                      | ESME_RINVMSGID          | Message ID is invalid                              |
| 0x0000000D                  | 13                      | ESME_RBINDFAIL          | Bind failed                                        |
| 0x0000000E                  | 14                      | ESME_RINVPASWD          | Invalid password                                   |
| 0x0000000F                  | 15                      | ESME_RINVSYSID          | Invalid System ID                                  |
| 0x00000010                  | 16                      | Reserved                |                                                    |
| 0x00000011                  | 17                      | ESME_RCANCELFAIL        | Canceling message failed                           |
| 0x00000012                  | 18                      | Reserved                |                                                    |
| 0x00000013                  | 19                      | ESME_RREPLACEFAIL       | Message replacement failed                         |
| 0x00000014                  | 20                      | ESME_RMSSQFUL           | Message queue full                                 |
| 0x00000015                  | 21                      | ESME_RINVSERTYP         | Invalid service type                               |
| 0x00000033                  | 51                      | ESME_RINVNUMDESTS       | Invalid number of destinations                     |
| 0x00000034                  | 52                      | ESME_RINVDLNAME         | Invalid distribution list name                     |
| 0x00000040                  | 64                      | ESME_RINVDESTFLAG       | Invalid destination flag                           |
| 0x00000041                  | 65                      | Reserved                |                                                    |
| 0x00000042                  | 66                      | ESME_RINVSUBREP         | Invalid submit with replace request                |
| 0x00000043                  | 67                      | ESME_RINVESMCLASS       | Invalid esm class set                              |
| 0x00000044                  | 68                      | ESME_RCNTSUBDL          | Invalid submit to distribution list                |
| 0x00000045                  | 69                      | ESME_RSUBMITFAIL        | Submitting message has failed                      |
| 0x00000046                  | 70                      | Reserved                |                                                    |
| 0x00000047                  | 71                      | Reserved                |                                                    |
| 0x00000048                  | 72                      | ESME_RINVSRCTON         | Invalid source address type of number ( TON )      |
| 0x00000049                  | 73                      | ESME_RINVSRCNPI         | Invalid source address numbering plan ( NPI )      |
| 0x00000050                  | 80                      | ESME_RINVDSTTON         | Invalid destination address type of number ( TON ) |
| 0x00000051                  | 81                      | ESME_RINVDSTNPI         | Invalid destination address numbering plan ( NPI ) |
| 0x00000052                  | 82                      | Reserved                |                                                    |
| 0x00000053                  | 83                      | ESME_RINVSYSTYP         | Invalid system type                                |
| 0x00000054                  | 84                      | ESME_RINVREPFLAG        | Invalid replace_if_present flag                    |
| 0x00000055                  | 85                      | ESME_RINVNUMMSGS        | Invalid number of messages                         |
| 0x00000056                  | 86                      | Reserved                |                                                    |
| 0x00000057                  | 87                      | Reserved                |                                                    |
| 0x00000058                  | 88                      | ESME_RTHROTTLED         | Throttling error                                   |
| 0x00000059                  | 89                      | Reserved                |                                                    |
| 0x00000060                  | 96                      | Reserved                |                                                    |
| 0x00000061                  | 97                      | ESME_RINVSCHED          | Invalid scheduled delivery time                    |
| 0x00000062                  | 98                      | ESME_RINVEXPIRY         | Invalid Validity Period value                      |
| 0x00000063                  | 99                      | ESME_RINVDFTMSGID       | Predefined message not found                       |
| 0x00000064                  | 100                     | ESME_RX_T_APPN          | ESME Receiver temporary error                      |
| 0x00000065                  | 101                     | ESME_RX_P_APPN          | ESME Receiver permanent error                      |
| 0x00000066                  | 102                     | ESME_RX_R_APPN          | ESME Receiver reject message error                 |
| 0x00000067                  | 103                     | ESME_RQUERYFAIL         | Message query request failed                       |
| 0x00000068-0x000000BF       | 104 -191                | Reserved                |                                                    |
| 0x000000C0                  | 192                     | ESME_RINVTLVSTREAM      | Error in the optional part of the PDU body         |
| 0x000000C1                  | 193                     | ESME_RTLVNOTALLWD       | TLV not allowed                                    |
| 0x000000C2                  | 194                     | ESME_RINVTLVLEN         | Invalid parameter length                           |
| 0x000000C3                  | 195                     | ESME_RMISSINGTLV        | Expected TLV missing                               |
| 0x000000C4                  | 196                     | ESME_RINVTLVVAL         | Invalid TLV value                                  |
| 0x000000C5-0x000000FD       | 197 -253                | Reserved                |                                                    |
| 0x000000FE                  | 254                     | ESME_RDELIVERYFAILURE   | Transaction delivery failure                       |
| 0x000000FF                  | 255                     | ESME_RUNKNOWNERR        | Unknown error                                      |
| 0x00000100                  | 256                     | ESME_RSERTYPUNAUTH      | ESME not authorised to use specified servicetype   |
| 0x00000101                  | 257                     | ESME_RPROHIBITED        | ESME prohibited from using specified operation     |
| 0x00000102                  | 258                     | ESME_RSERTYPUNAVAIL     | Specified servicetype is unavailable               |
| 0x00000103                  | 259                     | ESME_RSERTYPDENIED      | Specified servicetype is denied                    |
| 0x00000104                  | 260                     | ESME_RINVDCS            | Invalid data coding scheme                         |
| 0x00000105                  | 261                     | ESME_RINVSRCADDRSUBUNIT | Invalid source address subunit                     |
| 0x00000106                  | 262                     | ESME_RINVSTDADDRSUBUNIR | Invalid destination address subunit                |
| 0x00000400-                 | 1024 -                  |                         | Operator specific error codes                      |



------



### Fork de:

 https://github.com/rayed/smpp_php
