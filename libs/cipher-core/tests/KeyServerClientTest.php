<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';

class KeyServerClientTest extends \PHPUnit\Framework\TestCase {

  /**
   * @var IKeyServerClient
   */
   public $keyServerClient;
  
  function setUp() {
    $this->keyServerClient = new Key_Server_Client();
  }

  public function testGenerateKey256() {
    $generatedKey = $this->keyServerClient->generate_key();
    $decodedKey = base64_decode($generatedKey);
    $this->assertEquals(Constants::AES_256_KEY_SIZE_BYTES, mb_strlen($decodedKey, '8bit'));
  }

  public function testGenerateKey128() {
    $generatedKey = $this->keyServerClient->generate_key(false);
    $decodedKey = base64_decode($generatedKey);
    $this->assertEquals(Constants::AES_128_KEY_SIZE_BYTES, mb_strlen($decodedKey, '8bit'));
  }

  public function testGenerateKeyIsRandom() {
    $firstKey = $this->keyServerClient->generate_key();
    $secondKey = $this->keyServerClient->generate_key();
    $this->assertNotEquals($firstKey, $secondKey);
  }
}