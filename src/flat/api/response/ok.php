<?php
/**
 * \flat\api\response\ok definition
 *
 * PHP version >=7.1
 *
 * Copyright (c) 2012-2015 Doug Bird.
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
class ok extends \flat\api\response {
   public function __construct($data=NULL) {
      $this->_set_status(new \flat\api\status\ok());
      if (!empty($data))
      if (is_object($data)) {
         $this->object_to_properties( $data,array('add_if_not_exists'=>true));
      } else
      if (is_array($data)) {
         if (count($data)) {
            foreach($data as $key=>$val) {
               break 1;
            }
            if (is_string($key)) {
               $this->list_to_properties($data,array('add_if_not_exists'=>true));
            } else {
               $this->data = $data;
            }
         } else {
            $this->data = [];
         }
      } else
      if (is_scalar($data)) {
         $this->data = $data;
      }
   }
}