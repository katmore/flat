<?php
/**
 * \flat\core\event\factory\consumer interface 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\event\factory;
/**
 * indicate implementing class wants an event factory to play with
 * 
 * @package    flat\core\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface consumer {
   public function set_event_factory(\flat\core\event\factory &$factory); 
}








