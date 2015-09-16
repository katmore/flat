<?php
/**
 * File:
 *    base64
 * 
 * Purpose:
 *    path to cafile on local filesystem
 * 
 * @package flat/db/mongo
 * @link http://php.net/manual/en/mongo.connecting.ssl.php
 */
namespace flat\db\mongo\ssl\cafile;
abstract class file extends \flat\db\mongo\ssl\cafile {
   abstract protected function _get_cafile_path();
   final public function __construct() {
      if ($this->ca = file_get_contents($this->__get_cafile_path())) {
         $this->file = $this->__get_cafile_path();
         return;
      }
   }
}