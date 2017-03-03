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

use \net\authorize\api\contract\v1\AnetApiResponseType;
use \net\authorize\api\contract\v1\MessagesType;
/**
 * Exception populated with details of an authorize.net response error
 */
class response_error extends exception {
   /**
    * @var string
    *    Authorize.net error message code
    */   
   private $_message_code;
   /**
    * @var string
    *    Authorize.net error message text
    */   
   private $_message_text;
   /**
    * @return string
    *    Authorize.net error message text
    */
   public function get_message_text() {
      return $this->_message_text;
   }
   /**
    * @return string
    *    Authorize.net error message code
    */
   public function get_message_code() {
      return $this->_message_code;
   }

   /**
    * @return \net\authorize\api\contract\v1\AnetApiResponseType
    */
   public function get_response() {
      return $this->_response;
   }
   /**
    * @var \net\authorize\api\contract\v1\AnetApiResponseType
    */
   private $_response;
   /**
    * Creates an exception corresponding to an authorize.net response
    * 
    * @param \net\authorize\api\contract\v1\AnetApiResponseType $response
    */
   public function __construct($msg,$msg_code=null) {
      $exmsg_suffix = "";
      if ($msg instanceof AnetApiResponseType) {
         $this->_response = $msg;
         if ($this->_response->getMessages() instanceof MessagesType) {
            $this->_message_code = $this->_response->getMessages()->getMessage()[0]->getCode();
            $this->_message_text = $this->_response->getMessages()->getMessage()[0]->getText();
         }  
      } else {
         $this->_message_text = $msg;
         $this->_message_code = $msg;
      }
      if (!empty($this->_message_code) && is_scalar($this->_message_code) && !is_bool($this->_message_code)) {
         $exmsg_suffix .= " '{$this->_message_code}'";
      }
      if (!empty($this->_message_text) && is_string($this->_message_text)) {
         $exmsg_suffix .= ": ".$this->_message_text;
      }
      // echo "Response : " . $response->getMessages()->getMessage()[0]->getCode() . "  " .$response->getMessages()->getMessage()[0]->getText() . "\n";
      parent::__construct("authorize.net response error$exmsg_suffix");
   }
}










