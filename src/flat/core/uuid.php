<?php
/**
 * \flat\core\uuid class definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * BY ACCESSING THE CONTENTS OF THIS SOURCE FILE IN ANY WAY YOU AGREE TO BE 
 * BOUND BY THE PROVISIONS OF THE "SOURCE FILE ACCESS AGREEMENT", A COPY OF 
 * WHICH CAN IS FOUND IN THE FILE 'LICENSE.txt'.
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core;
/**
 * Generates RFC 4211 compliant Universally Unique Identifiers (UUID);
 *    versions 3, 4 and 5. 
 *    callable class with magic method __invoke alias of \flat\core\uuid::get()
 *    
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 */
class uuid implements \flat\core\generator {
   /**
    * generates RFC 4211 compliant version 3 UUID
    * @return string
    * @param string $namespace
    * @param string $name
    */
  public static function get_v3($namespace, $name) {
    self::check_namespace($namespace);

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = md5($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 3
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }
   /**
    * generates RFC 4211 compliant version 4 UUID
    * @return string
    */
  public static function get_v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }
  /**
   * generates RFC 4211 compliant version 5 UUID
   * @return string
   * @param string $namespace
   * @param string $name
   */
  public static function get_v5($namespace, $name) {
    self::check_namespace($namespace);

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = sha1($nstr . $name);

    return sprintf('%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  private static function check_namespace($uuid) {
      if (!is_string($uuid)) {
         throw new uuid\exception\invalid_namespace("must be a string");
      }     
      if (empty($uuid)) {
         throw new uuid\exception\invalid_namespace("cannot be empty");
      }
    if ( preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid)!==1) {
      throw new uuid\exception\invalid_namespace("is not valid rfc 4122 UUID");
    } 
  }
  
  /**
   * generates RFC 4211 compliant UUID versions 3, 4, or 5
   * @return string
   * @param int | array $param denotes UUID version if integer value,
   *     if array, denotes parameters in an assoc array...
   *     $param['version'] : UUID version.
   *     $param['namespace'] : UUID 3 or 5 namespace.
   *     $param['name'] : UUID 3 or 5 name.
   * @param string $namespace (optional) UUID versions 3 or 5 namespace. ignored if $version is array.
   * @param string $name (optional) UUID versions 3 or 5 name. ignored if $version is array.
   */
  public static function get($param=4,$namespace=null,$name=null) {
     if (!is_array($param)) {
        $version = $param;
        $param =[];
        $param['version'] = $version;
        $param['namespace'] = $namespace;
        $param['name'] = $name;
     }
     
     $p = array(
         'namespace'=>"",
         'name'=>"",
         'version'=>5,
     );
     foreach ($p as $k=>&$v) if (isset($param[$k])) $v = $param[$k];
     //var_dump($p);
     if (!empty($p['namespace']) || !empty($p['name'])) {
        if ($p['version']==3) {
           $uuid = self::get_v3($p['namespace'], $p['name']);
        } else
        if ($p['version']==5) {
           $uuid = self::get_v5($p['namespace'], $p['name']);
        } else {
           if ($p['version']==4) {
              throw new uuid\exception\namespace_unused_version4();
           } else {
               throw new uuid\exception\invalid_version();
           }
        }
         
      } else {
         if (empty($p['version']) || $p['version']==4 || empty($p['namespace'])) {
            $uuid = uuid::get_v4();
         } else {
            throw new uuid\exception\invalid_version();
         }
      }
      return $uuid;
  }
  /**
   * callable class using magic method __invoke()...
   * generates RFC 4211 compliant UUID versions 3, 4, or 5.
   *
   * @param int | array $version denotes UUID version if integer value,
   *     if array, denotes parameters in an assoc array like in uuid::get().
   * @param string $namespace (optional) UUID versions 3 or 5 namespace. ignored if $version is array.
   * @param string $name (optional) UUID versions 3 or 5 name. ignored if $version is array.
   *
   * @see \flat\core\uuid::get()
   */
  public function __invoke($version=4,$namespace=null,$name=null) {
     return self::get($version=4,$namespace=null,$name=null);
  }
  
  private $_version;
  private $_namespace;
  private $_name;
  /**
   * generates an RFC 4211 compliant UUID
   * 
   * @return string
   */
  public function generate() {
     return self::get($this->_version,$this->_namespace,$this->_name);
  }
  /**
   * prepares for generating RFC 4211 compliant UUIDs, versions 3, 4, or 5.
   * 
   * @param int  $version UUID version, valid values are 3, 4, or 5.
   * @param string $namespace (optional) UUID versions 3 or 5 namespace. ignored if $version is array.
   * @param string $name (optional) UUID versions 3 or 5 name. ignored if $version is array.
   * 
   * @see \flat\core\uuid::get()
   */
  public function __construct($version=4,$namespace=null,$name=null) {
     /*
      * sanity check by generating once to trigger any errors
      */
     self::get($version,$namespace,$name);
     /*
      * set values for later
      */
     $this->_version = $version;
     $this->_name = $name;
     $this->_namespace = $namespace;
  }
  public function __get() {
     return (string) self::get($this->_version,$this->_namespace,$this->_name);
  }
}












