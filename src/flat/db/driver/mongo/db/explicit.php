<?php
/**
 * \flat\db\driver\mongo\db\explicit interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\mongo\db;
/**
 * database is defined explicitly
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface explicit {
   /**
    * defines default mongo database
    * @return string
    */
   public function get_client_db();
}