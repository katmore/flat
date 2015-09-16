<?php
/**
 * \flat\core\controller\route\restful_status interface 
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
namespace flat\core\controller\route;
/**
 * provides a RESTful status to route controller that can be provided to route invoker via callback 
 * 
 * @see \flat\core\controller\route
 * @see \flat\core\controller\route\restful_status\callback_data data provided to callback
 * 
 * @package    flat\route
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html official RFC documenting valid status codes 
 * @link http://en.wikipedia.org/wiki/List_of_HTTP_status_codes helpful article on status codes
 * 
 * @todo create parent class and children with valid status code 
 *    and status string combintions. The methods here could be prone
 *    to breaking an HTTP session if the implementer is not totally
 *    familiar and careful to use HTTP status codes / string combos.
 * 
 * @todo change interface to have just get_status()
 * @todo create /flat/core/controller/route/restful_status/controller.php
 *     as convenience for applying this interface.
 */
interface restful_status {
   /**
    * HTTP status code, such as: 200, 404, 500
    * @return int
    */
   public function get_status_code();
   /**
    * HTTP status string, such as: "OK", "Not Found", "Internal Server Error"
    * @return string
    */
   public function get_status_string();
}









