<?php
namespace CipherCore\v1;

/**
 * Class SecPartVer
 * @package CipherCore\v1
 * Constants for different security partitions
 */
class SecPartVer {
  /*
  * ***** CipherCore.Test.KeyManagement.SecPartVerTest.PrintAllSecPartVers
  *  Reserved Count: 20
  *  First ....... : 1
  *  MaxSecPartVer : 2147483627
  *  Tokenization  : 2147483628
  *  Latest ...... : 2147483646
  *  All ......... : 2147483647
  */

  /**
   * Basic and Legacy
   */
  const First = 1;

  const MaxSecPartVer = self::All - self::ReservedCount;
  
  // RESERVED KEYS STARTS HERE
  // SecPart Versions below this have special implicity meaning
  // while above these are regularly rolled over keys
  const ReservedCount = 20;
  const Tokenization = self::MaxSecPartVer + 1;

  // IMPLICIT MEANINGS (not keys) STARTS HERE
  /// <summary>
  /// Latest is defined from the administrators/absolute sense,
  /// NOT latest based on a role and RBAC
  /// </summary>
  const Latest = self::All - 1;

  // Would use PHP_INT_MAX, but it can be 64 bit
  const All = 2 ** 31 - 1;
}
