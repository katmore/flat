<?php
/**
 * File:
 *    preg_error.php
 * 
 * Purpose:
 *    exception for bad preg pattern
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.. All works herein are considered to be trade secrets, and as such are afforded 
 * all criminal and civil protections as applicable to trade secrets.
 * 
 * @package    flat/core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 */
namespace flat\core\util\validate\match\regexp\exception\invalid_regex;
class preg_error extends \flat\core\util\validate\match\regexp\exception\invalid_regex {
   public static function preg_errtxt($errcode)
   {
       static $errtext;
   
       if (!isset($errtxt))
       {
           $errtext = array();
           $constants = get_defined_constants(true);
           foreach ($constants['pcre'] as $c => $n) if (preg_match('/_ERROR$/', $c)) $errtext[$n] = $c;
       }
   
       return array_key_exists($errcode, $errtext)? $errtext[$errcode] : NULL;
   }   
   public function __construct($preg_error,$pattern) {
      parent::__construct(
         "pcre error ".self::preg_errtxt($preg_error)." for pattern: $pattern"
      );
   }
}





















