<?php
namespace flat\core\session\exception;
class nonce_expired extends invalid_nonce {
   public function get_ttl() {
      return $this->_ttl;
   }
   private $_ttl;
   public function __construct($ttl) {
      $this->_ttl = $ttl;
      parent::__construct("the specified nonce has expired with a ttl of $ttl");
   }
}