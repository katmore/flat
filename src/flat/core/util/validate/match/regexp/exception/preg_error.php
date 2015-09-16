<?php
/**
 * File:
 *    preg_error.php
 * 
 * Purpose:
 *    derive preg error description about the last preg error 
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
namespace flat\core\util\validate\match\regexp\exception;
class preg_error extends \flat\core\util\validate\match\regexp\exception {

   private function preg_error_string($err) {
      
      switch ($err) {
         case PREG_INTERNAL_ERROR:
            return "PREG_INTERNAL_ERROR: internal PCRE error";
            break;
         case PREG_BACKTRACK_LIMIT_ERROR:
            return "PREG_BACKTRACK_LIMIT_ERROR: backtract limit exhausted";
            break;
         case PREG_RECURSION_LIMIT_ERROR:
            return "PREG_RECURSION_LIMIT_ERROR: recursion limit exhausted";
            break;
         case PREG_BAD_UTF8_ERROR:
            return "PREG_BAD_UTF8_ERROR: malformed UTF-8 data";
            break;
         case PREG_BAD_UTF8_OFFSET_ERROR:
            return "PREG_BAD_UTF8_OFFSET_ERROR: offset did not correspond to the start of a valid UTF-8 code point";
            break;
      }
      return "unknown error";
   }

   public function __construct() {
      $err = preg_last_error();
      parent::__construct(
         $this->preg_error_string($err),
         $this->_value_to_code($this->preg_error_string($err))
      );
   }

}














