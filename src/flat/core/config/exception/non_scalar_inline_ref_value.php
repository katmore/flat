<?php
/**
 * class definition
 *
 * PHP version >=7.2
 * 
 * Copyright (c) 2012-2018 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2018  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2018 Doug Bird. All Rights Reserved.
 */
namespace flat\core\config\exception;
class non_scalar_inline_ref_value extends \flat\core\config\exception {

   public function get_key() : string {
      return $this->_key;
   }
   private $_key;
   
   public function get_basename() : string {
      return $this->_basename;
   }
   private $_basename;
   
   public function get_ns() : string {
      return $this->_ns;
   }
   private $_ns;
   public function __construct(string $key,string $basename, string $ns) {
      $this->_key = $key;
      $this->_basename = $basename;
      $this->_ns = $ns;
      parent::__construct(
         "a non-scalar value for the inline ref-value $basename.$ns was by config key: $key"
      );
   }

}
