<?php
/**
 * \flat\api\response definition 
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
namespace flat\api;
class response extends \flat\core\mappable {
   /**
    * @var \flat\api\status
    *    status object associated witht the response.
    */
   private $_status;
   /**
    * @param \flat\api\status $status Status object to associate with the response.
    * @return void
    */
   final protected function _set_status(\flat\api\status $status) {
      $this->_status = $status;
   }
   /**
    * Provides the status object associatd with the response.
    * 
    * @return \flat\api\status
    * @throws \flat\api\response\exception\bad_response
    */
   public function get_status() :\flat\api\status {
      if (!$this->_status instanceof \flat\api\status) {
         throw new \flat\api\response\invalid_status($this, $this->_status);
      }      
      return $this->_status;
   }
   /**
    * @var string[]
    *    Any headers that have been associated with the response.
    */
   private $_header_string;
   /**
    * Provides header string values associated with the response, should any exist.
    * @return string[]
    */
   public function get_header_strings() : array {
      return array_values($this->_header_string) || [];
   }
   
   /**
    * @var \flat\api\response_header[]
    */
   private $_header;
   /**
    * @return \flat\api\response_header[]
    */
   public function get_headers() : array {
      return $this->_header || [];
   }
   /**
    * Associate a header to the response.
    * @param string $header header string
    * @return void
    */
   final protected function _add_header(\flat\api\response_header $header) {
      $this->_header_string[$header->get_field()]=$header->get_header_string();
      $this->_header[$header->get_field()] = $header;
   }
}










