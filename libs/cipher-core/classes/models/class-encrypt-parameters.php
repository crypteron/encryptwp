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
    public $plaintext = null;

  /**
   * @var string | null
   */
    public $key;
   
   /**
   * @var string | null
   */
    public $iv;
   
   /**
   * @var string | null
   */
    public $aad;
}
