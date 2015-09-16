<?php
/**
 * Provides \flat\deploy\html class definition and instantiates \flat\deploy\html 
 * class if this file is the executing script.
 * 
 * Suitable as entry point for a cli application.
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
use \flat\core\cli as core;

/**
 * cli application resolver
 * 
 * @package flat\deploy
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version 0.1.0-alpha
 */
class cli {
   /**
    * executes a cli aplication
    * 
    * @see \flat\cli\error ends script with an error status if the 'cli\error' exception is thrown 
    */
   public function __construct() {

      if (PHP_SAPI != 'cli') {
         trigger_error ( "must be in command line", E_USER_ERROR );
      }
      
      require_once(__DIR__."/vendor/autoload.php");
      
      /*
       * prepare configuration
       */
      $config = require (__DIR__ . "/config.php");
      
      
      /*
       * prepare config controller
       */
      config::set_base_dir ( $config ['config_base_dir'] );
      
      
      /*
       * determine resource from command line arguments
       */
      $resource = "";
      
      if ($_SERVER ['argv'] && ! empty ( $_SERVER ['argv'] ) &&
             is_array ( $_SERVER ['argv'] )) {
                
         $argv = $_SERVER ['argv'];

         for($i=1;$i<100;$i++) {
            if (!isset($argv[$i])) break 1;
            if (substr($argv[$i],0,1)=="-") continue;
            if (! empty ( $argv [$i] )) {
               $char1 = substr ( $argv [$i], 0, 1 );
               if (preg_match ( '/[a-zA-Z]/', $char1 ) &&
                      preg_match ( '/[a-zA-Z_0-1]/', $argv [$i] )) {
                  $resource = $argv [$i];
               }
               break 1;
            }
         }
         
         core::set_argv ( $argv ,1+$i);
      }
      
      if (empty($resource)) $resource = "/";
      
      /*
       * instantiate resolver
       */      
      try {

         $cli = new \flat\core\controller\route ( 
            array (
               'resource' => $resource,'input' => array (),
               'route_factory' => '\flat\app\route\cli' 
            )
         );
      /*
       * 'cli\error' thrown within resolved class
       *    exit gracefully, displaying message
       *    with status code as given by 'cli\error' exception
       */
      } catch (\flat\cli\error $e) {
         
         /*
          * display error details
          */
         core::line("error:");
         core::line($e->get_details(),1);
         
         /*
          * display 'usage' as appropriate
          */
         if ($e->is_display_usage_on()) {

            $usage = core::get_command()." ".$resource;
            core::line("usage:");
            /*
             * display parameter from route's 'meta' event if it exists 
             */
            if (class_exists('\flat\app\event\meta\app')) {
               $app = NULL;
               \flat\app\event\meta\app::trigger(function($data=NULL) use(& $app) {
                  $app = $data;
               });
               if (!empty($app->params)) {
                  $usage .= " ".$app->params;
               }
            }
            
            core::line($usage,1);
         
         }
         /*
          * exit with status code as given in exception
          */
         exit($e->get_status());
      }
      /*
       * bla bla bla
       */
      if (! $cli->get_resolve_count ()) {
         trigger_error ( "no resolution for resource", E_USER_ERROR );
      }
      
   
   }

}
/**
 * @uses \flat\deploy\cli class is instanitated class definition file is the executing script
 */
if (PHP_SAPI == 'cli') {
   /*
    * if running under cli SAPI
    * must concatonate current-directory (PWD) to filename (SCRIPT_FILENAME)
    */
   if (! empty ( $_SERVER ) && ! empty ( $_SERVER ['SCRIPT_FILENAME'] ) &&
         ! empty ( $_SERVER ['PWD'] ) &&
          ($_SERVER ['PWD'] . "/".$_SERVER ['SCRIPT_FILENAME'] == __FILE__)) {
      return new cli ();
   }
} else {
   if (! empty ( $_SERVER ) && ! empty ( $_SERVER ['SCRIPT_FILENAME'] ) &&
          ($_SERVER ['SCRIPT_FILENAME'] == __FILE__)) {
      return new cli ();
   }
}














