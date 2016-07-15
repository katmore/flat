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
   
   final public function __construct($param=null, $callback=null) {
      if ($param!==null) {
         $method=null;$input=null;$response_handler=null;$response_cb=null;
         if (is_array($param)) {
            if (array_key_exists('method', $param)) $method = $param['method'];
            if (array_key_exists('input', $param)) $input = $param['input'];
            if (array_key_exists('response_handler', $param)) $response_handler = $param['response_handler'];
         }elseif(is_string($param)) {
            $method=$param;
         }
         if (is_array($input) || is_object($input)) {
            $input = new \flat\input\map($input);
         } elseif (is_scalar($input)) {
            $input = new \flat\input\map(['data'=>$input]);
         } else {
            $input = new \flat\input\map([]);
         }
         if (is_callable($callback)) {
            $response_cb=$callback;
         }elseif (is_array($callback) && (array_key_exists('always', $callback))) {
            $response_cb = $callback['always'];
         }
         $callback_ok = null;
         $callback_error = null;
         if (is_array($callback) && (array_key_exists('ok', $callback))) {
            $callback_ok = $callback['ok'];
         }elseif (is_array($callback) && (array_key_exists('success', $callback))) {
            $callback_ok = $callback['success'];
         }
         if (is_array($callback) && (array_key_exists('error', $callback))) {
            $callback_error = $callback['error'];
         }
         $error_callback_invoked = false;
         $response=null;
         try {
            $response = $this->set_input($input,$method,$response_handler);
         } catch (\Exception $e) {
            if ($e instanceof \flat\api\response\exception) {
               $response = $e->get_response();
            }            
            if (is_callable($callback_error)) {
               $msg = $e->getMessage();
               if ($e instanceof \flat\api\response\exception) {
                  $data = $e->get_response();
                  if ($data instanceof \flat\api\response\error) {
                     $msg = $data->message;
                     $data = $data->data;
                  }
               }
               $error_callback_invoked = true;
               $callback_error($response,$msg,$data,$e);
            } else {
               throw $e;
            }
            if (is_callable($response_cb) && ($response!==null)) {
               $response_cb($response,$e);
            } elseif (!$error_callback_invoked) {
               throw $e;
            }
            return;
         }
         if (!$error_callback_invoked && ($response instanceof \flat\api\response\error) && is_callable($callback_error)) {
            $msg = $data->message;
            $data = $data->data;
            $error_callback_invoked = true;
            $callback_error($response,$msg,$data);
         }
         $status_sub = substr((string) $response->get_status()->get_code(),0,1);
         if (!$error_callback_invoked && (($status_sub == 4) || ($status_sub == 5)) && is_callable($callback_error)) {
            $error_callback_invoked = true;
            $callback_error($response,$msg,$data,$e);
         }
         if (!$error_callback_invoked && ($status_sub==2) && is_callable($callback_ok) && ($response instanceof \flat\api\response)) {
            $callback_ok($response);
         }
         if (is_callable($response_cb)) {
            $response_cb($response);
         }
      }
   }
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
   final public function set_input(\flat\input $input,$method="",$response_handler=null) {
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
      
      if ($response_handler===null) {
         $response_handler = self::$response_handler;
      }
      
      if ($r->implementsInterface("\\flat\\api\\method\\any")) {
         try {
            $response = $this->any_method($input);
         } catch (\flat\api\response\exception $e) {
            $response = $e->get_response();
         }
         if ($response instanceof \flat\api\response) {
            if (is_callable($response_handler)) {
               return $response_handler($response,"any");
            }
         }         
      }

      $f = $method."_method";
      try {
         $response = $this->$f($input);
      } catch (\flat\api\response\exception $e) {
         $response = $e->get_response();
      }       
      if (!$response instanceof \flat\api\response) {
         throw new api\exception\bad_response(
            get_class($this),
            $method
         );
      }
      
      if (is_callable($response_handler)) {
         return $response_handler($response,$method);
      }
      return $response; 
   }

}
























