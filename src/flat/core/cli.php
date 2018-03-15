<?php
/**
 * class definition
*
* PHP version >=7.2
*
* Copyright (c) 2012-2018 Doug Bird.
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
namespace flat\core;

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
    * @var string resource
    * @static
    */
   private static $_resource;

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
   /**
    * Entry point method;
    * Sets the cli argument list which maps to the cli command, params, switches,
    * and options.
    *
    * @param array $argv argv array
    * @param int $param_start_idx (optional) starting index in argv array to use
    *    for cli parameters. default value: (int) 2.
    *
    * @return void
    *
    * @uses cli::get_option()
    * @uses cli::get_command()
    * @uses cli::is_switch_on()
    * @uses cli::get_param()
    * @uses cli::each_arg()
    *
    * @static
    */
   public static function set_argv(array $argv,$param_start_idx=2,$resource_idx=1) {
      if (!is_int($param_start_idx) || ($param_start_idx<0)) $param_start_idx=2;
      if (!is_int($resource_idx) || ($resource_idx<0)) $resource_idx=1;
      if (($cmdarg = $param_start_idx-3)<0) $cmdarg = 0;
      self::$_command = (isset($argv[$cmdarg])) ? pathinfo($argv[$cmdarg],\PATHINFO_BASENAME) : self::command;
      self::$_resource = (isset($argv[$resource_idx])) ? $argv[$resource_idx] : "";
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
    
   /**
    * Sets the reported 'command'
    */
   public static function set_command(string $command) {
      self::$_command = $command;
   }

   /**
    * @var callable
    *    function invoked to get value of the next "line" of "cli input"
    */
   private static $_input_handler;

   /**
    * Entry point method;
    *    Activates a callback handler function invoked to get the value of the next "line" of
    *    "cli input" ideally corresponding stdin (or some interface equivelent).
    *
    * @param callable $handler A handler function which is assumed to provide whatever the next "line"
    *    of "cli input" is; it must either return (string) or (null) value.
    *    This handler is used by cli Application methods to determine if any "cli input" remains
    *    and to get the value of the next  "line" of "cli input" . It is up to this handler implement buffering of input
    *    and to tracking of current "line" position as approprate for the entry point interface.
    *    A return value of (null) by this handler indicates that no remaining "line(s)" of "cli input"
    *    exist.  Callback signature: function() {};
    */
   public static function set_input_line_handler(callable $handler) {
      self::$_input_handler = $handler;
   }
   /**
    * Application method;
    *    Invokes the specified function for each "line" of "cli input" that exists as provided by the
    *    entry porint line handler.
    *
    * @param callable $line_callback Function invoked for each "line" of "cli input", the
    *    value of the "line" is passed in the $line arugment.
    *    Callback defintion: function(string | null $line).
    *    Callback signature: function($line) {};
    */
   public static function each_input_line(callable $line_callback) {
      if (is_callable(self::$_input_handler)) {
         $handler = self::$_input_handler;

         while($line = $handler()) {
            $line_callback($line);
         }
      }
   }
   /**
    * Application method;
    * Determines if a parameter with given name was provided as cli param.
    *
    * @param string $switch_name name of the switch to look for in the params, ie: "--my-switch-name"
    * @param string $char_alias one character long string that is an alias for given switch name, ie: "-s".
    *
    * @return bool
    * @static
    */
   public static function is_switch_on($switch_name,$char_alias=NULL) {
      if (!is_array(self::$_argv)) return false;
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
      return false;
   }
   /**
    * Application method;
    * Provides the value of an optional named cli parameter as specified in argument list.
    *       1. --name=value style:
    *          name and value are delinated by '=' char.
    *             ex: --my-option='my option value'
    *                or
    *             ex: --my-option=my_option_value
    *       2. --name value style:
    *          key and value are delinated by a space, ie: --my-option 'my option value'.
    *       or single-. An empty string return value indicates
    *    that the optional parameter was specified without a value or literally an empty string.
    *    A null value indicates the option was not specified.
    *
    * @return string | null
    * @param string $option_name option name (ie: "--my-option='my option value'")
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
      return null;
   }
   /**
    * Application method:
    *    Provides the value of a "cli param".
    *
    * @param int $idx specifies the "cli param" index position.
    * @return string
    * @static
    */
   public static function get_param($idx=0) {
      if (isset(self::$_param[$idx])) {
         return self::$_param[$idx];
      }
   }
   /**
    * Application method:
    *    Invokes the specified callback function for each item that
    *    exists in the active "cli argument list" as specified by the
    *    cli entry point interface.
    *
    *
    * @param callable $callback callback invoked for each parameter;
    *    callback signature: (void) function(string $argval) {};
    * @static
    * @return void
    */
   public static function each_arg(callable $callback) {
      if (!is_array(self::$_argv)) return;
      if (!count(self::$_argv)) return;
      foreach(self::$_argv as $arg) {
         $callback($arg);
      }
   }
   
   /**
    * Application method:
    *    Provides an enumeration of every long or short "optval" in the active "cli argument list".
    *    
    * @return array contains an element for each "optval" in the order encountered in the "cli argument list";
    *    each element value is an assoc array with the key equal to the option name corresponding to the option value,
    *    <b>for example:</b>
    *    <ul>
    *       <li> <b>cli command</b>: <code>cli.php some-command some-arg --some-flag --foo bar1 --foo=bar2 -- --another-option anotherValue -f=bar1 -f=bar2 -x -y -z -invalid-flag -invalid-optval=foobar</code></li>
    *       <li> <b>code</b>: <code>var_dump(\flat\core\cli::enum_optval())</code></li>
    *       <li> <b>output</b>:
    *    <code><pre>array(4) {
  [0]=>
  array(1) {
    ["foo"]=>
    string(4) "bar1"
  }
  [1]=>
  array(1) {
    ["foo"]=>
    string(4) "bar2"
  }
  [2]=>
  array(1) {
    ["-"]=>
    string(0) ""
  }
  [3]=>
  array(1) {
    ["another-option"]=>
    string(12) "anotherValue"
  }
}</pre></code>
    *    </ul>
    *    
    */
   public static function enum_optval() : array {
      $option = [];
      $last_option_name = null;
      foreach(self::$_argv as $arg) {
         if ($arg==='--') {
            $last_option_name = '--';
            continue;
         } 
         $argsub = null;
         $shortopt = false;
         if ($last_option_name==='--') {
            $option []= ['-'=>''];
            $last_option_name=null;
         } 
         if (substr($arg,0,2)==='--') {
            $argsub  = substr($arg,2);
         } else if (substr($arg,0,1)==='-') {
            $shortopt = true;
            $argsub = substr($arg,1);
         } else if ($last_option_name!==null) {
            if ($last_option_name!=='') {
               $option []= [ $last_option_name=>$arg ];
            }
            $last_option_name=null;
         }
         if ($argsub!==null) {
            if ($last_option_name!==null) {
               $last_option_name = null;
            }
            if (false!==($tokenpos = strpos($argsub,'='))) {
               $optname = substr($argsub,0,$tokenpos);
               if (!$shortopt || (strlen($optname) === 1)) {
                  $option []= [$optname=>substr($argsub,$tokenpos+1)];
               }
            } else {
               if (!$shortopt || (strlen($argsub) === 1)) {
                  $last_option_name = $argsub;
               }
            }
         }
      }
      unset($arg);
      
      return $option;
   }
   
   /**
    * Application method:
    *    Provides an enumeration of each "flag" in the active "cli argument list".
    *    
    * @return array contains an element for each "flag" in the order encountered in the "cli argument list";
    *    each element value is a string equal to the flag name, 
    *    <b>for example:</b>
    *    <ul>
    *       <li> <b>cli command</b>: <code>cli.php some-command some-arg --some-flag --foo bar1 --foo=bar2 -- --another-option anotherValue -f=bar1 -f=bar2 -x -y -z -invalid-flag -invalid-optval=foobar</code></li>
    *       <li> <b>code</b>: <code>var_dump(\flat\core\cli::enum_flag())</code></li>
    *       <li> <b>output</b>:
    *    <code><pre>array(5) {
  [0]=>
  string(9) "some-flag"
  [1]=>
  string(1) "-"
  [2]=>
  string(1) "x"
  [3]=>
  string(1) "y"
  [4]=>
  string(1) "z"
}</pre></code>
    *    </ul>
    */
   public static function enum_flag() : array {
      $flag = [];
      $last_flag = null;
      foreach(self::$_argv as $arg) {
         
         if ($arg==='--') {
            $flag['-'] = true;
            continue;
         } else if ($arg==='-') {
            $flag[''] = true;
            continue;
         }
         
         $argsub = null;
         $shortname = false;
         if (substr($arg,0,2)==='--') {
            $argsub  = substr($arg,2);
         } else if (substr($arg,0,1)==='-') {
            $argsub  = substr($arg,1);  
            $shortname = true;
         } else if ($last_flag!==null) {
            $last_flag=null;
         }
         
         if ($argsub!==null) {
            if ($last_flag!==null) {
               $flag[$last_flag]=true;
               $last_flag = null;
            }
            if (false===($tokenpos = strpos($argsub,'='))) {
               if (!$shortname || (strlen($argsub)===1)) {
                  $last_flag = $argsub;
               }
            }
         }
      }
      unset($arg);
      
      if ($last_flag!==null) {
         $flag[$last_flag]=true;
      }
      
      return array_keys($flag);
   }
   
   /**
    * Application method:
    *    Provides reported cli command.
    *
    * @return string
    * @static
    */
   public static function get_command() {
      //if (empty(self::$_command)) return self::command;
      return self::$_command;
   }

   /**
    * Application method:
    *    Provides the resource as indicated by the cli argument list.
    *
    * @return string
    * @static
    */
   public static function get_resource() {
      //if (empty(self::$_command)) return self::command;
      return self::$_resource;
   }

   /**
    * @var callable
    *    function invoked to determine "cli line width"
    */
   private static $_width_handler;
   /**
    * Entry point method;
    *    Sets a handler which is invoked when the "cli line width" is needed by an
    *    an Application method; for example, when displaying a line of text.
    *
    * @param callable $handler Function invoked each time "cli line width" is needed.
    *    Must return (null) or (int) value. (int) return value indicates
    *    "cli line width" in columns. Callback defintion: (null | int) function().
    *    Callback signature function() { return (int) $cols | null };
    * @return void
    * @static
    */
   public static function set_width_handler(callable $handler) {
      self::$_width_handler = $handler;
   }

   /**
    * Application method;
    *    Returns (bool) true if quiet mode is active, (bool) false otherwise.
    */
   public static function is_quiet() {
      if (self::is_switch_on("quiet","q")) {
         return true;
      }
      return false;
   }

   /**
    * @var callable
    *    Invoked when an "cli error line" has been provided.
    * @static
    */
   private static $_error_line_handler;
   /**
    * Entry point method.
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

   /**
    * @var callable
    *    Invoked with a "cli line" has been provided.
    */
   private static $_line_handler;
   /**
    * Entry point method;
    *    Sets the handler invoked each time a "cli line" has been provided
    *       by the cli application.
    *       Callback signature: function(string $text,array $options=null) {};
    *
    * @param callable $handler
    *
    * @return void
    * @static
    */
   public static function set_line_handler(callable $handler) {
      self::$_line_handler = $handler;
   }
   /**
    * @var callable handler invoked to print strings
    */
   private static $_print_handler;
   /**
    * Entry point method;
    *    Specifies a handler invoked each time a "cli print string" has been provided by
    *    the cli application.
    *
    * @param callable $handler Callback invoked to handle "cli print" events;
    *    The handler is passed the string to be printed as the first argument.
    *    Callback definition: (void) function(string $string) {};
    *
    * @return void
    * @static
    */
   public static function set_print_handler(callable $handler) {
      self::$_print_handler = $handler;
   }


   /**
    * Application method;
    *    Prints a string.
    *    Provides a "cli print string" to the cli entry point interface.
    *
    * @param string $string specifies the "cli print string".
    * @param array $options (optional) assoc array of options.
    *
    * @static
    * @return void
    */
   public static function print_string($string,array $options=null) {
      if (is_callable(self::$_print_handler)) {
         $handler = self::$_print_handler;
         $handler($string,$options);
         return;
      }
      echo $string;
   }

   /**
    * @var callable
    *    invoked when an "cli dump expression" has been provided.
    */
   private static $_dump_handler;

   /**
    * Entry point method;
    *    Specifies a handler invoked a "cli dump expression" has been provided
    *    by a cli application.
    *
    * @param callable $handler Callback signature: (void) function(...mixed $expr) {};
    *
    * @return void
    * @static
    */
   public static function set_dump_handler(callable $handler) {
      self::$_dump_handler = $handler;
   }
   /**
    * Application method;
    *    Displays a dump of given expression.
    *    Provides a "cli dump expression" to the cli entry point interface.
    *
    * @param $expr,... expression to dump, such as in php native var_dump().
    */
   public static function var_dump(...$expr) {
      if (self::$_dump_handler) {
         $handler = self::$_dump_handler;
         call_user_func_array($handler,func_get_args());
         return;
      }
      call_user_func_array("var_dump",func_get_args());
   }

   /**
    * Application method;
    *    Prints a string according to given format.
    *
    * @param string $format string/format to print
    * @param mixed $args,... OPTIONAL args
    *
    * @see sprintf()
    * @return void
    */
   public static function printf($format,...$args) {
      $param_arr = func_get_args();
      if (!count($param_arr)) {
         $param_arr = [""];
      }
      if (count($param_arr)==1) {
         $param_arr[0] = str_replace("%","%%",$param_arr[0]);
      }
      $sprintf = "sprintf";
      self::print_string(call_user_func_array($sprintf, $param_arr));
   }

   /**
    * Application method;
    *    Displays a line of text with optional indentation.
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
      //$not_quiet_only = false;
      if (!is_array($options)) {
         if (is_int($options)) $tabs = $options;
      } else {
         if (!empty($options['tabs'])) {
            $tabs = $options['tabs'];
         }
      }
      $cbopt=['tabs'=>$tabs];

      $error = false;
      if (is_array($options)) {
         if (!empty($options['error']) || in_array('error',$options,true)) {
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
         }
      }
      $cbopt['error']=$error;
      if (is_callable(self::$_line_handler)) {
         $handler = self::$_line_handler;
         $handler($text,$cbopt);
         return;
      }

      if ($error===false) {
         if (!self::is_quiet()) {
            echo self::get_wrapped_line($text,$cbopt);
         }
         return;
      }
      echo \PHP_EOL."ERROR MSG:".\PHP_EOL;
      echo self::get_wrapped_line($text);
      echo \PHP_EOL;
   }

   /**
    * Application method;
    *    Formats a string as specified by wrapping as appropriate for the
    *    currently active "cli width".
    *
    * @param string $text string to format
    * @param int | array $options Tab indentation count or assoc array of options:
    *    int $options['tab'] Tab indentation count.
    *
    * @return string
    */
   public static function get_wrapped_line($text="",$options=null) {
      $tabs = 0;
      if (!is_array($options)) {
         if (is_int($options)) $tabs = $options['tabs'];
      } else {
         if (!empty($options['tabs'])) {
            $tabs = $options['tabs'];
         }
      }
      $identstring = "";
      if ((int)$tabs>0) {
         $tabcount = 1 * (int) $tabs;
         for($i=0;$i<$tabcount;$i++) {
            $identstring.=self::tabstring;
         }
      }
      $text = str_replace("\t",$identstring,$text);
      if (!is_callable(self::$_width_handler)) {
         return $text.\PHP_EOL;
      }
      $width_handler = self::$_width_handler;
      $width = $width_handler();
      if (is_int($width) && ($width >= self::min_cols)) {
         return wordwrap ( $identstring.$text, $width-strlen($identstring) , \PHP_EOL.$identstring ,true).\PHP_EOL;
      }
      return $text.\PHP_EOL;
   }

}
