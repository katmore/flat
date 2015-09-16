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
namespace flat\core;
class shutdown extends \flat\core {
   private static $_init=false;
   
   public static function invoke_shutdown_callbacks() {
      
      $first = self::$_first;
      if ($first)
      foreach ($first as $callback) $callback();
      
      $weight = self::$_weight;
      $weight_max = self::$_weight_max;
      for($i=0;$i<$weight_max;$i++) {
         if (isset($weight[$i])) {
            $callback = $weight[$i];
            $callback();
         }
      }
      
      $default = self::$_default;
      if ($default)
      foreach ($default as $callback) $callback();
      
      $last = self::$_last;
      if ($last)
      foreach ($last as $callback) $callback();
      
      //self::_clear();
   }
   private static function _clear() {
      
      $_default =  NULL;
      $_default_h =  NULL;
      $_default_c =  NULL;
   
      $_first =  NULL;
      $_first_h =  NULL;
      $_first_c =  NULL;
   
      $_last =  NULL;
      $_last_h =  NULL;
      $_last_c =  NULL;
   
      $_weight =  NULL;
      $_weight_h =  NULL;
      $_weight_c =  NULL;
   
      $_weight_max=0;
      
      self::$_init = false;
   }
   private static function _init() {
      
      if (self::$_init===true) return;

      
      register_shutdown_function(function() {
         self::invoke_shutdown_callbacks();
      });
      self::$_init=true;
   }
   
   const weight_ceiling = 1000;
   const weight_offset = 50;
   
   private static $_default;
   private static $_default_h;
   private static $_default_c;
   
   private static $_first;
   private static $_first_h;
   private static $_first_c;
   
   private static $_last;
   private static $_last_h;
   private static $_last_c;
   
   private static $_weight;
   private static $_weight_h;
   private static $_weight_c;
   
   private static $_weight_max=0;
   
   /**
    * adds a callback to a queue to be invoked on script shutdown, 
    *    optionally specifying priority that the affects order it will be
    *    invoked compared to any prior or subsequently registered callbacks 
    *    that may be in queue on script shutdown. returns shutdown handle
    *    (a uuid unique id) which may be used later to un-register a callback.
    * 
    * @static
    * @return string
    * @param callable $callback function invoked on script shutdown; callback
    *    signature: function().
    * @param NULL | int | string $priority (optional)    
    *    (string) 'first': adds callback to beginning of the queue but behind 
    *       any previously registered callback with 'first' priority.
    *    (string) 'last': adds callback to end of queue.
    *    (int) -50 to 50: integer weighted callback, adds with given 
    *       position in queue, moving any prior registered callbacks behind this 
    *       one, and any subsequent callbacks with same weight will likewise be
    *       invoked ahead.
    *    NULL or empty value: adds to end of the queue, after 'first' priority 
    *       and any integer weighted priority callbacks, but invoked before any
    *       callbacks that get registered with 'last' priority.
    * @param string $context associate a value with callback, can be used later to
    *    un-register a callback.
    */
   public static function register(callable $callback,$priority=NULL,$context=NULL) {
      //echo "context: $context, priority: $priority<br>\n";
      if (!is_callable($callback)) throw new shutdown\exception\not_callable();
      if (empty($context)) $context = NULL;
      $handle = \flat\core\uuid::get();
      
      self::_init();
      if (!$priority || $priority=='default') {
         self::$_default[] = $callback;
         self::$_default_h[count(self::$_default)]=$handle;
         self::$_default_c[count(self::$_default)]=$context;
         return $handle;
      } else {
         if ($priority=='first') {
            self::$_first[] = $callback;
            self::$_first_h[count(self::$_first)]=$handle;
            self::$_first_c[count(self::$_first)]=$context;
            return $handle;
         } else
         if ($priority=='last') {
            self::$_last[] = $callback;
            self::$_last_h[count(self::$_last)]=$handle;
            self::$_last_c[count(self::$_last)]=$context;
            return $handle;
         } else
         if (is_int($priority) && $priority>=(self::weight_offset*-1) && $priority<=self::weight_offset) {
            $priority = $priority + self::weight_offset;
            if (isset(self::$_weight[$priority])) {
               $start = $priority+1;
               $last = NULL;
               $last_h = NULL;
               $last_c = NULL;
               
               $last_TMP = NULL;
               $last_TMP_h = NULL;
               $last_TMP_c = NULL;
               
               for($i=$start;;$i++) {
                  if ($i>self::weight_ceiling) {
                     throw new shutdown\exception\weight_ceiling(
                        self::weight_ceiling
                     );
                  }
                  if ($i>self::$_weight_max) self::$_weight_max=$i;
                  if (isset(self::$_weight[$i])) {
                     if ($last) {
                        $last_TMP = self::$_weight[$i];
                        $last_TMP_h = self::$_weight_h[$i];
                        $last_TMP_c = self::$_weight_c[$i];
                        
                        self::$_weight[$i] = $last;
                        self::$_weight_h[$i] = $last_h;
                        self::$_weight_c[$i] = $last_c;
                        
                        $last = $last_TMP;
                        $last_h = $last_TMP_h;
                        $last_c = $last_TMP_c;
                        
                        $last_TMP = NULL;
                        $last_TMP_h = NULL;
                        $last_TMP_c = NULL;
                     } else {
                        $last = self::$_weight[$i];
                        $last_h = self::$_weight_h[$i];
                        $last_c = self::$_weight_c[$i];
                        
                        self::$_weight[$i]=$callback;
                        self::$_weight_h[$i]=$handle;
                        self::$_weight_c[$i]=$context;
                     }
                  } else {
                     if ($last) {
                        self::$_weight[$i] = $last;
                        self::$_weight_h[$i] = $last_h;
                        self::$_weight_c[$i] = $last_c;
                     } else {
                        self::$_weight[$i]=$callback;
                        self::$_weight_h[$i]=$handle;
                        self::$_weight_c[$i]=$context;
                     }
                     break 1;
                  }
               }
               return $handle;
            } else {
               if ($priority>self::$_weight_max) self::$_weight_max=$priority+1;
               self::$_weight[$priority] = $callback;
               self::$_weight_h[$priority] = $handle;
               self::$_weight_c[$priority]=$context;
               return $handle;
            }
         } else {
            self::$_default[] = $callback;
            self::$_default_h[count(self::$_default)]=$handle;
            self::$_default_c[count(self::$_default)]=$context;
            return $handle;
         }
      }
      
   }
}