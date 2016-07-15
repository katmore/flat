<?php
namespace flat\api\header\exception;

use \flat\api\header\exception;

class missing_request_field extends exception {
   public function __construct() {
      parent::__construct("missing request field");
   }
}