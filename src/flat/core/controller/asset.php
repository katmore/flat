<?php
/**
 * \flat\core\controller\asset class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (C) 2012-2015  Doug Bird.
 * ALL RIGHTS RESERVED. THIS COPYRIGHT APPLIES TO THE ENTIRE CONTENTS OF THE WORKS HEREIN
 * UNLESS A DIFFERENT COPYRIGHT NOTICE IS EXPLICITLY PROVIDED WITH AN EXPLANATION OF WHERE
 * THAT DIFFERENT COPYRIGHT APPLIES. WHERE SUCH A DIFFERENT COPYRIGHT NOTICE IS PROVIDED
 * IT SHALL APPLY EXCLUSIVELY TO THE MATERIAL AS DETAILED WITHIN THE NOTICE.
 * 
 * The flat framework is copyrighted free software.
 * You can redistribute it and/or modify it under either the terms and conditions of the
 * "The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
 * of the "GPL v3 License" (see the file GPL-LICENSE.txt).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @license The MIT License (MIT) http://opensource.org/licenses/MIT
 * @license GNU General Public License, version 3 (GPL-3.0) http://opensource.org/licenses/GPL-3.0
 * @link https://github.com/katmore/flat
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat\core\controller;

/**
 * provides routable and cascading resource locations
 *
 * @package flat\asset
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version 0.1.0-alpha
 */
class asset implements 
   \flat\core\controller {

   protected static $_data_to_refs=true;
   public static function data_to_refs_on() {
      self::$_data_to_refs = true;
   }
   public static function data_to_refs_off() {
      self::$_data_to_refs = false;
   }
   
   public function print_data_uri($mtype,$always=false) {
      if (static::$_data_to_refs && !$always) {
         echo $this->get_url();
         return;
      }
      echo "data:$mtype;base64,";
      echo base64_encode(file_get_contents($this->get_system()));
   }
   /**
    * prints a style tag with contents of resource's system path (file).
    * 
    * @return void
    * 
    * @throws \flat\lib\exception\app_error if resource does not resolve on system to a .css file
    * @uses \flat\asset\system_base
    */
   public function print_style($always=false) {
      if (static::$_data_to_refs && !$always) {
         $this->style_link();
         return;
      }
      if (substr($this->get_system(),-4)!=".css") {
         throw new \flat\lib\exception\app_error("resource ".$this->get_system()."is not a css file");
      }
      ?>
      <!--START flat/app/asset/vendor: <?=$this->get_url();?>-->
      <style>
      <?=file_get_contents($this->get_system());?>
      
      </style>
      <!--END flat/app/asset/vendor: <?=$this->get_url();?>-->
      <?php
    }
      /**
       * prints a script tag with contents of resource's system path (file).
       *
       * @return void
       *
       * @throws \flat\lib\exception\app_error if resource does not resolve on system to a .js file
       * @uses \flat\asset\system_base
       */
      public function print_script($always=false) {
         if (static::$_data_to_refs && !$always) {
            $this->script_tag();
            return;
         }
         if (substr($this->get_system(),-3)!=".js") {
            throw new \flat\lib\exception\app_error("resource ".$this->get_system()."is not a javascript file");
         }
   ?>
   <!--START flat/app/asset/vendor: <?=$this->get_url();?>-->
   <script>
   <?=file_get_contents($this->get_system());?>
   
   </script>
   <!--END flat/app/asset/vendor: <?=$this->get_url();?>-->
   <?php
      }
      
      /**
       * prints a script tag with src attribute to resource's url.
       *
       * @return void
       *
       * @throws \flat\lib\exception\app_error if resource does not resolve on system to a .js file
       * @uses \flat\asset\system_base
       */      
      public function script_tag() {
         if (substr($this->get_url(),-3)!=".js") {
            throw new \flat\lib\exception\app_error("resource ".$this->get_url()."is not a javascript file");
         }
         ?><!--vendor::script_tag()--><script src="<?=$this->get_url();?>"></script><?php
      }   
      
      public function style_link() {
         /*
          * <link href="<?=lib::asset('styles.css')?>" rel="stylesheet">
          */
         if (substr($this->get_url(),-4)!=".css") {
            throw new \flat\lib\exception\app_error("resource ".$this->get_url()."is not a javascript file");
         }
         ?><!--vendor::style_link()--><link href="<?=$this->get_url();?>" rel="stylesheet"><?php
      }
   
   /**
    * determines if flag exists in given param in the following manner: returns
    * bool true if $param['flag'] exists and is an array and $flag exists as
    * value in $param['flag'].
    * returns bool false if $flag does not exist as element value in
    * $param['flag'], or $param['flag'] element does not exist or $param['flag']
    * is not array.
    * returns NULL if $flag is not scalar value.
    *
    * @return bool|NULL
    *
    * @param scalar $flag
    *           value of flag to check. typically a string or integer derived
    *           from hash table.
    * @param mixed $param
    *           parameter as given to self::asset()
    */
   protected static function _asset_param_to_flag($flag, $param = NULL) {

      if (! is_scalar($flag))
         return NULL;
      if (is_array($param) && ! empty($param['flag']) && is_array(
         $param['flag'])) {
         if (in_array($flag, $param['flag']) ||
             array_key_exists($flag, $param['flag'])) {
            return true;
         }
      }
      return false;
   
   }

   protected static function _asset_param_to_option(
      $option, $param, $default_val = NULL) {

      if (! is_scalar($option))
         return $default_val;
      if (is_array($param) && ! empty($param['option']) &&
          is_array($param['option'])) {
         if (isset($param['option'][$option])) {
            return $param['option'][$option];
         }
      }
      return $default_val;
   
   }

   /**
    * alias of asset::load()
    *
    * @uses asset::load()
    */
   public static function asset($resource = "", $param = NULL) {

      return self::load($resource, $param);
   
   }

   /**
    * loads asset object.
    *
    * @return string
    *
    * @param string $resource
    *           (optional) specify resource to be resolved into asset
    * @uses \flat\core\controller\asset::__construct()
    * @throws \flat\core\controller\asset\exception\bad_resource
    * @throws \flat\core\controller\asset\exception\not_resolvable
    */
   public static function load($resource = "", $param = NULL) {

      /*
       * concat full asset namespace
       */
      $asset = "\\" . get_called_class();
      
      /*
       * sanity enforcement for $resource param
       *    (must be a string) 
       */
      if (! is_string($resource))
         throw new asset\exception\bad_resource($asset);
         /*
       * canonicalize resource string
       */
      if (! empty($resource)) {
         $resource = str_replace("\\", "/", $resource);
         if (substr($resource, 0, 1) == "/")
            $resource = substr($resource, 1);
      }
      if (static::_asset_param_to_flag('cascade', $param)) {
         
         $base = "\\" . get_called_class();
         // var_dump($param);var_dump($resource);die('asset controller:
         // '."base=$base");
         if (static::_asset_param_to_option('cascade', $param)) {
            $base = str_replace("/", "\\", 
               static::_asset_param_to_option('cascade', $param));
         }
         // var_dump($param);die('asset controller: '."base=$base");
         if (substr($base, 0, 1) == "\\" && class_exists($base) &&
             is_a($base, "\\flat\\core\\controller\\asset", true)) {
            $asset = $base;
         } else {
            if (class_exists("$asset\\$base") &&
                is_a("$asset\\$base", "\\flat\\core\\controller\\asset", true)) {
               $r = new \ReflectionClass("$asset\\$base");
               if ($r->isInstantiable())
                  $asset = "$asset\\$base";
            }
         }
         /*
          * convert path to namespace to check if it's relative class reference
          */
         $ns = str_replace("/", "\\", $resource); // convert slashes
         $base = pathinfo($resource, PATHINFO_BASENAME);
         $ns = preg_replace('/^([^\.]*).*$/', '$1', $ns); // remove file
                                                         // extension if has one
         $ns = "$asset\\$ns";
         
         /*
          * check relative resource without file extension is an asset
          */
         $ns_instance = false;
         if (class_exists($ns) &&
             is_a($ns, "\\flat\\core\\controller\\asset", true)) {
            $r = new \ReflectionClass($ns);
            if ($r->isInstantiable()) {
               $ns_instance = true;
               $asset = $ns;
               $ns = explode("\\", $ns);
               $resource = $base;
            }
         }
         /*
          * if relative resource wasnt asset yet...
          *    remove one level at a time from resource and check it
          */
         if (! $ns_instance) {
            $reslevel = array(
               $base
            );
            $ns = str_replace("/", "\\", $resource); // convert slashes
            $base = pathinfo($resource, PATHINFO_BASENAME);
            $ns = preg_replace('/^([^\.]*).*$/', '$1', $ns); // remove file
                                                            // extension if has
                                                            // one
            $ns = "$asset\\$ns";
            $nslevel = explode("\\", $ns);
            if (count($nslevel) > 1) {
               $level_count = count($nslevel);
               for($i = 0; $i < $level_count; $i ++) {
                  $ns_resource = array_pop($nslevel);
                  if ($i != 0)
                     $reslevel[] = $ns_resource;
                  $ns = implode("\\", $nslevel);
                  if (class_exists($ns) &&
                      is_a($ns, "\\flat\\core\\controller\\asset", true)) {
                     $r = new \ReflectionClass($ns);
                     if ($r->isInstantiable()) {
                        $ns_instance = true;
                        $asset = $ns;
                        
                        // $resource = $ns_resource;
                        $resource = implode("\\", array_reverse($reslevel));
                        // var_dump($asset);echo('asset controller echo'."\n");
                        break 1;
                     }
                  }
               }
            }
         }
      }
      
      $r = new \ReflectionClass($asset);
      if (! $r->isInstantiable())
         throw new asset\exception\not_resolvable(get_called_class(), $asset);
      $asset = new $asset($resource);
      // return $asset->get_url();
      return $asset;
   
   }

   /**
    * retrieves asset's URL when object invoked as string
    *
    * @uses \flat\core\controller\asset::get_url()
    * @return string
    */
   public function __toString() {
      try {
         return $this->get_url();
      } catch (\Exception $e) {
         trigger_error("magic method asset::__toString() failed because exception ".get_class($e)." code: ".$e->getCode(). " message: \"".$e->getMessage()."\" thrown by ".get_called_class()."::get_url()",E_USER_ERROR);
         return "";
      }
   }

   /**
    * provides an asset's system path.
    * 
    * @return string
    */
   public function get_system() {
      if (!$this instanceof \flat\asset\system_base) {
         throw new asset\exception\missing_system_base(get_called_class());
      }
      $path = $this->_get_system_base();
      if (empty($this->resource))
         return $path;
      return "$path/" . $this->resource;
   }

   /**
    * retrieves asset's URL
    *
    * @return string
    */
   public function get_url() {

      $url = "";
      if ($this instanceof \flat\asset\base) {
         $url .= $this->_get_base();
      }
      if (empty($this->resource))
         return $url;
      return "$url/" . $this->resource;
   
   }

   /**
    *
    * @var string $resource resource specified to be resolved into asset
    */
   private $resource;

   /**
    * re-resolves asset and retrieves URL when invoked as method
    * 
    * @see get_url()
    * @return string
    */
   public function __invoke($resource = "") {

      $this->__construct($resource);
      return $this->get_url();
   
   }

   /**
    *
    * @param string $resource
    *           (optional) specify resource to be resolved into asset
    */
   public function __construct($resource = "") {

      $this->resource = $resource;
      if ($this instanceof \flat\asset\resource\transform) {
         $transform = $this->get_resource_transform($resource);
         // var_dump($resource);
         if (is_string($transform) && ( $transform !== NULL )) {
            $this->resource = $transform;
         }
      }
   
   }

}







