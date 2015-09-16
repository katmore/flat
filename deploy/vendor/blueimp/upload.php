<?php
/**
 * defines \flat\deploy\vendor\blueimp\upload class
 * 
 * suitable as entry-point upload handler for blueimp jquery plugin
 * 
 * uses blueimp PHP's UploadHandler class modified to make PSR-0 compliant
 */
namespace flat\deploy\vendor\blueimp;

class upload {
   private $_data;
   public function __construct(array $config) {
      ob_start();
      new \blueimp\UploadHandler($config['blueimp']);
      $json = ob_get_clean();
       
      if (!($data = json_decode($json))) {
         throw new \flat\lib\exception\app_error("invalid response from UploadHandler");
      }
      $this->_data = $data;
   }
   public function get_data() {
      return $this->_data;
   }
   /**
    * starts upload service, optionally loading flat framework
    * 
    * @param array $config assoc array of configuration params:
    *    array $config['blueimp'] assoc array of $options to pass to blueimp's UploadHandler.
    *    bool $config['load_flat'] (optional) if true, will load the flat framework
    *    string $config['flat_loader'] (optional) defaults to __DIR__."flat.php", specifies php file 
    *       that loads flat framework.
    */
   public static function start_service(array $config=NULL) {
      if (!$config) {
         $config = [];
      }
      
      if (!empty($config['load_flat'])) {
         $flat_loader = __DIR__."/flat.php";
         if (!empty($config['flat_loader'])) $flat_loader = $config['flat_loader'];
         /*
          * load flat framework...
          *    enables xml-based error handler
          */
         require_once($flat_loader);
      }
      if (empty($config['blueimp'])) {
         $config['blueimp']= [
            /*
             * 'upload_dir'
             */
            'upload_dir' => \flat\core\config::get("deploy/upload/basedir")."/",
            /*
             * make sure each session gets it's own sub-directory within file upload path
             */
            'user_dirs' => true,
            /*
             * disable blueimp's garbage-ass thumbnail generation
             */
            'image_versions' => [],
         ];
      }
      if (!empty($config['load_flat'])) {
         /*
          * start save_only error_handling for now...
          */
         $errcol = \flat\deploy\error_handler::add_handler("save_only");
      }
      
      $upload = new static($config);
      
      $data = $upload->get_data();
      
      if (!empty($config['load_flat'])) {
         if ($errcol->get_has_errors()) {
            $data->errdata = $errcol->get_errdata();
         }
      }
      
      echo json_encode($data);
   }
}

/**
 * loads flat framework and creates new class instance if this file same as $_SERVER['SCRIPT_FILENAME']
 *
 * @see $_SERVER['SCRIPT_FILENAME']
 */
if (! empty($_SERVER) && ! empty($_SERVER['SCRIPT_FILENAME']) && ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )) {
   upload::start_service(['load_flat'=>true]);
}









