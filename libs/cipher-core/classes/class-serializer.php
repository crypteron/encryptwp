<?php
namespace CipherCore\v1;

class Serializer {

	/**
	 * @const string
	 */
	const MAGIC_BLOCK = "\xCD\xB3";

	/**
	 * @var \AvroSchema
	 */
	private $headerSchema;

	public function __construct() {
		$avprSchemaJson = file_get_contents(__DIR__ .'/../assets/header-v3.avpr');
		$avprSchema = json_decode($avprSchemaJson, true);
		$schemata = new \AvroNamedSchemata();
		foreach($avprSchema['types'] as $headerSchema) {
			$this->headerSchema = \AvroSchema::real_parse($headerSchema, null, $schemata);
		}
	}

	/**
	 * Deserialize a header into a CipherCore_Header object and return a container with it and the bytes read
	 *
	 * @param $serialized_header string
	 *
	 * @return CipherCore_Header_Container
	 * @throws CipherCore_Exception
	 */
	public function deserialize($serialized_header) {
		$read = new \AvroStringIO($serialized_header);
		$magicBlockLength = mb_strlen(self::MAGIC_BLOCK, '8bit');
		$actualMagicBlock = $read->read($magicBlockLength);
		if($actualMagicBlock !== self::MAGIC_BLOCK){
			throw new CipherCore_Deserialize_Exception("Header magic block doesn't match");
		}

		$decoder = new \AvroIOBinaryDecoder($read);
		$reader = new \AvroIODatumReader($this->headerSchema);
		$deserializedHeader = new CipherCore_Header_Container();
		$headerDto = $reader->read($decoder);
		$deserializedHeader->header    = $this->avro_object_to_header($headerDto);
		$deserializedHeader->bytesRead = $read->tell();
		return $deserializedHeader;
	}

	/**
	 * Serialize a CipherCore_Header object into a string based on the Avro schema
	 * @param $header CipherCore_Header
	 *
	 * @return string
	 */
	public function serialize($header) {
		$io = new \AvroStringIO();
		$writer = new \AvroIODatumWriter($this->headerSchema);
		$encoder = new \AvroIOBinaryEncoder($io);
		$io->write(self::MAGIC_BLOCK);
		$headerDto = $this->header_to_avro_object($header);
		$writer->write($headerDto, $encoder);
		return $io->string();
	}

	/**
	 * Convert an Avro Object to a CipherCore Header
	 * @param $headerDto array
	 *
	 * @return CipherCore_Header
	 */
	public function avro_object_to_header($headerDto){
		$header = new CipherCore_Header();
		foreach($headerDto as $key => $value) {
			$header->$key = $value;
		}
		return $header;
	}

	/**
	 * Convert a CipherCore Header to an Avro Object
	 * @param $header CipherCore_Header
	 *
	 * @return array
	 */
	public function header_to_avro_object($header){
		$headerDto = (array)$header;
		if($headerDto['DekInfo']) {
			$headerDto['DekInfo'] = (array)$headerDto['DekInfo'];
		}
		return $headerDto;
	}

}
