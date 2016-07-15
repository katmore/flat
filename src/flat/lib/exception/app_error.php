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

class app_error extends \flat\lib\exception {
   public function get_data() {
      return $this->_data;
   }
   public function get_details() {
      return $this->_details;
   }
   const summary_max_len = 40;
   /**
    * provides a summary of the data associated with this exception instance as follows:
    *    <ul>
    *       <li>(string) '<strong>data: (none)</strong>' no data was associated
    *       <li>(string) '<strong>{data string value}</strong>' string value was associated as data
    *       <li>(int) <strong>{data int value}</strong> integer value was associated as data 
    *       <li>(string) '<strong>data: (bool) {true|false}</strong>' boolean value was associated as data 
    *       <li>(string) '<strong>data: ({scalar type}) {value}</strong>' scalar value other than boolean, 
    *          string, or integer was associated as data
    *       <li>(string) '<strong>data: (array) {truncated json value}</strong>' array was 
    *          associated as data and able to be serialized as json; value provided is truncated to 40 characters
    *          with the string "(truncated)...}" appended if truncated.
    *       <li>(string) '<strong>data: ({class name}) {truncated json value}</strong>' object was 
    *          associated as data and able to be serialized as json; value provided is truncated to 40 characters
    *          with the string "(truncated)...}" appended if truncated.
    *       <li>(string) '<strong>data: ({scalar type}) {value}</strong>' scalar value other than boolean, 
    *          string, or integer was associated as data
    *       <li>(string) '<strong>data: (array|{class name})</strong>' array or object was 
    *          associated as data and could not be serialized to json.
    *       <li>(string) '<strong>data: ({PHP type})</strong>' PHP type provided as data value other than documented above.
    *    </ul>
    * 
    * @return string | int
    */
   public function get_data_summary() {
      if (!is_null($this->_data)) {
         $data = $this->_data;
         if (is_scalar($data)) {
            if (is_bool($data)) {
               if ($data) {
                  return "data: (bool) true";
               } else {
                  return "data: (bool) false";
               }
            }
            if (is_int($data) || is_string($data)) {
               return $data;
            }
            return "data: (".gettype($data).") $data";
         } else {
            if (is_object($data) || is_array($data)) {
               if ($json = json_encode($data)) {
                  if (is_object($data)) $json = json_encode((array) $data);
                  if (strlen($json)>self::summary_max_len) $json = substr($json,0,self::summary_max_len)."(truncated)...}";
                  if (is_object($data)) {
                     return "data: (".get_class($data).") $json";
                  } else {
                     return "data: $json";
                  }
               }
               if (is_array($data)) return "data: (array)";
               return "data: (".get_class($data).")";
            }
            return "data: (".gettype($data).")";
         }
      }
      return "data: (none)";
   }
   protected $_data;
   protected $_details;
   
   const error_label = "application_error";
   /**
    * @param string $details error detail message
    * @param mixed $data optional data to associate with this error
    * @param bool $include_data_summary if true, a summary of the data will be included in the exception message
    * @param mixed 
    */
   public function __construct($details="",$data=null,$include_data_summary=false) {
      $this->_data = $data;
      if (!empty($details)) {
         $this->_details = $details;
         $details = self::error_label.": $details";
      } else {
         $details = self::error_label;
      }
      if ($include_data_summary && ($data_summary = $this->get_data_summary())) {
         $details.=". $data_summary.";
      }    
      parent::__construct($details);
   }
}






