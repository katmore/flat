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
    * top level of namespace used to canonicalize config key and path 
    * @internal
    */
   const ns_top_level = 'flat';
   
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
    */
   private static function _get_value($key,array $options=NULL) {
      
      $not_found_exception = true;
      if (isset($options['not_found_exception'])) $not_found_exception =$options['not_found_exception'];
      
      /**
       * @uses self::$value all loaded config values indexed assoc by config key of 
       */
      if (isset(self::$value[$key])) return self::$value[$key];
      
      /**
       * @uses $not_found_exception if truthy
       */
      if ($not_found_exception) throw new config\exception\key_not_found($key);
      
   }
   
   /**
    * retrieve all config values from a given config path
    * 
    * @static
    * @param string $path config path. note this is NOT a system path.  
    * @return array
    */
   public static function get_values($path,array $options=NULL) {
      $path = self::_canonicalize_path($path,$options);
      $file = self::$base_dir . "/".$path.".php";
      return self::_get_config_arr($file);
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
       * @uses config::ns_top_level top level of namespace 
       * @uses $path remove top level of namespace from $path (ie: 'flat/')
       * 
       * @internal
       */
      $topns = self::ns_top_level.'/';
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
   private static $_not_config=array();
   
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
    */
   private static function _get_config_arr($filename) {
      
      /*
       * leave if already determined that given filename cannot be loaded
       */
      if (in_array($filename,self::$_not_config)) return;
      
      /*
       * proceed if file can be included
       */
      if (is_file($filename) && is_readable($filename)) {
         
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
    * attempt to load a given namespace's config to memory
    * returns bool true if successful, false otherwise
    * 
    * @static
    * @access private
    * @param string $ns namespace
    * @return bool
    */
   private static function _load_config($ns) {
      
      $file = self::$base_dir . "/".$ns.".php";
      if (in_array($file,self::$loaded)) return true;
              
      if (is_array($cfg = self::_get_config_arr($file))) {
         foreach ($cfg as $key=>$val) {
            //$full_key = "$ns/".$key;
            //echo "src/config::key: $full_key=$val<br>";
            self::_set_value("$ns/".$key,$val);
         }
         
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









