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
class time {
   /**
    * converts HH:MM:SS timecode into number of total seconds
    * 
    * @return int
    * 
    * @param string $timecode HH:MM:SS timecode 
    * 
    * @link http://stackoverflow.com/questions/4834202/convert-hhmmss-to-seconds-only
    */
   public static function timecode_to_seconds($timecode) {
      
      $timecode = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $timecode);
      
      sscanf($timecode, "%d:%d:%d", $hours, $minutes, $seconds);
      
      return (int) $hours * 3600 + $minutes * 60 + $seconds;
   }
   
   /**
    * converts seconds to HH:MM:SS format
    * 
    * @return string
    * 
    * @param int $seconds
    * 
    * @link http://stackoverflow.com/questions/3534533/output-is-in-seconds-convert-to-hhmmss-format-in-php
    */
   public static function seconds_to_timecode($seconds) {
      $seconds = (int) sprintf("%d",$seconds);
      $t = round($seconds);
      return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
   }
}