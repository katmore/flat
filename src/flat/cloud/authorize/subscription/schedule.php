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
namespace flat\cloud\authorize\subscription;
class schedule extends \flat\data 
   implements \flat\data\ready
{
   
   /**
    * @var \flat\cloud\authorize\arb\interval
    *    ARB subscription interval data.
    */
   public $interval;
   
   /**
    * @var string Date the subscription begins in YYYY-MM-DD format in 'America/Denver' timezone.
    *     The date entered must be greater than or equal to the 
    *     date the ARB subscription was/will be submitted adjusted to 'America/Denver' timezone.
    *     (including adjusting for Daylight Savings Time as it existed on that date).
    *     Non-empty value is sanitized into YYYY-MM-DD format.
    */
   public $startDate;
   
   /**
    * @var int An empty value will be transformed to (int) 9999. Number of billing occurrences 
    *    for the subscription. Value of 9999 indicates subscription with 
    *    no end date (an ongoing subscription).
    */
   public $totalOccurrences;
   
   /**
    * @var string If a valid Time Zone string is specified, will convert the date of
    *    arb_schedule::startDate to 'America/Denver' timezone. Useful when creating a new
    *    time zone.
    * @see http://php.net/manual/en/timezones.php List of valid time zones.
    */
   protected $startDate_timeZone;
   
   /**
    * @var bool convienience fields to indicate have fields prepared with 
    *    defaults as if the schedule is for a new ARB Subscription. 
    */
   protected $new_subscription;
   
   /**
    * @uses arb_schedule::startDate sanitizes to YYYY-MM-DD format.
    * @uses arb_schedule::totalOccurrences sanitizes non-empty 
    *    value to be cast as integer. changes an empty value into 
    *    (int) 9999 to indicate an ongoing subscription.
    * @uses arb_schedule::new_subscription If value set to (bool) true, 
    *    and arb_schedule::startDate_timeZone has an empty value,
    *    will set arb_schedule::startDate_timeZone to the current timezone
    *    set in php. 
    * @uses arb_schedule::interval If value is an array, maps it to 
    *    an \flat\cloud\authorize\arb_interval object and sets value 
    *    to that object. 
    */
   public function data_ready() {
      if (empty($this->totalOccurrences)) {
         $this->totalOccurrences = 9999;
      } else {
         if (
               !empty($this->totalOccurrences) && 
               !is_int($this->totalOccurrences) && 
               !is_bool($this->totalOccurrences) && 
               is_scalar($this->totalOccurrences)
         ) {
            $this->totalOccurrences = (int) sprintf("%d",$this->totalOccurrences);
         }
      }

      if (($this->new_subscription === true) && (empty($this->startDate_timeZone))) {
         $this->startDate_timeZone = date_default_timezone_get();
      }
      if (!empty($this->startDate_timeZone) && ($this->startDate_timeZone!='America/Denver')) {
          try {
              $dateTime = new \DateTime ($this->startDate, new \DateTimeZone($this->startDate_timeZone));
              $dateTime->setTimezone(new DateTimeZone('America/Denver'));
              $this->startDate = $dateTime->format("Y-m-d");
          } catch (\Exception $e) {
              $this->startDate = null;
          }
      }
      if (!empty($this->startDate) && is_string($this->startDate)) {
         $this->startDate = date("Y-m-d",strtotime($this->startDate));
      }
      
      if (!empty($this->interval) && is_array($this->interval)) {
         $this->interval = new interval($this->interval);
      }
   }
}


























































