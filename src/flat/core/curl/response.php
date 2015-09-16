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
      //var_dump($this->_simplexml);die('core curl respones');
      if (empty($this->_simplexml)) $this->_init_dom();
      if (false === ($result =   $this->_simplexml->xpath($xquery))) {
         throw new exception\xpath_error($xquery,"url: ".$this->request->url_string);
      }
      if ($demand_result && count($result)<1) {
         throw new exception\no_xpath_results($xquery,"url: ".$this->request->url_string);
      }
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
   
   public function __destruct() {
      
      curl_close($this->_ch);
      
      if ($this->_tmp_cookie_file)
         unlink($this->_tmp_cookie_file);
   }
   
   public function __construct(config $config, array $flags=array('use_robots'), $cookie_file=NULL) {
      
      $request = $config->request;
      $use_robots = true;
      $use_dom = false;

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
            $this->_tmp_cookie_file = $cookie_file = tempnam ( sys_get_temp_dir(),"curl");
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
      curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
      if (false === ($this->_doc = curl_exec($ch))) {
         throw new exception\curlexec_error($ch,$request->url_string);
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