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

class rand implements \flat\core\generator {

   /**
    * returns randomly generated unsigned integer
    * 
    * @static
    * @param int $min
    * @param int $max
    * @return int
    */   
   protected static function _get($min,$max) {
      if (\PHP_VERSION>=7) {
         return random_int($min,$max);
      }
      extract(unpack('Nrandom', openssl_random_pseudo_bytes(4,$strong)));
      if (!$strong) throw new rand\exception\failed();
      return abs($random) % (($max-$min)+1) + $min;
   }
   
   public static function bytes($len) {
      if (\PHP_VERSION>=7) {
         $bytes = random_bytes($len);
      } else {
         $bytes = openssl_random_pseudo_bytes($len,$strong);
         if (!$strong) throw new rand\exception\failed();
      }
      return $bytes;
   }
   
   /**
    * sanity check on min, max parameters
    * @return void
    * @throws \flat\core\rand\exception\bad_param failed sanity check
    * @static
    */
   protected static function _check_params($min,$max) {
      if (!is_int($min)) throw new rand\exception\bad_param(
         'min','must be unsigned integer'
      );
      if (!is_int($max)) throw new rand\exception\bad_param(
         'max','must be unsigned integer'
      );
      if ($min<0) throw new rand\exception\bad_param(
         'min',"must 0 or greater. min=$min"
      );
      if ($max>getrandmax()) throw new rand\exception\bad_param(
         'max',"cannot be greater than getrandmax() '".getrandmax()."'. max=$max"
      );
      if ($max<$min) throw new rand\exception\bad_param(
         'max',"must be greater than min. min=$min, max=$max"
      );
   }
   
   /**
    * generates a random unsigned integer
    * 
    * @return int
    * 
    * @param int $min (optional) default = 0. genreated number will be this 
    *    value or greater. must be unsigned.
    * @param int $max (optional) default = getrandmax(). generated number will
    *    be this value or lower. must be unsigned.
    * 
    * @throws \flat\core\rand\exception\bad_param failed parameter sanity check
    * 
    * @see http://php.net/manual/en/function.getrandmax.php
    * @see http://php.net/openssl_random_pseudo_bytes
    */
   public static function get( $min=0, $max=NULL) {
      if (!$max) $max = getrandmax();
      self::_check_params($min,$max);
      return self::_get($min, $max);
   }   
   public function __invoke( $min=0, $max=NULL) {
      return self::get($min,$max);
   }
   /**
    * generates a random integer as prepared in constructor
    * 
    * @return int
    * 
    * @see \flat\core\generator part of the generator interface
    * @see rand::__construct()
    */
   public function generate() {
      return self::get($this->_min,$this->_max);
   }
   private $_min;
   private $_max;
   /**
    * prepares for generating a random integer
    * 
    * @param array $config (optional) assoc array of paramters:
    *    int $config['min'] min value of randomly generated integer.
    *    int $config['max'] max value of randomly generated integer.
    */
   public function __construct($min=0,$max=NULL) {
      /*
       * sanity check by generating one throw-away random value with given args
       *    to trigger any exceptions
       */
      self::get($min,$max);
      $this->_min = $min;
      $this->_max = $max;
   }
}
















