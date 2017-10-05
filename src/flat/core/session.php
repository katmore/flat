<?php
/**
 * \flat\core\session class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core;
/**
 * ns_shortname
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class session {
   
   const root_ns = 'flat\core\session';
   const nonce_subns = 'nonce';
   const keyval_subns = 'keyval';
   const refresh_subns='refresh';
   const nonce_len = 16;
   
   /**
    * consumes specified nonce; thus making it unconsumable thereafter.
    *    returns associated data if it was specified for the nonce;
    *    otherwise, returns void. 
    * @return mixed
    * 
    * @param string $key logical namespace key for nonce
    * @param int $ttl (OPTIONAL) number of seconds from creation time
    *    until the nonce is no longer valid
    * 
    * @throws \flat\core\session\exception\bad_key
    * @static
    */
   public static function consume_nonce($key,$nonce) {
      self::_nonce_enforce($key);
      if (!isset($_SESSION[self::root_ns."/".self::nonce_subns][$key][$nonce])) {
         throw new session\exception\nonce_not_found();
      }
      $meta = new session\nonce_meta((array) $_SESSION[self::root_ns."/".self::nonce_subns][$key][$nonce]);
      if (!$meta instanceof session\nonce_meta) {
         var_dump($meta);
         die(__FILE__);
         throw new session\exception\invalid_nonce_data();
      }
      if ($meta->consumed) {
         throw new session\exception\nonce_consumed();
      }
      if (!is_null($meta->ttl)) {
         if (!is_int($meta->ttl)) {
            throw new \flat\lib\exception\app_error(
               "bad ttl value: expected integer, got '".gettype($meta->ttl). "' instead"
            );
         }
         $created = strtotime($meta->created);
         if ((time() - $created)>$meta->ttl) {
            throw new session\exception\nonce_expired($meta->ttl);
         }
      }
      $_SESSION[self::root_ns."/".self::nonce_subns][$key][$nonce]->consumed = true;
      if (!is_null($meta->data)) {
         return $meta->data;
      }
   }
   /**
    * enforces key and ttl parameters for usage by nonce and
    *    ensures a usable session exists (starts one if not present).
    * 
    * @param string $key logical namespace key for nonce
    * @param int $ttl (OPTIONAL) number of seconds from creation time
    *    until the nonce is no longer valid
    * @throws \flat\core\session\exception\bad_key
    * @static
    * @return void
    * @see \flat\core\session::generate_nonce()
    * @see \flat\core\session::consume_nonce()
    */
   private static function _nonce_enforce($key,$ttl=null) {
      if (empty($key)) {
         throw new session\exception\bad_key("must be non-empty value");
      }
      if (!is_string($key)) {
         throw new session\exception\bad_key("must be string");
      }
      if (!is_null($ttl)) {
         if (!is_int($ttl)) {
            throw new \flat\lib\exception\bad_param("ttl", "must be integer if specified");
         }
      }
      self::start(['refresh']);
   }
   /**
    * generates a nonce and saves it to a session
    * 
    * @param string $key logical namespace key for nonce
    * @param int $ttl (OPTIONAL) number of seconds from creation time
    *    until the nonce is no longer valid
    * @param mixed $data (OPTIONAL) data to associate with this nonce
    * @return string
    * @static
    * @throws \flat\core\session\exception\bad_key
    * 
    */
   public static function generate_nonce($key,$ttl=null,$data=null) {
      self::_nonce_enforce($key,$ttl);
      /*
       * get random byes from openssl_random_pseudo_bytes
       */
      $nonce = base64_encode(
         openssl_random_pseudo_bytes(self::nonce_len,$strong)
      );
      if (!$strong) {
         throw new \flat\lib\exception\app_error(
            'openssl_random_pseudo_bytes \crypto_strong is false'
         );
      }
      $meta = [
         'consumed'=>false,
         'created'=>date("c")
      ];
      if (!is_null($data)) {
         $meta['data'] = $data;
      }
      if (!is_null($ttl)) {
         $meta['ttl'] = $ttl;
      }
      $_SESSION[self::root_ns."/".self::nonce_subns][$key][$nonce]=json_decode(json_encode((new session\nonce_meta($meta))));
      return $nonce;
   }
   
   
   /**
    * sets a $key/val in session
    * 
    * @return void
    * @throws \flat\core\session\exception\bad_key
    */
   public static function set($key,$val) {
      if (!is_string($key) && !is_int($key) && !is_float($key)) {
         throw new session\exception\bad_key(
            "must be string or numeric value"
         );
      }
      self::start(['refresh']);
      if ($val===null) {
         unset($_SESSION[self::root_ns."/".self::keyval_subns][$key]);
      } else {
         $_SESSION[self::root_ns."/".self::keyval_subns][$key]=$val;
      }
   }
   
   /**
    * retrieves session's id
    */
   public static function get_id() {
      return self::load()->id;
   }
   
   /**
    * retrieves a key/val from session.
    *    returns null if no value
    * 
    * @return mixed | null
    */
   public static function get($key) {
      self::start(['refresh']);
      if (isset($_SESSION[self::root_ns."/".self::keyval_subns][$key])) {
         return $_SESSION[self::root_ns."/".self::keyval_subns][$key];
      }
      return null;
   }

   /**
    * starts session if not yet started then verifies the $_SESSION
    *    variable is accessible.
    *    returns true if session was not existing and successfully started.
    *    returns void if session already existed.
    * @return bool | void
    * @static
    * @throws \flat\core\session\exception\no_session_var
    */
   protected static function _start(string $session_id="") {
      $newstart = false;
      if (session_status() == \PHP_SESSION_NONE) {
         if (!empty($session_id)) {
            session_id($session_id);
         }
         $newstart = session_start();
      }
      if (!isset($_SESSION)) {
         throw new session\exception\no_session_var();
      }
      if ($newstart) {
         return true;
      }
   }
   
   /**
    * destroys current session
    * @return void
    */
   public static function destroy() {
      self::_start();
      if (session_status() == \PHP_SESSION_ACTIVE) {
         $_SESSION = [];
         session_regenerate_id(true);
         if (ini_get("session.use_cookies")) {
             $params = session_get_cookie_params();
             setcookie(session_name(), '', time() - 42000,
                 $params["path"], $params["domain"],
                 $params["secure"], $params["httponly"]
             );
         }         
         session_destroy();
      }
   }
   /**
    * creates an assoc array containing current time information
    *    and saves it to a session element as specified 
    * 
    * @static
    * @return void
    * @param string $key (OPTIONAL) session key to update with current time.
    *    default is self::root_ns."/".self::refresh_subns
    *    
    */
   public static function refresh($key=null) {
      self::_start();
      if (is_null($key)) {
         $key = self::root_ns."/".self::refresh_subns;
      } else
      if (!is_string($key)) {
         throw new session\exception\bad_key("must be string");
      } else {
         if (substr($key,0,strlen(self::root_ns))==self::root_ns) {
            throw new session\exception\bad_key("cannot collide with session root namespace: ".self::root_ns);
         }
      }
      $_SESSION[$key] = [
         'time_float'=>microtime(true),
         'date'=>date("c")
      ];     
   }
   
   /**
    * really starts php session, optionally destroying an old one beforehand
    * 
    * @param string[] $flags optionally specify any of the following flags an array element string value:
    * <ul>
    *    <li><b>destroy</b>: destroy any existing session data</li>
    *    <li><b>refresh</b>: updates the session meta-data activity with current time</li>
    *    <li><b>no-cookies</b>: do not utilize PHP's built-in "session cookie" methodology</li>
    * </ul>
    *    
    * @return string session id
    * @static
    */
   public static function start(array $flags=['refresh'], string $session_id="") {
      
      if (in_array('destroy',$flags)) self::destroy();
      
      if (in_array('no-cookies',$flags)) {
         if (empty($session_id)) {
            throw new session\exception\bad_config("'session_id' cannot be empty when 'no-cookies' flag is specified");
         }
         ini_set("session.use_cookies", 0);
         ini_set("session.use_only_cookies", 0);
         ini_set("session.cache_limiter", "");
      }

      if (self::_start($session_id)) {
         if (in_array('refresh',$flags)) {
            self::refresh();
         }
      }
      
      return session_id();
   }
   /**
    * starts session if not already, and returns data object containing id. 
    *    optionally also includes a 'deep copy' snapshot $_SESSION values.
    * 
    * @static
    * @return \flat\data\generic
    */
   public static function load($session_data=false) {
      self::start();
      if (!$session_data) return new \flat\data\generic([
         'id'=>session_id()
      ]);
      
      return new \flat\data\generic([
         'id'=>session_id(),
         'data'=>(object) \flat\core\util\deepcopy::arr($_SESSION)
      ]);
   }
}








