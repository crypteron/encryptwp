<?php
namespace CipherCore\v1;
require_once '../cipher-core.php';

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
    $this->assertEquals($serializedHeader, $expectedHeader);
  }

  /**
    * @dataProvider provider
    */
  public function testDeserialize($expectedHeader, $originalHeader) {

    $deserializedHeader = $this->serializer->deserialize($expectedHeader);
    $this->assertEquals($deserializedHeader->bytesRead, mb_strlen($expectedHeader, '8bit'));
    $this->assertEquals((array)$deserializedHeader->header, (array)$originalHeader);
  }

  public function provider() {
    $testHeadersJson = file_get_contents(__DIR__  . "/test-header-data.json");
    $testHeaders = json_decode($testHeadersJson);

    $convertTestHeader = function($testHeader) {
      $expectedHeader = base64_decode($testHeader->headerBytes);
      $originalHeader = new CipherCore_Header();
      foreach($testHeader->header as $key => $value) {
        if(is_string($value)) {
          $originalHeader->$key = base64_decode($value);
        } else {
          $originalHeader->$key = $value;
        }
      }
      return array('expectedHeader' => $expectedHeader, 'originalHeader' => $originalHeader);
    };

    return array_map($convertTestHeader, $testHeaders);
  }
}
?>