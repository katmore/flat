<?php
/**
 * File:
 *    map.php
 * 
 * Purpose:
 *    facilitate creating \flat\data object from storage
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
 * @package    flat/data
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 */
namespace flat\data\orm;
use flat\core\util\validate\assoc;
class map {
   public $data;
   public $type;
   public function get_data() {
      return $this->data;
   }
   final public function __construct(array $param) {
      if (isset($param['object'])) {
         if (is_object($param['object'])) {
            $this->data = $param['object'];
            $this->type = get_class($param['object']);
            return;
         }
      }
      if (isset($param['data'])) {
         if (assoc::non_empty_scalar($param,"type")) {
            if (class_exists($param['type'])) {
               $type = $param['type'];
               $data = new $type($param['data']);
               $this->type = $param['type'];
               $this->data = $data;
               return;
            } else {
               throw new exception\bad_param(
                  "class indicated by 'type' param does not exist: ".$param['type']
               );
            }
         } else {
            throw new exception\bad_param(
               "missing 'type' param as needed with 'data' param"
            );
         }
      }
      throw new exception\bad_param("must have 'object' or 'data' param");
   }
}