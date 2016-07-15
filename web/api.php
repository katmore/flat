<?php
//
// -- this file originally from flat/web/api.php
//
require __DIR__.'/../vendor/autoload.php';
/*
 * api-route.php
 * This file is an api entry point.
 * It creates a router that creates objects
 *    based on rules in the /flat/app/route/api class definition.
 */
/*
 * the vlaue of $display_error_details determines if 
 *    details of php errors are displayed.
 * example:
 *    $display_error_details = true; //display error details
 *    
 */
$display_error_details = true;
/*
 * HTTP SERVER CONFIGURATION: 
 *    successful flat api routing is dependant on the HTTP server configuration.
 *    please consult the following files:
 *       flat/examples/web-server/config-samples/apache2-htaccess.txt
 *       flat/examples/web-server/config-samples/nginx-location.txt
 *       flat/examples/web-server/config-samples/nginx-server.txt
 */
/*
 * THERE IS NO CONFIGURATION BEYOND THIS POINT
 * 
 * The flat framework is copyrighted free software.
 * Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * See the full license and copyright:
 * https://github.com/katmore/flat/LICENSE.txt
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
if ($display_error_details) ini_set("display_errors","1");
if (! class_exists('\flat\deploy\api')) {
   $error=[];
   if ($display_error_details) {
      $error['msg'] = "\flat\deploy\api not found";
      $error['suggestion'] = "ensure that you have configured this project\n" .
            "\tie: by running composer.phar\n".
            "also, check the file \n\t'".__FILE__."'\n".
            "ensure the require 'autoload.php' path is correct.";
   } else {
      $error['msg']= "we are experiencing difficulties";
      $error['suggestion'] = "if this problem persists, contact support or your system administrator.";
   }
   if (isset($_SERVER['SERVER_PROTOCOL'])) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      header("Content-Type:text/xml");
      echo 
'<?xml version="1.0"?>
<error>
';
      foreach($error as $key=>$val) {
         echo "
   <$key>".htmlentities($val)."</$key>";
      }
      echo
'
</error>';
   }
   ?>
   <?php
   exit(1);
}
return new \flat\deploy\api();