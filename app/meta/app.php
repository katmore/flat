<?php
namespace flat\app\meta;

use \flat\app\event\meta\app as event;


class app extends \flat\data
   implements 
      \flat\data\ready,
      \flat\core\routable
{
   const unknown_version = 'unknown';
   public $name='Flat Core';
   public $major_version='0';
   public $minor_version='1';
   public $rev_version='0';
   public $api='201503';
   public $tmpl='201503';
   public $core='201503';
   public $description='A Flat Application';
   public $copyright = "(c) 2012-%Y% All Rights Reserved.";
   public $baseurl = "https://dev.apl.actvp.ch";
   
   public function data_ready() {
      $app = $this;
      if (!empty($this->copyright)) {
         $this->copyright = str_replace("%Y%",date("Y"),$this->copyright);
      }
      foreach (array('api','tmpl') as $prop) {
         if (empty($this->$prop)) {
            
            if ($source = \flat\core\config::get('app/meta/app/'.$prop.'_source',array('default'=>false))) {
               
               if ($source=='svn_info' && function_exists("svn_info")) {
                  $cache = \flat\core\config::get('app/meta/app/'.$prop.'_cache');
                  if ($cache) {
                     if (file_exists($cache) && is_readable($cache) && is_file($cache)) {
                        $this->$prop = file_get_contents($cache);
                        echo "<pre>cached</pre>";
                     }
                  }
                  $path = \flat\core\config::get('app/meta/app/'.$prop.'_path',array('default'=>false));
                  //$info = svn_info($path);
                  $info = array();
                  foreach ($info as $data) {
                     $this->$prop = $data['revision'];
                     if ($cache) {
                        echo "<pre>should cache $cache</pre>";
                        if (is_writable($cache)) {
                           echo "<pre>written $cache</pre>";
                           file_put_contents($cache, $this->$prop);
                        }
                     }
                     break 1;
                  }
               }
            }
         }
         if (empty($this->$prop)) {
            $this->$prop = self::unknown_version;
         }
      }
      event::set_handler(function() use(& $app) {
         return $app;
      });
   }
}















