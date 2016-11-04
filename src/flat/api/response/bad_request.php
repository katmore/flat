<?php
/**
 * \flat\api\response\forbidden definition
*
* PHP version >=7.0
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
* @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
*/
namespace flat\api\response;
class bad_request extends \flat\api\response {
    
   public function __construct($message="",$data=null,int $trace_offset=0) {
      if (empty($message)) {
         $trace = debug_backtrace();
         if (isset($trace[1+$trace_offset]) && !empty($trace[1+$trace_offset]['class'])) {
            try {
               $this->message = "Bad request indicated by ".(new \ReflectionClass($trace[1+$trace_offset]['class']))->getShortName();
            } catch (\Exception $e) {
               $this->message = "Bad request indicated";
            }
         }
      } else {
         $this->message = $message;
      }
      if (!empty($data)) {
          
         if (!empty($this->message) && (is_array($data) ||is_object($data))) {
            $msgTest = (array)$data;
            if (empty($msgTest['message'])) {
               if (is_array($data)) {
                  $data['message'] = $this->message;
               } else {
                  $data->message = $this->message;
               }
            }
         }
         $this->data = $data;
      }
      $this->_set_status(new \flat\api\status\bad_request());
   }

}