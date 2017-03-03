<?php
/**
 * class definition 
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
namespace flat\event;
class resolver {
   private static $_event=NULL;
   private static function _event() {
      if (self::$_event===NULL) {
         self::$_event = new \flat\core\event\factory();
      }
      return self::$_event;
   }
   private $_ns;
   /**
    * @param string $namespace of event
    */
   public function __construct($namespace) {
      $this->_ns = $namespace;
      self::_event()->add_event($this->_ns,array('ignore_if_exists'=>true));
   }
   public function set_data($key,$val) {
      
   }
   /**
    * adds event listener
    */
   public function add_listener(callable $listener) {
      self::_event()->add_listener($this->_ns,$listener);
   }
   /**
    * sets handle for given event
    */
   public function set_handler(callable $handler) {
      self::_event()->set_handler($this->_ns,$handler);
   }
   /**
    * triggers event
    */
   public function trigger($trigger_data=NULL,array $callback=NULL) {
      self::_event()->trigger_event($this->_ns,$trigger_data,$callback);
   }
}


















































