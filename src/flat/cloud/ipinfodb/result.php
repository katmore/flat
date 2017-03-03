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
namespace flat\cloud\ipinfodb;
class result extends \flat\data 
   implements \flat\data\ready
{
   
   //public $data;
   public $raw;
   
   /**
    * @var string IP address
    */
   public $ip;
   
   /**
    * @var string City, Region, Country-Code
    */
   public $location;
   
   /**
    * @var string two-char country code
    */
   public $country_code;
   
   /**
    * @var string country name
    */
   public $country;
   
   /**
    * @var string region, ie: the name of the 'state', 'province', 'oblast', 
    *    'prefecture', etc. 
    */
   public $region;
   
   /**
    * @var string city name
    */
   public $city;
   
   /**
    * @var string postal code, ie: 'zip code'
    */
   public $postal_code;
   
   /**
    * @var string latitude expressed in signed decimal format
    */
   public $lat;
   
   /**
    * @var string longitude expressed in signed decimal format
    */
   public $long;
   
   /**
    * @var string UTC offset
    */
   public $utc_offset;
   
   /**
    * @var string record creation timestamp in ISO 8601 format
    */
   public $created;
   
   public function data_ready() {
      if (empty($this->created)) {
         $this->created = date("c");
      }
   }
}












