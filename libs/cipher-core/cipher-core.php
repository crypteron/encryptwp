<?php
namespace CipherCore\v1;

// Libraries
// Avro
if(!class_exists('Avro', false)){
	require_once 'lib/avro.php';
}

// To do: create auto loader

// Interfaces
require_once 'interfaces/interface-key-server-client.php';

// Classes
require_once 'classes/class-encryptor.php';
require_once 'classes/class-key-server-client.php';
require_once 'classes/class-serializer.php';

// Models
require_once 'classes/models/class-cell-attribute.php';
require_once 'classes/models/class-cipher-core-exception.php';
require_once 'classes/models/class-cipher-core-header.php';
require_once 'classes/models/class-cipher-core-header-container.php';
require_once 'classes/models/class-cipher-suite.php';
require_once 'classes/models/class-constants.php';

