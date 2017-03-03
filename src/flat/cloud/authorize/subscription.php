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
class subscription extends \flat\cloud\authorize
   implements 
      \flat\cloud\ccgateway\subscription,
      \flat\cloud\ccgateway\transaction
 {
    
    /**
     * loads an AuthorizeNet Subscription object with payment properties.
     *
     * @param array $params assoc array of parameters:
     *    array | \flat\cloud\authorize\subscription\payment $params['payment'] assoc array of updated payment fields,
     *       or a payment type specification:
     *    array | \flat\cloud\authorize\subscription\payment\bankAccount $params['payment']['bankAccount'] (optional)
     *       specifies that data contained within element are 'bankAccount' payment type field specifications.
     *    array | \flat\cloud\authorize\subscription\payment\creditCard $params['payment']['creditCard'] (optional)
     *       specifies that data contained within element are 'bankAccount' payment type field specifications.
     *
     * @return \flat\cloud\authorize\subscription\payment\bankAccount_Subscription | \flat\cloud\authorize\subscription\payment\creditCard_Subscription
     */
    private static function _load_Subscription_with_Payment(array $params) {
       /*
        * sanity enforcement for payment params
        */
       if (!isset($params['payment'])) {
          throw new \flat\cloud\authorize\missing_required_param('payment');
       }
       if (isset($params['payment']['creditCard']) && isset($params['payment']['bankAccount'])) {
          throw new \flat\cloud\authorize\bad_param(
                'payment',
                "only one payment type can be specified"
          );
       }
       $subscription = null;
       if (is_array($params['payment'])) {
          if (isset($params['payment']['accountType'])) {
             $params['payment']['bankAccount'] = new authorize\subscription\payment\bankAccount($params['payment']);
          } elseif (isset($params['payment']['cardNumber'])) {
             $params['payment']['creditCard'] = new authorize\subscription\payment\creditCard($params['payment']);
          }
       } elseif ($params['payment'] instanceof authorize\subscription\payment) {
          $params['payment'][$params['payment']->get_data_type()] = new $params['payment'];
       }
       foreach (['creditCard','bankAccount'] as $prop) {
          if (isset($params['payment'][$prop])) {
             if (!is_array($params['payment'][$prop]) && (!$params['payment'][$prop] instanceof authorize\subscription\payment)) {
                throw new authorize\bad_params("payment.$prop","must be assoc array or ".'\flat\cloud\authorize\subscription\payment object');
             }
             $class_name = "\\flat\\cloud\\authorize\\subscription\payment\\$prop";
             if (is_array($params['payment'][$prop])) {
                $payment = new $class_name($params['payment']['creditCard']);
             } else {
                $payment = $params['payment'][$prop];
             }
             $class_name = "\\flat\\cloud\\authorize\\subscription\payment\\$prop"."_Subscription";
             $subscription = new $class_name($payment);
             break 1;
          }
       }
       if (!$subscription) {
          throw new \flat\cloud\authorize\bad_param(
                'payment',
                "must specify creditCard or bankAccount payment"
          );
       }
       return $subscription;
    }
    
    /**
     * retrieves info on past transactions.
     * 
     * @param array $params: assoc array of parameters:
     *     int $params['subscriptionId'] specify subscriptionId.
     *     int $params['limit'] (optional) default 5, maximum 100. specifies number of matching transactions to return.
     *     int $params['skip'] (optional) default 0. specifies number of matching transactions to skip.
     * 
     * @return \flat\cloud\authorize\subscription\transaction | \flat\cloud\authorize\subscription\transaction[] 
     */
    public function get_transaction(array $params) {
       
    }
    
    /**
     * changes the 'amount' property of an existing ARB subscription.
     *
     * @param array $params assoc array of parameters:
     *    string $params['subscriptionId'] specifies the subscriptionId of ARB
     *    string $params['amount'] new amount
     *
     * @return void
     *
     * @see \AuthorizeNetARB::updateSubscription()
     *
     * @throws \flat\cloud\authorize\response_error
     * @throws \flat\cloud\authorize\missing_required_param
     * @throws \flat\cloud\authorize\bad_param
     */
    public function update_amount(array $params) {
       self::_enforce_arb_params($params);
       $arb = $this->_get_ARB();
       if (!isset($params['amount'])) throw new authorize\missing_required_param('amount');
       if (!is_int($params['amount'])) throw new authorize\bad_param('amount', 'must be int');
       $response = $arb->updateSubscription($params['subscriptionId'], new authorize\subscription\amount_Subscription($params['amount']) );
       if ($response->isError()) {
          throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
       }
    }
    
    /**
     * creates an ARB Subscription
     *
     * @return string
     *
     * @param array $params assoc array of parameters:
     *    string $params['amount'] currency amount.
     *    array | \flat\cloud\authorize\subscription\schedule $params['payment']['schedule']
     *       an subscription\schedule object, or schedule specification as assoc array:
     *       string $params['payment']['schedule']['interval']['unit']
     *          (required) @see \flat\cloud\authorize\subscription\interval::unit.
     *       int $params['payment']['schedule']['interval']['length']
     *          (required) @see \flat\cloud\authorize\subscription\interval::length.
     *       string $params['payment']['schedule']['startDate']
     *          (required) @see \flat\cloud\authorize\subscription\schedule::startDate.
     *       string $params['payment']['schedule']['totalOccurrences']
     *          (optional) @see \flat\cloud\authorize\subscription\schedule::totalOccurrences.
     *       string $params['payment']['schedule']['startDate_timeZone']
     *          (optional) @see \flat\cloud\authorize\subscription\schedule::startDate_timeZone.
     *       string $params['payment']['schedule']['new_subscription']
     *          (optional) @see \flat\cloud\authorize\subscription\schedule::new_subscription.
     *
     *    array | \flat\cloud\authorize\subscription\payment $params['payment'] assoc array of payment fields,
     *       an subscription\payment object, or a payment type specification:
     *       array | \flat\cloud\authorize\subscription\payment\bankAccount $params['payment']['bankAccount'] (optional)
     *          specifies that data contained within element are 'bankAccount' payment type field specifications.
     *       array | \flat\cloud\authorize\subscription\payment\creditCard $params['payment']['creditCard'] (optional)
     *          specifies that data contained within element are 'bankAccount' payment type field specifications.
     *
     * @throws \flat\cloud\authorize\response_error
     * @throws \flat\cloud\authorize\missing_required_param
     * @throws \flat\cloud\authorize\bad_param
     *
     * @see \flat\cloud\authorize\subscription\payment\bankAccount
     * @see \flat\cloud\authorize\subscription\payment\creditCard
     * @see \AuthorizeNetARB::createSubscription()
     */
    public function create(array $params) {
       $arb = $this->_get_ARB();
    
       /*
        * using _load_Subscription_with_Payment() to
        *    initialize the subscription object with payment info
        *    also enforces parameter sanity.
       */
       $subscription = self::_load_Subscription_with_Payment($params);
    
       /*
        * enforce ['amount'] param exists
       */
       if (!isset($params['amount'])) {
          throw new missing_required_param("amount");
       }
       if (empty($params['amount']) || !is_scalar($param['amount']) || is_bool($param['amount'])) {
          throw new bad_param("amount","must be non-empty non-bool scalar value");
       }
    
       /*
        * enforce ['payment']['schedule'] param exists
        */
       if (!isset($params['payment']['schedule'])) {
          throw new missing_required_param("payment['schedule']");
       }
    
       /*
        * enforce ['payment']['schedule'] param is assoc array or subscription\schedule object
        */
       if (!is_array($params['payment']['schedule'] && (!$params['payment']['schedule'] instanceof subscription\schedule))) {
          throw new bad_param("payment['schedule']","must be assoc array or \flat\cloud\authorize\subscription\schedule object");
       }
    
       /*
        * derive the subscription\schedule object:
        *    convert to \flat\cloud\authorize\subscription\schedule object
        *    if ['payment']['schedule'] is array
        */
       if (is_array($params['payment']['schedule'])) {
          $schedule = new \flat\cloud\authorize\subscription\schedule($params['payment']['schedule']);
       } else {
          $schedule = $params['payment']['schedule'];
       }
    
       /*
        * prepare to map schedule and interval properties
        */
       $interval = \flat\core\util\deepcopy::data( $schedule->interval );
       $schedule_map = \flat\core\util\deepcopy::data($schedule);
       unset($schedule_map->interval);
    
       /*
        * map the schedule object's properites to the Subscription object
       */
       foreach($schedule_map as $k=>$v) {
          $prop = $k;
          if (isset($subscription->$prop)) $subscription->$prop = $v;
       }
    
       /*
        * map the schedule object's properites to the Subscription object
        */
       foreach($interval as $k=>$v) {
          $prop = "interval".ucfirst($k); //camelCase the property name
          if (isset($subscription->$prop)) $subscription->$prop = $v;
       }
    
       /*
        * map some authorize\subscription\ objects to $subscription
        */
       foreach(['customer','order','billTo','shipTo'] as $shortname) {
          if (isset($params[$shortname])) {
             $class_name = "\\flat\\cloud\\authorize\\subscription\\$shortname";
             if (is_array($params[$shortname])) {
                $object = new $class_name($params[$shortname]);
             } elseif (
                   is_object($params[$shortname]) &&
                   is_a($params[$shortname], $class_name)
             ) {
                $object = $params[$shortname];
             } else {
                throw new authorize\bad_param(
                      "$shortname",
                      "if specified, must be assoc array or '$class_name' object"
                );
             }
             foreach($customer as $k=>$v) {
                $prop = $shortname.ucfirst($k); //camelCase the property name
                if (isset($subscription->$prop)) $subscription->$prop = $v;
             }
          }
       }
       
       /*
        * map inconsitently named params to Subscription object
        */
       if (isset($params['order'])) {
          if (is_array($params['order']) && isset($params['order']['name'])) {
             $subscription->name = $params['order']['name'];
          } else
          if (is_object($params['order']) && ($params['order'] instanceof subscription\order)) {
             $subscription->name = $params['order']->name;
          }
       }
       
       /*
        * map some more inconsistently parameters to Subscription object
        */
       foreach(['name','amount'] as $prop) {
          if (isset($param[$prop])) {
             $v = $param[$prop];
             $subscription->$prop = $v;
          }
       }
    
       $response = $arb->createSubscription($subscription);
       if ($response->isError()) {
          throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
       }
    
    }
   const arb_search_max_tics = 100000;
   const arb_search_result_ttl = 3600; //1 hour ttl
   /**
    * retrieves info for specified subscription.
    *    if mongo_crud_cache is configured, will search there for
    *    subscriptionId, using that data if found and within
    *    arb_search_result_ttl.
    *
    *    searches for an arb with given subscriptionId by paginating over
    *    ARBGetSubscriptionList Responses until a match is found.
    *
    * @return \stdClass
    */
    public function get_info(array $params) {
      self::_enforce_arb_params($params);
      $subscriptionId = $params['subscriptionId'];
      /*
      * see if arb data is cached in mongo
          *    and not older than the ttl
      *    (don't worry it'll be encrypted
      *    except $ttl and $subscriptionId)
      */
      //       try {
       
      //       } catch (\Exception $e) {
       
      //       }
      
      $list = new \AuthorizeNetGetSubscriptionList();
      $list->paging = new \AuthorizeNetSubscriptionListPaging();
      $list->paging->limit = 1000;
      $list->paging->offset = 1;
      
      /*
      * first get the status
      *    to know if need to look at
      *       'subscriptionActive' or 'subscriptionInactive'
      *    search types.
      */
      if ($this->get_arb_info($params)=='active') {
       $list->searchType = 'subscriptionActive';
       } else {
       $list->searchType = 'subscriptionInactive';
       }
      
       /*
       * paginate through SubscriptionList 1000 at a time
       *    until we find it :|
       */
          $subscriptionData = null;
         $i=0;
         $s=0;
          for(;;) {
             $response = $this->_get_ARB()->getSubscriptionList();
             if ($response->isError()) {
              throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
      }
      if ($total = $response->xpath('/ARBGetSubscriptionListResponse/ARBGetSubscriptionListResult/totalNumInResultSet')) {
      
      } else {
      throw new authorize\unexpected_response("'totalNumInResultSet' field not found");
      }
      $subscriptionDetail = $response->xpath(
      '/ARBGetSubscriptionListResponse'.
      '/ARBGetSubscriptionListResult'.
                     '/subscriptionDetails'.
                        '/subscriptionDetail'
      );
         foreach ($subscriptionDetail as $detail) {
         if ($id = $detail->id) {
                  if ($id == $subscriptionId) {
         $subscriptionData = json_decode(json_encode($detail));
         break 2;
                  }
      }
               $s++;
      }
      if (count($subscriptionDetail)<1000) {
            break 1;
      }
            if ($s>$total) break 1;
            if ($i>self::arb_search_max_tics) break 1;
         $i++;
      }
      if ($subscriptionData) return $subscriptionData;
         throw new authorize\subscription\not_found($subscriptionId);
      
         }
          
         /**
         * updates an existing ARB subscription's payment details.
         *
         * @return void
         *
         * @param array $params assoc array of parameters:
         *    string $params['subscriptionId'] specifies the subscriptionId of ARB
            *    array | \flat\cloud\authorize\subscription\payment $params['payment'] assoc array of updated payment fields,
            *       or a payment type specification:
            *       array | \flat\cloud\authorize\subscription\payment\bankAccount $params['payment']['bankAccount'] (optional)
            *          specifies that data contained within element are 'bankAccount' payment type field specifications.
            *       array | \flat\cloud\authorize\subscription\payment\creditCard $params['payment']['creditCard'] (optional)
            *          specifies that data contained within element are 'bankAccount' payment type field specifications.
            *
            * @see \AuthorizeNetARB::updateSubscription()
            *
            * @see \flat\cloud\authorize\subscription\payment\bankAccount
            * @see \flat\cloud\authorize\subscription\payment\creditCard
            *
            * @throws \flat\cloud\authorize\response_error
            * @throws \flat\cloud\authorize\missing_required_param
            * @throws \flat\cloud\authorize\bad_param
            */
            public function update_paymethod(array $params) {
            self::_enforce_arb_params($params);
            $arb = $this->_get_ARB();
            $response = $arb->updateSubscription($params['subscriptionId'],  self::_load_Subscription_with_Payment($params));
            if ($response->isError()) {
            throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
            }
            }
             
            /**
            * cancels an ARB subscription
            *
            * @param array $params assoc array of parameters:
            *    string $params['subscriptionId'] specifies the subscriptionId of ARB
            *
            * @return void
            *
            * @see \AuthorizeNetARB::cancelSubscription()
            *
            * @throws \flat\cloud\authorize\response_error
            * @throws \flat\cloud\authorize\missing_required_param
            */
            public function cancel(array $params) {
            self::_enforce_arb_params($params);
            $arb = $this->_get_ARB();
            $response = $arb->cancelSubscription($params['subscriptionId']);
            if ($response->isError()) {
            throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
      }
   }
    
   /**
   * retrieves an ARB subscription status.
   *    possible statuses are: active, expired, suspended, cancelled, terminated.
   *
   * @return string
   *
   * @see \AuthorizeNetARB::getSubscriptionStatus()
   *
   * @throws \flat\cloud\authorize\response_error
   * @throws \flat\cloud\authorize\missing_required_param
   */
   public function get_status(array $params) {
   self::_enforce_arb_params($params);
   $arb = $this->_get_ARB();
   $response = $arb->getSubscriptionStatus($params['subscriptionId']);
   if ($response->isError()) {
   throw new authorize\response_error($response->getMessageCode(), $response->getMessageText());
   }
   return strtolower($response->getSubscriptionStatus());
   }
    
   /**
   * determines if an ARB can have its amount or payment details updated
   *
   * @return bool
   *
   * @throws \flat\cloud\authorize\response_error
   * @throws \flat\cloud\authorize\missing_required_param
   *
   * @see \AuthorizeNetARB::getSubscriptionStatus()
   */
   public function is_updatable(array $params) {
      $status = $this->get_arb_status($params);
      if ($status == 'active' || $status == 'suspended') {
         return true;
      }
      return false;
   }
   
   private $_ARB;
    
   /**
    * provides an AuthorizeNetARB object corresponding to the
    *    \flat\cloud\authorize child object configuration.
    * @return \AuthorizeNetARB
    */
   protected function _get_ARB() {
      if (!$this->_ARB) {
         $arb = new \AuthorizeNetARB(
               $this->_get_login_id(),
               $this->_get_transaction_key()
         );
         $this->_ARB = $this->_prepare_AuthorizeNetRequest($arb);
      }
      return $this->_ARB;
   }
}