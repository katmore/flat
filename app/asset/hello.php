<?php
namespace flat\app\asset;
class hello extends \flat\asset implements \flat\asset\base  {
   const lib_base="/flat/asset/lib/hello";
   public function _get_base() {
      return static::lib_base;
   }
}