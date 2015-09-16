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
namespace flat\core\curl;

use \flat\core\curl\exception;


class request {
   public $url;
   public $url_string;
   public $content_type;
   public $data;
   public $field_count;
   
   public static function parse_url($url) {
      if (!is_string($url)) throw new exception\bad_param("url","must be string");
      if (empty($url)) throw new exception\missing_param("url");
      if (false === ($parsed = parse_url($url))) {
         throw new exception\bad_param("url","not valid url");
      }
      if ( ($parsed['scheme']!="http") && ($parsed['scheme']!="https")) {
         throw new exception\bad_param("url","$url has bad protocol: ". $parsed['scheme'] ." (must be http or https)");
      }
      if (empty($parsed['host'])) throw new exception\bad_param("url","missing host");
      return $parsed;
   }
   
   /**
    * 
    * @param string $url
    * @param mixed $data (optional) request data
    * @param string $encoding (optional) default to "urlencoded"
    *    other acceptable values are "none" and "json"
    * @param string $content_type (optional) ignored unless $encode = "none".
    *    
    * @throws exception\bad_param if malformed url, or data encoding issue.
    * @throws exception\missing_param if missing content_type if $data is non-empty value
    *    and $encode = "none"
    */
   public function __construct($url,$data=NULL,$encode="urlencoded",$content_type=NULL) {
      $this->url = self::parse_url($url);
      $this->url_string = $url;
      if (!empty($data)) {
         if ($encode=="none") {
            if (!empty($content_type)) throw new exception\bad_param(
               "content_type","must provide content when encode='none'"
            );
            if (!is_string($content_type)) throw new exception\bad_param(
               "content_type","content_type must be string if provided"
            );
            $this->content_type = $content_type;
         } else
         if ($encode=="json") {
            $this->content_type = 'application\json';
            if (!$this->data = json_encode($data)) throw new exception\bad_param(
               "data","json_encode failed"
            );
         } else
         if ($encode=="urlencoded") {
            $this->content_type = "application/x-www-form-urlencoded";
            if (is_string($data)) {
               parse_str($data,$parse_str);
               if (empty($parse_str)) throw new exception\bad_param(
                  "data",
                  "if encoding='urlencoded' and data param is string, ".
                  "data param must be urlencoded key/value query string"
               );
               $data = $parse_str;
            }
            if (is_array($data) || is_object($data)) {
               $this->field_count=0;
               foreach($data as $key=>$value) {
                  $fields_string .= $key.'='.urlencode($value).'&';
                  $this->field_count++;
               }
               $this->data = rtrim($fields_string, '&');
            }
         }
      }
   }
}








