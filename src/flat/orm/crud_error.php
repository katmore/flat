<?php
namespace flat\orm;
class crud_error extends exception {
   public function get_details() {
      return $this->_details;
   }
   private $_details;
   public function get_op() {
      return $this->_op;
   }
   private $_op;
   public function __construct($op,$details=null) {
      $this->_op = $details;
      $this->_details = $details;
      if (is_string($op)) {
         $msg = "orm $op error";
      } else {
         $msg = 'orm error';
      }
      if (!empty($details) && is_string($details)) {
         $msg .= ": $details";
      }
      parent::__construct($msg,$this->_value_to_code($op.$details));
   }
}