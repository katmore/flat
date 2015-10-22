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
use Symfony\Component\EventDispatcher\Tests\CallableClass;
class cli {
   /**
    * string indentation to display per 'n' tabs
    */
   const tabstring = "  ";
   /**
    * int text wrapping logic will ignore reported column counts less than this amount  
    */
   const min_cols = 20;
   
   /**
    * string default command to report
    */
   const command = "flat.php";
   /**
    * @var int | null text wrapping logic will add newline every '$_cols' chars in given line of text
    * @static
    */
   private static $_cols=null;
   
   /**
    * @var string command to report
    * @static
    */
   private static $_command;
   
   /**
    * @var string[] the argv array provided
    * @static
    */
   private static $_argv;
   
   /**
    * @var string[] parameter list
    * @static
    */
   private static $_param;
   

   
   /**
    * provides a reportable 'command' from given argv array.
    * 
    * @return string | null
    * @static
    */
   private static function _argv_to_command(array $argv) {
      if (isset($argv[0])) return $argv[0];
   }
   
//    /**
//     * @var int status code to report
//     * @static
//     */
//    private static $_status_code=0;   
//    /**
//     * provides the reported status code.
//     * 
//     * @static
//     * @return int
//     */
//    public static function get_status_code() {
//       return self::$_status_code;
//    }
//    /**
//     * sets the reported status code.
//     * 
//     * @param int $status status code
//     * @return void
//     * @static
//     */
//    public static function set_status_code($status_code) {
//       self::$_status_code = (int) $status_code;
//    }
   /**
    * Sets the cli params from given array. Convenient way to convert the argv array to param list.
    * 
    * @param array $argv argv array
    * @param int $param_start_idx (optional) starting index in argv array to use 
    *    for cli parameters. default value: (int) 2.
    * 
    * @return void
    * @static
    */
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
   
   private static $_input_handler;
   public static function set_input_line_handler(callable $handler) {
      self::$_input_handler = $handler;
   }
   public static function each_input_line(callable $line_callback) {
      if (is_callable(self::$_input_handler)) {
         $handler = self::$_input_handler;
         
         while($line = $handler()) {
            $line_callback($line);
         }
      }
   }
   /**
    * Determines if a parameter with given name was provided as cli param.
    * @param string $switch_name name of the switch to look for in the params, ie: "--my-switch-name"
    * @param string $char_alias one character long string that is an alias for given switch name, ie: "-s".
    * 
    * @return bool
    * @static
    */
   public static function is_switch_on($switch_name,$char_alias=NULL) {
      foreach (self::$_argv as $p) {
         if (substr($p,0,2)=="--") {
            if (substr($p,2)==$switch_name) {
               return true;
            }
         } else
         if ($char_alias && (substr($p,0,1)=="-")) {
            if (false !==(strpos($p,$char_alias))) return true;
         }
      }
   }
   /**
    * Provides the value of parameter as specified by option name (ie: "--my-option='my option value'") 
    *    as it exists in cli params.
    * 
    * @return string | null
    * @static
    */
   public static function get_option($option_name,$char_alias=null) {
      $expect_char_val = false;
      foreach (self::$_argv as $p) {
         if ($expect_char_val) {
            $expect_char_val = false;
            if (substr($p,0,1)!="-") {
               return $p;
            } else {
               return "";
            }
         }
         if (substr($p,0,2)=="--") {
            if (substr($p,2,strlen($option_name))==$option_name) {
               if (false!==(strpos($p,"="))) {
                  $kv = explode("=",$p);
                  $name_check = substr(array_shift($kv),2);
                  if ($name_check==$option_name) {
                     return array_pop($kv);
                  }
               }
               if (substr($p,2)==$option_name) return "";
            }
         } else
         if ($char_alias && (substr($p,0,1)=="-")) {
            if (false !== (strpos($p,$char_alias))) $expect_char_val = true;
         }
      }
   }
   /**
    * Provides the value of given parameter.
    * 
    * @param int $idx specify parameter index
    * @return string
    * @static
    */
   public static function get_param($idx=0) {
      if (isset(self::$_param[$idx])) {
         return self::$_param[$idx];
      }
   }
   /**
    * Provides the currently active cli command
    * 
    * @return string
    * @static
    */
   public static function get_command() {
      //if (empty(self::$_command)) return self::command;
      return self::$_command;
   }
   /**
    * provides the reported width of the current console interface. 
    *    returns (bool) false if unavailable.
    * 
    * @return int | bool
    * @static
    */
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
   /**
    * @var callable | null
    *    callback function to be invoked when an error line has been provided
    * @static
    */
   private static $_error_line_handler=null;
   /**
    * Sets a callback function to be invoked when error line text has been provided.
    *    Callback signature: function(string $text,array $options=null) {};
    * 
    * @param callable $handler
    * 
    * @return void
    * @static
    */
   public static function set_error_line_handler(callable $handler) {
      self::$_error_line_handler = $handler;
   }
   private static $_line_handler;
   /**
    * Sets a callback function to be invoked each time line text has been provided.
    *    Callback signature: function(string $text,array $options=null) {};
    *
    * @param callable $handler
    *
    * @return void
    * @static
    */   
   public static function set_line_handler(callable $handler) {
      self::$_line_handler = $handler;
   }
   private static $_print_handler;
   /**
    * Sets a callback function to be invoked each time a string to be printed has been provided.
    *    Callback signature: function(string $string) {};
    *
    * @param callable $handler
    *
    * @return void
    * @static
    */   
   public static function set_print_handler(callable $handler) {
      self::$_print_handler = $handler;
   }
   
   /**
    * Prints a string.
    * 
    * @static
    * @return void
    */
   public static function print_string($string) {
      if (is_callable(self::$_print_handler)) {
         $handler = self::$_print_handler;
         $handler($string);
         return;
      }
      echo $string;
   }
   
   private static $_dump_handler;
   
   /**
    * Sets a callback function to be invoked each time an expression to be dumped has been provided.
    *    Callback signature: function(...mixed $expr) {};
    *
    * @param callable $handler
    *
    * @return void
    * @static
    */   
   public static function set_dump_handler(callable $handler) {
      self::$_dump_handler = $handler;
   }
   /**
    * displays a dump of given variable
    * 
    * @param $expr,... expression to dump
    */
   public static function var_dump($expr) {
      if (self::$_dump_handler) {
         $handler = self::$_dump_handler;
         call_user_func_array($handler,func_get_args());
         return;
      }
      var_dump(func_get_args());
   }
   
   /**
    * Prints a string according to given format.
    * 
    * @param string $format string/format to print
    * @param mixed $args,... OPTIONAL args
    * 
    * @see sprintf()
    * @return void 
    */
   public static function printf($format) {
      $param_arr = func_get_args();
      $sprintf = "sprintf";
      self::print_string(call_user_func_array($sprintf, func_get_args()));
   }
   
   /**
    * Displays a line of text with optional indentation.
    * 
    * @param string $text (optional) text to display.
    * @param array $options (optional) assoc array of optional parameters:
    *    int $options['tabs'] number of indentations to prepend to text.
    *    bool | int $options['error'] When a non-empty int or bool value; indicates the text is error related.
    *       When value is non-empty integer, the value becomes the reported status code.
    * 
    * @return void
    */
   public static function line($text="",$options=null) {
      $tabs = 0;
      if (!is_array($options)) {
         if (is_int($options)) $tabs = $options['tabs'];
      } else {
         if (!empty($options['tabs'])) {
            $tabs = $options['tabs'];
         }
      }
      $cbopt=['tabs'=>$tabs];
      
      $error = false;
      if (is_array($options)) {
         if (!empty($options['error']) || in_array('error',$options)) {
            if (is_callable(self::$_error_line_handler)) {
               $handler = self::$_error_line_handler;
               $handler($text,$cbopt);
               return;
            }
            if (isset($options['error']) && is_int($options['error'])) {
               $error = $options['error'];
            } else {
               $error = true;
            }
            //$text = \PHP_EOL."ERROR MSG:".\PHP_EOL.$text;
            //$tabs=0;
         }         
      }
      $cbopt['error']=$error;
      
      if (is_callable(self::$_line_handler)) {
         $handler = self::$_line_handler;
         $handler($text,$cbopt);
         return;
      }
      
      if ($error===false) {
         echo self::get_wrapped_line($text,$cbopt);
         return;
      } 
      echo \PHP_EOL."ERROR MSG:".\PHP_EOL;
      echo self::get_wrapped_line($text);
      echo \PHP_EOL;
   }
   public static function get_wrapped_line($text="",$options=null) {
      $tabs = 0;
      if (!is_array($options)) {
         if (is_int($options)) $tabs = $options['tabs'];
      } else {
         if (!empty($options['tabs'])) {
            $tabs = $options['tabs'];
         }
      }
      $tabstring = "";
      if ((int)$tabs>0) {
         $tabcount = 1 * (int) $tabs;
         for($i=0;$i<$tabcount;$i++) {
            $tabstring.=self::tabstring;
         }
      }
      $text = str_replace("\t",self::tabstring,$text);
      if (self::_width()) {
         return wordwrap ( $tabstring.$text, self::$_cols-strlen($tabstring) , \PHP_EOL.$tabstring ,true).\PHP_EOL;
      } else {
         return $text.\PHP_EOL;
      }      
   }

}