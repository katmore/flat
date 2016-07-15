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
namespace flat\core\rand;

class str implements \flat\core\generator {
   private $allvalid;
   private $len;
   /**
    * fallback value when no length provided
    * @uses str::load()
    * @uses str::__construct()
    */
   const default_len=20;
   
   /**
    * generates a random string from given parameters
    * 
    * @param int $len length of random string
    * @param array $options assoc config parameters:
    *    bool $options['use_upperalpha'] (optional) default true, 
    *       if true, "ABCDEFGHIJKLMNOPQRSTUVWXYZ" chars are included potential 
    *       use in generated strings.
    *    bool $options['use_loweralpha'] (optional) default true, 
    *       if true, "abcdefghijklmnopqrstuvwxyz" chars are included potential 
    *       use in generated strings.
    *    bool $options['use_digits'] (optional) default true,
    *       if true, "0123456789" chars are included for potential use in
    *       generated strings.
    *    string $options['charpool'] (optional) if given, only chars in this value will be 
    *       potentially used in generated strings.
    *    bool $options['load_object'] (optional) default false,
    *       if true, returns \flat\core\rand\str() object rather than string created by.
    * 
    * @throws \flat\core\rand\exception\not_enough_valid_chars 
    *    if $config['charpool'] length is less than str::min_valid_chars.
    * 
    * @throws \flat\core\rand\exception\bad_config paramter sanity errors
    * 
    * @throws \flat\core\rand\exception\failure if system fails to generate string expected length
    * @static
    */   
   public static function load($len=NULL,array $options=NULL) {
      $config = array('len'=>$len);
      if (is_array($options)) {
         foreach ( array(
            'charpool',
            'use_upperalpha',
            'use_loweralpha',
            'use_digits'
         ) as $key ) {
            if (isset($options[$key])) {
               $config[$key] = $options[$key];
            } else {
               if (in_array($key,$options,true)) {
                  $config[$key] = true;
               }
            }
         }
      }

      if (isset($options['load_object']) && $options['load_object']) {
         return new static($config);
      }
      
      return (string) new static($config);
   }   
   
   /**
    * alias of str::load()
    * 
    * @see \flat\core\rand\str::load()
    */
   public static function get($len=NULL,array $options=NULL) {
      return self::load($len,$options);
   }

   const min_len = 1;
   
   /**
    * generates a new random string
    * 
    * @return string
    * 
    * @throws \flat\core\rand\exception\bad_config
    */
   public function get_string($len_param=NULL) {
      $len = $this->len;
      if ($len_param!==NULL) {
         if (is_int($len_param)) {
            if ($len_param>=self::min_len) {
               $len = $len_param;
            }
         }
      }
      $validmax = strlen($this->allvalid) - 1;
      $str = "";
      try {
         new \flat\core\rand(0,$validmax);
      } catch (exception\bad_param $e) {
         throw new exception\bad_config(
            "could not calculate long enough random max"
         ); 
      }
      $rand = new \flat\core\rand(0,$validmax);
      for ($l=0;$l<$len;$l++) {
         $str .= $this->allvalid[$rand->generate()];
      }
      
      if (strlen($str)!=$len) throw new exception\failed($len);
      
      return $str;
   }
   
   /**
    * generates a random string as prepared in constructor
    *
    * @return string
    * 
    * @see \flat\core\generator part of the generator interface
    * @see str::__construct()
    * @see str::get_string()
    */
   public function generate() {
      return $this->get_string();
   }
   /**
    * magic method __invoke
    *    alias of str::load()
    * @return string
    * @param int $len (optional) random string length
    * @param array $options (optional) options regarding generated output,
    *    see str::load().
    * 
    * @see \flat\core\rand\str::load()
    */
   public function __invoke($len=NULL,array $options=NULL) {
      return self::load($len,$options);
   }

   const min_valid_chars = 2;
   /**
    * criteria for generating random strings
    * 
    * @param array $config assoc config parameters:
    *    int $config['len'] (optional) override default random string length,
    *       ignored unless higher than str::min_len.
    *    bool $config['use_upperalpha'] (optional) default true, 
    *       if true, "ABCDEFGHIJKLMNOPQRSTUVWXYZ" chars are included potential 
    *       use in generated strings.
    *    bool $config['use_loweralpha'] (optional) default true, 
    *       if true, "abcdefghijklmnopqrstuvwxyz" chars are included potential 
    *       use in generated strings.
    *    bool $config['use_digits'] (optional) default true,
    *       if true, "0123456789" chars are included for potential use in
    *       generated strings.
    *    string $config['charpool'] (optional) if given, only chars in this value will be 
    *       potentially used in generated strings.
    * 
    * @throws \flat\core\rand\exception\not_enough_valid_chars 
    *    if $config['charpool'] length is less than str::min_valid_chars.
    * 
    * @throws \flat\core\rand\exception\bad_config
    */
   public function __construct(array $config=NULL) {
      
      $this->len = self::default_len;
      if (!empty($config['len'])) {
         if (is_int($config['len']) || ((int) sprintf("%d",$config['len']) == $config['len'])) {
            if ($config['len']>=self::min_len) {
                $this->len = $config['len'];
            }
         }
      }
      $this->allvalid = "";
      if (!empty($config['charpool'])) {
         if (!is_string($config['charpool'])) throw new exception\bad_config(
            "charpool if given must be string"
         );
         $this->allvalid = $config['charpool'];
         
      } else {
            
         $loweralpha = "abcdefghijklmnopqrstuvwxyz";
         $upperalpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
         $digits = "0123456789";
         
         $use_upperalpha = true;
         $use_loweralpha = true;
         $use_digits = true;
         
         if (isset($config['use_upperalpha'])) {
            if ($config['use_upperalpha']===false) {
               $use_upperalpha = false;
            }
         }
         
         if (isset($config['use_loweralpha'])) {
            if ($config['use_loweralpha']===false) {
               $use_loweralpha = false;
            }
         }
         
         if (isset($config['use_digits'])) {
            if ($config['use_digits']===false) {
               $use_digits = false;
            }
         }            
   
         if ($use_upperalpha)
            $this->allvalid .= $upperalpha;
         
         if ($use_loweralpha)
            $this->allvalid .= $loweralpha;
         
         if ($use_digits)
            $this->allvalid .= $digits;
         
      }

      if (strlen($this->allvalid)<self::min_valid_chars) throw new exception\not_enough_valid_chars();
      
      $this->_string = $this->get_string();
   }/*end __construct()*/
   private $_string;
   /**
    * \flat\core\rand\str::__toString() magic method
    * @link http://php.net/manual/en/language.oop5.magic.php#object.tostring
    * @return string
    */
   public function __toString() {
      return $this->_string;
   }   
}
















