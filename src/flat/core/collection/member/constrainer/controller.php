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
namespace flat\core\collection\member\constrainer;
class controller implements \flat\core\collection\member\checker {
   private $_class=false;
   private $_interface=false;
   private $_col;
   public function has_active_checks() {
      if (
         ($this->_class!==false) || 
         ($this->_interface!==false)
      ) {
         return true;
      }
      return false;
   }
   public function check_member($data) {
      if ($this->_class!==false) {
         foreach ($this->_class as $class) {
            if (!is_a($data,$class)) throw new exception\failed(
               "member is not a $class"
            );
         }
      }
      if ($this->_interface!==false) {
         foreach ($this->_interface as $inf) {
            if (!$data instanceof $inf) throw new exception\failed(
               "member is not a $inf"
            );
         }
      }
      return $data;
   }
   private function _check_class_constraint($constraint) {
      if (is_array($constraint)) {
         $arr = array();
         foreach ($constraint as $inf) {
            $res = $this->_check_class_constraint($inf);
            if (count($res)==1) {
               $arr[] = $res[0];
            } else {
               throw new exception\bad_constrainer(
                  get_class($this->_col)."->get_member_class_constraint() ".
                  "should not return a multi-dimensional array"
               );
            }
         }
         return $arr;
      } else
      if (is_string($constraint)) {
         if (interface_exists($constraint)) return array($constraint);
      }
      throw new exception\bad_constrainer(
         get_class($this->_col)."->get_member_interface_constraint() ".
         "must return only valid interface names that exist"
      );
      
   }
   private function _check_interface_constraint($constraint) {
      if (is_array($constraint)) {
         $arr = array();
         foreach ($constraint as $inf) {
            $res = $this->_check_interface_constraint($inf);
            if (count($res)==1) {
               $arr[] = $res[0];
            } else {
               throw new exception\bad_constrainer(
                  get_class($this->_col)."->get_member_interface_constraint() ".
                  "should not return a multi-dimensional array"
               );
            }
         }
         return $arr;
      } else
      if (is_string($constraint)) {
         if (interface_exists($constraint)) return array($constraint);
      }
      throw new exception\bad_constrainer(
         get_class($this->_col)."->get_member_interface_constraint() ".
         "must return only valid interface names that exist"
      );
      
   }   
   public function __construct(\flat\core\collection $col) {
      $this->_col = $col;
      if ($col instanceof collection\member\constrainer\class_constraint) {
         $this->_class = array();
         $this->_class = $this->_check_class_constraint(
            $col->get_member_class_constraint()
         );
      }
      if ($col instanceof collection\member\constrainer\interface_constraint) {
         $this->_interface = array();
         $this->_interface = $this->_check_interface_constraint(
            $col->get_member_interface_constraint()
         );
      }
   }
}








































































