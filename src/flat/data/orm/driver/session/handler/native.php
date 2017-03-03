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
namespace flat\data\orm\driver\session\handler;
use flat\data\orm\driver\session\exception;
class native extends \flat\data\orm\driver\session\handler {
   
   const fallback_ormkey = "flat/data";
   private static $_ormkey = self::fallback_ormkey;
   private static $_id;
   
   public static function destroy(array $options=NULL) {
      self::_init_session($options);
      
      session_destroy();
      
      session_regenerate_id(true);
   }
   
   protected static function _start_session(array $options=NULL) {
      session_name(self::_get_ormkey($options));
      session_start();
   }
   
   final protected static function _init_session(array $options=NULL) {
      if (session_status()==PHP_SESSION_DISABLED) {
         throw new exception\failure(
            "PHP sessions are not enabled"
         );
      } else
      if (session_status()==PHP_SESSION_NONE) {
         self::_start_session($options);
      }
      return self::$_id = session_id();
   }
   
   final protected static function _check_key($key) {
      if (empty($key)) throw exception\bad_param(
         "key cannot be empty"
      );
      if (!is_scalar($key)) throw exception\bad_param(
         "key must be scalar value"
      );
      return $key;
   }
   
   final public static function set_ormkey($key) {
      self::_check_key($key);
      self::$_ormkey = $key;
   }
   
   final protected function _get_ormkey(array $options=NULL) {
      if (isset($options['ormkey'])) return self::_check_key(
         $options['ormkey']
      );
      
      $key = \flat\core\config::get_or_default(
         "flat/data/orm/driver/session/ormkey",
         array(
            'default'=>self::$_ormkey
         )
      );
      
      return self::_check_key($key);
   }
   
   final public function get($key,array $options=NULL) {
      self::_check_key($key);
      self::_init_session($options);
      
      if (isset($_SESSION[$this->_get_ormkey($options)][$key]))
         return $_SESSION[$this->_get_ormkey($options)][$key];
   }
   
   final public function set($key, \flat\data $data,array $options=NULL) {
      self::_check_key($key);
      self::_init_session($options);
            
      $_SESSION[$this->_get_ormkey($options)][$key] = $data;
   }
}

























