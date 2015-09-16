<?php
/**
 * File:
 *    exception.php
 * 
 * Purpose:
 *    parent class for exceptions in flat framework
 * 
 *    To assist in debugging, an exception code will be generated if not given.
 *    
 *    The generation routine attempts to create a code that is
 *    consistent and unique to the condition resulting in an exception.
 *
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved..
 * 
 * @package    flat/core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 * 
 */
namespace flat\core;
abstract class exception extends \Exception{
   protected function _value_to_code($value) {
      /*
       * generate checksum by serializing our crc array
       */
      $code_suffix = sprintf("%u",crc32(json_encode($value)));
      
      /*
       * shorten the base of the code
       *    (knowingly ruin the checksum by knocking all but 3 places)
       */
      $code_suffix = substr($code_suffix,0,3);   
      //900000000   
      $code = (int) substr($this->_derive_code(),0,6).$code_suffix;
      return $code;
   }
   protected function _derive_code($code_offset=900000000) {
         /*
          * prepare an array to serialize into a string
          *    start with caller trace array
          */
         $crc=$this->getTrace();
         
         /*
          * use only 0th caller from trace
          */
         $crc = $crc[0]; 
         
         /*
          * to keep code same in more conditions...
          *    remove trace's args data
          */
         if (isset($crc['args'])) unset($crc['args']);
         
         /*
          * add class name
          */
         $crc['name'] = get_class($this);
         
         /*
          * add caller file
          */
         $flatfile = basename( $this->getFile());

         $crc['file'] = basename( $this->getFile());
         
         /*
          * generate checksum by serializing our crc array
          */
         $code = sprintf("%u",crc32(json_encode($crc)));
         
         /*
          * shorten the base of the code
          *    (knowingly ruin the checksum by knocking all but 5 places)
          */
         $code = (int) substr($code,0,5);
         
         /*
          * add offset to the code to indicate it was derived
          */
         $code += $code_offset;
         
         /*
          * to indicate namespace prefix of caller
          *    add 10 million indicates any namespace prefix 
          *    other than found in $flatind array below
          *       each member within $flatind offsets indication an extra +10 mil
          */
         $code += 10000000; 
         $flatind = array(
            "flat\\api",
            "flat\\cli",
            "flat\\core",
            "flat\\data",
            "flat\\fsm",
            "flat\\listener",
            "flat\\meta",
            "flat\\route",
            "flat\\theme",
            "flat\\view",
         );
         /*
          * calculate a 10s of millions place
          */
         $ind=0;
         foreach ($flatind as $pre) {
            $ind+=10000000;
            $len = strlen($pre);
            if(substr($crc['name'],0,$len) == $pre) {
               $code += ($ind);
               break 1;
            }
         }
         return $code;
   }
   public function __construct($msg,$code=0) {
      if (!is_int($code)) $code = 0;
      /*
       * if no code provided...
       *    derive a useful code based on some exception attributes
       */      
      if (empty($code)) $code = $this->_derive_code();
      
      parent::__construct($msg,$code);
   }
}