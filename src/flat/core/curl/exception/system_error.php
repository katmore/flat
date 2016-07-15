<?php
namespace flat\core\curl\exception;

use \flat\core\curl\exception;

class system_error extends exception {
   public function get_data() {
      return $this->_data;
   }
   private $_data;
   
   public function get_details() {
      return $this->_details;
   }
   private $_details;   
   
   public function __construct($details,$data=null) {
      $this->_details = $details;
      $this->_data = $data;
      parent::__construct("system error: $details");
   }
}