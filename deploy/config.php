<?php
/**
 * config.php - provides deploy config
 */
$config['resource'] = (isset ( $_SERVER ['PATH_INFO'] )) ? $_SERVER ['PATH_INFO'] : "/";
//$config['resource'] = (isset ( $_SERVER ['PATH_TRANSLATED'] )) ? $_SERVER ['PATH_TRANSLATED'] : "/";
//$config['resource'] = (isset ( $_GET ['p'] )) ? $_GET ['p'] : "/";

/**
 * @var string root path for flat application configuration.
 */
$config['config_base_dir'] = __DIR__."/config";

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















