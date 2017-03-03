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
namespace flat\core\util;

use \flat\vendor\neitanod\forceutf8\Encoding as utf8Encoding;
use flat\data\stringify;
/**
 * reliably encode most any string to uft8 without garbage chars in result.
 *    depends on having the flat\vendor package for neitanod's utf8 fixing library.
 * 
 * @package /flat/core/util/utf8
 * @uses \flat\vendor\neitanod\forceutf8\Encoding
 */
class utf8 extends utf8Encoding {
   /**
    * @var string
    *    utf8 encoded string of original value passed to constructor.
    * @see \flat\core\util\utf8::__construct() 
    */
   private $_value;
   /** 
    * provides the utf8 encoded string of value passed to constructor
    *    when an instance is cast as string. This is a PHP magic method.
    * 
    * @see \flat\core\util\utf8::get_value() 
    */
   public function __toString() {
      
      return $this->get_value();
   }
   /** 
    * provides the utf8 encoded string of value passed to constructor.
    * 
    * @see \flat\core\util\utf8::fix() 
    */
   public function get_value() {
      if (empty($this->_value) || !is_string($this->_value)) return "";
      return $this->_value;
   }
   /**
    * instantiate this class for the convience of accessing value 
    *    as string with utf8 encoded value by either casting the
    *    instance to a (string) or using the get_value() method.
    * 
    * @param string $value value to ensure is encoded as utf8 string.
    *    when value is not utf8 encoded it will be converted to utf8.
    * 
    * @see \flat\core\util\utf8::get_value()
    * @see \flat\core\util\utf8::__getString()
    */
   public function __construct($value) {
      $this->_value = self::fix($value);
   }
   /**
    * reliably ensures any string is encoded to utf8 without garbage chars.
    * 
    * @static
    * @return string
    * @param string $value value to ensure is encoded as utf8 string.
    *    when value is not utf8 encoded it will be converted to utf8.
    */
   public static function fix($value) {
      return self::fixUTF8($value);
   }
}

