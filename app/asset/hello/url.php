<?php
namespace flat\app\asset\hello;
class url extends \flat\asset 
implements 
\flat\asset\base,
\flat\asset\resource\transform  
{
   const home_resource = "home";
   private static function _get_url_base() {
      return \flat\core\config::get("app/asset/hello/url/base");
   }
   /**
    * the return value (as long as it is string) will be set as the asset's resource
    * 
    * @return string|NULL
    * @see \flat\asset\resource\transform part of the resource transform interface
    * @uses \flat\core\controller\asset::__construct()
    */
   public function get_resource_transform($resource) {
      if ($resource==self::home_resource) return "";
   }
   public function _get_base() {
      return static::_get_url_base();
   }
}