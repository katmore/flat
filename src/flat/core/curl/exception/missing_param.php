<?php
namespace flat\core\curl\exception;
use \flat\core\curl\exception;
class missing_param extends exception{
   public function __construct($name) {
      
      parent::__construct("missing param: $name");
   }
}