<?php
/**
 * config.php - provides deploy config
 */
$config['resource'] = (isset ( $_SERVER ['PATH_INFO'] )) ? $_SERVER ['PATH_INFO'] : "/";
//$config['resource'] = (isset ( $_SERVER ['PATH_TRANSLATED'] )) ? $_SERVER ['PATH_TRANSLATED'] : "/";
//$config['resource'] = (isset ( $_GET ['p'] )) ? $_GET ['p'] : "/";
/**
 * @var bool determines if display errors
 */
$config['display_errors'] = true;

/**
 * @var string root path for class definitions within the \flat\deploy namespace  
 */
$config['deploy'] =__DIR__;

/**
 * @var string root path for class definitions within the \flat\deploy namespace
 */
$config['vendor'] =__DIR__."/vendor";

/**
 * @var string root path for class definitions other than those 
 *    in namespaces \flat\app and \flat\deploy. 
 */
$config['package'] = __DIR__.'/../src/flat';

/**
 * @var string root path for class definitions within the \flat\app namespace  
 */
$config['app'] = __DIR__.'/../app';

/**
 * @var string root path for flat application configuration.
 */
$config['config_base_dir'] = __DIR__."/config";

/**
 * @var string path to autoloader parent class definition.
 *    if empty value, no autoloaders will be registered.
 */
$config['autoload'] = __DIR__."/autoload.php";

/**
 * @var string path to app autoloader class definition 
 *    for loading class definitions within the \flat\app namespace.
 *    if empty value, app autoloader will not be registered.
 */
$config['autoload_app'] = __DIR__."/autoload/app.php";

/**
 * @var string path to deploy autoloader class definition 
 *    for loading class definitions within the \flat\app namespace
 *    if empty value, deploy autoloader will not be registered.
 */
$config['autoload_deploy'] = __DIR__."/autoload/deploy.php";

/**
 * @var string path to package autoloader class definition 
 *    for loading class definitions other than those in namespaces 
 *    \flat\app and \flat\deploy. if empty value, package autoloader 
 *    will not be registered.
 */
$config['autoload_package'] = __DIR__."/autoload/package.php";

/**
 * @var string error handler display document type.
 *    acceptable values are 
 *    "html", "xml", "json", "text", or "wsod"
 */
$config['error_handler_display'] = "html";

/**
 * @var string error handler display level.
 *    any matching errors will be cause the script to end
 *    and error document above to be displayed.
 * 
 *    This will not cause the details of the error to display
 *    unless $config['error_handler_display_details'] = true below.
 */
$config['error_handler_display_level'] = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED;

/**
 * @var bool wheather to display full error and exception
 *    details in the error display document.
 */
$config['error_handler_display_details'] = true;

/**
 * @var array assoc array of with key indicating 
 *    silent error handler to activate, value the corresponding error level. 
 *    acceptable element keys are "syslog", "logfile", "email"
 */
$config['error_handler_silent'] = array("syslog"=>E_ALL & ~E_WARNING & ~E_DEPRECATED,"logfile"=>E_ALL & ~E_WARNING & ~E_DEPRECATED);

/**
 * @return array
 */
return $config;















