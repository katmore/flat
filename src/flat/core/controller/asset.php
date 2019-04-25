<?php
/*
 * \flat\core\controller\asset definition 
 *
 * PHP version >=7.2
 * 
 * This file is part of the "The Flat Framework".
 * 
 * More information regarding "The Flat Framework" is available by viewing the 
 * url "https://github.com/katmore/flat" in any web browser.
 * 
 * The following copyright notice and license notice shall apply to this file 
 * and to any other part of The Flat Framework.
 * 
 * COPYRIGHT NOTICE:
 * Copyright (c) 2012-2019 Doug Bird. All Rights Reserved.
 * 
 * LICENSE NOTICE:
 * The Flat Framework is copyrighted free software.
 * 
 * You may use, modify, and distribute it under the terms and conditions of any
 * one of the following licenses: the "MIT License" (MIT); the "GNU General 
 * Public License v3.0 or later" (GPL-3.0-or-later); or the "GNU Lesser General 
 * Public License v3.0 or later" (LGPL-3.0-or-later).
 * 
 * A full copy of the MIT license is available by viewing the url
 * "https://raw.githubusercontent.com/katmore/flat/master/LICENSE" in any web 
 * browser.
 * 
 * A full copy of the GNU General Public License v3.0 is available by viewing 
 * the url "https://www.gnu.org/licenses/gpl-3.0-standalone.html" in any web 
 * browser.
 * 
 * A full copy of the GNU Lesser General Public License v3.0 is available by
 * viewing the url "https://www.gnu.org/licenses/lgpl-3.0-standalone.html" 
 * in any web browser.
 */
namespace flat\core\controller;

use ReflectionClass;

/**
 * provides routable and cascading resource locations
 * 
 * @abstract
 * 
 * @license MIT
 * @license GPL-3.0-or-later
 * @license LGPL-3.0-or-later
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2019 Doug Bird. All Rights Reserved.
 */
abstract class asset implements \flat\core\controller {
   
   use asset\deprecated_methods_trait;
   
   /**
    *
    * @var string $resource resource specified to be resolved into asset
    */
   private $resource;
   
   /**
    * @var callable
    * @see asset::set_resolved_handler()
    */
   private static $resolved_handler;
   
   /**
    * provides the resource uri path
    * 
    * @return string resource uri path
    */
   public function get_resource_uri(): string {
      return (string) $this->resource;
   }
   
   /**
    * Invoked for each param argument item passed to the "resolve" method.
    * The return value is used as the class name of a replacement asset to instantiate.
    * 
    * @see \flat\core\controller\asset::resolve()
    * 
    * @return string 
    */
   protected function on_resolve_param(string $asset, string $key, $value): string {
      return $asset;
   }
   
   /**
    * Invoked when the "resolve" method has resolved the asset.
    * The return value will become the asset this is finally returned 
    * by the "resolve" method.
    *
    * @see \flat\core\controller\asset::resolve()
    *
    * @return \flat\core\controller\asset
    */
   protected function on_resolve_ready(asset $resolved_asset) : asset {
      return $resolved_asset;
   }
   
   final public function is_equal(asset $asset) : bool {
      return get_class($asset)===static::class && $asset->get_resource_uri() === $this->get_resource_uri();
   }
   
   /**
    * Resolves an asset
    * 
    * @return \flat\core\controller\asset
    */
   final public function resolve(array $param = null): asset {
      $param === null && $param = [];

      $orig_asset = $asset = "\\" . static::class;

      foreach ( $param as $key => $value ) {
         if ("" !== ($param_asset = trim($this->on_resolve_param($asset,$key,$value),"\\"))) {
            $param_asset = "\\$param_asset";
            if ($param_asset !== $asset) {
               if ((new ReflectionClass($param_asset))->isInstantiable()) {
                  $asset = $param_asset;
               }
            }
         }
      }
      unset($key);
      unset($value);

      if ($orig_asset === $asset) {
         $resolved_asset = $this;
      } else {
         $resolved_asset = new $asset($this->resource,$param);
      }
      
      $resolved_asset = $this->on_resolve_ready($resolved_asset);

      return $resolved_asset;
   }

   /**
    * Returns information about the resource path.
    * 
    * @param int $options [optional]
    * <br><br>
    * If present, specifies a specific element to be returned; one of
    * PATHINFO_DIRNAME, PATHINFO_BASENAME, PATHINFO_EXTENSION or PATHINFO_FILENAME. 
    * <br><br>
    * If options is not specified, returns all available elements. 
    * 
    * @return string|string[] If the options parameter is not passed, an associative array containing the following elements is returned: 
    * dirname, basename, extension (if any), and filename. 
    * <br><br>
    * If the path has more than one extension, PATHINFO_EXTENSION returns only the last one and PATHINFO_FILENAME only strips the last one. 
    * (see first example below). 
    * <br><br>
    * If the path does not have an extension, no extension element will be returned(see second example below). 
    * <br><br>
    * If the basename of the path starts with a dot, the following characters are interpreted asextension, and the filename is empty 
    * (see third example below). 
    * <br><br>
    * If options is present, returns a string containing the requested element. 
    * 
    * @see http://www.php.net/manual/en/function.pathinfo.php 
    */
   public function get_pathinfo(int $options = null): string {
      return pathinfo($this->resource,$options);
   }

   

   /**
    * sets a handler invoked for each resolved asset.
    * 
    * @return void
    * @param callable $handler
    *    callback signature: function(\flat\core\controller\asset $asset, $resource)
    * 
    */
   public static function set_resolved_handler(callable $handler) : void {
      self::$resolved_handler = $handler;
   }
   
   /**
    * Canonicalizes and sets a new resource.
    * 
    * @return void
    */
   protected function set_resource(string $resource) : void {
      $resource = str_replace("\\","/",$resource);
      if (substr($resource,0,1) === "/") {
         $resource = substr($resource,1);
      }
      $this->resource = $resource;
   }
   
   /**
    * Provides the asset "resource".
    * 
    * @return string asset resource
    */
   protected function get_resource(): string {
      return $this->resource;
   }
   
   /**
    * Invoked after the asset "resource" has been canonicalized for the first time.
    * 
    * @see \flat\core\controller\asset::__construct()
    * 
    * @return void
    */
   protected function on_resource_ready(array $param=null) : void {
      if (self::$resolved_handler) {
         $handler = self::$resolved_handler;
         if (is_string($resource = $handler($this,$this->resource))) {
            $this->set_resource($resource);
         }
      }
   }

   /**
    * @param string $resource (optional) the asset "resource" value
    */
   public function __construct(string $resource = "",array $param=null) {
      
      $this->set_resource($resource);
      
      $this->on_resource_ready($param);
   }
}







