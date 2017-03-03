<?php
/**
 * \flat\api\response_header
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
namespace flat\api;
class response_header extends \flat\event {
   private $_field;
   private $_value;
   public function get_header_string() : string {
      if (!empty($this->_field)) {
         if (!empty($this->_value)) {
            return $this->_field . ": ". $this->_value;
         } else {
            return (string) $this->_field;
         }
      } else {
         return "";
      }
   }
   /**
    * @return string header field name
    */
   public function get_field() : string {
      return (string) $this->_field;
   }
   /**
    * @return string header value
    */
   public function get_value() : string {
      return (string) $this->_value;
   }
   /**
    * triggers an api response_header event. 
    * See wikipedia link for standard and common non-standard header RESPONSE FIELDS.
    * 
    * @param string $field HTTP header request field
    * @param string $value
    * @param \flat\data OPTIONAL specifies a data object to provide to any header event listeners
    * 
    * @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
    */
   public function __construct(string $field,string $value=null,\flat\data $meta=null) {
      if (empty($field)) {
         throw new header\exception\missing_request_field();
      }
      $this->_field = $field;
      $this->_value = $value;
      self::set_data("field",[$field=>$value],false);
      self::set_data("meta",$meta,false);
      self::trigger();
   }
   
}









