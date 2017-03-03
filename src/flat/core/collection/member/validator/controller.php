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
namespace flat\core\collection\member\validator;
class controller implements \flat\core\collection\member\checker {
   private $_validation;
   
   public function has_active_checks() {
      if ($this->_validation === true) return true;
      return false;
   }
   
   public function check_member($data) {
      if ($this->_validation===true) {
         /*
          * _validate $data
          *    deal with return value appropriately
          *    see @return  description in _validate() definition
          */
         $validate = $this->member_validate($data);
         if ($validate===false) {
            throw new exception\failed();
         } else
         if (is_a($validate,"\\Exception")) {
            throw $validate;
         } else
         if (is_a($validate,"\\flat\\core\\collection\\member\\ignore")) {
            return $validate;
         } else
         if ($validate!==true) {
            if (!empty($validate)) {
               if (is_string($validate)) {
                  throw new exception\failed($validate);
               } else {
                  throw new exception\failed();
               }
            }
         }
         
         /*
          * _filter $data
          *    apply the collections filter as appropriate
          *    see @return description in _filter() definition
          */
         $filter = $this->member_filter($data);
         if ($filter!==NULL) return $filter;
         
         return $data;
      }
   }
   public function __construct(\flat\core\collection $col) {
      if ($col instanceof collection\member\validator) {
         $this->_validation = true;
         return;
      }
   }
}