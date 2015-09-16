<?php
namespace flat\core\curl\exception;

use \flat\core\curl\exception;

class xpath_error extends exception {
   public function get_query() {
      return $this->_query;
   }
   private $_query;
   public function __construct($query,$details=null) {
      $this->_query = $query;
      if (!empty($details)) {
         $this->_details = $details;
         $details = ": $details";
      } else {
         $this->_details = "no xpath results for given query: $query";
         $details = "";
      }
      parent::__construct("no xpath results for given query: $query");
   }
}