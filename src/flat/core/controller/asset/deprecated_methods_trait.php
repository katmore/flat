<?php
namespace flat\core\controller\asset;

trait deprecated_methods_trait {
   /**
    * @return void
    * @static
    * @deprecated
    */
   public static function data_to_refs_on() {
   }

   /**
    * @return void
    * @static
    * @deprecated
    */
   public static function data_to_refs_off() {
   }

   /**
    * @return void
    * @static
    * @deprecated
    */
   protected static function _asset_param_to_flag($flag, $param = NULL) {
      if (!is_scalar($flag))
         return NULL;
      if (is_array($param) && !empty($param['flag']) && is_array($param['flag'])) {
         if (in_array($flag,$param['flag']) || array_key_exists($flag,$param['flag'])) {
            return true;
         }
      }
      return false;
   }
   
   /**
    * @return void
    * @static
    * @deprecated
    */
   protected static function _asset_param_to_option($option, $param, $default_val = NULL) {
      if (!is_scalar($option))
         return $default_val;
      if (is_array($param) && !empty($param['option']) && is_array($param['option'])) {
         if (isset($param['option'][$option])) {
            return $param['option'][$option];
         }
      }
      return $default_val;
   }
}