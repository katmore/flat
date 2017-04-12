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

class response {
   
   public $request;
   public $status_code;
   private $_tmp_cookie_file;
   private $_dom;
   private $_simplexml;
   private $_doc;
   private $_ch;
   public function is_status_ok() {
      if (empty($this->status_code)) return false;
      $status = (string) $this->status_code;
      if (substr($status,0,1)=="2") return true;
      return false;
   }
   public function get_dom() {
      if (empty($this->_doc)) $this->_init_dom();
      return $this->_dom;
   }
   public function get_response_data() {
      return $this->_doc;
   }
   public function simplexpath($xquery,$demand_result=true) {
      if (empty($this->_simplexml)) $this->_init_dom();
      if (false === ($result =   $this->_simplexml->xpath($xquery))) {
         $details = [];
         if (count($xmlerr = libxml_get_errors())) {
            $xmlerr = array_pop($xmlerr);
            if (($xmlerr instanceof \LibXMLError) && !empty($xmlerr->message)) {
               $details []= $xmlerr->message;
            }
         }
         throw new exception\xpath_error($xquery,implode(", ",$details));
      }
      //error_reporting($old_reporting);
      if ($demand_result && count($result)<1) {
         throw new exception\no_xpath_results($xquery,"url: ".$this->request->url_string);
      }
      if (empty($result)) return [];
      return $result;
   }
   
   private static function _check_robots_allowed($url,$useragent) {
      if (!self::is_robots_allowed($url,$useragent)) throw new exception\robots_denied($url,$useragent);
   }
   
   public static function is_robots_allowed($url,$useragent) {
      $parsed = request::parse_url($url);
      
      $agents = array(preg_quote('*'));
      $agents[] = preg_quote($useragent, '/');
      $agents = implode('|', $agents);
      
      $robots_url = "{$parsed['scheme']}://{$parsed['host']}/robots.txt";
      $handle = curl_init($robots_url);
      curl_setopt($handle,  \CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($handle, \CURLOPT_FOLLOWLOCATION, true);
      $response = curl_exec($handle);
      $httpCode = curl_getinfo($handle, \CURLINFO_HTTP_CODE);
      if($httpCode == 200) {
         $robotstxt = explode("\n", $response);
      } else {
         $robotstxt = false;
      }
      curl_close($handle);
      
      
      // if there isn't a robots, then we're allowed in
      if(empty($robotstxt)) return true;
      
      $rules = array();
      $ruleApplies = false;
      foreach($robotstxt as $line) {
         // skip blank lines
         if(!$line = trim($line)) continue;
         
         // following rules only apply if User-agent matches $useragent or '*'
         if(preg_match('/^\s*User-agent: (.*)/i', $line, $match)) {
            $ruleApplies = preg_match("/($agents)/i", $match[1]);
            continue;
         }
         
         if($ruleApplies) {
            $rule_parts = explode(':', $line, 2);
            if (!empty($rule_parts[0])) {
               $type = trim(strtolower($rule_parts[0]));
            }
            if (!empty($rule_parts[1])) {
               $rule = trim($rule_parts[1]);
            }
            // add rules that apply to array for testing
            $rules[] = array(
               'type' => $type,
               'match' => preg_quote($rule, '/'),
            );
         }
      }
      
      $isAllowed = true;
      $currentStrength = 0;
      foreach($rules as $rule) {
         // check if page hits on a rule
         if(preg_match("/^{$rule['match']}/", $parsed['path'])) {
            // prefer longer (more specific) rules and Allow trumps Disallow if rules same length
            $strength = strlen($rule['match']);
            if($currentStrength < $strength) {
               $currentStrength = $strength;
               $isAllowed = ($rule['type'] == 'allow') ? true : false;
            } elseif($currentStrength == $strength && $rule['type'] == 'allow') {
               $currentStrength = $strength;
               $isAllowed = true;
            }
         }
      }
      
      return $isAllowed;
   }
   
   private $_purge_save = false;
   public function __destruct() {
      
      curl_close($this->_ch);
      
      if ($this->_tmp_cookie_file) {
         unlink($this->_tmp_cookie_file);
      }
      
      if ($this->_purge_save && $this->_save) {
         unlink($this->_save);
      }
   }
   
   private $_save = false;
   /**
    * provides filename of response data.
    *    if a file has not been created, one will be made.
    *
    * @return string
    */
   public function get_file($path=null) {
      if (empty($this->_save)) {
         if (empty($path)) {
            $this->_save = $path = tempnam(sys_get_temp_dir(), "flat-curl-");
            $this->_purge_save = true;
         }
         file_put_contents($path, $this->_doc);
         return $path;
      }
      if (!empty($path)) {
         copy($this->_save,$path);
         return $path;
      }
      return $this->_save;
   }
   const use_robots_default = true;
   /**
    *
    * @param mixed $param
    * @param array $flags OPTIONAL
    * @param string $cookie_file OPTIONAL
    */
   public function __construct( $param, array $flags=array('use_robots'), $cookie_file=NULL) {
      
      /* most common use-type...?
       new \flat\core\curl\config(
       new \flat\core\curl\request($company_logos_cdn)
       ),
       */
      
      $use_robots = self::use_robots_default;
      $use_dom = false;
      $save = false;
      $save_file = null;
      
      if ($param instanceof config) {
         $config = $param;
      } else {
         if (is_scalar($param)) {
            $config = new config(
                  new request($param)
                  );
         } elseif (is_array($param)) {
            $requestp=[
               'url'=>null,
               'data'=>null,
               'data_encoding'=>"urlencoded",
               'data_content_type'=>null,
            ];
            foreach($requestp as $k=>&$v) {
               if (isset($param[$k])) {
                  $v = $param[$k];
               }
            }
            $configp = [
               'request_method'=>null,
               'user_agent'=>null,
               'referrer'=>null,
            ];
            foreach($configp as $k=>&$v) {
               if (isset($param[$k])) {
                  $v = $param[$k];
               }
            }
            $config = new config(
                  new request(
                        $requestp['url'],
                        $requestp['data'],
                        $requestp['data_encoding'],
                        $requestp['data_content_type']
                        ),
                  $configp['request_method'],
                  $configp['user_agent'],
                  $configp['referrer']
                  );
            if (!empty($param['save_file']) && is_string($param['save_file'])) {
               $save = true;
               $save_file = $param['save_file'];
            }
            if (!empty($param['save']) && is_string($param['save'])) {
               $save = true;
               $save_file = $param['save'];
            }
         }
      }
      $request = $config->request;
      
      if (in_array('save',$flags)) {
         $save = true;
      }
      
      if (in_array('use_dom',$flags)) {
         $use_dom=true;
      }
      if (!in_array('use_robots',$flags)) {
         $use_robots=false;
      }
      
      
      
      $provide_cookies = false;
      if (in_array('provide_cookies',$flags)) {
         $provide_cookies=true;
      }
      $save_cookies = false;
      if (in_array('save_cookies',$flags)) {
         $save_cookies=true;
      }
      $accept_cookies = false;
      if (in_array('accept_cookies',$flags)) {
         $accept_cookies=true;
      }
      
      if ($accept_cookies) {
         if (empty($cookie_file)) {
            $this->_tmp_cookie_file = $cookie_file = tempnam ( sys_get_temp_dir(),"flat_curl_cookie_");
         } else {
            if (!is_string($cookie_file)) throw new exception\bad_param("cookie_file","must be string");
            if (!is_writable($cookie_file)) throw new exception\bad_param("cookie_file","must be writable file if provided");
         }
      }
      
      
      //var_dump($config->request->url);die('core curl response');
      if ($use_robots) self::_check_robots_allowed(
            $config->request->url_string,
            $config->user_agent
            );
      
      //var_dump($request->url);die('curl response (die)');
      $url = $request->url_string;
      
      $this->_ch = $ch = curl_init($url);
      
      curl_setopt($ch, \CURLOPT_USERAGENT, $config->user_agent);
      if ($save_cookies) {
         curl_setopt($ch, \CURLOPT_COOKIEJAR, $cookie_file);
      }
      if ($provide_cookies) {
         curl_setopt($ch, \CURLOPT_COOKIEFILE, $cookie_file);
      }
      
      if (!empty($request->data)) {
         curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, $config->request_method);
         curl_setopt($ch, \CURLOPT_HTTPHEADER, array(
            'Content-Type: '.$request->content_type,
            'Content-Length: ' . strlen($request->data))
               );
      }
      
      curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT ,5);
      curl_setopt($ch, \CURLOPT_TIMEOUT, $config->timeout);
      
      curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
      
      if (!empty($save)) {
         $use_dom=false;
         if (!empty($save_file)) {
            $path = $save_file;
         } else {
            $path = tempnam(sys_get_temp_dir(), "flat-curl-");
            $this->_purge_save = true;
         }
         if (!($fh = fopen($path,"wb"))) {
            throw new exception\system_error("error opening file: $path");
         }
         curl_setopt($ch, \CURLOPT_FILE, $fh);
         curl_exec($ch);
         fclose($fh);
         $this->_save = $path;
      } else {
         curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
         if (false === ($this->_doc = curl_exec($ch))) {
            throw new exception\curlexec_error($ch,$request->url_string);
         }
      }
      $this->filetime = curl_getinfo($ch, \CURLINFO_FILETIME);
      if (curl_errno($ch)!=0) {
         throw new exception\curlexec_error($ch,$request->url_string);
      }
      if (false === ($this->info = curl_getinfo($ch))) {
         throw new exception\missing_curlinfo();
      }
      $this->status_code = $this->info['http_code'];
      
      /*
       * if usedom indicated
       */
      if ($use_dom) {
         $this->_init_dom();
      }
      
      $this->request = $request;
   }
   private function _init_dom() {
      /*
       * tell libxml not be a whiney
       */
      libxml_use_internal_errors(true);
      
      /*
       * create DOM object from the imdb result page
       */
      $this->_dom = new \DOMDocument();
      $this->_dom->strictErrorChecking = false;
      $this->_dom->loadHTML($this->_doc );
      
      /*
       * create SimpleXML object out of the DOM object
       *    i use SimpleXML here because it's a bit
       *    'simplier' to traverse than DOM (which can be cumbersome)
       */
      $this->_simplexml = simplexml_import_dom($this->_dom);
   }
}