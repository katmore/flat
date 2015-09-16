<?php
/**
 * \flat\core\util\image\exception\system_err class
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\util\image\exception;
/**
 * system_err exception
 * 
 * @package    flat\core\util\image
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class system_err extends \flat\core\util\image\exception {
   /**
    * @param string $err error description
    */
   public function __construct($err) {
      parent::__construct("system error: $err.");
   }
}