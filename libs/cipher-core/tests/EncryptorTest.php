<?php
namespace CipherCore\v1;
require_once 'cipher-core.php';

class EncryptorTest extends \PHPUnit\Framework\TestCase {
  private $plaintext;
  private $key;
  private $iv;
  private $aad;
  private $ciphertext;
  private $encryptor;
  
  function setUp() {
    $this->plaintext = '6015-6956-8952-4805';
    $this->key = base64_decode('/v/pkoZlcxxtao+UZzCDCP7/6ZKGZXMcbWqPlGcwgwg=');
    $this->iv = base64_decode('F+H5RP3jw9CiEWuL');
    $this->aad = base64_decode('d5BcHFfsYJ4MTR8fXX+Ilg==');
    $this->ciphertext = 'zbMAAAIAGBfh+UT948PQohFriwIgd5BcHFfsYJ4MTR8fXX+IlgAAyMKitXfSPcxzQHL9xCYazC50p9qW0BkIjL556teZiaw9I7w=';
    $this->encryptor = new Encryptor();
  }

  public function testEncrypt() {
    $actualEncrypted = $this->encryptor->encryptWithParameters(
      $this->plaintext, $this->key, $this->iv, $this->aad, true
    );
    $this->assertEquals($this->ciphertext, $actualEncrypted);
  }

  public function testDecrypt() {
    $actualDecrypted = $this->encryptor->decryptWithParameters(
      $this->ciphertext, $this->key, true
    );
    $this->assertEquals($this->plaintext, $actualDecrypted);
  }
}
?>