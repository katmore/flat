<?php
/**
 * \flat\api\response\invalid_status exception
 *
 * PHP version >=7.0
 *
 * Copyright (c) 2012-2015 Doug Bird.
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
 * "MIT License" (also known as the Simplified BSD License or 2-Clause BSD License
 * See the file MIT-LICENSE.txt), or the terms and conditions
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
namespace flat\api\response;

class invalid_status extends \flat\core\exception {
   /**
    * Provides the response object associated this exception.
    * @return \flat\api\response 
    */
   public function get_response() : \flat\api\response {
      return $this->_response;
   }
   /**
    * Provides the 'bad' status value associated with this exception.
    * @return mixed
    */
   public function get_status_value() {
      return $this->_status_value;
   }
   /**
    * @var \flat\api\response
    */
   private $_response;
   /**
    * @var mixed
    *    the 'bad' status value that was the cause of this exception.
    */
   private $_status_value;
   /**
    * @param \flat\api\response $response The response object associated this exception.
    * @param mixed $status_value the 'bad' status value that was the cause of this exception.
    */
   public function __construct(\flat\api\response $response,$status_value) {
      $this->_response = $response;
      $this->_status_value = $status_value;
      parent::__construct("missing or invalid status object asssociated with the response");
   }
}