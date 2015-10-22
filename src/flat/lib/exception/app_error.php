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
   public function get_data_details() {
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
            if (is_class($data) || is_array($data)) {
               if ($json = json_encode($data)) {
                  if (is_class($data)) $json = json_encode((array) $data);
                  if (strlen($json)>10) $json = substr($json,0,10)."(truncated)...}";
                  return "data: (".get_class($data).") $json";
               }
               return "data: (".get_class($data).")";
            }
            return "data: (".gettype($data).")";
         }
      }
   }
   private $_data;
   private $_details;
   public function __construct($details="",$data=null) {
      if (!empty($details)) {
         $this->_details = $details;
         $details = ": $details";
      }
      $this->_data = $data;
      $data_details = $this->get_data_details();
      if (!empty($data_details)) {
         if (substr($data_details,0,1)!=".") $details .= ".";
         if (substr($data_details,1,1)!=" ") $details .= " ";
         $details .= $data_details;
      }
      parent::__construct("\\flat\\lib error".$details);
   }
}






