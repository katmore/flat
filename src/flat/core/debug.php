<?php
/**
 * \flat\__SUB_NAMESPACE__\ns_shortname definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core;
/**
 * debug message controller
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.3-beta
 * 
 */
class debug extends \flat\core {
   
   /**
    * @var callable
    * @static
    */
   private static $_kill_handler;
   
   /**
    * Sets handler for when debug::kill() event.
    * 
    * @param callable $handler callback definition:
    *    function(array $caller_summary,string $msg)
    *       string $caller_summary['file'] kill caller file path. 
    *       int $caller_summary['line'] kill caller line of file.
    *       string $caller_summary['class']
    *       string $caller_summary['
    *    callback signature: function($caller_summary,$msg) {}; 
    * @static
    */
   public static function set_kill_handler(callable $handler) {
      self::$_kill_handler = $handler;
   }
   
   /**
    * Triggers "kill" handler intended to display caller information
    *    and ends script execution. Default kill handler displays
    *    caller summary and kill message.
    * 
    * @static
    * @param string $msg message
    * @param array $options
    * 
    */
   public static function kill($msg=null,array $options=null) {

      if (!is_array($options)) $options = [];
      
      $param = self::_args2param("kill",$options);
      
      if (!is_string($msg)) {
         $msg = print_r($msg,true);
      }
      
      if (empty($msg)) $msg = "";
      
      foreach(['file','line','class','function','type','object'] as $prop) {
         if (!empty($param['caller']->$prop)) {
            $caller_summary[$prop]=$param['caller']->$prop;
         } else { 
            $caller_summary[$prop]='';
         }
      }
      
      /*
       * determine if there are any options to pass to the kill handler
       */
      $h_options = [];
      foreach(['exit_status'] as $hopt) {
         if (isset($options[$hopt])) {
             $h_options = $options[$hopt];
         } 
      }
      /*
       * sanitize exit_status handler option
       */
      if (
            isset($h_options['exit_status']) &&
            (
                  !is_string($h_options['exit_status']) && 
                  !is_int($h_options['exit_status'])
            )
      ) {
         unset($h_options['exit_status']);
      }      
      
      /*
       * define the fallback kill handler if none exists
       */
      if (empty(self::$_kill_handler)) {
         self::$_kill_handler = function($caller_summary,$msg,$fh_options) {
            $tpos=3;
            echo "\n<pre style='text-align: left;' data-role='debug-kill'><code>(start debug kill handler)\n";
            if (!empty($msg)) echo str_repeat(" ",$tpos)."<strong>message:</strong> $msg\n";
            echo str_repeat(" ",$tpos)."<strong>caller summary:</strong>\n";
            $tpos +=3;
            foreach($caller_summary as $k=>$v) echo str_repeat(" ",$tpos)."$k: $v\n";
            $tpos -=3;
            
            $exit_status = null;
            if (isset($fh_options['exit_status'])) {
               $exit_status = $fh_options['exit_status'];
            }
            
            //$status_string = (($exit_status)?$exit_status:"{none}");
            echo str_repeat(" ",$tpos)."<strong>exit status:</strong> ".(($exit_status)?$exit_status:"{none}")."\n";
            
            echo("(end debug kill handler)</code></pre>\n");            
            
            exit($exit_status);
            
            
         };
      }
    
      
      $handler = self::$_kill_handler;
      $handler($caller_summary,$msg,$h_options);
      echo("\nend script: /flat/core/debug::kill()\n");
      if (isset($fh_options['exit_status'])) $exit_status = $h_options['exit_status'];
      exit($exit_status);      
      
   }
   
   /**
    * @var bool $_suppress when true some methods will not do anything useful.
    * @static
    */
   private static $_suppress=false;
   /**
    * debug supression status
    * 
    * @return bool
    * @see \flat\core\debug::supress_on()
    * @see \flat\core\debug::supress_off()
    * @static
    */
   public static function is_suppress_active() {
      if (self::$_suppress) return true;
      return false;
   }
   
   /**
    * activates debug supression
    * all future calls to debug::msg(), debug::display(), debug::dump() are ignored.
    *    overrides config:debug/active.
    * 
    * @see \flat\core\debug::supress_off()
    * @see \flat\core\debug::is_suppressed()
    * @return void
    * @static
    */
   public static function set_suppress_on() {
      self::$_suppress=true;
   }
   /**
    * deactivates debug supression if active
    * 
    * @see \flat\core\debug::supress_on()
    * @see \flat\core\debug::is_suppressed()
    * @return void
    * 
    * @static
    */   
   public static function set_suppress_off() {
      self::$_suppress=false;
   }
   /**
    * 
    */
   private static function _args2param($label,$options,$default_label=null) {
      if (!is_string($label)) {
         if (is_string($default_label)) {
            $label = $default_label;
         } else {
            $label = "debug";
         }  
      }
      $param = [
         'trigger_error'=>false,
         'callers_offset'=>1,
         'label'=>$label,
         'show_args'=>false,
      ];
      if (!empty($options)) {
         if (is_int($options)) {
            $param['callers_offset'] = $options;
         } else
         if (is_array($options)) {
            if (isset($options['callers_offset'])) {
               $param['callers_offset'] = $options['callers_offset'];
            }
            if (isset($options['trigger_error'])) {
               if (
                  $options['trigger_error']==E_USER_ERROR ||
                  $options['trigger_error']==E_USER_WARNING ||
                  $options['trigger_error']==E_USER_NOTICE 
               ) {
                  $param['trigger_error'] = $options['trigger_error'];
                  if (is_string($label)) {
                     $param['error_msg'] = $label;
                  }    
               }
            }
         }
      }
      $callers_offset=$param['callers_offset'];
      
      $callers=debug_backtrace();

      
      $caller = new \stdClass();
      $caller->file = $callers[$callers_offset]['file'];
      $caller->line = $callers[$callers_offset]['line'];
      
//       $caller->type = "mooo";
//       $caller->class = null;
      
      /*
       * some caller info is correlated more usefully by
       *    increasing the offset 
       */
      foreach (['class','function','object','type'] as $key) {
         $caller->$key = null;
      }
      
      if (isset($callers[1+$callers_offset])) {
         foreach($callers[1+$callers_offset] as $prop=>$val) {
            if (!isset($caller->$prop)) $caller->$prop = $val;
         }
      }
      if ($caller->object && !is_string($caller->object)) {
         if (is_object($caller->object)) {
            $ohash = spl_object_hash($caller->object);
            $oid = ltrim(substr($ohash,0,16),"0").ltrim(substr($ohash,16),"0");
            $caller->object = "object(".get_class($caller->object).")#spl-$oid";
         } else {
            $caller->object = gettype($caller->object);
         }
      }
      
      if (!$param['show_args'] && isset($caller->args)) unset($caller->args);
      
      $param['caller'] = $caller;
      
      
      if ($param['trigger_error']!==false) {
         if (empty($param['error_msg'])) {
            $param['error_msg'] = "unlabled error triggered in ".$caller->file.":".$caller->line;
         } else {
            $param['error_msg'] = $label;
         }
      }
      return $param;
   }
   /**
    * display structured data
    * 
    * @return void
    * 
    * @param mixed $data something to display
    * @param string $label (optional) label of dump
    * @param int|array $options (optional) number of backtrace data 
    *    layers to ignore OR if array assoc array of option key=>val:
    *       int $options['callers_offset'] number of backtrace data layers to ignore,
    *       int $options['trigger_error'] runs trigger_error() with given/derived label
    *       if value = E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE.
    * @static
    */
   public static function dump($data,$label=null,$options=0) {
      
      if (self::$_suppress) return;
      if (!\flat\core\config::get("debug/active")) return;
      
      $param = self::_args2param($label,$options,"dump");
      
      ob_start();
      var_dump($data);
      $dump = ob_get_clean();
      $data = new debug\display_data($label,$param['caller']);
      $data->dump = $dump;
      /*
       * init debug/event_factory
       */
      self::_init_event_factory();
      self::$event_factory->trigger_event('display',$data);
      
      if ($param['trigger_error']!==false) trigger_error(
         $param['error_msg'],
         $param['trigger_error']
      );
   }  
   /**
    * @var \flat\core\debug\event\factory $event_factory event factory for 
    *    handling displayable debug events
    * @static
    */
   private static $event_factory;
   
   /**
    * initialize debug events as needed
    * 
    * @return void
    * @see \flat\core\debug::$event_factory
    * @static
    */
   private static function _init_event_factory() {
      if (!is_a(self::$event_factory,"\\flat\\core\\debug\\event\\factory")) {
         self::$event_factory = new \flat\core\debug\event\factory();
      }
   }
   /**
    * callable handler to override default handler. handler is invoked each 
    *    time a displayable event occurrs. function callback pattern: 
    *    my_display_handler(\flat\core\debug\display_data $data).
    * 
    * @see display_handler() default display handler
    *  
    * @return void
    * 
    * @param callable $handler handler that is invoked
    * 
    * @static
    * @example /deploy/test/demo/flat/core/debug.php
    */
   public static function set_display_handler(callable $handler) {
      if (!\flat\core\config::get("debug/active")) return;
      self::_init_event_factory();
      self::$event_factory->set_handler('display',$handler);
   }
   /**
    * @var int $display_no only used by default handler, otherwise unused. 
    *    incremental count of number of displable debug events.
    * @static
    */
   private static $display_no=0;
   /**
    * default display handler. displays debug message in a terse html format.
    * 
    * @see \flat\core\debug::$display_no
    * @static
    * @return void
    */
   public static function display_handler(debug\display_data $data) {
      if (!\flat\core\config::get("debug/active")) return;
      self::$display_no++;
      if ((PHP_SAPI != 'cli')) {
         
         //echo str_replace("\n","<br>\n",$data->str)."<br>\n";
         echo "<hr>debug #".self::$display_no.": ".$data->str."<br><textarea>";
         var_dump($data);
         echo "</textarea><hr>";
         
      } else {
         
         echo str_replace("\n",PHP_EOL,"$data").PHP_EOL;
      }      
   }
   
   const default_msg = 'debug';
   /**
    * display a simple message from given data.
    *    display up to 80 chars as string if $data is scalar.
    *    otherwise, will display object class or array length.
    *    debug::default_msg is displayed when given $data is empty.
    * 
    * @param mixed $data data to derive message from
    * @param int|array $options (optional) number of backtrace data 
    *    layers to ignore OR if array assoc array of option key=>val:
    *       int $options['callers_offset'] number of backtrace data layers to ignore,
    *       int $options['trigger_error'] runs trigger_error() with given/derived label
    * @return void
    * @static
    */
   public static function msg($data=self::default_msg,$options=0) {
      
      $str = self::data2str($data);
      
      $param = self::_args2param($str, $options, $str);
      $param['callers_offset']++;
      self::display($str,$param);
   }
   
   const data2str_array_format = "{array count: %count% elements}";
   const data2str_object_format = "{object class: %class%}";
   const data2str_empty_string = "{empty string}";
   const data2str_null = "{null}";
   const data2str_other_format = "{non-scalar %typename%}";
   const data2str_truncate = "{orig length: %len% truncated %typename%} %truncated%";
   const data2str_maxlen = 40;
   const data2str_scalar_format = "{%typename%} %strval%";
   /**
    * creates string from given data.
    *    if data is non-empty string, returns data untouched unless 
    *       greater than debug::data2str_maxlen.
    *    if data is scalar, will be returned cast as string.
    *    if data is scalar, and cast string length is greater than 
    *       debug::data2str_maxlen, will be returned as 
    *       debug::data2str_truncate having '%len%' replaced with result of
    *       strlen((string) $data), '%truncated%' replaced with 
    *       substr($data,0,debug::data2str_maxlen), and '%typename%' replaced
    *       with result of gettype($data).
    *    if data is scalar and not string, but cast length is 
    *       debug::data2str_maxlen or less, will be returned as 
    *       debug::data2str_scalar_format having '%typename%' replaced with 
    *       result of gettype($data) and '%strval%' replaced with string cast
    *       value.
    *    if data is an object, returns debug::data2str_object_format having 
    *       '%class%' replaced with class name.
    *    if data is an array, returns debug::data2str_array_format.
    *       having '%count%' replaced with count($data) result.
    *    if data is any other type, returns debug::data2str_other 
    *       having '%typename%' replaced with with gettype($data) result.
    * 
    * @return string
    * @param mixed $param
    */
   public static function data2str($data) {
      // if (is_scalar($data)) return (string) $data;
      // return "{non-scalar data}";
      
      if (is_scalar($data)) {
         if (is_string($data) && empty($data)) return self::data2str_empty_string;
         if (($len = strlen($data))>self::data2str_maxlen) {
            $str = self::data2str_truncate;
            $str = str_replace('%len%', $len, $str);
            $str = str_replace('%typename%', $len, $str);
            return str_replace('%truncated%', substr($data,0,self::data2str_maxlen), $str);
         }
         if (!is_string($data)) {
            $str = self::data2str_truncate;
            $str = str_replace('%strval%',(string) $data,$str);
            return str_replace('%typename%',gettype($data),$str);
         }
         return $data;
      } else
      if (is_object($data)) {
         return str_replace('%class%',get_class($data),self::data2str_object_format);
      } else
      if (is_array($data)) {
         return str_replace('%count%',count($data),self::data2str_object_format);
      } else 
      if (empty($data)) {
         if ($data===null) return self::data2str_null;
         return self::data2str_empty;
      }
      return str_replace('%typename%',gettype($data),self::data2str_other_format);
   }
   
   /**
    * display a string as a line formatted for interface (ie: CLI vs HTTP)
    * 
    * @param mixed $data data to display
    * @param int|array $options (optional) number of backtrace data 
    *    layers to ignore OR if array assoc array of option key=>val:
    *       int $options['callers_offset'] number of backtrace data layers to ignore,
    *       int $options['trigger_error'] runs trigger_error() with given/derived label.
    * @return void
    * @static
    */
   public static function display($data,$options=0) {
      if (self::$_suppress) return;
      if (!\flat\core\config::get("debug/active")) return;

      $param = self::_args2param($data, $options,self::data2str($data));
      
      if (!is_scalar($data)) {
         $param['callers_offset']++;
         return self::dump($data,null,$param);
      }
      
      $data = new debug\display_data($data,$param['caller']);
      
      /*
       * init debug/event_factory
       */
      self::_init_event_factory();

      self::$event_factory->trigger_event('display',$data);
      
      if ($param['trigger_error']!==false) trigger_error(
         $param['error_msg'],
         $param['trigger_error']
      );
   }
}










































































