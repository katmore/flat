<?php
/**
 * upload dir configuration for flat deployments 
 */
/**
 * @var string path to directory on deployed system 
 *    where \flat\api child classes can expect that HTTP uploaded files
 *    will be located.
 * 
 * @see \flat\api
 */
//$config['basedir'] = "/var/www/html/blueimp";
$config['basedir'] = realpath(__DIR__."/../../vendor/blueimp/files");
$config['system'] = gethostname();
/**
 * @return array
 */
return $config;
