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
namespace flat\cloud\authorize;
class missing_required_param extends bad_param {
   private $_all_keys_required;
   /**
    * retrieves the all_keys_required specification.
    * if return value is true, indicates that EACH of the specified keys are required.
    *    if return value is false, indicates that ONE of the specified keys is required.
    *    if return value is null it means the specification was non-applicable.
    *    
    * @return bool | null
    */
   public function is_all_keys_required() {
      if (is_bool( $this->_all_keys_required)) return $this->_all_keys_required;
      return null;
   }
   
   /**
    * @param string | string[] $key if string value: specifies a single missing 
    *    parameter key. if array value: specifies a list of parameter keys.
    *      
    * @param bool $all_keys_required (optional) ignored unless $key is an array.
    *    defaults to false. specifies the all_keys_required specification: 
    *       if value is true, the exception will indicate that 
    *       EACH member of the key list must be included, or that ONE of the members 
    *       must be included. 
    */
   public function __construct($key,$all_keys_required=null) {
      $this->_key = $key;
      //var_dump($key);die('exception');
      if (is_array($key)) {
         if ($all_keys_required) { 
            $msg = "must include ALL of the following: ".implode(", ",$key);
            $this->_all_keys_required = true;
         } else {
            $msg = "must include ONE of the following: ".implode(", ",$key);
            $this->_all_keys_required = false;
         }
         $this->_reason = $msg;
      } elseif (is_string($key)) {
         $msg = "missing required parameter: '$key'";
         $this->_reason = "'$key' must be included";
      } else {
         $msg = "missing required parameter";
         $this->_reason = "a required parameter is missing";
      }
      \flat\core\exception::__construct($msg);
   }
}