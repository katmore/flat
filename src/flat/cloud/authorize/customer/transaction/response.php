<?php
/**
 * class definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (C) 2012-2015  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat\cloud\authorize\customer\transaction;

abstract class response extends \flat\data 
   implements \flat\data\ready
{
//    const APPROVED = 1;
//    const DECLINED = 2;
//    const ERROR = 3;
//    const HELD = 4;
   /**
    * @var string transaction status description. acceptable values are:
    *    'APPROVED', 'DECLINED', 'ERROR', and 'HELD'
    */
   public $status;
   
   /**
    * @var string payment processor message, ie: "This transaction has been approved."
    */
   public $message;
   
   /**
    * @var string transaction type
    */
   public $transactionType;
   
   /**
    * @var \AuthorizeNetAIM_Response transactionResponse from authorize.net
    */
   protected $transactionResponse;
   
   /**
    * @var int maps to success::$status if provided.
    *    'APPROVED' = 1, 'DECLINED' = 2, 'ERROR' = 3, and 'HELD' = 4
    * @uses success:$status
    */
   protected $reponse_code;
   
   /**
    * retrieves value of a transactionResponse field
    *    if response::$transactionResponse is a AuthorizeNetAIM_Response object
    *    and specified field exists. 
    *     
    * @param string $field transactionResponse field (property) name
    * 
    * @return mixed
    */
   protected function _get_transactionResponse_field($field) {
      if ($this->transactionResponse instanceof \AuthorizeNetAIM_Response) {
         return $this->transactionResponse->$field;
      }
   }
   
   /**
    * loads a transaction success or failure object corresponding 
    *    to an AuthorizeNetAIM_Response response object.
    * 
    * @param \AuthorizeNetAIM_Response $transactionResponse
    * 
    * @return \flat\cloud\authorize\cusotmer\transaction\success | \flat\cloud\authorize\cusotmer\transaction\failure
    */
   public static function load(\AuthorizeNetAIM_Response $transactionResponse) {
      foreach([1,4] as $success_code) {
         if ($transactionResponse->response_code == $success_code) {
            return new response\success(['transactionResponse'=>$transactionResponse]);
         }
      }
      foreach([2,3] as $failure_code) {
         if ($transactionResponse->response_code == $error_code) {
            return new response\failure(['transactionResponse'=>$transactionResponse]);
         }
      }
   }
   
   public function data_ready() {
      if (empty($this->status) && is_int($this->status_int)) {
         foreach(['APPROVED' => 1, 'DECLINED' => 2, 'ERROR' => 3, 'HELD' => 4] as $code => $int) {
            if ($this->status_int==$int) {
               $this->status = $code;
               break 1;
            }
         }
      }
   }
   
}