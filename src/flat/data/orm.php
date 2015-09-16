<?php
/**
 * \flat\data\orm class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
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
 *    if \flat\data object is defined in \flat\app\data:
 *       checks if object has interface of \flat\app\
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
   private function _get_default_driver() {
      
   }
   public function __construct(array $driver=NULL) {
      //\flat\data\orm\driver
      if (!$driver) {
         $driver = self::default_handler;
         $driver = new $driver();
         return;
      }
      if (isset($param['driver'])) {
         
      }
      $this->_driver = $driver; 
      
   }
   
   public function get($key) {
      $this->_driver->get_data($key);
   }
   public function set($key,\flat\data $data) {
      $this->_driver->set_data($key,$data);
   } 
   
   private static $_s=NULL;
   private static function _get_orm(array $options=NULL) {
      if (isset($param['driver'])) {
         if (
            (assoc::non_empty_scalar($param,"driver")) ||
            (is_a($param['driver'],"\\flat\\data\\orm\\driver"))
         ) {
            $driver = $param['driver'];
            return new static($param['driver']);
         }
         throw new orm\exception\bad_param(
            "'driver' option must be either: a class name string, or orm driver object"
         );
      }
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









