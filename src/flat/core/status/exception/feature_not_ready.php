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
namespace flat\core\status\exception;
class feature_not_ready extends \flat\core\status\exception {
   public function __construct(array $param=NULL) {
      if (!empty($param['msg'])) {
         $msg = $param['msg'];
      } else
      if (!empty($param['object'])) {
         if (is_object($param['object'])) {
            $msg = get_class($param['object'])." not ready";
         } else
         if (is_string($param['object'])) {
            if (class_exists($param['object'])) {
               $msg = "class ".$param['object']." not ready";
            }
         }
      } else
      if (!empty($param['method'])) {
         if (is_string($param['method']))
            $msg = "method '".$param['method']."' not ready";
      } else
      if (!empty($param['function'])) {
         if (is_string($param['function']))
            $msg = "function '".$param['function']."' not ready";
      } else {
         $msg = "feature not ready";
         $callers=debug_backtrace();
         if (isset($callers[1]['class']) && isset($callers[1]['function'])) {
            $msg .= ": ".$callers[1]['class']."::".$callers[1]['function']."() not ready";
         } else
         if (isset($callers[1]['function'])) {
            $msg .= ": ".$callers[1]['function']. "() not ready";
         }
      }
      parent::__construct($msg);
   }
} 