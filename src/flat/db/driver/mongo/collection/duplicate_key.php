<?php
/**
 * \flat\db\driver\mongo\collection\duplicate_key definition 
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
 * mongo collection not found exception
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class duplicate_key extends \flat\db\op\exception\duplicate_key {
   public function get_namespace_label() {
      return "collection";
   }
}