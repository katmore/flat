<?php
/**
 * \flat\db\op\exception\not_found class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\op\exception;
/**
 * record not found exception
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * @abstract 
 */
abstract class not_found extends \flat\db\op\exception {
   /**
    * exception message
    * @return string
    */
   abstract protected function _get_msg(); 
   public function __construct($detail) {
      if (!empty($detail)) $detail = ": $detail";
      parent::__construct($this->_get_msg()."$detail");
   }
}