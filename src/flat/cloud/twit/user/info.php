<?php
/**
 * class definition
 * 
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
 * @package    flat/twit
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * 
 * See:
 *    https://dev.twitter.com/rest/reference/post/statuses/update
 *    in "Example Result" section json, see fields within "user": {...} field
 */
namespace flat\cloud\twit\user;
class info extends \flat\core\data {
   public $screen_name; //(string)
   public $name; //(string)
   public $profile_image_url; //(string)
   public $followers_count; //(int)
   public $statuses_count; //(int)
   public $url; //(object) \flat\twit\url
   public $description; //(string)
   private $_starting_prop;
   public function __construct(array $param) {
      parent::__construct($param);
      $this->_starting_prop = array();
      foreach ($this as $prop=>$val) {
         $p = \ReflectionProperty($this,$prop);
         if (!$p->isPrivate()) $this->_starting_prop[] = $prop;
      }
   }
   public function get_extra_fields($get_assoc=false) {
      $extra = array();
      foreach ($this as $prop=>$val) {
         $p = \ReflectionProperty($this,$prop);
         if (!$p->isPrivate()) {
            if (!in_array($prop,$this->_starting_prop)) {
               if ($get_assoc)
                  $extra[$prop] = $val;
               else
                  $extra[] = $prop;
            }
         }
      }
      return $extra;
   }
}