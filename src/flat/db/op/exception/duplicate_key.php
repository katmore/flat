<?php
/**
 * \flat\db\op\exception\failure class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\op\exception;
/**
 * record not found exception
 * 
 * @package    flat\db
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class duplicate_key extends \flat\db\op\exception {
   /**
    * namespace label on which duplicate key is thrown on
    * such as "table" or "collection" litterally... 
    *    NOT A TABLE or COLLECTION name! (that is given to constructor)
    * @return string
    */
   abstract public function get_namespace_label();
   private $_index;
   private $_value;
   /**
    * retrieve $value arg passed to constructor
    * @return mixed
    */
   final public function get_value() {
      return $this->_value;
   }
   /**
    * retrieve $index arg passed to constructor
    * @return scalar
    */
   final public function get_index() {
      return $this->_index;
   }
   /**
    * @param string $namespace name of table or collection, etc. where the duped index resides
    * @param scalar $index name of index with dupe
    * @param mixed $value (optional) duplicate value causing error 
    */
   final public function __construct($namespace,$index="",$value="") {
      if (is_string( $this->get_namespace_label() ) && !empty($this->get_namespace_label())) {
         $label = " ".$this->get_namespace_label();
      }  else {
         $label = "";
      }
      $code = $this->_value_to_code($label.$namespace.$index);
      $this->_index = $index;
      $this->_value = $value;      
      if (!empty($index)) $index = "index: $index";
      if (is_scalar( $index)) {
         $index = ", $index";
      } else {
         $index="";
         
      }      
      if (!empty($value)) {
         if (is_scalar($value)) {
            $value = " (value: $value)";
         } else {
            $value = " (non-scalar value)";
         }
      }
      parent::__construct(
         "duplicate key in$label: $namespace".$index.$value,
         $code
      );
   }
}


















