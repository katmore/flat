<?php
/**
 * \flat\app\tmpl\notfound 
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
/**
 * namespace
 */
namespace flat\app\tmpl;

use \flat\app\event\error;
use \flat\app\event\meta\app;
/**
 * notfound
 * 
 * @package    flat\app\ap\reeltrend
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
class notfound extends \flat\tmpl 
   implements \flat\tmpl\design, 
   \flat\core\controller\route\restful_status,
   \flat\core\resolver
{
   public function set_resource($resource) {
      $app = NULL;
      app::trigger(function($eventdata=NULL) use(& $app) {
         $app = $eventdata;
      });
      error::set_handler(function($data=NULL) use(& $resource, & $app) {
         return (object) array(
            'resource'=>$resource,
            'message'=>'resource "'.str_replace("\\","/",$resource).'" does not exist',
            'application'=>$app
         );
      });
   }
   /**
    * design namespace as string literal
    */
   const error_404_design='\flat\design\tmpl\error\minimal\404';
   /**
    * @see \flat\core\controller\route\restful_status
    *    makes controller give 404 status code to HTTP client
    */
   public function get_status_code() {
      return 404;
   }
   /**
    * @see \flat\core\controller\route\restful_status
    *    makes controller give 404 status to HTTP client
    */   
   public function get_status_string() {
      return "Not Found";
   }   
   public function get_design() {
      return self::error_404_design;
   }
}
















