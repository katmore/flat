<?php
/**
 * Provides \flat\deploy\api class definition
 *  
 * instantiates class if this file is the executing script.
 * 
 * Suitable as entry point for a RESTful api application.
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

/**
 * alias for config ns
 */
use \flat\core\config;

/**
 *
 * @package flat\deploy
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version 0.1.0-alpha
 */
class api {

   public function __construct() {
      
      /*
       * prepare configuration
       */
      $config = require (__DIR__ . "/config.php");
      
      /*
       * prepare config controller
       */
      config::set_base_dir ( $config ['config_base_dir'] );
      
      /*
       * prepare input object
       */
      if ($_SERVER['REQUEST_METHOD']=='GET') {
         /*
          * GET request method means input is in pre-parsed query string
          */
         $input = new \flat\input\map( $_GET );
         
      } else {
         /*
          * POST, PUT, DELETE, etc request methods...
          *    the input COULD be available preparsed query string (with $_POST, etc) 
          *       --but-- 
          *    need to handle request's with JSON data input
          *       also... 
          *    PHP doesn't put all request methods input data into a convenience super global 
          */
         $content_type = '';
         if (!empty($_SERVER["CONTENT_TYPE"])) {
            $content_type = $_SERVER["CONTENT_TYPE"];
         }
         //\flat\core\debug::dump($content_type,'content type');
         
         $rawinput = file_get_contents('php://input');
         if (!empty($rawinput)) {
            if (false !== (strpos($content_type,"application/json"))) {
               
               if (false !== ($json = json_decode($rawinput))) {
                  $input = new \flat\input\map($json);
               } else {
                  trigger_error(
                     "malformed JSON in request document",
                     E_USER_ERROR
                  );
               }
            } else {
               /*
                * determine if input is JSON document
                */
               $json = json_decode($rawinput);
               if (false !== $json && NULL !==$json) {
                  //\flat\core\debug::dump($json,"implied json input");
                  $input = new \flat\input\map( $json );
               } else {
                  //\flat\core\debug::dump($rawinput,"raw input");
                  /*
                   * determine if input is query string
                   *    (like browser processed HTML forms do by default for POST, etc actions)
                   */            
                  parse_str($rawinput, $input);
               
                  if (!empty($input)) {
                     $input = new \flat\input\map($input);
                  } else {
                     $input = new \flat\input\map();
                  }
               }
               
            }
         }
        
      }
      
      /*
       * prepare request method
       *    if the real request method is GET
       *       can invoke 'POST','PUT','DELETE' request methods
       *       as indicated by having a potential request method as an input property
       */    
      $method = $_SERVER['REQUEST_METHOD'];  
      if ($method=="GET") {

         foreach (array('POST','PUT','DELETE') as $invoke) {
            if (isset($input->$invoke)) {
               $method = $invoke;
               unset($input->$invoke);
               break 1;
            }
         }
      }
      
      /*
       * prepare api controller: set HTTP request method to what is determined
       * by the interface handler
       */
      \flat\core\controller\api::set_method ( $method );
      
      ob_start();
      /*
       * set the api response handler using \flat\deploy\api\interface_handler
       */
      \flat\core\controller\api::set_response_handler (function ($response) {
         
         $ob = ob_get_clean();
         if (!empty($ob)) {
            echo $ob;
            trigger_error("non-empty output buffer, cannot produce valid json response",E_USER_ERROR);
         }
         header('Content-Type: application/json',true);
         
         echo json_encode($response);
      });
      
      /*
       * process api call through api routing controller
       */
      $api = new \flat\core\controller\route ([ 
         'resource' => $config['resource'],
         'input' => $input,
         'route_factory' => '\flat\app\route\api' 
      ]);
      
      /*
       * prevent white screen of death when no route has been resolved trigger
       * an eror.
       */
      if (! $api->get_resolve_count ()) {
         trigger_error ( "no resolution for resource", E_USER_ERROR );
      }
   
   }

}


































































