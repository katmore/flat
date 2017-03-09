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
namespace flat\cloud;

use \net\authorize\api\contract\v1 as AnetAPI;
use \net\authorize\api\controller\base\ApiOperationBase;
use \flat\cloud\authorize\response_error;
use \net\authorize\api\contract\v1\AnetApiResponseType;


/**
 * Authorize.net API call handler.
 *
 * You can use the following boilerplate code to implement an API call handler...<br>
 *    <i>(boilerplate is for the "<b>Get Customer Profile IDs</b>" API call, adjust accordingly)</i>
 *    <br>
 *    <i>(see: http://developer.authorize.net/api/reference/#customer-profiles-get-customer-profile-ids)</i>
 <pre><code>
 //
 // ---START CODE EXAMPLE---
 //
 new class() extends \flat\app\cloud\activepitch\authorize {
 &nbsp;&nbsp;&nbsp;public function __construct() {
 &nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$request = new \net\authorize\api\contract\v1\GetCustomerProfileIdsRequest();
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$request->setMerchantAuthentication($this->_load_MerchantAuthentication());
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$response = $this->_executeWithApiResponse(
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;new \net\authorize\api\controller\GetCustomerProfileIdsController($request)
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if (!$response instanceof \net\authorize\api\contract\v1\GetCustomerProfileIdsResponse) {
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;throw new \flat\cloud\authorize\unexpected_response (
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"expected GetCustomerProfileIdsResponse, instead got: " . get_class($response)
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
 &nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;}
 &nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;protected function _get_transaction_key() {
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return "my-transaction-key";
 &nbsp;&nbsp;&nbsp;}
 &nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;protected function _get_login_id() {
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return "my-login-id";
 &nbsp;&nbsp;&nbsp;}
 &nbsp;&nbsp;&nbsp;
 &nbsp;&nbsp;&nbsp;protected function _get_end_point() {
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return \net\authorize\api\constants\ANetEnvironment::SANDBOX;
 &nbsp;&nbsp;&nbsp;}
 &nbsp;&nbsp;&nbsp;
 };
 //
 // ---END CODE EXAMPLE---
 //&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 </code></pre>
 */
abstract class authorize {

   /**
    * Specifies the merchant's transaction key.
    *
    * @return string
    */
   abstract protected function _get_transaction_key();

   /**
    * Specifies the merchant's API login ID.
    *
    * @return string
    */
   abstract protected function _get_login_id();

   /**
    * Specifies the API end point URL
    * @return string
    */
   abstract protected function _get_end_point();

   /**
    * prepares merchant authentication object
    *
    * @return \net\authorize\api\contract\v1\MerchantAuthenticationType
    */
   protected function _load_MerchantAuthentication() {
       
      $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
      $merchantAuthentication->setName($this->_get_login_id());
      $merchantAuthentication->setTransactionKey($this->_get_transaction_key());
       
      return $merchantAuthentication;
   }

   /**
    * convenience wrapper to invoke a controller::executeWithApiResponse()
    *    and throw a response_error if the result code is not 'Ok'.
    *
    * @return \net\authorize\api\contract\v1\AnetApiResponseType
    *
    * @see \net\authorize\api\contract\v1\MessagesType::getResultCode() If this value is not the string "Ok" a response_error is thrown.
    * @see \net\authorize\api\controller\base\ApiOperationBase::executeWithApiResponse()
    * @throws \flat\cloud\authorize\response_error
    *
    */
   protected function _executeWithApiResponse(ApiOperationBase $controller) {
       
      try {
         $response = $controller->executeWithApiResponse( $this->_get_end_point() );
      } catch (\Exception $e) {
         throw new \Exception(print_r($e,true));
      }
       
      if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
      {
         return $response;
      }
       
       
      throw new response_error($response);
   }

}





