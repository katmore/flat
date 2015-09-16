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
abstract class AuthorizeNetTransaction extends \AuthorizeNetTransaction {
   
   /**
    * returns (bool) false or (string) if invalid, if return value
    *    is (string), the value is the error message.
    *
    * @return bool | string
    */
   abstract protected function _validate_transaction_type(\flat\cloud\authorize\customer\transaction $transaction);
   
   public function __construct(\flat\cloud\authorize\customer\transaction $transaction) {

      //validate transaction object
      $validate = $this->_validate_transaction_type($transaction);
      if ($validate!==true) {
         if ($validate===false) {
            throw new \flat\cloud\authorize\bad_param("transaction","invalid transaction type");
         }
         if (is_string($validate)) {
            throw new \flat\cloud\authorize\bad_param("transaction",$validate_msg);
         }
      }
      parent::__construct();
      
      foreach ($transaction as $prop=>$val) {
         if (isset($this->$prop)) $this->$prop = $val;
      }
      
   }
}