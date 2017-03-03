<?php
/**
 * class definition 
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
namespace flat\core\util;
class time_ago {  
   
   /**
    * string term to use for relative time in the past
    */
   const future_term = 'from now';
   
   /**
    * string term to use for relative time in the future
    */   
   const past_term = 'ago';   
   
   /**
    * @var string[] unit type values mapped to their floor in seconds
    *    (floor is seconds required to use a respective unit in the relative phrase) 
    */
   protected static $unit_sec_floor = [ 
       31104000   =>  'Y',  /*--(12 * 30 * 24 * 60 * 60)--*/
       2592000    =>  'M', /*--(30 * 24 * 60 * 60)--*/
       86400      =>  'D',   /*--(24 * 60 * 60)--*/ 
       3600       =>  'H',  /*--(60 * 60)--*/
       60         =>  'I',
       1          =>  'S'
    ];
    
   /**
    * @var string[] map of singluar time terms mapped to unit type
    */   
   protected static $unit_term_singular = [
      'Y' =>  'year', 
      'M' =>  'month',
      'D' =>  'day',
      'H' =>  'hour',
      'I' =>  'minute',
      'S' =>  'second'
   ];   
   /**
    * @var string[] map of singluar time terms mapped to unit type
    */
   protected static $unit_term_plural = [
      'Y' =>  'years',
      'M' =>  'months',
      'D' =>  'days',
      'H' =>  'hours',
      'I' =>  'minutes',
      'S' =>  'seconds'
   ];   
   
   /**
    * creates a relative time phrase ("10 minutes ago", "1 year ago", etc.)
    *    and returns it as a string. returns (null) if $time specification 
    *    is invalid.  
    * 
    * @param string | int $time Specifies the time to measure to. 
    *    If value is a string, it must recognized by strtotime();
    *    such as an ISO timestamp, or any or date/time string.
    *    If value is an integer, it is assumed to be a unix timestamp.
    * 
    * @param string | int $relative_to OPTIONAL defaults to current time. 
    *    Specifies the time to measure from. 
    *    If value is a string, it must recognized by strtotime();
    *    such as an ISO timestamp, or any or date/time string.
    *    If value is an integer, it is assumed to be a unix timestamp.
    * 
    * @see strtotime() for acceptable $time, $relative_to string values
    * 
    * @return string | null
    * @static
    */
   public static function str($time,$relative_to=null)
   {
      if (!is_int($time)) {
         if (empty($time)) return null;
         if (false === ($time = strtotime($time))) return null;
      }
      if (!is_int($relative_to)) {
         if (!empty($relative_to)) {
            $relative_to = strtotime($relative_to);
         }
         if (empty($relative_to)) {
            $relative_to = time();
         }
      }      
       $etime = $relative_to - $time;
       
       if ($etime == 0) {
          return '0 '.self::$unit_term_plural['S'].' '.self::past_term;
       } elseif ($etime < 1) {
          $etime = $etime * -1;
           $relterm = self::future_term;
       } else {
          $relterm = self::past_term;
       }
   
       foreach (self::$unit_sec_floor as $sec => $unit_type)
       {
           $amount = $etime / $sec;
           if ($amount >= 1) {
               $amount = round($amount);
               if ($amount > 1) {
                  $unit_term = self::$unit_term_plural[$unit_type];
               } else {
                  $unit_term = self::$unit_term_singular[$unit_type];
               }
               return "$amount $unit_term $relterm";
           }
       }
   } 
   
   public function __toString() {
      return (string) self::str($this->_time);
   }
   /**
    * returns a relative time phrase ("10 minutes ago", "1 year ago", etc.)
    *    as a string. returns (null) if $time specification in the constructor was invalid.  
    */
   public function time_ago() {
      return self::str($this->_time);
   }
   
   /**
    * @param string | int time expression to measure to.
    */
   private $_time;
   /**
    * @param string | int | null
    *    time expression to measure from, null implies current time.
    */
   private $_relative_to;
   
   /**
    * string accessible object for relative time phrases.
    * 
    * @param string | int $time Specifies the time to measure to. 
    *    If value is a string, it must recognized by strtotime();
    *    such as an ISO timestamp, or any or date/time string.
    *    If value is an integer, it is assumed to be a unix timestamp.
    * 
    * @param string | int $relative_to OPTIONAL defaults to current time. 
    *    Specifies the time to measure from. 
    *    If value is a string, it must recognized by strtotime();
    *    such as an ISO timestamp, or any or date/time string.
    *    If value is an integer, it is assumed to be a unix timestamp.
    * 
    * @see strtotime() for acceptable $time, $relative_to string values
    * 
    */
   public function __construct($time,$relative_to=null) {
      $this->_time = $time;
      $this->_relative_to = $relative_to;
   }
}