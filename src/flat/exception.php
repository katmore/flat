<?php
namespace flat;

use flat\core\exception\controller;

abstract class exception extends \Exception {
   use controller;
   
   public function __construct($msg,$code=0) {
      if (!is_int($code)) $code = 0;
      /*
       * if no code provided...
       *    derive a useful code based on some exception attributes
       */
      if (empty($code)) $code = $this->_derive_code();
       
      parent::__construct($msg,$code);
   }  
   
}