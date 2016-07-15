<?php
/**
 * class definition 
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
namespace flat\lib\exception;
class missing_required_param extends \flat\lib\exception {
   /**
    * @return string[] list of missing parameters
    */
   public function get_required_keys() {
      return $this->_key;
   }
   private $_key;
   /**
    * @param string | string[] $key specify one or more names of missing parameters
    */
   public function __construct($key=null) {
      $keyp = $key;
      
      if (is_string($key)) {
         $keyp = explode(",",$key);
      } elseif(is_scalar($key) && !is_bool($key)) {
         $keyp=[$key];
      }
      
      $key_list = [];
      if (is_array($keyp)) {
         foreach($keyp as $k=>$v) {

            if (is_scalar($k) && !is_bool($k) && !is_int($k)) {
               $key_list[] = (string) $k;
            } elseif (
                (is_scalar($v) && !is_bool($v)) || 
                (
                ( !is_array( $v ) ) &&
                ( ( !is_object( $v ) && settype( $v, 'string' ) !== false ) ||
                ( is_object( $v ) && method_exists( $v, '__toString' ) ) )
                )
            ) {
               $key_list[] = (string) $v;
            }
         }
         $this->_key = $key_list;
      }
      
      if (count($key_list)==1) {
         foreach($key_list as $v) $keyname=$v;
         parent::__construct("missing required param: $keyname");
      } elseif (count($key_list)==0) {
         parent::__construct("missing an unknown required param");
      } else {
         parent::__construct("must specify one or more of the following params: ".implode(",",$key_list));
      }
   }
}


