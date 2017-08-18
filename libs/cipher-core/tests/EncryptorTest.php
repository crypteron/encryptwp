<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';

class EncryptorTest extends \PHPUnit\Framework\TestCase {
  private $encryptor;

  public function provider() {
    $testVectorsJson = file_get_contents(__DIR__  . "/test-encryptor-data.json");
    $testVectors = json_decode($testVectorsJson, true);

    $convertTestVector = function($testVector) {
      $expectedCiphertext = $testVector['ciphertext'];
      $encryptParameters = new EncryptParameters();
      $encryptParameters->plaintext = $testVector['plaintext'];
      $encryptParameters->key = hex2bin($testVector['key']);
      $encryptParameters->iv = hex2bin($testVector['iv']);
      $encryptParameters->aad = hex2bin($testVector['aad']);
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
