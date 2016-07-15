<?php
namespace flat\core\session\exception;
class invalid_nonce_data extends invalid_nonce {
   public function __construct() {
      parent::__construct("could not find a valid meta object associated with specified nonce");
   }
}