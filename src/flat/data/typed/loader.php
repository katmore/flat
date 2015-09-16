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
namespace flat\data\typed;
class loader extends \flat\data {

   protected static function _typed_classname($type) {

      if (!empty($type)) {
         if (substr($type,0,1)=="\\") {
            $classname = $type;
         } else {
            $classname = "\\".get_called_class()."\\".$type;
         }
         if (class_exists($classname)) {
            return $classname;
         }
      }      
   }
   
   public static function typed_class($type) {
      return self::_typed_classname($type);
   }
   
   /**
    * derives a relative \flat\data "type" as given . returns NULL if no type could be derived.
    * 
    * @return string | NULL
    * @param array | string $param 
    *    if array: type derived from element $param['type'], 
    *    if string: explicitly specify relative type
    */
   public static function typed_name($param) {
      
      if (is_array($param)) {
         if (isset($param['type'])) {
            $type = $param['type'];
         }
      } else {
         $type = $param; 
      }
      $classname = self::_typed_classname($type);
      if ($classname) {
         return str_replace("\\".get_called_class()."\\","",$classname);
      } else {
         return NULL;
      }
   }   
   
   /**
    * maps given data to an existing \flat\data child instance based on derived "type",
    *    returns NULL if no type could be derived.
    * 
    * @return \flat\data | NULL
    */
   public static function typed_load(array $data,$type=NULL) {
      if (isset($data['type']) && (empty($type) || !is_string($type))) {
         $type = $data['type'];
      }
      
      if ($classname = self::_typed_classname($type)) {
         if (isset($data['type'])) unset($data['type']);
         return new $classname($data);
      }
      return NULL;
   }
}











