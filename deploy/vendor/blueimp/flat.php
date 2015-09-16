<?php
/**
 * Include this file within a script to make the flat framework available.
 * 
 * The first portion of this file contains fully documented configuration
 * settings that may be changed as appropriate.
 */
/*
 * ---- CONFIGURATION STARTS HERE ----
 */
/*
 * $flat['vendor_class_file']:
 *    (string) path to dir containing \flat\deploy namespace classes and config directory.
 *    ie: "/path/to/flat/deploy"
 *    
 */
$flat['deploy_dir'] = __DIR__."/../../";

/*
 * $flat['enable_error_handler']:
 *    (bool) when set to true, enables flat's special php error handling.
 *    set to false to leave error handling as it may (or may not) be configured.
 *
 *    if enabled, it will be enabled for ALL php errors (not just those encountered
 *    within the flat framework).
 */
$flat['enable_error_handler'] = true;

/*
 * $flat['error_handler_type']:
 *    ignored if $flat['enable_error_handler'] is false. 
 *    
 *    (string) one of the following: "wsod", "html", "xml", "json".
 *    
 *    controls how php errors are displayed.
 */
$flat['error_handler_type'] = "xml";

/*
 * $flat['error_handler_display_details']:
 *    ignored if $flat['enable_error_handler'] is false. 
 *    
 *    (bool) when set to true, flat error handling will display full error details.
 *    
 *    when set to false, flat error handling will display a brief message stating
 *    the script encountered an error, such as "we are experiencing difficulties",
 *    depending on the error_handler_type.
 *
 *      
 */
$flat['error_handler_display_details'] = true;

/*
 * $flat['error_handler_display_level']:
 *    ignored if $flat['enable_error_handler'] is false. 
 *    
 *    (int) error handler display level.
 *    
 *    any matching errors will be cause the script to end
 *    and error document above to be displayed.
 *
 *    This will not cause the details of the error to display
 *    unless $config['error_handler_display_details'] = true below.
 */
$config['error_handler_display_level'] = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED;

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
   class flat_loader_error extends \Exception {}
   /**
    * prepares flat framework
    */
   class flat_loader {
      public function __construct(array $flat) {
         
         /*
          * prepare error handling as specified
          */
         if ($flat['enable_error_handler']) {
            require_once ( $flat['deploy_dir'] . "/error_handler.php" );
            \flat\deploy\error_handler::initialize();
         }
         
         
         /*
          * prepare error_handling as specified
          */
         if ($flat['enable_error_handler']) {
            \flat\deploy\error_handler::set_handler ( $flat['error_handler_type'], E_ALL & ~E_WARNING & ~E_DEPRECATED );
         }
         /*
          * prepare config controller
          */
         \flat\core\config::set_base_dir($flat['deploy_dir'] . "/config");
         
         /*
          * prepare debug handling: defer displaying debug output until end of
          * script format debug display in html
          */
         \flat\deploy\debug::add_display_handler("\\flat\\deploy\\debug\\display\\ignore");    
         
      }
   }
   //var_dump($flat);
   //die('flat/deploy/flat');
   new flat_loader($flat);
}










