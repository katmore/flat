<?php
/**
 * File:
 *    cafile.php
 * 
 * Purpose:
 *    public cert for client to provide to mongo server
 * 
 * @package flat/db/mongo
 * @link http://php.net/manual/en/mongo.connecting.ssl.php
 */
namespace flat\db\mongo\ssl;
abstract class localcert extends cafile {
   abstract protected function _get_passphrase();
}