<?php
namespace flat\core\curl\exception;

class no_xpath_results extends xpath_error {
   
   public function __construct($query,$details=null) {
      parent::__construct($query,"no xpath results for given query: $query");
   }
   
}