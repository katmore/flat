<?php
/**
 * \flat\core\collection\exception\out_of_range class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\collection\exception;
/**
 * out_of_range exception
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class out_of_range extends \flat\core\collection\exception {
   public function __construct($param,$reason="") {
      if (!empty($reason)) {
         $reason = ": $reason";
      }
      parent::__construct("given param is out of range$reason");
   }
}