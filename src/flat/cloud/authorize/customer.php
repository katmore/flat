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
namespace flat\cloud\authorize;

use \flat\core\util\deepcopy;

abstract class customer extends \flat\cloud\authorize
implements 
   \flat\core\ccgateway\customer,
   \flat\core\ccgateway\transaction
{
   private $_CIM;
    
   /**
    * provides an AuthorizeNetCIM object corresponding to the
    *    \flat\cloud\authorize child object configuration.
    *
    * @return \AuthorizeNetCIM
    */
   protected function _get_CIM() {
      if (!$this->_CIM) {
         $cim = new AuthorizeNetCIM(
            $this->_get_login_id(),
            $this->_get_transaction_key()
         );
         $this->_CIM = $this->_prepare_AuthorizeNetRequest($cim);
      }
      return $this->_CIM;
   }
   
   /**
    * retrieves an existing customer's info
    * 
    * @return \flat\cloud\authorize\customer\profile
    */
   public function get(array $params) {
      
      $cim = $this->_get_CIM();
      if (isset($params['customerProfileId'])) {
         $response = $cim->getCustomerProfile($params['customerProfileId']);
      } else {
         throw new missing_required_param(['customerProfileId']);
      }
      
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
      if (isset($response->xml->profile)) {
         return new customer\profile(
            deepcopy::data(
               $response->xml->profile,
               ['objects_to_assoc'=>true]
            )
         );
      }
   }
   
   /**
    * updates a CIM customer.
    * 
    * @param array $params assoc array of params:
    *    int $params['customerProfileId'] specify the customerProfileId to update.
    *    int $params['id'] (optional) alias of $params['customerProfileId'].
    *    int $params['merchantCustomerId'] (optional) if specified,
    *       updates the specified customerProfile 'merchantCustomerId' field to this value.
    *    int $params['description'] (optional) if specified,
    *       updates the specified customerProfile 'description' field to this value.
    *    int $params['email'] (optional) if specified,
    *       updates the specified customerProfile 'email' field to this value.
    * 
    * @return void
    * 
    * @throws \flat\cloud\authorize\missing_required_param
    * @throws \flat\cloud\authorize\response_error
    * @throws \flat\cloud\authorize\bad_param
    */
   public function update(array $params) {
      /*
    public merchantCustomerId;
    public description;
    public email;
       */
      //$customerProfileId;
      if (!empty($params['id'])) {
         $customerProfileId = $params['id'];
      } elseif (!empty($params['customerProfileId'])) {
         $customerProfileId = $params['customerProfileId'];
      } else {
         throw new missing_required_param("customerProfileId");
      }
      
      $update = [];
      foreach(['merchantCustomerId','description','email'] as $prop) {
         if (isset($params[$prop])) $update[$prop] = $params[$prop];
      }
      
      if (count($update)<1) {
         throw new bad_param("params","must specify at least one of the following: 'merchantCustomerId','description','email'");
      }
      
      //$customerProfile = new customer\profile_Customer( new customer\profile($update) );
      
      $response = $this->_get_CIM()->updateCustomerProfile(
         $customerProfileId,
         new customer\profile_Customer( new customer\profile($update) )
      );
      
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
   }
   /**
    * determines validationMode from specified params assoc array.
    * 
    * @return string
    * 
    * @param array $params:
    *    bool | string $params['validate']  (optional) default bool true. 
    *       defines any extra payment processor validation. if (bool) true 
    *       or (string) "liveMode". specifies "liveMode" validationMode. if 
    *       (bool) false or (string) "none" specifies no additional payment 
    *       processor validation should occur. if (string) "testMode", 
    *       specifies "testMode" validationMode.
    *    bool | string $params['validationMode'] (optional) alias of $params['validate'].
    *    
    * @throws \flat\cloud\authorize\bad_param
    */
   private static function _params_to_validationMode(array $params) {
      $validationMode = "liveMode";
      if (isset($params['validate'])) {
          if (is_string($params['validate'])) {
             if (in_array($params['validate'],['liveMode','testMode','none'])) {
                $validationMode = $params['validate'];
             } else {
                throw new bad_param('validate',"must be (bool) or (string) with value 'liveMode','testMode','none'");
             }
          }elseif (is_bool($params['validate'])) {
             if ($params['validate']) {
                $validationMode = "liveMode";
             } else {
                $validationMode = "none";
             }
          }
      } elseif 
      /*
       * recursion if alias 'validationMode' specified
       */
      (isset($params['validationMode'])) {
          return self::_params_to_validationMode(['validate'=>$params['validationMode']]);
      }
      
      return $validationMode;
   }
   /**
    * creates payment_profile object(s) as specified
    * 
    * @return \flat\cloud\authorize\customer\payment_profile
    */
   private static function _params_to_payment_profile(array $params) {
      $payment_profile = null;
      if (isset($params['payment'])) {
         if (is_array($params['payment'])) {
            $payment_profile = new customer\payment_profile($params['payment']);
         } elseif (is_object($params['payment']) && ($params['payment'] instanceof customer\payment_profile)) {
            $payment_profile = $params['payment'];
         } else {
            throw new bad_param(
                  "payment",
                  "must be assoc array or ".'\flat\cloud\authorize\customer\payment_profile object'
            );
         }
      }
      return $payment_profile;
   }
   /**
    * creates a CIM customer. returns the customerProfileId.
    *
    * @return string
    * 
    * @param array $params:
    *    \flat\cloud\authorize\customer\profile | array $params['profile'] customer profile data, 
    *       or assoc array specifying profile data fields.
    *    bool | string $params['validate']  (optional) default bool true. 
    *       defines any extra payment processor validation. if (bool) true 
    *       or (string) "liveMode". specifies "liveMode" validationMode. if 
    *       (bool) false or (string) "none" specifies no additional payment 
    *       processor validation should occur. if (string) "testMode", 
    *       specifies "testMode" validationMode.
    *    bool | string $params['validationMode'] (optional) alias of $params['validate'].
    *    int $params['merchantCustomerId'] (optional) if specified,
    *       updates the specified customerProfile 'merchantCustomerId' field to this value.
    *    int $params['description'] (optional) if specified,
    *       updates the specified customerProfile 'description' field to this value.
    *    int $params['email'] (optional) if specified,
    *       updates the specified customerProfile 'email' field to this value.
    *       
    * @throws \flat\cloud\authorize\bad_param
    * @throws \flat\cloud\authorize\missing_required_param
    * @throws \flat\cloud\authorize\response_error
    */
   public function create(array $params) {
      
      $create = [];
      
      $validationMode = self::_params_to_validationMode($params);
      
      foreach(['merchantCustomerId','description','email'] as $prop) {
         if (isset($params[$prop])) $create[$prop] = $params[$prop];
      }
      
      if (count($create)<1) {
         throw new bad_param("params","must specify at least one of the following: 'merchantCustomerId','description','email'");
      }
      
      $customerProfile = new customer\profile_Customer(new customer\profile($create));
      
      /*
       * set customerProfile payment profiles as specified
       *    with self::_params_to_payment_profile()
       */
      $payment_profile = self::_params_to_payment_profile($params);
      if ($payment_profile) {
         $customerProfile->paymentProfiles[] = $payment_profile;
      }
      
      /*
       * run API request and return customerProfileId if success
       */
      $response = $this->_get_CIM()->createCustomerProfile($customerProfile,$validationMode);
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
      return $response->getCustomerProfileId();
   }
    
   /**
    * adds a payment method to an existing CIM customer.
    *    returns the customerPaymentProfileId.
    *    
    * @return string
    * 
    * @throws \flat\cloud\authorize\bad_param
    * @throws \flat\cloud\authorize\missing_required_param
    * @throws \flat\cloud\authorize\response_error
    */
   public function create_paymethod(array $params) {
      
      if (empty($params['customerProfileId'])) {
         throw new missing_required_param('customerProfileId');
      }
      
      $payment_profile = self::_params_to_payment_profile($params);
      
      $response = $this->_get_CIM()->createCustomerPaymentProfile($customerProfileId, $payment_profile);
      
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }      
      //$customerPaymentProfileId = 
      $v = $response->xpath('//customerPaymentProfileId');
      if(!empty($v[0])){
         return (string) $v[0];
      }
      throw new unexpected_response("could not find 'customerPaymentProfileId'");
   }
   
   /**
    * edits a customer's existing payment method
    * 
    * @return void
    * 
    * @throws \flat\cloud\authorize\bad_param
    * @throws \flat\cloud\authorize\response_error
    * @throws \flat\cloud\authorize\missing_required_param
    */
   public function update_paymethod(array $params) {
      foreach(['customerPaymentProfileId','customerProfileId'] as $key) {
         if (empty($params[$key])) {
            throw new missing_required_param($key);
         }
      }
      $payment_profile = self::_params_to_payment_profile($params);
      
      $response = $this->_get_CIM()->updateCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId, $payment_profile);
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
   }
   
   /**
    * removes an existing payment method from a customer
    * 
    * @param array $params assoc array of parameters:
    *    int $params['customerPaymentProfileId'] specify the 
    *       customerPaymentProfileId to be removed.
    *    int $params['customerProfileId'] specify the 
    *       customerPaymentProfileId to be removed.
    * 
    * @return void
    * 
    * @throws \flat\cloud\authorize\response_error
    * @throws \flat\cloud\authorize\missing_required_param
    */
   public function delete_paymethod(array $params) {
      foreach(['customerPaymentProfileId','customerProfileId'] as $key) {
         if (empty($params[$key])) {
            throw new missing_required_param($key);
         }
      }
      $cim = $this->_get_CIM();
      $response = $cim->deleteCustomerPaymentProfile(
         $params['customerProfileId'],
         $params['customerPaymentProfileId']
      );
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
   }
   
   public function void_transaction(array $params) {
      
   }
   
   public function refund_transaction(array $params) {
      
   }
   
   public function authonly($customerProfileId,$customerPaymentProfileId,$amount,array $extra_params=NULL) {
      
   }
   
   public function authcapture($customerProfileId,$customerPaymentProfileId,$amount,array $extra_params=NULL) {
   
   }
   
   public function captureonly($customerProfileId,$customerPaymentProfileId,array $extra_params=NULL) {
      $params = [
         'customerProfileId'=>$customerProfileId,
         'customerPaymentProfileId'=>$customerPaymentProfileId,
      ];
      if (!empty($extra_params) && is_array($extra_params)) {
         foreach($extra_params as $k=>$v) {
            if (!empty($k)) {
               
            }
         }
      }
   }
   
   /**
    * creates a payment transaction.
    * 
    * @param array $params assoc array of parameters:
    *    array | \flat\cloud\authorize\customer\transaction $params['transaction'] customer transaction object OR
    *       assoc array of transaction specifications:
    *       string $params['transaction']['transactionType'] (required) transaction type. acceptable values are
    *          'authcapture', 'authonly' and 'captureonly'.
    *       string $params['transaction']['amount'] (required) amount.
    *       mixed $params['transaction'][(optional)] other transaction fields to specify.
    *    if $params['transaction'] is not specified then ONE of the following is required:
    *       array | \flat\cloud\authorize\customer\transaction\authonly $params['authonly'].
    *       array | \flat\cloud\authorize\customer\transaction\authonly $params['authcapture'].
    *       array | \flat\cloud\authorize\customer\transaction\authonly $params['captureonly'].
    * 
    * @return \flat\cloud\authorize\customer\transaction\response\success | \flat\cloud\authorize\customer\transaction\response\failure
    * 
    * @throws \flat\cloud\authorize\bad_param
    * @throws \flat\cloud\authorize\response_error
    * @throws \flat\cloud\authorize\missing_required_param
    */
   public function create_transaction(array $params) {
      
      $transaction = null;
      $transtypes = [
         'authcapture'=>'AuthCapture',
         'authonly'=>'AuthOnly',
         'captureonly'=>'CaptureOnly',
      ];
      if (isset($params['transaction'])) {
         foreach ($transtypes as $key=>$type) {
            if (isset($params[$key])) unset($params[$key]);
         }
         if (is_object($params['transaction'])) {
            if (!$params['transaction'] instanceof customer\transaction) {
               throw new bad_param("transaction","if specified must be a ".
                  '\flat\cloud\authorize\customer\transaction object or array ".
                  "defining fields for that class of object'
               );
            }
            $params[$params['transaction']->get_customer_transaction_type()] = $params['transaction'];
         } elseif(is_array($params['transaction'])) {
            if (!isset($params['transaction']['transactionType'])) {
               throw new missing_required_param("transaction.transactionType");
            }
            $customer_transaction_type = "";
            foreach ($transtypes as $key=>$type) {
               if ($params['transaction']['transactionType']==$type) {
                  $params['transaction']['transactionType'] = $key;
               }
            }
            if (empty($customer_transaction_type)) {
               throw new bad_param(
                  "transaction.transactionType",
                  "unknown transactionType specified"
               );
            }
            unset($params['transaction']['transactionType']);
            $params[$customer_transaction_type] = $params['transaction'];
         } else {
            throw new bad_param("transaction","if specified must be a ".
               '\flat\cloud\authorize\customer\transaction object or array ".
               "defining fields for that class of object'
            );
         }
      }
      foreach($transtypes as $key=>$type) {
         if (isset($params[$key])) {
            $classname = '\flat\cloud\authorize\customer\transaction'."\\$key";
            if (is_array($params[$key])) {
               $transaction = new $classname($params[$key]);
               $transactionType = $type;
            } elseif (is_object($params[$key]) && is_a($params[$key],$classname)) {
               $transaction = $params[$key];
               $transactionType = $type;
            } else {
               throw new bad_param(
                  $key,
                  "must be '$classname' object or assoc array ".
                  "specifying fields of '$classname' class"
               );
            }
            break 1;
         }
      }
      if (empty($transaction)) {
         throw new missing_required_param($transtypes);
      }
      
      $response = $this->_get_CIM()->createCustomerProfileTransaction($transactionType, $transaction);
      if ($response->isError()) {
         throw new response_error($response->getMessageCode(), $response->getMessageText());
      }
      
      return customer\transaction\response::load($response->getTransactionResponse());
   }
   
   /**
    * retrieves info on a past transaction
    * 
    * @return \flat\cloud\authorize\transaction
    */
   public function get_transaction(array $params) {
      
   }
}









