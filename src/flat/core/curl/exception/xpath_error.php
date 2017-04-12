<?php
namespace flat\core\curl\exception;

use \flat\core\curl\exception;

class xpath_error extends exception {
   /**
    * @return string
    */
   public function get_query() {
      return $this->_query;
   }
   /**
    * @return string
    */
   public function get_details() {
      return $this->_details;
   }
   /**
    * @return \LibXMLError
    */
   public function get_libXMLError() {
      return $this->_libXMLError;
   }
   
   private $_query;
   private $_details;
   private $_libXMLError;
   public function __construct($query,$details=null,\LibXMLError $libXMLError=null) {
      $msg = [];
      $this->_query = $query;
      $this->_details = $details;
      $this->_libXMLError = $libXMLError;
      
      if (!empty($details) && is_string($details)) {
         $msg []= $details;
      } else {
         $details = "";
      }
      
      if ($libXMLError instanceof \LibXMLError) {
         if (!empty($libXMLError->message) && (false===strpos($details,$libXMLError->message))) {
            $msg []= $libXMLError->message;
         }
      }
      
      if (!count($msg)) {
         $msg []= "xpath error";
      }
      
      if (!empty($query) && is_string($query) && (false===strpos($details,$query))) {
         
         $msg []= "query: $query";
         
      }
      
      parent::__construct(implode(", ",$msg));
   }
}