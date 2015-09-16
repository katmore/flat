<?php
/**
 * File:
 *    base64
 * 
 * Purpose:
 *    store cafile as string literal
 * 
 * @package flat/db/mongo
 * @link http://php.net/manual/en/mongo.connecting.ssl.php
 */
namespace flat\db\mongo\ssl\cafile;
abstract class base64 extends \flat\db\mongo\ssl\cafile {
   abstract protected function _get_cafile_base64();
   final public function __construct() {
      $this->ca = $this->_get_cafile_base64();
   }
}