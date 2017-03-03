<?php
/**
 * \flat\core\event\triggerable class 
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
 * event designed for outside processes to trigger occurances of
 * 
 * @package    flat\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class triggerable extends \flat\core\event {
   public function __construct($event_name,callable $handler=NULL,$data=NULL) {
      
      $this->set_params($event_name, $handler,$data);
      
   }
   public function trigger($trigger_data=NULL) {

      $this->call($trigger_data);

   }   
}