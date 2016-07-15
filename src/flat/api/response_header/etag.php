<?php
namespace flat\api\response_header;

use \flat\api\response_header;

class etag extends response_header {
   
   public function __construct($etag) {
      parent::__construct("etag",$etag);
   }
   
}