<?php
namespace flat\orm;
class invalid_param extends exception {
   public function get_field_key() {
      return $this->_key;
   }
   public function get_reason() {
      return $this->_reason;
   }
   private $_key;
   private $_reason;
   public function __construct($key,$reason) {
      $this->_key = $key;
      $this->_reason = $reason;
      parent::__construct("invalid field: $key, reason: $reason",$this->_value_to_code($key.$reason));
   }
}