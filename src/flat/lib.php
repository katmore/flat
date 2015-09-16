<?php
/**
 * \flat\lib class 
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat;
/**
 * ideal for a structure of business logic within \flat\app. 
 *    children of this class should not be resolved within \flat\app\route
 *    but ideal for being used by resolved classes.
 * 
 * @package    flat\lib
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class lib implements \flat\core\app {
   
   /**
    * enforces one or more class dependencies
    * @param string|string[] $class_name class(es) to enforce exist
    * @param string $description description of dependency
    * 
    * @return void
    * @throws \flat\lib\exception\missing_dependency if missing a class as specified
    */
   public static function enforce_class_dependency($class_name,$description) {
      $class = [];
      if (is_array($class_name)) {
         $class = $class_name;
      } else {
         $class[] = $class_name;
      }
      $missing = [];
      foreach($class as $name) { 
         if (!class_exists($name)) {
            $missing[] = $name;
         }
      }
      if (count($missing)) {
         throw new \flat\lib\exception\missing_dependency($description,$missing,$class_name);
      }
   }
   
   private static function _check_scalar_param(array $param=NULL,$param_key) {
      if (is_array($param_key)) {
         foreach ($param_key as $key) {
            self::_check_scalar_param($param,$key);
         }
         return;
      }
      if (empty($param[$param_key])) {
         throw new \flat\lib\exception\missing_required_param($param_key);
      }
      if (!is_scalar($param[$param_key])) {
         throw new \flat\lib\exception\bad_param(
            $param_key,
            "is not a scalar value"
         );
      }
   }
   /**
    * helper function to validate given param value is scalar
    * 
    * 
    * @return void
    * @throws \flat\lib\exception\missing_required_param if param missing
    * @throws \flat\lib\exception\bad_param if param not scalar value
    * 
    * @param array|null $param parameter assoc array
    * @param string $param_key assoc key to check within $param array
    * @static
    */
   public static function require_scalar_param(array $param=NULL,$param_key) {
      self::_check_param($param);
      return self::_check_scalar_param($param,$param_key);
   }   
   
   private static function _check_nonempty_param(array $param=NULL,$param_key) {
      if (is_array($param_key)) {
         foreach ($param_key as $key) {
            self::_check_nonempty_param($param,$param_key);
         }
         return;
      }
      if (empty($param[$param_key])) {
         throw new \flat\lib\exception\missing_required_param($param_key);
      }
   }
   
   /**
    * helper function to validate given param value is not empty
    * 
    * @return void
    * @throws \flat\lib\exception\missing_required_param if param missing
    * @throws \flat\lib\exception\bad_param if param not scalar value
    * 
    * @param array|null $param parameter assoc array
    * @param string $param_key assoc key to check within $param array
    * @static
    */   
   public static function require_nonempty_param(array $param=NULL,$param_key) {
      self::_check_param($param);
      return self::_check_nonempty_param($param,$param_key);
   }
   private static function _check_param(array $param=NULL) {
      if (!$param) throw new \flat\lib\exception\bad_param(
         "param",
         "missing param arg"
      );
   }
   /**
    * helper function to validate given param value is an array
    * 
    * @return void
    * @throws \flat\lib\exception\missing_required_param if param missing
    * @throws \flat\lib\exception\bad_param if param not scalar value
    * 
    * @param array|null $param parameter assoc array
    * @param string $param_key assoc key to check within $param array
    * @static
    */     
   public static function require_array_param(array $param=NULL,$param_key) {
      self::_check_param($param);
      if (!isset($param[$param_key])) {
         throw new \flat\lib\exception\missing_required_param($param_key);
      }
      if (!is_array($param[$param_key])) {
         throw new \flat\lib\exception\bad_param(
            $param_key,
            "must be array"
         );
      }
   }
}














