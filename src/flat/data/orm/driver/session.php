<?php
/**
 * File:
 *    session.php
 * 
 * Purpose:
 *    (description of the file's purpose)
 *
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
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.. All works herein are considered to be trade secrets, and as such are afforded 
 * all criminal and civil protections as applicable to trade secrets.
 * 
 * @package    flat/data
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 */
namespace flat\data\session\driver;
class session extends \flat\data\session\driver {

   private $_h;

   const fallback_handler = "\\flat\\data\\orm\\driver\\session\\handler\\native";

   private function _get_handler($input) {
      if (is_string($input) && !empty($input)) {
         if (class_exists($input)) {
            if (is_a($input,"\\flat\\data\\orm\\driver\\session\\handler",true)) {
               $handler = $input;
               $handler_param = NULL;
               if (isset($param['handler_param'])) {
                  if (is_array($param['handler_param'])) $handler_param = $param['handler_param'];
               }
               return new $handler($handler_param);
            }
         }
      } else
      if (is_object($input)) {
         if (is_a($input,"\\flat\\data\\orm\\driver\\session\\handler")) {
            return $input;
         }
      }
      return NULL;
   }

   public function __construct(array $param=NULL) {
      $handler = NULL;
      if (isset($param['handler'])) {
         
         $handler = $this->_get_handler($param['handler']);
         
      }
      if (empty($handler)) {
         $handler = $this->_get_handler(
            \flat\core\config::get_or_default("flat/data/orm/driver/session/handler")
         );
      }
      if (empty($handler)) {
         $handler = $this->_get_handler(self::fallback_handler);
      }
      if (!$this->_get_handler($handler)) {
         session\exception\failure(
            "could not initialize a session handler"
         );
      }
      $this->_h = $handler;
   }
   
   public function set_data($key,\flat\data $data,array $options=NULL) {
      
      return $this->_h->set($key,$data,$options);
      
   }
   
   public function get_data($key,array $options=NULL) {
      
      return $this->_h->get($key,$options);

   }
}



















