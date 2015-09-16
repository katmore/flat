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
namespace flat\cloud;

use flat\cloud\authorize\arb\amount_Subscription;

abstract class authorize {
   /**
    * specifies to enable caching capability
    * @return \flat\db\driver\mongo\crud
    */
   abstract protected function _get_mongo_crud_cache();
   
   /**
    * Specifies the merchant's transaction key.
    * 
    * @return string
    * 
    * @see \AuthorizeNetRequest::__construct()
    */
   abstract protected function _get_transaction_key();
   
   /**
    * Specifies the merchant's API login ID.
    *
    * @return string
    *
    * @see \AuthorizeNetRequest::__construct()
    */
   abstract protected function _get_login_id();
   
   /**
    * Specifies wheather sandbox mode is active or not.
    *    return (bool) true to enable sandbox mode.
    * 
    * @return bool
    * @see \AuthorizeNetRequest::setSandbox()
    */
   abstract protected function _is_sandbox();
   
   /**
    * Specifies transaction logging mode. Logging is disabled
    *    unless a string is returned. return (string) /path/to/log/file 
    *    to enable logging.
    *    
    * @return string | bool | null | void
    * @see \AuthorizeNetRequest::setLogFile()
    */
   abstract protected function _log_file();
   

   
   /**
    * sanity enforcement for params when dealing with existing ARB subscription.
    * @return void
    * @throws \flat\cloud\authorize\missing_required_param
    */
   protected static function _enforce_arb_params(array $params) {
      if (empty($params['subscriptionId'])) {
         throw new authorize\missing_required_param("subscriptionId");
      }
   }
   
   /**
    * prepares an AuthorizeNetRequest object by applying 
    *    configuration from the \flat\cloud\authorize child object.
    *    
    * @see \flat\cloud\authorize::_is_sandbox()
    * @see \flat\cloud\authorize::_get_log_file()
    * 
    * @return \AuthorizeNetRequest
    */
   protected function _prepare_AuthorizeNetRequest(\AuthorizeNetRequest $request) {
      if ($this->_is_sandbox()) {
         $request->setSandbox(true);
      }
      if (!empty($this->_log_file()) && is_string($this->_log_file())) {
         $request->setLogFile($this->_log_file());
      }
      return $request;
   }
   
}





