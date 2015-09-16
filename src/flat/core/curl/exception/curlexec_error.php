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
namespace flat\core\curl\exception;
class curlexec_error extends \flat\core\curl\exception {
   /**
    * provides curl error message
    * @return string
    */
   public function get_curl_error() {
      return $this->_error;
   }
   /**
    * provides curl error number
    * @return int
    */
   public function get_curl_errno() {
      return $this->_errno;
   }
   /**
    * provides url associated with curl error
    * @return string
    */
   public function get_url() {
      return $this->_url;
   }
   private $_error;
   private $_errno;
   private $_url;
   /**
    * @param resource $handle curl resource
    * @param string $url url associated with this error
    */
   public function __construct($handle,$url) {
      $this->_errno = curl_errno($handle);
      $this->_error = curl_error($handle);
      $this->_url = $url;
      parent::__construct("curl_exec error #".curl_errno($handle).": '".curl_error($handle). "' url: '$url'",100000+curl_errno($handle));
      
   }
}
