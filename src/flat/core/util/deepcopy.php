<?php
/**
 * class definition 
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
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\core\util;
use \flat\core\util\validate;
class deepcopy {
   public static function data($data=NULL,array $options=NULL) {
      $objects_to_stdClass = false;
      $objects_to_assoc = false;
      if (!$objects_to_stdClass = validate\assoc::only_bool_true($options,'objects_to_stdClass')) {
         $objects_to_stdClass = validate\assoc::only_bool_true($options,'objects_to_object');
      }
      if (!$objects_to_stdClass) {
         $objects_to_assoc = validate\assoc::only_bool_true($options,'objects_to_assoc');
      }
      if ($data) {
         if (is_object($data)) {
            if ($objects_to_stdClass || $objects_to_assoc) {
               $clone = (array) $data;
               if ($objects_to_stdClass) {
                  $obj = new \stdClass();
                  foreach($clone as $prop=>$val) {
                     $obj->$prop = self::data($val,$options);
                  }
               } else {
                  $obj = [];
                  foreach($clone as $prop=>$val) {
                     $obj[$prop] = self::data($val,$options);
                  }
               }
               return $obj;
            } else {
               return clone $data;
            }
         } else
         if (is_array($data)) {
            return self::arr($data,$options);
         } else
         if (is_scalar($data)) {
            return $data;
         }
      }
   }
   public static function arr(array $arr=NULL,array $options=NULL) {
      if ($arr) {
          /*
           * deep recursive copy array
           */
          return array_map(function($el) use(& $options){
             if (is_array($el)) return self::arr($el,$options);
             return self::data($el,$options);
          }, $arr);
      }
   }
}