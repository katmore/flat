<?php
/**
 * \flat\api\response\no_interface definition
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
/**
 * The response of \flat\core\controller\api when the resource has no interface for the request
 * method (for example `POST` to a resource that only implements `GET`).
 *
 * Its status is 500 Internal Server Error. (v1.72.0 answered 405 Method Not Allowed; the
 * maintainer changed it in v1.72.2 because a 405 must carry an `Allow` header, and flat does
 * not send headers itself.) To answer a 405 on purpose, return \flat\api\response\method_not_allowed.
 */
class no_interface extends \flat\api\response {
   public function __construct() {
      $this->message = 'no interface found matching the resolved request method';
      $this->_set_status(new \flat\api\status\server_error);
   }
}