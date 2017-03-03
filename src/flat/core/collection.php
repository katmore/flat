<?php
/**
 * \flat\core\collection class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core;

use \flat\core\collection\controller as collection_controller;

/**
 * collection of objects with create and read operations. also applies
 *    \Iterator interface so objects can be traversed natively; ie: with 
 *    foreach() et. al. 
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @see \flat\core\collection\member\constrainer\class_constraint
 * @see \flat\core\collection\member\constrainer\interface_constraint
 * @see \flat\core\collection\member\validator
 * 
 * @example /flat/deploy/test/demo/flat/core/collection.php
 */
class collection extends \flat\core implements \Iterator {
   use collection_controller;
   
   /**
    * Initializes and or populates collection.
    */
   public function __construct() {}   
   
   /**
    * set the members of collection by deep copying given $member array,
    *    erasing any existing members in the collection.
    *
    * @final
    * @see \flat\core\util\deep_copy::arr()
    *
    * @param array $member set members of collection
    * @param array $index (optional) parrallel array of assoc keys for given
    *    $member array
    */
   final public function set_member(array $member, array $index=NULL) {
      if ($index) if (count($index)!=count($member)) return;
       
      reset($this->member);
      $this->member = \flat\core\util\deepcopy::arr($member);
      if ($index) {
         $this->index = \flat\core\util\deepcopy::arr($index);
      } else {
         $this->index = NULL;
      }
       
      $this->count = count($member);
   }
   
   
   /**
    * get a deep copy of the collection
    *
    * @param string $copy_class (optional) class name to create instance of
    *    and to copy members of collection into. if not given, creates and
    *    deep copies into a \flat\core\collection object.
    *
    * @return \flat\core\collection
    *
    * @see \flat\core\util\deep_copy::arr()
    * @see \flat\core\collection::load_copy()
    * @final
    */
   final public function get_copy($copy_class=null) {
      return self::load_copy($this,$copy_class);
   }
   /**
    * load a deep copy of given collection
    * @param \flat\core\collection $col collection to copy
    * @param string $copy_class class name to create instance of
    *    and to copy members of collection into. if not given, creates and
    *    deep copies into a \flat\core\collection object.
    * @return \flat\core\collection
    *
    * @see \flat\core\util\deep_copy::arr()
    * @final
    */
   final public static function load_copy(\flat\core\collection $source,$dest=null) {
      $className = "";
      if (((is_string($dest) && class_exists($dest) && is_a($dest,'\flat\core\collection',true)) || (is_object($dest) && ($dest instanceof \flat\core\collection))) && (new \ReflectionClass($dest))->isInstantiable()) {
         $className = $dest;
      }
       
      if (empty($className)) {
         $className = get_class($source);
      }
   
      $colCopy = new $className();
      $colCopy->set_member($this->member,$this->index);
      return $colCopy;
   }   
}

   
   
   
   
   
   
   
   
   
   
   