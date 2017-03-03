<?php
/**
 * class \flat\core\controller\tmpl 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\core\controller;
/**
 * template controller
 * 
 * @package    flat\tmpl
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class tmpl implements \flat\core\controller ,\flat\core\resolver\prepared {
   /**
    * threshold as expressed in bytes at which tmpl::display() will read 
    *    non-php template file in chunks to avoid memory overruns
    */
   const min_chunking_size=102400;//min size is 10kB
   
   final public static function check_design($design) {
      try {
         static::display($design,NULL,true);
         return true;
      } catch (tmpl\exception\bad_design $e) {
         return false;
      }
   }
   
   /**
    * displays a design template as formatted by given parameters
    * 
    * @return void|string
    * @static
    * @param array $param assoc array of parameters:
    *    mixed $param['data'] (optional) data to pass to template design.
    * 
    *    string $flags[]='no_display' exists: formatted template
    *       will be returned as string, rather than being immediately 
    *       displayed.
    *    string $flags[]='strip_tags' exists:  strips all HTML
    *       tags.
    *    string $flags[]='nl2br' exists: converts all newlines to 
    *       'br' tags.
    *    string $flags[]='htmlescape' exists:  escapes all contents
    *       (even HTML tags) with HTML entities.
    * 
    * @todo string $flags[]='textescape' exists: escapes all contents
    *       except HTML tags with HTML entities.
    * 
    * @todo array[]string[] $param['replace'] array of assoc arrays:
    *    (string) 'search'=> (string) 'replace', search and replace within 
    *    template.
    * 
    * @see \flat\core\controller\tmpl::display() for possible exceptions.
    */
   final public static function format($design,array $flags=NULL,array $params=NULL) {
      $data = null;
      if (is_array($params) && isset($params['data'])) {
         $data = $params['data'];
      }

      
      if ($flags && (false!==($idx = array_search('silent_fail',$flags)))) {
         try {
            $sflags = $flags;
            array_splice($sflags,$idx,1);
            return self::format($design,$sflags,$params);
         } catch (\Exception $e) {
            return "";
         }
      } else {
//          if (get_called_class()=='flat\app\mail\tmpl\activepitch\artist') {
//             throw new \Exception(print_r($data,true));
//          }         
         ob_start();
         static::display($design,$data);
         $ob = ob_get_clean();
      }
//       if (get_called_class()=='flat\app\mail\tmpl\activepitch\artist') {
//          throw new \Exception('asdf');
//       }
      
      /**
       * $var string strip_tags should be first
       * @todo replace 
       */
      if ($flags && in_array('strip_tags',$flags)) $ob = strip_tags($ob);
      
      /*
       * nl2br should be after strip_tags, or else could have no effect
       */
      if ($flags && in_array('nl2br',$flags)) $ob = nl2br($ob);
      
      if ($flags && in_array('htmlescape',$flags)) $ob = htmlentities($ob);
      
      if ($params && isset($params['replace']) && is_array($params['replace'])) {
         foreach ($params['replace'] as $find=>$replace) {
            $ob = str_replace($find,$replace,$ob);
         }
      }
      if ($flags && in_array('no_display',$flags)) return $ob;
      
      echo $ob;
   }
   
   /**
    * displays a design template
    *  
    * @return void
    * @static
    * @param string $design filename or namespace that is resolved into a filename
    * @param mixed $data (optional) data to pass to template design
    * 
    * @uses \flat\core\config::get("design/basedir") template designs are placed in flat\design path
    * @throws \flat\core\controller\tmpl\exception\bad_design when cannot resolve design definition 
    * @todo .md (markdown) templates
    */
   public static function display($design,$data=null,$check_only=false) {
      
      
      /*
       * skip all resolving if design is a display class
       */
      if (is_string($design) && (substr($design,0,1)=="\\")) {
         if (class_exists($design) && is_a($design,'\flat\tmpl\display',true)) {
            $design = new $design($design,$check_only);
         }
      }
      
      if (is_object($design)) {
         if (!$design instanceof \flat\tmpl\output) {
            throw new tmpl\exception\bad_design(
               'design must be a string or object with \flat\tmpl\output interface'
            );
         }
         $output = $design->get_output($data,$check_only);
         if (!is_scalar($output) && !is_null($output)) throw new tmpl\exception\bad_design(
            '\flat\tmpl\output::get_output() return value must be scalar type or null'
         );
         echo $output;
         return;
      }
      
      /*
       * enforce sanity on $design arg
       */
      if (!is_string($design)) throw new tmpl\exception\bad_design(
         'design must be a string or \flat\tmpl\display object'.
         "instead got: ".gettype($design)
      );
      if (empty($design)) throw new tmpl\exception\bad_design(
         "design cannot be empty"
      );
      
      /*
       * transform all acceptable separators to backslash for consistency
       */
      $design = str_replace("/","\\",$design);
      
      /*
       * transform relative to absolute design path
       *    as appropriate
       */      
      if (substr($design,0,1)!="\\") {
         $r = new \ReflectionClass(get_called_class());
         if ($r->isSubclassOf( "\\flat\\tmpl\\design_base" )) {
            $design = static::get_design_base()."\\".$design;
         } else {
            $subns = get_called_class();
            $subns = str_replace("app","design",$subns);
            $design = "\\".$subns."\\".$design;
         }
      }
      

      
      $file = null;
      /*
       * skip remaining resolving logic if design is already full path to a php file
       */
      if (substr($design,0,1)=="/") {
         if (is_file($design) && is_readable($design)) {
            if ( pathinfo($design, PATHINFO_EXTENSION) == "php") {
               $file = $design;
            }
         }
      }
      /*
       * determine if resource resolves to a php filename
       *    if so...load template with a 'require' and return void.
       */
      if (empty($file)) {
         $file_base = str_replace("flat\\design","",$design);
         
         $file_base = \flat\core\config::get("design/basedir")."/".str_replace("\\","/",$file_base);
         $file = $file_base.".php";
      }
      if (is_file($file) && is_readable($file)) {
         if ($check_only) return;
         /*
          * load in closure for clean scope
          */
//          $loader = function($filename,$data) {
//             require($filename);
//          };
         //$loader($file,new \flat\tmpl\data($data));
         $data = new \flat\tmpl\data((array) $data);
         call_user_func(function() use($file,$data) {
            require($file);
         });
         return;
         
      }
      
      /**
       * determine if $design resolves to an existing html file
       *    if so...load template by echo'ing contents, and return void.
       * @todo consider loading into XML parser, and do 'cool things'
       */
      $tried = array($file);
      //$display = self::get_display_handler();
      foreach (array("html","htm") as $ext) {
         $file = $file_base.".$ext";
         if (is_file($file) && is_readable($file)) {
            if ($filesize = filesize($file)) {
               if ($check_only) return;
               if ($filesize>self::min_chunking_size) {
                  if (!$h = fopen($file, "r")) throw new tmpl\exception\system_err(
                     "could not open file '$file' for read"
                  );
                  while (!feof($h)) {
                      if (false === ($chunk = fread($h, self::min_chunking_size))) {
                        throw new tmpl\exception\system_err(
                           "could not read from file '$file'"
                        );
                      }
                      echo( $chunk );
                  }
                  if (!fclose($h)) {
                     throw new tmpl\exception\system_err(
                        "failed to close file '$file'"
                     );
                  }
               } else {
                  if (false === ($str = file_get_contents($file))) {
                     throw new tmpl\exception\system_err(
                        "could not read string from file '$file'"
                     );
                  }
                  echo( $str );
               }
               return;
            }
         }
         $tried[] = $file;
      }
      
      /*
       * determine if $design resolves to an existing txt or md files... 
       *    if so...load template by giving file to MD to HTML converter,
       *       echo converted HTML contents, and return void.
       */
      foreach (array("txt","md") as $ext) {
         $file = $file_base.".$ext";
         if (is_file($file) && is_readable($file)) {
            try {
               $md= new \flat\core\md\convert\file($file);
               if ($check_only) return;
               echo $md->get_html();
               return;
            } catch (\Exception $e) {
               //whatevs
            }
            return;
         }
         $tried[] = $file;
      }
      /**
       * determine if it's a php-md
       */
      foreach (array("md.php","txt.php") as $ext) {
         
         $file = $file_base.".$ext";
         if (is_file($file) && is_readable($file)) {
            if ( pathinfo($file, PATHINFO_EXTENSION) == "php") {
            
               try {
                  
                  /*
                   * load in closure for clean scope
                   */
                  $loader = function($filename,$data) {
                     require($filename);
                  };
                  ob_start();
                  $loader($file,$data);
                  if ($check_only) {
                     ob_get_clean();
                     return;
                  }
                  $text = ob_get_clean();
                  echo \flat\core\md\convert::string_to_html( $text );
                  return;
               } catch (\Exception $e) {
                  //whatevs
               }
               return;
            }
         }
         $tried[] = $file;
      }
      throw new tmpl\exception\bad_design(
         "'$design' did not resolve to regular non-zero length file. tried: '".implode("', '",$tried)."'"
      );

   }

   /**
    * base for design namespace
    */
   const root_design_base="flat\design\\tmpl";
   
   /**
    * base for template definitions
    */
   const root_app_base="flat\\app\\tmpl";
   
   /**
    * resolves inherited full name of class 
    *    into a design class name
    * 
    * @return string
    * 
    */
   private function _get_design_from_root() {

      // $r = new \ReflectionClass($this);
      // $app_base = $this::root_app_base."\\".$r->getShortName();
      // $design_base = $this::root_design_base."\\".$r->getShortName();
      
      //\flat\core\debug::dump(get_called_class(),"called_class");
      $ns_suffix = str_replace($this::root_app_base,"",get_called_class());
      
      
      //\flat\core\debug::dump($ns_suffix,"ns_suffix");
      
      
      $design_class = "\\".$this::root_design_base."\\".$ns_suffix;
      $design_class = str_replace("\\\\","\\",$design_class);
      //\flat\core\debug::dump($design_class,"design_class");
      //echo $this::root_design_base;
   
      return $design_class;
      
   }
   /**
    * part of the \flat\core\resolver\prepared interface
    * 
    * @see \flat\core\resolver\prepared
    */  
   final public function set_prepared_on() {
      
      if ($this instanceof \flat\tmpl\data_provider){
         //echo 'tmpl ASDFHGxxxx';
         if ($this instanceof \flat\tmpl\ignore) return;
         //echo 'tmpl ASDFHGxxxx';
         //echo "tmpl: ".get_called_class()."\n";
         if ($this instanceof \flat\tmpl\ignorable) {
            if ($this->is_ignored()) return;
         }
         return $this->_resolve_display(
            $this->get_data()
         );
      }
      if ($this instanceof \flat\tmpl\ignorable) {
         if ($this->is_ignored()) return;
         return $this->_resolve_display();
      }
   }

   private static $_display_h=NULL;
   
   /**
    * defaults to
    *    function($design,$data) {
    *       return static::display($design,$data);
    *    };
    * @param callable $handler signature:
    *    callback($design,$data)
    */
   final public static function set_display_handler(callable $handler) {
      //var_dump( $handler );
      self::$_display_h = $handler;
   }
   final public static function get_display_handler() {
      if (!self::$_display_h) return function($design,$data) {
         return self::display($design,$data);
      };
      return self::$_display_h;
   }
   
   private function _resolve_display($data=NULL) {
      $display = self::get_display_handler();
      if ($this instanceof \flat\tmpl\design) {
         $design = $this->get_design();
      } else {
         $design = $this->_get_design_from_root();
      }
      if ($data===NULL) $data = new \stdClass();
      $tmpl = $this;
      $display($design,$data,$tmpl);
   }
   /**
    * @param mixed $data data to pass to template design
    * 
    * @uses \flat\tmpl\design retrieves the template design explicitly
    *    if inherited class implements this interface
    * 
    * @throws \flat\core\controller\tmpl\exception\bad_design when cannot resolve design
    */
   final public function __construct() {
      if (($this instanceof \flat\tmpl\data_provider) || ($this instanceof \flat\tmpl\ignorable) ){
         // \flat\core\debug::msg("wait until set_prepared_on() called");
         // var_dump("waiting...");
      } else {
         
         if ($this instanceof \flat\tmpl\ignore) return;

         return $this->_resolve_display();
      }
   }
}










