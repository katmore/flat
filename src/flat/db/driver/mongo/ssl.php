<?php
/**
 * File:
 *    ssl.php
 * 
 * Purpose:
 *    indicate to controller that mongo connection is ssl
 * 
 * 
 * @package flat/db/mongo
 * @link http://php.net/manual/en/mongo.connecting.ssl.php
 */
namespace flat\db\driver\mongo;
interface ssl {
   public function get_cafile();
   public function get_localcert();
}