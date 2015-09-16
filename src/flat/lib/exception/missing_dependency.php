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
class missing_dependency extends \flat\lib\exception {
   private $_dependency;
   private $_missing_class;
   private $_class_set;
   public function get_dependency() {
      return $this->_dependency;
   }
   public function get_missing_class() {
      return $this->_missing_class;
   }
   public function get_class_set() {
      return $this->_class_set;
   }
   public function __construct($dependency,$missing_class=NULL,array $class_set=NULL) {
      $this->_dependency = $dependency;
      $this->_missing_class = $missing_class;
      $this->_class_set = $class_set;
      if (empty($class)) {
         $class = "";
      } else {
         if (is_array($missing_class)) $missing_class = implode(", ",$missing_class);
         $class = ", missing class '$missing_class'";
      }
      if (!empty($class_set)) {
         $class .= ", ".implode(", ",$class_set);
      }
      parent::__construct("missing dependency: '".$dependency."'".$class);
   }
}