<?php
/**
 * 
 * Provides \flat\deploy\html class definition and instantiates \flat\deploy\html 
 * class if this file is the executing script.
 * 
 * Suitable as entry point for an html application.
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
namespace flat\deploy;

use \flat\core\config;
use \flat\app\event\error;

/**
 * entry point for an html application
 *
 * @package flat\deploy
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version 0.1.0-alpha
 */
class html {
   /**
    */
   const primitive_error_details = false;
   /**
    * resolves an html application
    *
    * @param string $resource
    *           (optional) resource to resolve. defaults to the value of
    *           superglobal $_SERVER['PATH_INFO'] if it exists with non-empty
    *           value, otherwise defaults to "\".
    * @param string $route_factory
    *           (optional) defaults to '\flat\app\route\html'. route factory
    *           class which specifies rules to resolve $resource into
    *           \flat\app\* classes.
    * @see \flat\app\route\html default html application route factory
    * @see \flat\app\helper ideal namespace to place helper classes into.
    * @see \flat\app\tmpl ideal namespace to place template classes into.
    * @see \flat\helper parent class for helper classes.
    * @see \flat\tmpl parent class for template classes.
    * @see \flat\deploy\error_handler
    * @see \flat\core\controller\route resolves a route to one or more classes
    *      for a specified route factory and resource. In the following
    *      explaination the resource is: "my/resource" (as if the requested URL
    *      is http://www.example.com/flat/deploy/html.php/my/resource)
    */
   public function __construct($resource = "", $route_factory = '\flat\app\route\html') {
      
      require_once(__DIR__."/vendor/autoload.php");

      /*
       * prepare configuration
       */
      $config = require ( __DIR__ . "/config.php" );
      

      /*
       * prepare config controller
       */
      config::set_base_dir($config['config_base_dir']);
      
      if (empty($resource))
         $resource = $config['resource'];
      /**
       * prepare RESTful status callback for html route
       *
       * @todo implement display handler for non-\flat\tmpl apps providing
       *       status error
       */
      $param = array (
         'resource' => $resource,
         'restful_status_callback' => function ($status) {
            if ($status->code != 200) {
               header($_SERVER["SERVER_PROTOCOL"] . " " . $status->code . " " . $status->string, true, $status->code);
               if (! is_a($status->app, "\\flat\\tmpl", true)) {
                  die(get_class($status->app) . " provided non-ok RESTful status: #" . $status->code . ": " . $status->string);
               }
            }
         } 
      );
      
      /*
       * prepare display handler for template system
       */
      \flat\core\controller\tmpl::set_display_handler(
         function ($design, $data) use(& $errcol, & $errdis) {
            $data->errdata = array ();
            if (! empty($errcol))
               if ($errcol->get_has_errors()) {
                  $data->errdata = $errcol->get_errdata();
               }
            header('Content-Type: text/html; charset=utf-8');
            \flat\core\controller\tmpl::display($design, $data);
         });
      if (isset($_GET)) {
         if (is_array($_GET)) {
            $param['input'] = $_GET;
         }
      }
      $param['route_factory'] = $route_factory;
      $html = new \flat\core\controller\route($param);
      if (! $html->get_resolve_count()) {
         throw new html\exception\no_resolution($resource);
      }
   }
}
































