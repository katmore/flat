<?php
/**
 * class \flat\core\config 
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
namespace flat\core;

/**
 * configuration controller ideal for storing deployment details such as 
 * filesystem paths, connection parameters, etc. 
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 */
class config extends \flat\core {
   
   const NS_TOP_LEVEL = 'flat';
   const REFERENCE_VALUE_IDENTIFIER_PREFIX = 'CONFIG-REF';
   const REFERENCE_VALUE_IDENTIFIER_TERMINATOR = '::';
   const REFERENCE_VALUE_INLINE_STRING_START_TOKEN = '<%'.self::REFERENCE_VALUE_IDENTIFIER_PREFIX.'-STRING'.self::REFERENCE_VALUE_IDENTIFIER_TERMINATOR;
   const REFERENCE_VALUE_INLINE_STRING_END_TOKEN = '%>';
   const REFERENCE_VALUE_BASENAME_TERMINATOR_CHAR = '.';
   
   /**
    * @static
    * @access private
    * @var string base directory for config files
    */   
   private static $base_dir;
   
   /**
    * @static
    * @access private
    * @var string[] array of loaded config files
    */
   private static $ref_loaded = [];
   
   /**
    * @static
    * @access private
    * @var string[] array of loaded config files
    */
   private static $loaded;
   
   /**
    * @static
    * @access private
    * @var array associative array of config values indexed by config key
    */   
   private static $value;
   
   /**
    * @static
    * @access private
    * @var string[] array of config keys already searched for, successful or not
    */    
   private static $searched;

   /**
    * @static
    * @access private
    * @var string[] array of config keys already searched for, successful or not
    */
   private static $ref_searched;
   
   private static $ref_value = [];
   
   private static $transformed_value = [];
   
   /**
    * tests if given base directory is useable
    * 
    * @static
    * @access private
    * @param string $base_dir base directory path
    * @throws config\exception\bad_base_dir if directory is not useable
    * @return void
    */
   private static function _test_base_dir($base_dir) {
      if (empty($base_dir)) {
         throw new config\exception\bad_base_dir\is_empty();
      }
      if (!is_readable($base_dir)) {
         throw new config\exception\bad_base_dir\not_readable();
      }

      if (!is_dir($base_dir)) {
         throw new config\exception\bad_base_dir\not_dir();
      }
      
   }
   
   /**
    * save config value to memory for given key
    * 
    * @static
    * @access private
    * @param string $key config key
    * @param mixed $value value associated with given config key
    * @return void
    */
   private static function _set_value($key,$value) {
      self::$value[$key] = $value;
   }
   
   /**
    * save config ref value to memory
    *
    * @static
    * @access private
    * @param string $basename ref basename
    * @param string $ns ref namespace
    * @param mixed $value value associated with given config ref basename and ns
    * @return void
    */
   private static function _set_ref_value(string $basename, string $ns, $value) : void {
      if (!isset(self::$ref_value[$basename])) {
         self::$ref_value[$basename] = [];
      }
      self::$ref_value[$basename][$ns] = $value;
   }
   
   /**
    * retrieve a config reference value
    * 
    * @static
    * @param string $basename refernce basename 
    * @param string $ns reference 
    * @return mixed
    * @throws \flat\core\config\exception\not_ready
    * @throws \flat\core\config\exception\bad_key
    * @throws \flat\core\config\exception\bad_config_file
    */
   public static function get_ref_value(string $basename, string $ns) {
      if (empty(self::$base_dir)) {
         if (isset($options['default'])) return $options['default'];
         throw new config\exception\not_ready();
      }
      
      if (isset(self::$ref_searched[$basename]) && !empty(self::$ref_searched[$basename][$ns])) {
         return self::_get_ref_value($basename, $ns);
      }
      
      if (!isset(self::$ref_searched[$basename])) {
         self::$ref_searched[$basename] = [];
      }
      
      self::$ref_searched[$basename][$ns]=true;
      
      self::_load_config_ref($basename);
      
      return self::_get_ref_value($basename, $ns);
   }
   
   /**
    * retrieve a config reference value from memory
    * 
    * @static
    * @internal
    */
   private static function _get_ref_value(string $basename, string $ns) {
      if (isset(self::$ref_value[$basename]) && key_exists($ns,self::$ref_value[$basename])) {
         return self::$ref_value[$basename][$ns];
      }
      throw new config\exception\key_not_found(self::REFERENCE_VALUE_IDENTIFIER_PREFIX.self::REFERENCE_VALUE_IDENTIFIER_TERMINATOR."$basename.$ns");
   }
   
   private static function _get_transformed_value($rawval,$key) {
      if (is_array($rawval)) {
         foreach($rawval as $k=>$v) {
            $rawval[$k] = self::_get_transformed_value($v,$key);
         }
         unset($v);
         unset($k);
         return $rawval;
      } else
      if (is_string($rawval)) {
         if (substr($rawval,0,strlen(static::REFERENCE_VALUE_IDENTIFIER_PREFIX.static::REFERENCE_VALUE_IDENTIFIER_TERMINATOR)) == static::REFERENCE_VALUE_IDENTIFIER_PREFIX.static::REFERENCE_VALUE_IDENTIFIER_TERMINATOR) {
            $refsub = substr($rawval,strlen(static::REFERENCE_VALUE_IDENTIFIER_PREFIX.static::REFERENCE_VALUE_IDENTIFIER_TERMINATOR));
            if (false!==($dotpos = strpos($refsub,static::REFERENCE_VALUE_BASENAME_TERMINATOR_CHAR))) {
               $basename = substr($refsub,0,$dotpos);
               $ns = substr($refsub,$dotpos+1);
               if (!empty($basename) && !empty($ns)) {
                  try {
                     return self::get_ref_value($basename, $ns);
                  } catch (config\exception\key_not_found $e) {
                     throw new config\exception\key_not_found("$key=".static::REFERENCE_VALUE_IDENTIFIER_PREFIX.static::REFERENCE_VALUE_IDENTIFIER_TERMINATOR."$basename.$ns");
                  }
               }
            }
         } else {
            $val = $rawval;
            $offset = 0;
            $str_replace = [];
            for(;;) {
               if (false!==($pos1 = strpos($val,static::REFERENCE_VALUE_INLINE_STRING_START_TOKEN,$offset))) {
                  
                  if (false!==($pos2 = strpos($val,static::REFERENCE_VALUE_INLINE_STRING_END_TOKEN,$offset))) {
                     $offset = $pos2 + strlen(static::REFERENCE_VALUE_INLINE_STRING_END_TOKEN);
                     $refsub = substr($val,$pos1+strlen(static::REFERENCE_VALUE_INLINE_STRING_START_TOKEN));
                     if (false===($dotpos = strpos($refsub,static::REFERENCE_VALUE_BASENAME_TERMINATOR_CHAR))) {
                        continue;
                     }
                     $basename = substr($refsub,0,$dotpos);
                     $nslen = $pos2 - $pos1 - strlen($basename) - strlen(static::REFERENCE_VALUE_INLINE_STRING_START_TOKEN) - 1;
                     $ns = substr($refsub,$dotpos+1, $nslen);
                     $inline_config_ref = static::REFERENCE_VALUE_INLINE_STRING_START_TOKEN.$basename.static::REFERENCE_VALUE_BASENAME_TERMINATOR_CHAR.$ns.static::REFERENCE_VALUE_INLINE_STRING_END_TOKEN;
                     if (isset($str_replace[$inline_config_ref])) {
                        continue;
                     }
                     $refval = self::get_ref_value($basename, $ns);
                     if (!is_scalar($refval)) {
                        throw new config\exception\non_scalar_inline_ref_value($key, $basename, $ns);
                     } else
                        if (is_bool($refval)) {
                           $replace_with_string = $refval?"true":"false";
                        } else {
                           $replace_with_string = $refval;
                        }
                        $str_replace[$inline_config_ref] = $replace_with_string;
                  } else {
                     
                     break 1;
                     
                  }
                  
               } else {
                  
                  break 1;
               }
            }
            unset($inline_config_ref);
            unset($replace_with_string);
            
            foreach($str_replace as $inline_config_ref=>$replace_with_string) {
               $val = str_replace($inline_config_ref,$replace_with_string, $val);
            }
            unset($inline_config_ref);
            unset($replace_with_string);
            return $val;
            
         }
      }
//       var_dump($rawval);
//       die(__FILE__);
      return $rawval;
   }
   
   /**
    * retrieve a config value from memory
    * 
    * @static
    * @access private
    * @param string $key config key
    * @param array|null $options (optional) assoc array
    *    bool $options['not_found_exception'] set to bool false to not throw
    *    exception for key not found condition
    * @throws config\exception\key_not_found thrown when there is no value
    *    associated with given config key if $options['not_found_exception']
    *    is not false 
    * @return mixed
    * @internal
    */
   private static function _get_value($key,array $options=NULL) {
      
      $not_found_exception = true;
      if (isset($options['not_found_exception'])) $not_found_exception =$options['not_found_exception'];
      
      /**
       * @uses self::$value all loaded config values indexed assoc by config key of 
       * @internal
       */
      if (isset(self::$value[$key])) {
         if (!isset(static::$transformed_value[$key])) {
            static::$transformed_value[$key] = static::_get_transformed_value(self::$value[$key], $key);
         }
         return static::$transformed_value[$key];
      }
      
      /**
       * @uses $not_found_exception if truthy
       */
      if ($not_found_exception) throw new config\exception\key_not_found($key);
      
   }
   
   /**
    * retrieve all config reference values from a given basename
    * 
    * @static
    * @param string $basename reference basename 
    * @return array
    */
   public static function enum_ref_values(string $basename) {
      $subdir = $basename;
      $subdir = str_replace("\\","/",$subdir);
      $subdir = str_replace("/",\DIRECTORY_SEPARATOR, $subdir);
      
   }
   
   /**
    * retrieve all config values from a given config path
    * 
    * @static
    * @param string $path config path. note this is NOT a system path.  
    * @return array
    */
   public static function enum_values($path,array $options=NULL) {
      /*
       * @uses config::$base_dir when empty config values are not available
       *
       * @internal
       */
      if (empty(self::$base_dir)) {
         throw new config\exception\not_ready();
      }
      $path = self::_canonicalize_path($path,$options);
      $file = self::$base_dir . "/".$path.".php";
      $cfg = self::_get_config_arr($file);
      foreach($cfg as $k=>&$v) {
         $v=static::get("$path/$k",$options);
      }
      unset($k);
      unset($v);
      return $cfg;
   }
   
   /**
    * return config value or given default value if config is unavailable 
    * (such as key not found, config not loaded)
    * 
    * @static
    * @access private
    * @param string $key config key to retrieve
    * @param mixed $default (optional) value to return if unable to get config value
    * @param array|null $options optional assoc array
    * 
    * @return mixed
    */
   private static function _get_value_or_default($key,$default=NULL,array $options=NULL) {
      try {
         return self::get($key,$options);
      } catch (config\exception $e) {
         return $default;
      }
      
   }
   
   /**
    * convert and standarize path as appropriate
    * returns resulting canonicalized path
    * 
    * @static
    * @access static
    * @param string $path path to canonicalize
    * @param array|NULL $options optional assoc array
    * @return string 
    */
   private static function _canonicalize_path($path,array $options=NULL) {
      
      /** 
       * @uses $path must be string
       * 
       * @internal 
       */
      if (!is_string($path)) throw new config\exception\bad_key();
      
      /** @uses $path convert separators to backslash **/
      $path = str_replace("\\","/",$path);
      
      /** @uses $path trim whitespace, trim separator **/
      $path = trim(trim($path),"/");

      /** 
       * @uses config::NS_TOP_LEVEL top level of namespace 
       * @uses $path remove top level of namespace from $path (ie: 'flat/')
       * 
       * @internal
       */
      $topns = self::NS_TOP_LEVEL.'/';
      if (substr($path,0,strlen($topns))==$topns) {
         $path = substr($path,0,strlen($topns)*-1);
      }
      
      return $path;
   }
   
   /**
    * retrieve config value
    * 
    * @static
    * @param string $key key associated with desired config value
    * @param array $options (optional) assoc array of options.
    *    string $options['default'] default value to return if cannot retrieve config value.
    *    bool $options['not_found_exception'] set to bool false to suppress exception thrown when key not found.
    *    string $options['key_name_base'] value to remove from the beginning of key-name; useful for inherited classes
    *       to get an option from their own namespace.  
    * @return mixed
    * @throws \flat\core\config\exception\not_ready
    * @throws \flat\core\config\exception\bad_key
    * @throws \flat\core\config\exception\bad_config_file
    */
   public static function get($key,array $options=NULL) {
      
      /*
       * @uses config::$base_dir when empty config values are not available 
       * 
       * @internal
       */
      if (empty(self::$base_dir)) {
         if (isset($options['default'])) return $options['default'];
         throw new config\exception\not_ready();
      }
      
      $base_change = false;
      /*
       * apply 'key_name_base' option
       */
      if (!empty($options['key_name_base'])) {
         $key_new = str_replace($options['key_name_base'],'',$key);
         if ($key_new != $key) {
            $key = $key_new;
            $base_change = true;
         }
      }      
      
      /**
       * @uses config::_canonicalize_path() normalize key as needed for consistency
       *
       * @internal
       */
      $key = self::_canonicalize_path($key,$options);
      
      /*
       * apply 'key_name_base' option on canonicalized path
       */
      if (!$base_change && !empty($options['key_name_base'])) {
         $key_base = self::_canonicalize_path($options['key_name_base']);
         $key = str_replace($key_base,'',$key);
      }
               
      /*
       * apply 'default' option
       *    
       * @uses $options check if $option['default'] element exists
       *    and unsets the element if it does
       *  
       * @uses config::_get_value_or_default() returns value from 
       *    self::_get_value_or_default() if $option['default'] exists
       * 
       * 
       * @internal
       */
      if (is_array($options) && key_exists('default',$options)) {
         
         /* @var $default default value dereferenced from $options */
         $default = $options['default'];
         unset($options['default']);
         return self::_get_value_or_default($key,$default,$options);
      }
      

      
      /**
       * @uses self::$searched when $key exists as index it is unneccesary to read config file
       * @uses config::_get_value() wrapper to retrieve config value
       */
      if (!empty(self::$searched[$key])) return self::_get_value($key,$options);
      
      /**
       * @var $key find a config file matching given $key (path)
       * 
       * @var $resource array of path elements
       * 
       * @uses config::_load_config() attempt to load config file
       *    if not successful, remove element from path and try again
       * 
       * @internal
       */
      $resource = explode("/",$key);
      while(array_pop($resource)) if (self::_load_config(
         implode("/",$resource)) // concatonate path from current element list with implode()
      ) break 1;
      
      /**
       * @uses config::$searched indicate given $key has been searched
       *    wheather successful or not
       * 
       * @internal
       */
      self::$searched[$key]=true;
      
      /**
       * @uses config::_get_value() retrieve config value
       * 
       * @internal
       */
      return self::_get_value($key,$options);
      
   }
   
   /**
    * @static
    * @var string[] $_not_config filenames that previously failed to load
    * @access private
    */
   private static $_not_config_ref=[];

   /**
    * retrieve assoc array of config reference values from config file
    *
    * @static
    * @access private
    * @param string $filename filename
    * @throws config\exception\bad_config_file if config file
    *    does not contain assoc array
    *
    * @return array|null
    * @internal
    */
   private static function _get_config_ref_arr(string $filename) {
      /*
       * leave if already determined that given filename cannot be loaded
       */
      if (in_array($filename,self::$_not_config_ref)) return;
      
      /*
       * proceed if file can be included
       */
      if (is_file($filename) && is_readable($filename) && (pathinfo($filename,\PATHINFO_EXTENSION)=='json')) {
         
         $doc = file_get_contents($filename);
         
         if (empty($doc)) throw new config\exception\bad_config_file(
               $filename,
               "config ref file cannot be empty"
               );
         
         if (null === ($cfg = json_decode($doc))) {
            throw new config\exception\bad_config_file(
                  $filename,
                  "config ref file contains invalid JSON"
                  );
         }
         
         if (!is_object($cfg)) {
            throw new config\exception\bad_config_file(
                  $filename,
                  "config ref file must contain a JSON object"
                  );
         }
         
         $cfg = json_decode($doc, true);
         
         foreach ($cfg as $key=>$val) {
            if (!is_string($key))
            throw new config\exception\bad_key;
         }
            
         return $cfg;
            
      } else {
         
         self::$_not_config_ref[] = $filename;
         
      }
   }
   
   /**
    * @static
    * @var string[] $_not_config filenames that previously failed to load 
    * @access private
    */
   private static $_not_config=[];
   
   /**
    * retrieve assoc array of config values from config file
    * 
    * @static
    * @access private
    * @param string $filename filename
    * @throws config\exception\bad_config_file if config file 
    *    does not contain assoc array
    * 
    * @return array|null
    * @internal
    */
   private static function _get_config_arr($filename) {
      
      /*
       * leave if already determined that given filename cannot be loaded
       */
      if (in_array($filename,self::$_not_config)) return;
      
      /*
       * proceed if file can be included
       */
      if (is_file($filename) && is_readable($filename) && (pathinfo($filename,\PATHINFO_EXTENSION)=='php')) {
         
         $cfg = include($filename);
         
         if (!is_array($cfg)) throw new config\exception\bad_config_file(
            $filename,
            "config file must return assoc array"
         );
         
         foreach ($cfg as $key=>$val) if (!is_string($key)) 
            throw new config\exception\bad_key();
         
         return $cfg;
         
      } else {
         
         self::$_not_config[] = $filename;
         
      }
   }
   
   /**
    * attempt to load a given config ref basename to memory
    * returns bool true if successful, false otherwise
    *
    * @static
    * @access private
    * @param string $ns namespace
    * @return bool
    */
   private static function _load_config_ref(string $basename) : bool {
      $subpath = str_replace("\\","/",$basename);
      $file = self::$base_dir . "/".$subpath.".json";
      $file = str_replace("/",\DIRECTORY_SEPARATOR, $file);
      if (in_array($file,self::$ref_loaded)) return true;
      
      if (is_array($cfg = self::_get_config_ref_arr($file))) {
         foreach ($cfg as $ns=>$val) {
            self::_set_ref_value($basename,$ns,$val);
         }
         self::$ref_loaded []= $file;
         return true;
      }
      return false;
   }
   
   /**
    * attempt to load a given namespace's config to memory
    * returns bool true if successful, false otherwise
    * 
    * @static
    * @access private
    * @param string $ns namespace
    * @return bool
    */
   private static function _load_config($ns) : bool {      
      $file = self::$base_dir . "/".$ns.".php";
      $file = str_replace("/",\DIRECTORY_SEPARATOR, $file);
      if (in_array($file,self::$loaded)) return true;
              
      if (is_array($cfg = self::_get_config_arr($file))) {
         foreach ($cfg as $key=>$val) {
            self::_set_value("$ns/".$key,$val);
         }
         self::$loaded []= $file;
         return true;
      }
      return false;
   }   
   
   /**
    * set base directory for config files
    * 
    * @static
    * @param string $base_dir system path of config base directory
    * @throws config\exception\bad_base_dir if directory is not useable
    * @return void
    */
   public static function set_base_dir($base_dir) {

      self::_test_base_dir($base_dir);
      self::$loaded = array();
      self::$base_dir = $base_dir;
      self::$value = array();
      self::$searched = array();
   }
}








