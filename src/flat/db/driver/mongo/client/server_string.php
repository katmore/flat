<?php
/**
 * \flat\db\driver\mongo\client\server_string interface 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\mongo\client;
/**
 * connection options
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @link http://php.net/manual/en/mongoclient.construct.php
 */
interface server_string {
   /**
    * @return string
    */
   public function get_client_server_string();
}