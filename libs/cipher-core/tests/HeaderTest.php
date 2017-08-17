<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';

function convertHexField($field) {
  if($field === NULL) {
    return NULL;
  } else {
    return hex2bin($field);
  }
}

class HeaderTest extends \PHPUnit\Framework\TestCase {

  protected $serializer;
  
  function setUp() {
    $this->serializer = new Serializer();
  }

  /**
    * @dataProvider provider
    */
  public function testSerialize($expectedHeader, $originalHeader) {
    $serializedHeader = $this->serializer->serialize($originalHeader);
    $this->assertEquals($expectedHeader, $serializedHeader);
  }

  /**
    * @dataProvider provider
    */
  public function testDeserialize($expectedHeader, $originalHeader) {
    $deserializedHeader = $this->serializer->deserialize($expectedHeader);
    $this->assertEquals(mb_strlen($expectedHeader, '8bit'), $deserializedHeader->bytesRead);
    $expectedDto = $this->serializer->header_to_avro_object($originalHeader);
    $actualDto = $this->serializer->header_to_avro_object($deserializedHeader->header);
    $this->assertEquals($expectedDto, $actualDto);
  }

  public function provider() {
    $testHeadersJson = file_get_contents(__DIR__  . "/test-header-data.json");
    $testHeaders = json_decode($testHeadersJson, true);

    $convertTestHeader = function($testHeader) {
      $expectedHeader = convertHexField($testHeader['headerBytes']);
      $headerDto = $testHeader['header'];
      $originalHeader = new CipherCore_Header();
      $originalHeader->Token = convertHexField($headerDto['Token']);
      $originalHeader->SecPartId = $headerDto['SecPartId'];
      $originalHeader->SecPartVer = $headerDto['SecPartVer'];
      $originalHeader->CipherSuite = $headerDto['CipherSuite'];
      $originalHeader->IV = convertHexField($headerDto['IV']);
      $originalHeader->AAD = convertHexField($headerDto['AAD']);
      $originalHeader->CellAttributes = $headerDto['CellAttributes'];
      if($headerDto['DekInfo']) {
        $DekInfo = $headerDto['DekInfo'];
        $originalHeader->DekInfo = new DekInfo();
        $originalHeader->DekInfo->DekCipherSuite = $DekInfo['DekCipherSuite'];
        $originalHeader->DekInfo->DekIV = convertHexField($DekInfo['DekIV']);
        $originalHeader->DekInfo->DekEnc = convertHexField($DekInfo['DekEnc']);
      }
      return array('expectedHeader' => $expectedHeader, 'originalHeader' => $originalHeader);
    };

    return array_map($convertTestHeader, $testHeaders);
  }
}
?>