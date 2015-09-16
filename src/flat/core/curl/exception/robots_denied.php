<?php
namespace flat\core\curl\exception;
use \flat\core\curl\exception;
class robots_denied extends exception {
   public function __construct($url,$useragent) {
      parent::__construct("robots.txt not allow this url");
   }
}