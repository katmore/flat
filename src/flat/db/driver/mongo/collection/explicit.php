<?php
/**
 * \flat\db\driver\mongo\collection\explicit interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\mongo\collection;
/**
 * interface to explicitly specify collection
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface explicit {
   /**
    * retrieves name of mongo collection in context for mongo db controller
    * 
    * @return string
    * 
    * @see \flat\db\driver\mongo\collection\explicit part of the explicit mongo collection interface
    * @see \flat\db\driver\mongo::get_collection()
    */
   public function get_mongo_collection();
}