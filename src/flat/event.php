<?php
/**
 * \flat\event class definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
 * ALL RIGHTS RESERVED. THIS COPYRIGHT APPLIES TO THE ENTIRE CONTENTS OF THE WORKS HEREIN
 * UNLESS A DIFFERENT COPYRIGHT NOTICE IS EXPLICITLY PROVIDED WITH AN EXPLANATION OF WHERE
 * THAT DIFFERENT COPYRIGHT APPLIES. WHERE SUCH A DIFFERENT COPYRIGHT NOTICE IS PROVIDED
 * IT SHALL APPLY EXCLUSIVELY TO THE MATERIAL AS DETAILED WITHIN THE NOTICE.
 * 
 * The flat framework is copyrighted free software.
 * You can redistribute it and/or modify it under either the terms and conditions of the
 * "The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
 * of the "GPL v3 License" (see the file GPL-LICENSE.txt).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @license The MIT License (MIT) http://opensource.org/licenses/MIT
 * @license GNU General Public License, version 3 (GPL-3.0) http://opensource.org/licenses/GPL-3.0
 * @link https://github.com/katmore/flat
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat;
/**
 * provides access to single event
 * 
 * @package    flat\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class event implements 
   \flat\core\app, 
   \flat\event\triggerable,
   \flat\event\listenable
{
   private static $_event=NULL;
   private static function _event() {
      if (self::$_event===NULL) {
         self::$_event = new \flat\core\event\factory();
      }
      return self::$_event;
   }
   
   /**
    * sets a data keyval for event
    * 
    * @param string $key data key to set
    * @param mixed $val data value
    * @param bool $no_overwrite if true, will only set value to key 1 time
    * 
    * @return void
    * @throws \flat\core\event\exception\invalid_key\is_not_string if key not string
    * @throws \flat\core\event\exception\invalid_key\cannot_be_empty if key empty
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    */   
   public static function set_data($key,$val,$no_overwrite=false) {
      self::_event()->set_data(get_called_class(),$key,$val,$no_overwrite);
   }
   
   /**
    * adds callback function for event which invoked after any handler.
    * multiple listeners can exist for event
    * 
    * @param callable $listener callback performed when event is called (triggered)
    * @return void
    * 
    */   
   public static function add_listener(callable $listener) {
      self::_event()->add_listener(get_called_class(),$listener);
   }

   /**
    * sets callback function for event, overriding any existing handler.
    * 
    * @param callable $handler callback performed when event is called (triggered)
    * @return void
    * 
    */
   public static function set_handler(callable $handler) {
      self::_event()->set_handler(get_called_class(),$handler);
   }

   /**
    * triggers an event
    * 
    * @param mixed $trigger_data (optional) data passed to handler and listeners
    * @param array $callback (optional) assoc array of event callback:
    *    callable $callback['handler'] sets event handler like \flat\core\factory::set_handler(),
    *    callable $callback['listener'] adds listener like \flat\core\factory::add_listener()
    * @return void
    */
   public static function trigger($trigger_data=NULL,array $callback=NULL) {
      
      if (is_callable($trigger_data) && !$callback) {
         //var_dump("hi");
         $callback = array('listener'=>$trigger_data);
         $trigger_data = NULL;
      }
      // var_dump($callback);
      // var_dump($trigger_data);
      self::_event()->trigger_event(get_called_class(),$trigger_data,$callback);
   }
   
   public static function load($namespace) {
      return new event\resolver($namespace);
   }
}









