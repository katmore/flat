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
class cli {

   const tabstring = "  ";
   const min_cols = 20;
   const command = "flat.php";
   
   private static $_cols=NULL;
   private static $_command;
   private static $_argv;
   private static $_param;
   private static function _argv_to_command(array $argv) {
      if (isset($argv[0])) return $argv[0];
   }
   public static function set_argv(array $argv,$param_start_idx=2) {
      self::$_command = self::_argv_to_command( $argv);
      self::$_param = [];
      self::$_argv = $argv;
      if (count($argv) && (count($argv)>$param_start_idx)) {
         for($i=$param_start_idx;$i<count($argv);$i++) {
            $val = $argv[$i];
            
            if ( substr($val,0,1)!="-" ) {
               //echo "$val doesn't start with dash";
               self::$_param[] = $val;
            }
         }
      }
   }
   public static function is_switch_on($switch_name,$char_alias=NULL) {
      foreach (self::$_argv as $p) {
         if (substr($p,0,2)=="--") {
            //echo "yes: '".substr($p,2)."' vs. $switch_name\n";
            if (substr($p,2)==$switch_name) {
               return true;
            }
         } else
         if ($char_alias && (substr($p,0,1)=="-")) {
            if (false !==(strpos($p,$char_alias))) return true;
            //if (substr($p,1)==$char_alias) return true;
         }
      }
   }
   public static function get_option($option_name,$char_alias=NULL) {
         
     foreach (self::$_argv as $p) {
         $found = false;
         if (substr($p,0,2)=="--") {
            if (substr($p,2)==$option_name) $found = true;
         } else
         if ($char_alias && (substr($p,0,1)=="-")) {
            if (substr($p,1)==$char_alias) $found = true;
         }
         if ($found) {
            if (false!==(strpos($p,"="))) {
               return array_pop(explode("=",$p));
            } else {
               return "";
            }
         }
      }
   }
   public static function get_param($idx=0) {
      if (isset(self::$_param[$idx])) {
         return self::$_param[$idx];
      }
   }
   public static function get_command() {
      //if (empty(self::$_command)) return self::command;
      return self::$_command;
   }
   private static function _width() {
      if (self::$_cols===NULL) {
         if ($tput_cols = (int) sprintf("%d",trim(exec('tput cols',$output,$return)))) {
            if (!$return && ($tput_cols > self::min_cols)) {
               self::$_cols = $tput_cols;
            }
         }
         if (empty(self::$_cols)) {
            self::$_cols = false;
         }
      }
      return self::$_cols;
   }
   public static function line($text,$tabs=0) {
      $tabstring = "";
      if ((int)$tabs>0) {
         $tabcount = 1 * (int) $tabs;
         for($i=0;$i<$tabcount;$i++) {
            $tabstring.=self::tabstring;
         }
      }
      $text = str_replace("\t",self::tabstring,$text);
      if (self::_width()) {
         echo wordwrap ( $tabstring.$text, self::$_cols-strlen($tabstring) , \PHP_EOL.$tabstring ,true).\PHP_EOL;
      } else {
         echo $text.\PHP_EOL;
      }
   }

}