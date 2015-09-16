<?php
/**
 * File:
 *    cafile.php
 * 
 * Purpose:
 *    public cert for mongo connection
 * 
 * @package flat/db/mongo
 * @link http://php.net/manual/en/mongo.connecting.ssl.php
 */
namespace flat\db\mongo\ssl;
abstract class cafile {
   public $ca; //certificate value (base64)
   protected $file;
   public function get_file(array $param=NULL) {
      if (empty($this->ca)) return NULL;
      if (!empty($param['path'])) {
         $path=$param['path']; 
         if ($path==$this->file) return $this->file;
      } else {
         if (!empty($this->file)) return $this->file;
         $path = tempnam(sys_get_temp_dir(), "cafile");
         register_shutdown_function(function() use($path) {
            unlink($path);
         });
      }
      if (file_put_contents($path, $this->ca)) {
         $this->file = $path;
         return $this->file;
      }
   }
}