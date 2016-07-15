<?php
namespace flat\core\session\exception;
class nonce_consumed extends invalid_nonce {
   public function __construct() {
      parent::__construct("the specified nonce has already been consumed");
   }
}