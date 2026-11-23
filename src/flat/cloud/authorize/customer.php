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

/**
 * @deprecated since v1.73.1. Authorize.Net no longer supports the legacy SDK classes this is built on
 *    (AuthorizeNetAIM, AuthorizeNetARB, AuthorizeNetCIM). It will be removed in v2.X; see
 *    docs/modernization/DEFERRED-V2.md, item 2.
 */
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
    * builds the SDK payment profile (\AuthorizeNetPaymentProfile) from the 'payment' param, or returns null if
    *    there is none.
    *
    * @return \AuthorizeNetPaymentProfile | null
    *
    * @throws \flat\cloud\authorize\bad_param
    */
   private static function _params_to_payment_profile(array $params) {
      if (!isset($params['payment'])) {
         return null;
      }
      $payment = $params['payment'];
      if ($payment instanceof customer\payment_profile) {
         $spec = ['billTo'=>$payment->billTo, 'payment'=>$payment->payment];
         if (property_exists($payment, 'customerType')) {
            $spec['customerType'] = $payment->customerType;
         }
      } elseif (is_array($payment)) {
         $spec = $payment;
      } else {
         throw new bad_param(
               "payment",
               "must be assoc array or ".'\flat\cloud\authorize\customer\payment_profile object'
         );
      }
      $profile = new \AuthorizeNetPaymentProfile();
      if (isset($spec['customerType'])) {
         $profile->customerType = $spec['customerType'];
      }
      if (isset($spec['billTo'])) {
         self::_copy_fields($spec['billTo'], $profile->billTo, 'payment.billTo');
      }
      if (isset($spec['payment'])) {
         self::_copy_payment($spec['payment'], $profile->payment);
      }
      if (!isset($spec['customerType']) && !isset($spec['billTo']) && !isset($spec['payment'])) {
         throw new bad_param("payment","must specify at least one of: 'billTo','payment','customerType'");
      }
      return $profile;
   }
   /**
    * copies the 'payment' part of a payment profile: a payment object, an assoc array with a 'creditCard' or
    *    'bankAccount' element, or an assoc array of card or bank account fields.
    *
    * @throws \flat\cloud\authorize\bad_param
    */
   private static function _copy_payment($payment, \AuthorizeNetPayment $destination) {
      if ($payment instanceof customer\payment) {
         $type = $payment::get_data_type();
         $fields = get_object_vars($payment);
         unset($fields['type']); // flat's own marker of the payment type, not a field Authorize.Net defines
      } elseif (is_array($payment)) {
         if (isset($payment['creditCard']) && isset($payment['bankAccount'])) {
            throw new bad_param('payment.payment', 'only one payment type can be specified');
         }
         if (isset($payment['creditCard'])) {
            $type = 'creditCard'; $fields = $payment['creditCard'];
         } elseif (isset($payment['bankAccount'])) {
            $type = 'bankAccount'; $fields = $payment['bankAccount'];
         } elseif (isset($payment['cardNumber'])) {
            $type = 'creditCard'; $fields = $payment;
         } elseif (isset($payment['accountNumber']) || isset($payment['routingNumber']) || isset($payment['accountType'])) {
            $type = 'bankAccount'; $fields = $payment;
         } else {
            throw new bad_param('payment.payment', "must specify creditCard or bankAccount payment");
         }
      } else {
         throw new bad_param('payment.payment', "must be assoc array or ".'\flat\cloud\authorize\customer\payment object');
      }
      self::_copy_fields($fields, $destination->$type, "payment.payment.$type");
   }
   /**
    * copies fields from an assoc array or flat data object onto an SDK object.
    *
    * @throws \flat\cloud\authorize\bad_param
    */
   private static function _copy_fields($fields, $destination, $name) {
      if (is_object($fields)) {
         $fields = get_object_vars($fields);
      }
      if (!is_array($fields)) {
         throw new bad_param($name, "must be assoc array or object of fields");
      }
      foreach ($fields as $field=>$value) {
         if (!property_exists($destination, $field)) {
            throw new bad_param($name, "unknown field '$field'");
         }
         if ($value !== null) {
            $destination->$field = $value;
         }
      }
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
    *       specifies "testMode" validationMode. it applies only when a 'payment'
    *       is given; without one the mode is always "none".
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
      } else {
         // A validation mode only applies to a payment profile; Authorize.Net refuses one without it (E00013).
         $validationMode = 'none';
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
    * @param array $params assoc array of parameters:
    *    string $params['customerProfileId'] (required) the customer.
    *    array | \flat\cloud\authorize\customer\payment_profile $params['payment'] (required) the payment profile:
    *       array | \flat\cloud\authorize\customer\billTo $params['payment']['billTo'] (optional) billing name and address.
    *       string $params['payment']['customerType'] (optional) 'individual' or 'business'.
    *       $params['payment']['payment'] the payment details: an array with a 'creditCard' or 'bankAccount'
    *          element, an array of card fields (cardNumber, expirationDate, cardCode) or bank account fields
    *          (accountType, routingNumber, accountNumber, nameOnAccount, echeckType, bankName), or a
    *          \flat\cloud\authorize\customer\payment_profile\creditCard or bankAccount object.
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
      
      if (!isset($params['payment'])) {
         throw new missing_required_param('payment');
      }
      $payment_profile = self::_params_to_payment_profile($params);
      
      $response = $this->_get_CIM()->createCustomerPaymentProfile($params['customerProfileId'], $payment_profile);
      
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
    * @param array $params assoc array of parameters:
    *    string $params['customerProfileId'] (required) the customer.
    *    string $params['customerPaymentProfileId'] (required) the payment method to change.
    *    array | \flat\cloud\authorize\customer\payment_profile $params['payment'] (required) the new details,
    *       as for create_paymethod().
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
      if (!isset($params['payment'])) {
         throw new missing_required_param('payment');
      }
      $payment_profile = self::_params_to_payment_profile($params);
      
      $response = $this->_get_CIM()->updateCustomerPaymentProfile($params['customerProfileId'], $params['customerPaymentProfileId'], $payment_profile);
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
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException('authorize customer void_transaction() is not implemented');
   }
   
   public function refund_transaction(array $params) {
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException('authorize customer refund_transaction() is not implemented');
   }
   
   public function authonly($customerProfileId,$customerPaymentProfileId,$amount,array $extra_params=NULL) {
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException("authorize customer authonly() is not implemented; use create_transaction() with transactionType 'authonly'");
   }
   
   public function authcapture($customerProfileId,$customerPaymentProfileId,$amount,array $extra_params=NULL) {
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException("authorize customer authcapture() is not implemented; use create_transaction() with transactionType 'authcapture'");
   }
   
   public function captureonly($customerProfileId,$customerPaymentProfileId,array $extra_params=NULL) {
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException("authorize customer captureonly() is not implemented; use create_transaction() with transactionType 'captureonly'");
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
            $customer_transaction_type = $params['transaction']['transactionType'];
            if (!isset($transtypes[$customer_transaction_type])) {
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
      
      // The SDK needs its own transaction object: flat's adapter for this type checks the type and copies the fields.
      $adapter = '\flat\cloud\authorize\customer\transaction'."\\$key\\AuthorizeNetTransaction";
      $response = $this->_get_CIM()->createCustomerProfileTransaction($transactionType, new $adapter($transaction));
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
      // RETIRED by owner decision: never implemented (empty body returned null).
      throw new \BadMethodCallException('authorize customer get_transaction() is not implemented');
   }
}









