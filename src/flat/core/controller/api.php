<?php
/**
 * class definition for \flat\core\controller\api
*
* PHP version >=7.0
*
* Copyright (c) 2012-2016 Doug Bird.
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
* @copyright  Copyright (c) 2012-2016 Doug Bird. All Rights Reserved..
*
*
*/

namespace flat\core\controller;
/**
 * Interface neutral controller for defining RESTful responses to flat routes.
 *
 */
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
   /**
    * @var string
    */
   private static $response_handler;
   /**
    * @return void
    *
    */
   final static public function set_response_handler(callable $handler) {
      self::$response_handler=$handler;
   }
    
   /**
    * @var string fallback 'request method' when not specified by the input.
    */
   private static $_method;
   /**
    * string[] list of methods recognized by the api controller
    */
   const VALID_METHOD_LIST = ['DELETE','GET','TRACE','OPTIONS','POST','PUT','HEAD'];
   /**
    * Sets the fallback 'request method' when not specified by the input.
    *
    * @param string $method request method; Must be one of the following:
    *   GET, PUT, POST, DELETE, TRACE, HEAD, or OPTIONS.
    *
    * @return void
    *
    * @throws \flat\core\controller\api\exception\unknown_method
    */
   final static public function set_method(string $method) {
      if (!in_array($method,self::VALID_METHOD_LIST,true)) {
         throw new api\exception\unknown_method($method);
      }
      self::$_method = $method;
   }
   /**
    * @var string
    *    request method corresponding to the current 'input', if any.
    */
   private $_request_method;
   /**
    * @return string request method corresponding to the current 'input'.
    * @throws \flat\core\controller\api\exception\missing_method
    */
   final protected function _get_request_method() :string {
      if (empty($this->_request_method)) throw new api\exception\missing_method();
      return (string) $this->_request_method;
   }
    
   /**
    * @var bool
    *    true if the request method corresponding to the current 'input'
    *    was set to HEAD.
    */
   private $_HEAD_request;
   /**
    * @return bool
    *    Determines if the request method corresponding to the current 'input'
    *    was set to HEAD ('HEAD' methods are always invoked as 'GET')
    */
   final protected function _is_HEAD_request() : bool {
      return !! $this->_HEAD_request;
   }
   /**
    * Passes an input object to the the interface call associated
    *    with a request method. Optionally, invokes a callback with the
    *    once a response is resolved from the interface.
    *
    * @return \flat\api\response
    *
    * @param \flat\input $input
    * @param string $method (optional) Specify the request method to associate with this input. By default,
    *    uses the 'fallback method' specified by calling the static \flat\core\controller\api::set_method().
    *
    * @param callable $response_handler (optional) callback invoked after response object is resolved.
    *    callback signature: function(\flat\api\response $response,string $request_method).
    *    If the callback returns a \flat\api\response object, this object becomes returned value.
    *
    * @throws \flat\core\controller\api\exception\missing_method
    * @throws \flat\core\controller\api\exception\unknown_method
    * @throws \flat\core\controller\api\exception\bad_response
    *
    *
    */
   final public function set_input(\flat\input $input,string $method=null,callable $response_handler=null) :\flat\api\response {
      //       var_dump(get_called_class());
      //       die(__FILE__);
      if (empty($method)) {
          
         $method = self::$_method;
      }
      $invoked_method = $method;
      if ($method=='HEAD') {
         $invoked_method = 'GET';
         $this->_HEAD_request = true;
      }

      if ($method=='TRACE') {
         return new \flat\api\response\ok($input->get_as_assoc());
      }

      $this->_input_method = $invoked_method;

      if (!in_array($method,self::VALID_METHOD_LIST,true)) {
         throw new api\exception\unknown_method($method);
      }

      if (empty($invoked_method)) throw new api\exception\missing_method();
      $r = new \ReflectionClass($this);

      if ($response_handler===null) {
         $response_handler = self::$response_handler;
      }
      $response = null;
       
      if ($this instanceof \flat\api\method\any) {
         try {
            $response = $this->any_method($input);
         } catch (\flat\api\response\exception $e) {
            $response = $e->get_response();
         }
      }
      if (!$response instanceof \flat\api\response) {
         if ($r->implementsInterface('\flat\api\method') && $r->implementsInterface('\flat\api\method\\'.$invoked_method)) {
            $f = $invoked_method."_method";
            try {
               $response = $this->$f($input);
            } catch (\flat\api\response\exception $e) {
               $response = $e->get_response();
            }
            if (!$response instanceof \flat\api\response) {
               throw new api\exception\bad_response(
                     get_class($this),
                     $invoked_method
                     );
            }
         }
      }
       
      if ($response instanceof \flat\api\response) {
         if (is_callable($response_handler)) {
            $handler_return = $response_handler($response,$method);
            if ($handler_return instanceof \flat\api\response) {
               $response = $handler_return;
            }
         }
         return $response;
      } else {
         return new \flat\api\response\no_interface;
      }
   }

}
























