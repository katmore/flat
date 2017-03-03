<?php
/**
 * class definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
 * ALL RIGHTS RESERVED. THIS COPYRIGHT APPLIES TO THE ENTIRE CONTENTS OF THE WORKS HEREIN
 * UNLESS A DIFFERENT COPYRIGHT NOTICE IS EXPLICITLY PROVIDED WITH AN EXPLANATION OF WHERE
 * THAT DIFFERENT COPYRIGHT APPLIES. WHERE SUCH A DIFFERENT COPYRIGHT NOTICE IS PROVIDED
 * IT SHALL APPLY EXCLUSIVELY TO THE MATERIAL AS DETAILED WITHIN THE NOTICE.
 * 
 * The flat framework is copyrighted free software.
 * You can redistribute it and/or modify it under either the terms and conditions of the
 * "The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
 * of the "GPL v3 License" (see the file GPL-LICENSE.txt).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @license The MIT License (MIT) http://opensource.org/licenses/MIT
 * @license GNU General Public License, version 3 (GPL-3.0) http://opensource.org/licenses/GPL-3.0
 * @link https://github.com/katmore/flat
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\cloud\authorize\customer\transaction\response;

use \flat\cloud\authorize\customer\transaction\response as data;

class failure extends data 
   implements \flat\data\ready
{
   /**
    * @var string reason text for failure.
    *    maps from \AuthorizeNetResponse::$response_reason_text
    */
   public $reason;
   
   /**
    * @var string reason code for failure.
    *    maps from \AuthorizeNetResponse::$response_reason_code
    */
   public $reason_code;
   
   /**
    * @var string response subcode regarding failure.
    *    maps from \AuthorizeNetResponse::$response_subcode
    */
   public $response_subcode;
   
   public function data_ready() {
      parent::data_ready();
      
      foreach ([
         'reason'=>'response_reason_text',
         'reason_code'=>'response_reason_code',
         'response_subcode'=>'response_subcode'
      ] as $this_prop=>$trans_field) {
         if (empty($this->$this_prop)) {
            $this->$this_prop = $this->_get_transactionResponse_field($trans_field);
         }
      }
   }
}











