<?php
namespace CipherCore\v1;

if(class_exists('CipherCore', false)){
	return;
}

// Libraries
// Avro
if(!class_exists('Avro', false)){
	require_once 'lib/avro.php';
}

// To do: create auto loader

// Composer
require_once 'vendor/autoload.php';

// Interfaces
require_once 'interfaces/interface-key-server-client.php';

// Classes
require_once 'classes/class-encryptor.php';
require_once 'classes/class-key-server-client.php';
require_once 'classes/class-serializer.php';

// Models
require_once 'classes/models/class-cell-attribute.php';
require_once 'classes/models/class-cipher-core-header.php';
require_once 'classes/models/class-cipher-core-header-container.php';
require_once 'classes/models/class-cipher-suite.php';
require_once 'classes/models/class-constants.php';
require_once 'classes/models/class-decrypt-parameters.php';
require_once 'classes/models/class-dek-info.php';
require_once 'classes/models/class-encrypt-parameters.php';
require_once 'classes/models/class-read-key-request.php';
require_once 'classes/models/class-sec-part-ver.php';
require_once 'classes/models/class-settings.php';

// Exceptions
require_once 'classes/exceptions/class-cipher-core-exception.php';
require_once 'classes/exceptions/class-cipher-core-deserialize-exception.php';
require_once 'classes/exceptions/class-cipher-core-aad-exception.php';

// Container class.
class CipherCore{
	const VERSION = '1.0.0';
}