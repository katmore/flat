<?php
/**
 * File:
 *    api.php
 * 
 * Purpose:
 *    create a stateless API using HTTP verbage (RESTful)
 *    can be bound to make interfaces for
 *       HTTP, native (PHP), cli, etc
 *
 *
 * PHP version >=5.6
 *
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (C) 2012-2015  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved..
 * 
 * @package    flat/core/api
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 * 
 */

namespace flat\core\controller;
abstract class api implements  \flat\core\input\consumer, \flat\core\controller {
   private static $response_handler;
   final static public function set_response_handler(callable $handler) {
      self::$response_handler=$handler;
   }
   private static $_method;
   final static public function set_method($method) {
      if (interface_exists("\\flat\\api\\method\\$method")) {
         self::$_method = $method;
      } else {
         throw new api\exception\unknown_method(
            $_method
         );
      }
   }
   final public function set_input(\flat\input $input,$method="") {
      if (empty($method)) $method = self::$_method;
      if (empty($method)) throw new api\exception\missing_method();
      $r = new \ReflectionClass($this);
      $interface = "\\flat\\api\\method\\".$method;
      if (!$r->implementsInterface($interface)) {
         if (!$r->implementsInterface("\\flat\\api\\method\\any")) {
            throw new api\exception\method_mismatch(
               $method
            );
         }
      }
      /*
       * if is \flat\api\validator...
       *    get validate collection
       */
      if ($this instanceof \flat\core\util\validate\collector) {
         $col = $this->get_validate_collection();
         if (is_a($col,"\\flat\\core\\util\\validate\\factory")) {
            /*
             * apply validation
             */
         }
      }
      
      
      

      if ($r->implementsInterface("\\flat\\api\\method\\any")) {
         try {
            $response = $this->any_method($input);
         } catch (\flat\api\response\exception $e) {
            $response = $e->get_response();
         }
         if (is_a($response,"\\flat\api\\response")) {
            $response_handler = self::$response_handler;
            if (is_callable($response_handler)) {
               return $response_handler($response);
            }
            return $response;
         }         
      }

      //GET_method(\flat\input $input)
      $f = $method."_method";
      try {
         $response = $this->$f($input);
      } catch (\flat\api\response\exception $e) {
         $response = $e->get_response();
      }       
      if (!is_a($response,"\\flat\api\\response")) {
         throw new api\exception\bad_response(
            get_class($this),
            $method
         );
      }
      $response_handler = self::$response_handler;
      if (is_callable($response_handler)) {
         return $response_handler($response);
      }
      return $response; 
   }

}
























