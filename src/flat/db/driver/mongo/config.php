<?php
/**
 * File:
 *    config.php
 * 
 * Purpose:
 *    get values from config system
 */
namespace flat\db\driver\mongo;
abstract class config extends \flat\db\driver\mongo\crud
implements 
   \flat\db\driver\mongo\client\trivial_connection
   ,\flat\db\driver\mongo\db\explicit
   ,\flat\db\driver\mongo\client\options
   ,\flat\db\driver\mongo\client\driver_options
 {
   abstract protected function _get_config_ns();
   public function get_client_db() {
      return \flat\core\config::get($this->_get_config_ns().'/db');
   }

   public function get_client_host() {
      return \flat\core\config::get($this->_get_config_ns().'/host');
   }
   public function get_client_port() {
      return \flat\core\config::get($this->_get_config_ns().'/port');
   }
   public function get_client_options() {
      return \flat\core\config::get($this->_get_config_ns().'/options',['default'=>[]]);
   }
   public function  get_client_driver_options() {
      return \flat\core\config::get($this->_get_config_ns().'/driver_options',['default'=>[]]);
   }
}














