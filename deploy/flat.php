<?php
/**
 * Include this file within a script to make the flat framework available.
 * 
 * The first portion of this file contains fully documented configuration
 * settings that may be changed as appropriate.
 */
call_user_func(function($flat=[]) {
   /*
    * ---- CONFIGURATION STARTS HERE ----
    */
   /*
    * $flat['vendor_class_file']:
    *    (string) path to dir containing \flat\deploy namespace classes and config directory.
    *    ie: "/path/to/flat/deploy"
    *
    */
   $flat['deploy_dir'] = __DIR__;
   
   /*
    * $flat['config']:
    *    (string) OR (array) path to flat config file, as found in /flat/deploy/config.php, 
    *       or asociative array containing all the following values:
    *          (string) $flat['config']['app'] path to root directory containing \flat\app class definitions.
    *          (string) $flat['config']['deploy'] path to directory containing \flat\deploy class definitions.
    *          (string) $flat['config']['package'] path to directory containing flat "packages", that means
    *             \flat namespace not in \flat\app or \flat\deploy (ie: "/path/to/flat/src").
    *          (string) $flat['config']['vendor'] path to directory containing vendor dependancies that may be
    *          required by \flat namespace classes. (ie: "/path/to/flat/
    */
   $flat['config'] = $flat['deploy_dir'] . "/config.php";
   
   /*
    * ---- NO CONFIGURATION BEYOND THIS POINT ----
    *    there are no configurable settings to edit beyond this point.
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    * 
    */
   if (!class_exists("flat_loader")) {
      /**
       * flat framework loader exception
       */
      class flat_loader_error extends \Exception {}
      /**
       * flat framework initializer
       */
      class flat_loader {
         /**
          * loads flat framework:
          *    sets custom error handling as specified and initializes config controller.  
          */
         public function __construct(array $flat) {
            
            
            require_once ( $flat['deploy_dir'] . "/vendor/autoload.php" );
            
            /*
             * prepare error handling as specified
             */
            if ($flat['enable_error_handler']) {
               require_once ( $flat['deploy_dir'] . "/error_handler.php" );
               \flat\deploy\error_handler::initialize();
               \flat\deploy\error_handler::set_handler ( $flat['error_handler_type'],$flat['error_handler_display_level'] );
            }
            
            /*
             * prepare config controller
             */
            \flat\core\config::set_base_dir($flat['deploy_dir'] . "/config");
            
         }
      }
      new flat_loader($flat);
   }

},(isset($flat))?$flat:[]);/*end self-executing anonymous function*/








