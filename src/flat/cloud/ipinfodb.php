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
namespace flat\cloud;

use \flat\core\curl;

abstract class ipinfodb {
   public function __get($what) {
      if (isset($this->_result->$what)) return $this->_result->$what;
      return "";
   }
   /**
    * @return string
    */
   abstract protected function _get_api_key();
   
   /**
    * @return \flat\db\driver\mongo\crud
    */
   abstract protected function _get_mongo_crud();
   
   const api_base_url = 'https://api.ipinfodb.com/v3/ip-city';
   /**
    * number of seconds IP data that exists in Mongo is considered usable
    */
   const location_ttl = 604800; //1 day = 604800 seconds
   
   private $_result;
   /**
    * result
    * @return \flat\cloud\ipinfodb\result
    */
   public function result() {
      return $this->_result;
   }
   /**
    * retrieves ip location data from external API and saves to MongoDB
    * @return void
    * @uses ipinfodb::_get_mongo_crud()
    */
   private function _iplookup($ip_addr) {
      //https://api.ipinfodb.com/v3/ip-city/?key=%3Cyour_api_key%3E&ip=192.241.224.96
      $url = self::api_base_url . "?" . http_build_query([
         'key'=>$this->_get_api_key(),
         'ip'=>$ip_addr,
      ]);
       
      try {
         $curl_cfg = new curl\config(
               new curl\request($url),
               'GET'
         );
         $response = new curl\response($curl_cfg,[]);
      } catch (curl\exception\curlexec_error $e) {
         throw new ipinfodb\api_error("failure",(object) [
            'http_client_error'=>$e->get_curl_error(),
            'http_client_errno'=>$e->get_curl_errno(),
            'api_url'=>$e->get_url(),
         ]);
      }
      if (!$response->is_status_ok()) {
         throw new ipinfodb\api_error("status not ok",(object) [
            'status_code'=>$response->status_code
         ]);
      }
      $raw = $response->get_response_data();
       
      $result = new ipinfodb\result([
         'raw'=>$raw,
      ]);
       
      $result->description = $raw;
      
      /*
       * map raw API response to result object
       */
      $data = explode(";",$raw);
      $map = [
         2 => 'ip',
         3 => 'country_code',
         4 => 'country',
         5 => 'region',
         6 => 'city',
         7 => 'postal_code',
         8 => 'lat',
         9 => 'long',
         10 => 'utc_offset',
      ];
      foreach ($map as $key=>$prop) {
         if (!empty($data[$key])) {
            $result->$prop = $data[$key];
         }
      }
      
      /*
       * concatonate $result->location 
       */
      $location = [];
      if (!empty($result->city)) $location[] = $result->city;
      if (!empty($result->region)) $location[] = $result->region;
      if (!empty($result->country_code)) $location[] = $result->country_code;
      if (count($location)) $result->location = implode(", ",$location);
      
      /*
       * save result to MongoDB
       */
      $db = $this->_get_mongo_crud();
      $db->create($result,['upsert'=>['ip'=>$result->ip]]);
      
      $this->_result = $result;
   }
   /**
    * retrieves locataion of given ip address
    * @param string $ip_addr ip address
    * 
    * @uses ipinfodb::_get_mongo_crud() uses MongoDB to cache IP location data.
    * 
    * @uses ipinfodb::location_ttl if Mongo cached data is older than 
    *    this ttl, the external API is used.
    *    
    * @uses ipinfodb::_iplookup() performs external API lookup if no IP data is cached in 
    *    Mongodb or cache is too old.   
    */
   public function __construct($ip_addr) {
      
      $db = $this->_get_mongo_crud();
      
      try {
         $result = $db->read(['find'=>['ip'=>$ip_addr],'record'=>new ipinfodb\result()]);
         $this->_result = $result;
         if ($result->created) {
            $created = strtotime($result->created);
            if ((time() - $created)>=self::location_ttl) {
               $this->_result = NULL;
               $this->_iplookup($ip_addr);
            }
         }
      } catch (\flat\db\driver\mongo\record\not_found $e) {
         $this->_iplookup($ip_addr);
      }
   }
   
   public static function load($ip_addr) {
      
   }
}






