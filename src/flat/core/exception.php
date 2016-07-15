<?php
/**
 * File:
 *    exception.php
 * 
 * Purpose:
 *    parent class for exceptions in flat framework
 * 
 *    To assist in debugging, an exception code will be generated if not given.
 *    
 *    The generation routine attempts to create a code that is
 *    consistent and unique to the condition resulting in an exception.
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved..
 * 
 * @package    flat/core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 * 
 */
namespace flat\core;

abstract class exception extends \Exception{
   use exception\controller;
   
   public function __construct($msg,$code=0) {
      if (!is_int($code)) $code = 0;
      /*
       * if no code provided...
       *    derive a useful code based on some exception attributes
       */
      if (empty($code)) $code = $this->_derive_code();
       
      parent::__construct($msg,$code);
   }   
}