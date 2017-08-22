<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';
require_once 'tests/test-utils.php';

class EncryptorTest extends \PHPUnit\Framework\TestCase {
  /**
   * @var \CipherCore\v1\Encryptor
   */
  private $encryptor;
  /**
   * @var string
   */
  private $plaintext;
  /**
   * @var string
   */
   private $aad;

  public function provider() {
    $testVectorsJson = file_get_contents(__DIR__  . '/test-encryptor-data.json');
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

  public static function setUpBeforeClass() {
    $keyClient = new Key_Server_Client();
    define('CIPHER_CORE_KEY', $keyClient->generate_key());
    define('CIPHER_CORE_TOKEN_KEY', $keyClient->generate_key());
  }
  
  function setUp() {
    $this->encryptor = new Encryptor();
    $this->plaintext = "6015-6956-8952-4805";
    $this->aad = "user_1";
  }

    /**
    * @dataProvider provider
    */
  public function testEncryptWithParameters($expectedCiphertext, $encryptParameters) {
    $actualEncrypted = $this->encryptor->encryptWithParameters($encryptParameters, true);
    $this->assertEquals($expectedCiphertext, $actualEncrypted);
    if($encryptParameters->searchable) {
      $searchPrefix = $this->encryptor->getSearchPrefixForParameters($encryptParameters, true);
      $this->assertStringStartsWith($searchPrefix, $actualEncrypted);
    }
  }

    /**
    * @dataProvider provider
    */
  public function testDecryptWithParameters($expectedCiphertext, $encryptParameters) {
    $decryptParameters = new DecryptParameters();
    $decryptParameters->ciphertext = $expectedCiphertext;
    $decryptParameters->key = $encryptParameters->key;
    $actualDecrypted = $this->encryptor->decryptWithParameters($decryptParameters, true);
    $this->assertEquals($encryptParameters->plaintext, $actualDecrypted);
  }

  public function testEncryptDecryptLifecycleWithNoAadNotSearchable() {
    $ciphertext = $this->encryptor->encrypt($this->plaintext);
    $actualDecrypted = $this->encryptor->decrypt($ciphertext);
    $this->assertEquals($this->plaintext, $actualDecrypted);
    $searchPrefix = $this->encryptor->getSearchPrefix($this->plaintext);
    $this->assertStringStartsNotWith($searchPrefix, $ciphertext);
  }

  public function testEncryptDecryptLifecycleWithAadNotSearchable() {
    $ciphertext = $this->encryptor->encrypt($this->plaintext, $this->aad);
    $actualDecrypted = $this->encryptor->decrypt($ciphertext);
    $this->assertEquals($this->plaintext, $actualDecrypted);
    $searchPrefix = $this->encryptor->getSearchPrefix($this->plaintext);
    $this->assertStringStartsNotWith($searchPrefix, $ciphertext);
  }
  
  public function testEncryptDecryptLifecycleWithNoAadSearchable() {
    $ciphertext = $this->encryptor->encrypt($this->plaintext, NULL, true);
    $actualDecrypted = $this->encryptor->decrypt($ciphertext);
    $this->assertEquals($this->plaintext, $actualDecrypted);
    $searchPrefix = $this->encryptor->getSearchPrefix($this->plaintext);
    $this->assertStringStartsWith($searchPrefix, $ciphertext);
  }

  public function testEncryptDecryptLifecycleWithAadSearchable() {
    $ciphertext = $this->encryptor->encrypt($this->plaintext, $this->aad, true);
    $actualDecrypted = $this->encryptor->decrypt($ciphertext);
    $this->assertEquals($this->plaintext, $actualDecrypted);
    $searchPrefix = $this->encryptor->getSearchPrefix($this->plaintext);
    $this->assertStringStartsWith($searchPrefix, $ciphertext);
  }
}
