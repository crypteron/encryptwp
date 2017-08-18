<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';
require_once 'tests/test-utils.php';

class EncryptorTest extends \PHPUnit\Framework\TestCase {
  private $encryptor;

  public function provider() {
    $testVectorsJson = file_get_contents(__DIR__  . "/test-encryptor-data.json");
    $testVectors = json_decode($testVectorsJson, true);

    $convertTestVector = function($testVector) {
      $expectedCiphertext = $testVector['ciphertext'];
      $encryptParameters = new EncryptParameters();
      $encryptParameters->plaintext = convertHexField($testVector['plaintext']);
      $encryptParameters->key = convertHexField($testVector['key']);
      $encryptParameters->iv = convertHexField($testVector['iv']);
      $encryptParameters->aad = convertHexField($testVector['aad']);
      if(array_key_exists('searchable', $testVector)) {
        $encryptParameters->searchable = $testVector['searchable'];
        $encryptParameters->tokenKey = convertHexField($testVector['tokenKey']);
      }
      return array('expectedCiphertext' => $expectedCiphertext, 'encryptParameters' => $encryptParameters);
    };

    return array_map($convertTestVector, $testVectors);
  }
  
  function setUp() {
    $this->encryptor = new Encryptor();
  }

    /**
    * @dataProvider provider
    */
  public function testEncrypt($expectedCiphertext, $encryptParameters) {
    $actualEncrypted = $this->encryptor->encryptWithParameters($encryptParameters, true);
    $this->assertEquals($expectedCiphertext, $actualEncrypted);
    if($encryptParameters->searchable) {
      $searchPrefix = $this->encryptor->searchPrefixForParameters($encryptParameters, true);
      $this->assertStringStartsWith($searchPrefix, $actualEncrypted);
    }
  }

    /**
    * @dataProvider provider
    */
  public function testDecrypt($expectedCiphertext, $encryptParameters) {
    $decryptParameters = new DecryptParameters();
    $decryptParameters->ciphertext = $expectedCiphertext;
    $decryptParameters->key = $encryptParameters->key;
    $actualDecrypted = $this->encryptor->decryptWithParameters($decryptParameters, true);
    $this->assertEquals($encryptParameters->plaintext, $actualDecrypted);
  }
}
