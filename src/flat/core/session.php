<?php
/**
 * \flat\core\session class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
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
   
   const root_key = 'flat\core\session';
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
      self::start(array('refresh'));
      $_SESSION[self::root_key][$key]=$val;
   }
   
   /**
    * retrieves session's id
    */
   public static function get_id() {
      return self::load()->id;
   }
   
   /**
    * retrieves a key/val from session.
    *    returns NULL if no value
    * 
    * @return mixed | NULL
    */
   public static function get($key) {
      self::start(array('refresh'));
      if (isset($_SESSION[self::root_key][$key])) return $_SESSION[self::root_key][$key];
      return NULL;
   }

   /**
    * starts session if not yet started
    * @static
    */
   protected static function _start() {
      if (session_status() == \PHP_SESSION_NONE) {
         return session_start();
      }
   }
   
   /**
    * destroys current session
    * @return void
    */
   public static function destroy() {
      self::_start();
      if (session_status() == \PHP_SESSION_ACTIVE) {
         $_SESSION = array();
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
   
   const refresh_key='flat\core\session\refresh';
   /**
    * @static
    * @return void
    * @param string $key session key to update with current time
    */
   public static function refresh($key=self::refresh_key) {
      if (!empty($key)) {
         if (is_scalar($key)) {
            $refresh = array(
               'time_float'=>microtime(true),
               'date'=>date("c")
            );
            
            $_SESSION[$key] = $refresh;
         }
      }
   }
   /**
    * really starts php session, optionally destroying an old one beforehand
    * 
    * @param string[] $flags optional flags:
    *    
    * @return void
    * @static
    */
   public static function start(array $flags=array('refresh')) {
      
      if (in_array('destroy',$flags)) self::destroy();
      
      
      if (self::_start()) {
                  
         if (in_array('refresh',$flags)) {
            self::refresh();
         }
      }
      
      if (!isset($_SESSION)) throw new session\exception\no_session_var();
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
      if (!$session_data) return new \flat\data\generic(array(
         'id'=>session_id()
      ));
      
      return new \flat\data\generic(array(
         'id'=>session_id(),
         'data'=>(object) \flat\core\util\deepcopy::arr($_SESSION)
      ));
   }
}








