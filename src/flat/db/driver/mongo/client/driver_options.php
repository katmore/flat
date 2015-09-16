<?php
/**
 * \flat\db\driver\mongo\client\driver_options interface 
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
 * connection driver options
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @link http://php.net/manual/en/mongoclient.construct.php
 */
interface driver_options {
   /**
    * @return array
    */
   public function get_client_driver_options();
}