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
namespace flat\core\util;
class time_ago {
   public static function str($time)
   {
      if (empty($time)) return NULL;
      if (!is_int($time)) if (!$time = strtotime($time)) return NULL;
      
       $etime = time() - $time;
   
       if ($etime < 1)
       {
           return '0 seconds ago';
       }
   
       $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                   30 * 24 * 60 * 60       =>  'month',
                   24 * 60 * 60            =>  'day',
                   60 * 60                 =>  'hour',
                   60                      =>  'minute',
                   1                       =>  'second'
                   );
   
       foreach ($a as $secs => $str)
       {
           $d = $etime / $secs;
           if ($d >= 1)
           {
               $r = round($d);
               return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
           }
       }
   }
   public function set_time($time) {
      $this->time = $time;
   }   
   public function __invoke($time) {
      $this->set_time($time);
      return $this->__toString();
   }
   public function __toString() {
      return self::str($this->time);
   }
   private $time;
   public function __construct($time) {
      $this->set_time($time);
   }
}