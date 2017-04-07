<?php
/**
 * \flat\cloud\aws\client_provider\from_config class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud\aws\client_provider;
/**
 * from_config
 * 
 * @package    flat\cloud\aws
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class from_config  {
   private $_client;
   public function get_client() {
      return $this->_client;
   }
   /**
    * retrieve an AWS client
    * @param string $config_ns config namespace, 
    *    ex: "app/db/myapp/my/package"
    *  
    * @return mixed
    */
   abstract protected function _client_from_config($config_ns);
   /**
    * @param string $config_ns config namespace, 
    *    ex: "app/db/myapp/my/package"
    */
   final public function __construct($config_ns) {
      $this->_client = $this->_client_from_config($config_ns);
   }
}


















