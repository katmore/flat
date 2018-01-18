<?php
/**
 * \flat\api\response\error definition
 *
 * PHP version >=7.1
 *
 * Copyright (c) 2012-2017 Doug Bird.
 *    All Rights Reserved.
 *
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
 * ALL RIGHTS RESERVED. THIS COPYRIGHT APPLIES TO THE ENTIRE CONTENTS OF THE WORKS HEREIN
 * UNLESS A DIFFERENT COPYRIGHT NOTICE IS EXPLICITLY PROVIDED WITH AN EXPLANATION OF WHERE
 * THAT DIFFERENT COPYRIGHT APPLIES. WHERE SUCH A DIFFERENT COPYRIGHT NOTICE IS PROVIDED
 * IT SHALL APPLY EXCLUSIVELY TO THE MATERIAL AS DETAILED WITHIN THE NOTICE.
 *
 * The flat framework is copyrighted free software.
 * You can redistribute it and/or modify it under either the terms and conditions of the
 * "MIT License" (also known as the Simplified BSD License or 2-Clause BSD License
 * See the file MIT-LICENSE.txt), or the terms and conditions
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
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\api\response;
class method_not_allowed extends \flat\api\response {
   public $message;
   public $data;
   public function __construct($message="",$data=NULL,$trace_offset=0) {
      if (empty($message)) {
         $trace = debug_backtrace();
         if (!empty($trace[1+$trace_offset]['class'])) {
            $r = new \ReflectionClass($trace[1+$trace_offset]['class']);
            $this->message = "error indicated by ".$trace[1+$trace_offset]['class'];
         }
      } else {
         $this->message = $message;
      }
      if (!empty($data)) {
         $this->data = json_decode(json_encode($data));
         if (!isset($this->data->message)) {
            $this->data->message = $message;
         } else {
            $this->data->{'message-'.uniqid()} = $message;
         }
      }
      $this->_set_status(new \flat\api\status\method_not_allowed);
   }

}