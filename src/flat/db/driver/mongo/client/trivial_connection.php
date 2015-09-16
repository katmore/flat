<?php
/**
 * \flat\db\driver\mongo\client\trivial_connection interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\mongo\client;
/**
 * connection only needs hostname:port
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface trivial_connection {

   /**
    * client hostname definition for concatonating trivial connection string
    * @return string|string[]
    */
   public function get_client_host();
   /**
    * client port definition for concatonating trivial connection string
    * @return string|int|string[]|int[]
    */   
   public function get_client_port();
}