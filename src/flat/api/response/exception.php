<?php
/**
 * \flat\api\response\exception definition
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
 * "MIT License" (also known as the Simplified BSD License or 2-Clause BSD License
 * See the file MIT-LICENSE.txt), or the terms and conditions
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat\api\response;
/**
 * throws exception while making a correlating \flat\api\response object
 * available for any exception handler.
 * 
 * @package    flat\api
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class exception extends \flat\api\exception {
   private $_response;
   /**
    * provides correlating \flat\api\response object
    * 
    * @return \flat\api\response
    */
   public function get_response() {
      return $this->_response;
   }
   /**
    * 
    * @param scalar|\flat\api\response $data If given scalar
    *    value, will create \flat\api\response\error object with value as 
    *    response message. It will be made available as response object.
    *    If \flat\api\response object given, that will be made available.
    *    If \flat\api\response object has message property containing 
    *    non-empty scalar value, it will be made part of the
    *    exception message.
    * 
    * @see \flat\api\response\exception::get_response()
    */
   public function __construct($data) {
      if (is_a($data,"\\flat\\api\\response")) {
         $response = $data;
      } else {
         if (is_scalar($data) && (!empty($data))) {
            $message = (string) $data;
         } else {
            $message = "error indicated";
            $trace = debug_backtrace();
            if (!empty($trace[1]['class'])) {
               $r = new \ReflectionClass($trace[1]['class']);
               $message .= " by ".$r->getClassName();
            }
         }
         $response = new \flat\api\response\error($message);         
      }
      $this->_response = $response;
      $extra = "";
      if (isset($response->message)) {
         if (is_scalar($response->message) && (!empty($response->message))) {
            $extra = " Message: '".$response->message."'.";
         }
      }
      parent::__construct(
         "RESTful status: '".$response->get_status()->get_str()."'.".$extra
      );
   }
}