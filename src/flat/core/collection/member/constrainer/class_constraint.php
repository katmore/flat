<?php
/**
 * \flat\core\collection\member\constrainer\class_constraint interface 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\collection\member\constrainer;
/**
 * class constraining interface for collection members
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface class_constraint {
   /**
    * a member will not be added to collection unless it belongs to the class 
    *    or classes given. it should returns class name as string, 
    *    or array of class names.
    * 
    * @return string|string[]
    */   
   public function get_member_class_constraint();
}