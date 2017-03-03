<?php
/**
 * \flat\cloud\aws class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud;
/**
 * \flat\social\aws parent class, contains shared controller functionality
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class aws extends \flat\core\mappable {
   private $_client=null;
   /**
    * @return string 
    */
   abstract protected function _get_client_class();
   /**
    * set current AWS client object
    * 
    * @param mixed $client
    * @return void
    * @throws \flat\cloud\aws\bad_client given client object has problem
    */   
   protected function _set_client($client) {
      $client_class = $this->_get_client_class();
      if (!is_a($client,$client_class)) {
         throw new aws\bad_client(
            "did not get $client_class result using interface".
            " \\flat\\cloud\\aws\\client_provider".
            " class \\".get_called_class()."::get_aws_client()"
         );
      }
      $this->_client = $client;
   }
   /**
    * provides AWS client object, attempts to derive from interface if client
    *    property empty. 
    * 
    * @see _set_client() for potential exceptions
    */
   protected function _get_client() {
      if ($this->_client!==null) {
         return $this->_client;
      }
      if ($this instanceof aws\client_provider) {
         $client = $this->get_aws_client();
         $this->_set_client($client);
         return $client;
      } else {
         throw new aws\client_unavailable(
            "no client set, and no client provided by interface"
         );
      } 
   }
}












