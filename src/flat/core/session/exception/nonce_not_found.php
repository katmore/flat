<?php
namespace flat\core\session\exception;
class nonce_not_found extends invalid_nonce {
   public function __construct() {
      parent::__construct("the specified nonce does not exist");
   }
}