<?php
/**
 * File:
 *    validate.php
 * 
 * Purpose:
 *    facilitate validating a value
 *       as defined by child class
 * 
 * Usage:
 *    child::_validate($value)
 *       validation passes; if return value is bool true ($return === true): 
 *       validation fails; any other return value
 *    
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
 * @package    flat/
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    1.0.0 (alpha release state)
 * 
 */
namespace flat\core\util;
abstract class validate {
   /**
    * function: _validate
    * 
    * @return bool true indicates success
    * 
    * any other return value indicates failure
    * 
    */
   protected static function _validate($value,array $param=NULL){}
   
   private $_value=NULL;
   final public function __toString() {
      if (is_scalar($this->_value)) return $this->_value;
      return "";
   }
   final public function get_value() {
      return $this->_value;
   }
   public function __construct(array $param=NULL) {
      if (isset($param['value'])) {
         $vparam=array();
         if (isset($param['validate'])) {
            if (is_array($param['validate'])) {
               $vparam=$param['validate'];
            }
         }
         $this->_value = self::validate($param['value'],$vparam);
      }
   }
   
   final public static function validate($value,array $option=NULL) {
      $param = array(
         'fail_handler'=>NULL,
         'exception_on_fail'=>false,
         'value_on_pass'=>NULL,
         'value_on_fail'=>NULL
      );
      foreach ($option as $k=>$v) if (isset($param[$k])) $param[$k]=$v;
      if (self::is_valid($value,$option)) {
         if ($param['value_on_pass']===NULL) {
            return $value;
         } else {
            return $param['value_on_pass'];
         }
      } else {
         if (is_callable($param['fail_handler'])) {
            $handler = $param['fail_handler'];
            return $handler($value);
         }
         if ($param['value_on_fail']===NULL) {
            return NULL;
         } else 
         if ($param['exception_on_fail']===true) {
            if (self instanceof validate\exceptionable) {
               self::throw_exception(array('value'=>$value));
            } else {
               throw new validate\exception\fail(array('value'=>$value,"rule"=>get_class(self)));
            }
         } 
      }
   }
   final public static function is_valid($value,array $option=NULL) {
      if (static::_validate($value,$option)===true) {
         return true;
      } else {
         return false;
      }
   }
}

































































