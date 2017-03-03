<?php
/**
 * \flat\core\collection\member\ignore definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\collection\member;
/**
 * indication class to ignore collection member check failures
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * @see \flat\core\collection::_validate_data()
 */
class ignore {
   final public function __call($name,$args) {
      return "";
   }
   final public function __get($name) {
      return "";
   }
   final public function __toString() {
      return "";
   }
}