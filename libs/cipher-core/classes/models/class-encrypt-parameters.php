<?php
namespace CipherCore\v1;

/**
 * Class EncryptParameters
 * @package CipherCore\v1
 */
class EncryptParameters {
  /**
   * @var string | null
   */
    public $plaintext = NULL;

  /**
   * @var string
   */
    public $key;
   
   /**
   * @var string
   */
    public $iv;
   
   /**
   * @var string | null
   */
    public $aad = NULL;
}
