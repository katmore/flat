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
namespace flat\core\curl;

use \flat\core\curl\exception;

class config {
   
   /**
    * @var \flat\core\curl\request
    */
   public $request;
   
   
   /**
    * @var string
    *    request method, ie: GET, POST, PUT, DELETE 
    */
   public $request_method = "GET";
   
   /**
    * @var string
    *    referrer URL string to provide to HTTP host
    */
   public $referrer;
   
   /**
    * @var string
    *    user agent string to provide to HTTP host
    */
   public $user_agent = config::default_user_agent;
   
   /**
    * @var int number of seconds until cached URL response data 
    *    is expired 
    */
   public $cache_ttl = 0;
   
   /**
    * @var int number of seconds for curl timeout
    */
   public $timeout = 300;
   
   /**
    * @var int number of seconds to wait for connection
    */
   public $connect_timeout = 5;
   
   /**
    * @var string path to save cache
    */
   public $cache_dir;
   
   const default_user_agent = "flat/curl";
   const chrome_user_agent_str = "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/%chrome_version%.0.9999.99 Safari/537.36";
   const chrome_latest_version_str = "43";
   const user_agent_chrome = 0;
   /**
    * 
    * 
    * @param string $request_method (optional) defaults to "GET".
    * 
    * @param string $referrer (optional) referrer. defualts to value for $url
    * 
    * @param string $user_agent (optional) user-agent to provide. defaults to config::default_user_agent.
    * 
    * @param int $cache_ttl maximum age in seconds cache for given url will be used.
    *    ignored unless $request_method = "GET".
    * 
    * @param string $cache_dir path to where cache files will be stored on system.
    * 
    * @uses curl\request::parse_url()
    */
   public function __construct(request $request, $request_method=NULL,$user_agent=NULL,$referrer=NULL,$cache_ttl=NULL,$cache_dir=NULL,$timeout=NULL,$connect_timeout=NULL) {
      
      $this->request = $request;
      
      foreach (['request_method','user_agent','referrer','cache_ttl','cache_dir','timeout','connect_timeout'] as $param) {
         if (!is_null($$param)) {
            $this->$param = $$param;
         }
      }
      if (is_int($this->user_agent) && $this->user_agent == self::user_agent_chrome) {
         $this->user_agent = self::chrome_user_agent_str;
         $this->user_agent = str_replace("%chrome_version%",self::chrome_latest_version_str,$this->user_agent);
      }
      foreach(array("request_method","referrer","user_agent","cache_dir") as $param) {
         if (!empty($$param) && !is_string($this->$param)) throw new exception\bad_param($param,"must be string");
         $this->$param = $$param;
      }
      
      if (!is_int($this->timeout)) {
         throw new exception\bad_param("timeout","must be int");
      }
      
      if (!is_int($this->cache_ttl)) {
         throw new exception\bad_param("cache_ttl","must be int");
      }
      
      if (!is_int($this->connect_timeout)) {
         throw new exception\bad_param("connect_timeout","must be int");
      }
      
      if (!empty($this->cache_ttl)) {
         if (empty($this->cache_dir)) {
            $this->cache_dir = sys_get_temp_dir();
         }
         if (!is_writable($this->cache_dir)) throw new exception\bad_param(
            "cache_dir",
            "if given positive cache_ttl value, must provide writable ".
            "dir unless sys_get_temp_dir() is writable"
         );
      }
      
      
   }
}