<?php
/**
 * index.php
 * 
 * this file exists because many web servers are configured to look for it
 */
namespace flat\deploy\vendor\blueimp;
require_once("upload.php");
/**
 * loads flat framework and creates new class instance if this file same as $_SERVER['SCRIPT_FILENAME']
 *
 * @see $_SERVER['SCRIPT_FILENAME']
 */
if (! empty($_SERVER) && ! empty($_SERVER['SCRIPT_FILENAME']) && ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )) {
   upload::start_service(['load_flat'=>true]);
}