<?php
/**
 * \flat\data\orm class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\data;
/**
 * save and retrieve \flat\data objects from persistent storage
 *
 * when driver not given to class functions:
 *    if \flat\data object is defined in \actvp\data:
 *       checks if object has interface of \actvp\
 * 
 *    --otherwise--
 * 
 *    looks in config:
 *       looks driver name (ns short name) in config key at: 
 *          {config_base_dir}/flat/data/orm/driver
 *       gets driver config options config file:
 *          {config_base_dir}/flat/data/orm/{driver name}.php
 * 
 * @package    flat\data
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class orm implements \flat\core\controller {
   private $_driver;
   const fallback_driver = "\\flat\\data\\orm\\driver\\session";
   /* CONSERVATIVELY DISABLED unused private placeholder: no callers exist.
    * private function _get_default_driver() {}
    */
   /**
    * @param array $driver (optional) driver configuration. 'driver' selects the
    *    driver: a \flat\data\orm\driver class name or object. The remaining
    *    entries are given to the driver's constructor. Without 'driver', the
    *    fallback session driver receives the configuration.
    * @throws \flat\data\orm\exception\bad_param
    */
   public function __construct(array $driver=NULL) {
      $config = $driver ? $driver : [];
      $selected = self::fallback_driver;
      if (isset($config['driver'])) {
         $selected = $config['driver'];
         unset($config['driver']);
      }
      // A driver is anything providing the get_data()/set_data() calls made
      // below; the fallback session driver does not extend orm\driver.
      $is_driver = function ($candidate) {
         return method_exists($candidate, 'get_data') && method_exists($candidate, 'set_data');
      };
      if (is_object($selected) && $is_driver($selected)) {
         $this->_driver = $selected;
      } elseif (is_string($selected) && class_exists($selected) && $is_driver($selected)) {
         $this->_driver = new $selected($config ? $config : NULL);
      } else {
         throw new orm\exception\bad_param(
            "'driver' option must be either: a class name string, or orm driver object"
         );
      }
   }
   
   public function get($key) {
      return $this->_driver->get_data($key);
   }
   public function set($key,\flat\data $data) {
      return $this->_driver->set_data($key,$data);
   } 
   
   private static $_s=NULL;
   private static function _get_orm(array $options=NULL) {
      /* CONSERVATIVELY DISABLED unreachable driver-option branch. This
       * method accepts $options, but the branch only reads undefined $param;
       * its isset guard always fails. Public static callers also do not pass
       * options into this method. Preserve current selection until its input
       * contract is settled; original code follows:
       *       if (isset($param['driver'])) {
       *          if (
       *             (assoc::non_empty_scalar($param,"driver")) ||
       *             (is_a($param['driver'],"\\flat\\data\\orm\\driver"))
       *          ) {
       *             $driver = $param['driver'];
       *             return new static($param['driver']);
       *          }
       *          throw new orm\exception\bad_param(
       *             "'driver' option must be either: a class name string, or orm driver object"
       *          );
       *       }
       */
      if (empty(self::$_s)) self::$_s = new static();
      return self::$_s;
   }
   public static function get_data($key,array $options=NULL) {
      $s = self::_get_orm();
      return $s->get($key);
   }   
   public static function set_data($key,\flat\data $data, array $options=NULL) {
      $s = self::_get_orm();
      return $s->set($key,$data);
   }
}









