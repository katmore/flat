<?php
/**
 * upload service class
 * 
 * suitable as an entry point for a file upload service
 */
namespace flat\deploy;
class upload {
   
   public function __construct(array $config=NULL) {
      
      \flat\deploy\vendor\blueimp\upload::start_service($config);
      
   }
}

/**
 * loads flat framework and creates new class instance if this file same as $_SERVER['SCRIPT_FILENAME']
 *
 * @see $_SERVER['SCRIPT_FILENAME']
*/
if (! empty($_SERVER) && ! empty($_SERVER['SCRIPT_FILENAME']) && ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )) {
   /*
    * composer class autoloader
    */
   require(__DIR__."/vendor/autoload.php");
   
   /*
    * flat initialization
    */
   require(__DIR__."/flat.php");
   
   /*
    * instantiate upload class
    */
   new upload();
}





