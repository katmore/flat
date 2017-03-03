<?php
/**
 * \flat\core\event\listener class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\event;
/**
 * listener class
 *    
 * 
 * @package    flat\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @todo evaluate if this is used / needed anywhere at all. suspect it is not
 * 
 * @abstract
 */
abstract class listener{
   abstract protected function callback($data,$event_data);
   public function __construct($data,$event_data) {
      $this->callback($data, $event_data);
   }
}