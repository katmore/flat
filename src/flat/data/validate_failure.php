<?php
namespace flat\data;
class validate_failure extends \flat\core\exception {
   /**
    * @var string[]
    *    one or more data fields associated with the failure
    */
   private  $_field;
   private $_message;
   /**
    * one or more \flat\data fields associated with failure 
    *    (public properties of \flat\data object are 'fields')
    * @return string[]
    */
   public function get_field() {
      return $this->_field;
   }
   public function get_message() {
      return $this->_message;
   }
   public function add_field($field) {
      $this->_field[]=$field;
   }
   public function __construct($field,$message=null) {
      $this->_field = [];
      if (!empty($field)) {
         if (is_string($field)) {
            $this->_field[] = $field;
         } elseif (is_array($field)) {
            foreach ($field as $f) {
               if (is_string($f)) {
                  $this->_field[] = $f;
               }
            }
         }
      }
   }
}