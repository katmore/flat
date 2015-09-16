<?php
/**
 * File:
 *    regexp.php
 * 
 * Purpose:
 *    match validation against perl regex pattern
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.. All works herein are considered to be trade secrets, and as such are afforded 
 * all criminal and civil protections as applicable to trade secrets.
 * 
 * @package    flat/core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 */
namespace flat\core\util\validate\match;
class regexp extends  \flat\core\util\validate {
   private $_regexp;
   
   public function __construct(array $param=NULL) {
      $vparam = array();
      if (isset($param['regexp'])) {
         $vparam['regexp'] = $param['regexp'];
      } else {
         if ($this instanceof regexp\definer) {
            $vparam['regexp'] = $this->get_regexp();
         }
      }
      parent::__construct(
         $pparam = array(
            'value'=>$param,
            'validate'=>$vparam
         )
      );
      \flat\core\debug::dump($pparam,"parent constructor params");
   }
   
   protected static function _validate($value,array $param=NULL) {
      /**
       * option sanity check
       */
      if (empty($param['regexp'])) throw new regexp\exception\bad_param(
         "regexp",
         "cannot be empty"
      );
      $regexp_arr = array();
      if (is_array($param['regexp'])) {
         $regexp_arr = $param['regexp'];
      } else
      if (is_scalar($param['regexp'])) {
         $regexp_arr[] = $param['regexp'];
      } else {
         throw new regexp\exception\bad_param(
            "regexp",
            "must be string or array of strings"
         );
      }
      foreach ($regexp_arr as $regexp) self::_check_regexp($regexp);
      foreach ($regexp_arr as $regexp) {
         \flat\core\debug::dump($value,"preg match value");
         \flat\core\debug::dump($regexp,"preg match regexp");
         if (1==($result = preg_match($regexp, $value))) continue;
         
         if ($result==0) return false;
         
         throw new regexp\exception\preg_error(preg_last_error(),$regexp);
      }
      return true;
   }
   final protected static function _check_regexp($regexp) {
      if (empty($regexp)) {
         throw new regexp\exception\invalid_regex\cannot_be_empty();
      }
      if (!is_string($regexp)) {
         throw new regexp\exception\invalid_regex\bad_pattern();
      }
      if (preg_match($regexp,"test")===false) {
         throw new regexp\exception\invalid_regex\bad_pattern(preg_last_error(),$regexp);
      }
   }
   // final protected function _add_regexp($regexp) {
      // if (is_array($regexp)) {
         // foreach ($regexp as $p) {
            // $this->_add_regexp($p);
         // }
      // } else {
         // if (empty($regexp)) {
            // throw new regexp\exception\invalid_regex\cannot_be_empty();
         // }
         // if (!is_string($regexp)) {
            // throw new regexp\exception\invalid_regex\cannot_be_empty();
         // }
         // if (preg_match($regexp,"test")===false) {
            // throw new regexp\exception\invalid_regex\bad_pattern(preg_last_error(),$regexp);
         // }
         // $this->_regexp[] = $regexp;
      // }
   // }
   // final protected function _set_regexp($regexp) {
// 
      // $this->_regexp = array();
//       
      // $this->_add_regexp($regexp);
   // }
}



















