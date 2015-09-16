<?php
//
// -- this file originally from flat/examples/web-service/api-route.php
//
/*
 * api-route.php
 * This file is an api entry point.
 * It creates a router that creates objects
 *    based on rules in the /flat/app/route/api class definition.
 */
/*
 *    set value of $flat_deploy_dir to actual path of the flat/deploy directory
 *    example: 
 *       $flat_deploy_dir = "/var/lib/flat/deploy";
 */
$flat_deploy_dir = "/path/to/flat/deploy";
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
if (! is_file("$flat_deploy_dir/api.php") || !is_readable("$flat_deploy_dir/api.php")) {
   $error=[];
   if ($display_error_details) {
      $error['msg'] = "$flat_deploy_dir/api.php not found";
      $error['suggestion'] = "please edit the file \n\t'".__FILE__."'\n".
            "set the value of the variable\n".
            "\t\$flat_deploy_dir\n".
            "to the system path containing flat/deploy directory of the flat package.";
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
require ("$flat_deploy_dir/api.php");
return new \flat\deploy\api();