<?php
namespace flat\core;
trait mapper  {
   

   /**
    * iterates through class properties (as given) invoking a callback function on each one.
    */
   protected function each_property(callable $callback,array $property_list=null,array $options=null) {
      $option = array(
         'get_private_property'=>false,
         'get_protected_property'=>false,
         'ignore_cannot_get'=>false,
         'ignore_cannot_set'=>false,
         'set_private_property'=>false,
         'set_protected_property'=>false,
      );
      

      $r = new \ReflectionClass(get_called_class());
      if (!$property_list) {
         
         $filter = \ReflectionProperty::IS_PUBLIC;
         
         if (!empty($option['get_protected_property']))
            $filter = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED;;
         
         if (!empty($option['get_private_property'])) $filter = NULL;
         
         $prop = $r->getProperties($filter);
      } else {
         $prop=array();
         foreach ($property_list as $p) {
            try {
               $rp = $r->getProperty($p);
               if ($rp->isPublic() || $rp->isProtected()) {
                  $prop[] = $rp;
               } else {
                  if (!empty($option['get_private_property'])) $prop[] = $rp;
               }
            } catch (\ReflectionException $e) {
               if (empty($option['ignore_cannot_get'])) {
                  throw new mappable\exception\cannot_get_property(
                     $property,
                     "does not exist"
                  );
               }
               //ignore properties that dont exist
            }
         }
      }
      
      foreach ($prop as $rp) {
         $ret = $callback($rp->getValue($this),$rp->getName());
         if ($ret!==null) {
            if ($rp->isPrivate()) {
               if (!empty($option['set_private_property'])) {
                  $rp->setAccessible(true);
               } else {
                  throw new mappable\exception\cannot_set_property(
                     $property,
                     'private property'
                  );
               }
            } else
            if ($rp->isProtected()) {
               if (empty($option['set_protected_property'])) {
                  throw new mappable\exception\cannot_set_property(
                     $property,
                     'protected property'
                  );
               }
            }
            if ($ret instanceof \flat\core\mappable\property\nulled) $ret = NULL;
            $rp->setValue($this,$ret);
         }
      }
   }
   private function _get_ignored_value($value_if_ignored) {
      if (is_object($value_if_ignored)) return $value_if_ignored;
      if (class_exists($value_if_ignored)) return new $value_if_ignored;
      return $value_if_ignored;
   }

   protected function get_property_value($property,array $options=null) {
      $option = array(
         'get_private_property'=>false,
         'ignore_cannot_get'=>false,
         'value_if_ignored'=>"\\flat\\core\\mappable\\ignored"
      );
      if (is_array($options)) {
         foreach($options as $key=>$val) if (isset($option[$key])) $option[$key] = $val;
      }
            
      if ($this instanceof mappable\property\ignore_cannot_get) {
         $option['ignore_cannot_get'] = true;
      }            
            
      if ($property instanceof \ReflectionProperty) {
         $rp = $property;
      } else {
         if (!is_string($property)) throw new mappable\exception\bad_property_name();
         if (property_exists($this, $property)) {
            $rp = new \ReflectionProperty($this,$property);
         } else {
            if ($option['ignore_cannot_get']===true) return $this->_get_ignored_value($option['value_if_ignored']);
            throw new mappable\exception\cannot_get_property(
               $property,
               "does not exist"
            );
         }
      }
      if ($rp->isPrivate()) {
         if ($option['get_private_property']===true) {
            $rp->setAcessible(true);
         } else {
            if ($option['ignore_cannot_get']===true) return $this->_get_ignored_value($option['value_if_ignored']);
            throw new mappable\exception\cannot_get_property(
               $property,
               "is private"
            );
         }
      }
      return $rp->getValue($this);
   }

   protected function set_property_value($property,$value,array $options=null) {
      $option = array(
         'ignore_cannot_set'=>false,
         'set_private_property'=>false,
         'add_if_not_exists'=>false,
         'ignore_non_existing_set'=>false
      );
      if (empty($property)) throw new mappable\exception\cannot_set_property(
         $property,
         'empty property given'
      );
      if (is_array($options)) {
         foreach($options as $key=>$val) if (isset($option[$key])) $option[$key] = $val;
      }
      
      if ($this instanceof mappable\property\ignore_cannot_set) {
         $option['ignore_cannot_set'] = true;
      }
      if ($this instanceof mappable\property\ignore_non_existing_set) {
         $option['ignore_non_existing_set'] = true;
      }
      if ($this instanceof mappable\property\add_if_not_exists) {
         $option['add_if_not_exists'] = true;
      }

      if ($property instanceof \ReflectionProperty) {
         $rp = $property;
      } else {
         if (!is_string($property = (string) $property)) throw new mappable\exception\bad_property_name();
         if (property_exists(get_class($this), $property)) {
            $rp = new \ReflectionProperty($this,$property);
         } else 
         if (method_exists($this, $property)) {
            if ($option['ignore_cannot_set']===true) return false;
            throw new mappable\exception\cannot_set_property(
               $property,
               "is method"
            );
         } else {
            if (!$option['add_if_not_exists']) {
               if ($option['ignore_non_existing_set']===true) return false;
               if ($option['ignore_cannot_set']===true) return false;
               throw new mappable\exception\cannot_set_property(
                  $property,
                  "does not exist"
               );
            }
         }
      }
      if (isset($rp)) {
         if ($rp->isPrivate()) {
            if ($option['set_private_property']!==true) {
               if ($option['ignore_cannot_set']===true) return false;
               throw new mappable\exception\cannot_set_property(
                  $property,
                  "is private"
               );
            }
         }
         $rp->setAccessible(true);
         
         $rp->setValue($this,$value);
      } else {
         $this->$property = $value;
      }
      return true;
   }

   private function _get_object($object) {
      if (!$object) return $this;
      $object = (object) $object;
      if (!is_object($object)) throw new mappable\exception\invalid_object();
      return $object;
      
   }

   protected function _object_to_assoc($object=null,$get_private_properties=false) {
      $object = $this->_get_object($object);
      $array = array();
      $rc = new \ReflectionClass($object);
      $props = $rc->getProperties();
      foreach($props as $rp) {
         if (!empty($rp->getName())) {
            if ($rp->isPrivate() || $rp->isProtected()) {
               if ($get_private_properties===true) {
                  $rp->setAcessible(true);
                  $array[$rp->getName()]=$rp->getValue($object);
               }
            } else {
               $array[$rp->getName()]=$rp->getValue($object);
            }
         }
      }
      return $array;
   }
   public function get_as_assoc($object=null,$get_private_properties=false) {
      return $this->_object_to_assoc($object,$get_private_properties);
   }

   public function get_as_stdClass($object=null,$get_private_properties=false) {
      $object = $this->_get_object($object);
      $stdClass = new \stdClass();
      $rc = new \ReflectionClass($object);
      $props = $rc->getProperties();
      foreach($props as $rp) {
         $name = $rp->getName();
		 if (!isset($object->$name)) continue;
         if ($rp->isPrivate() || $rp->isProtected()) {
            if ($get_private_properties===true) {
               $rp->setAcessible(true);
               
               $stdClass->$name=$rp->getValue($object);
            }
         } else {
            $stdClass->$name=$rp->getValue($object);
         }
      }
      return $stdClass;
   }

   public function args_to_properties(array $args,array $arg_list,array $options=null) {
      // echo "<pre>mappable:args_to_properties args:
      // ";
      // var_dump($args);
      // echo "</pre>";
      /*
       * if first argument is array, and there's only 1 argument
       *    use that as the arg list
       *
       */

      if (count($args)==1) {
         if (isset($args[0]))
         if (is_array($args[0])) {
            $args = $args[0];
         }
      }
      $i=0;
      
      $argval = array();
      foreach ($args as $val) {
         $argval[$i] = $val;
         $i++;
      }
      $i=0;
      $list = array();
      //foreach ($arg_list as $key=>$val) {
      foreach ($arg_list as $key=>$val) {
         /*
          * if $key is an integer
          */
         if (($key==$i) && is_int($key)) {
            
            $list[$val] = "";
            if (isset($args[$val])) {
               $list[$val] = $args[$val];
            } else {
               if (isset($argval[$i])) {
                  $list[$val] = $argval[$i];
               }
            }
         } else {
            if (isset($args[$key])) {
               $list[$key] = $args[$key];
            } else {
               if (isset($argval[$i])) {
                  $list[$key] = $argval[$i];
               } else {
                  $list[$key] = $val;
               }
            }
         }
         $i++;
      }
      // echo "<pre>mappable:args_to_properties list:
      // ";      
      // var_dump($list);
      // echo "mappable:args_to_properties arg_list:
      // ";      
      // var_dump($arg_list);
      // echo "</pre>";
      $this->list_to_properties($list,$options);

      //\flat\core\debug::dump($list,"list");
   }

   public function object_to_properties( $object,array $options=null) {
 
      foreach ($object as $key=>$value) $this->set_property_value($key,$value,$options);
   }

   public function list_to_properties(array $list,array $options=null) {
      foreach ($list as $key=>$value) $this->set_property_value($key,$value,$options);

   }
   public function params_to_properties(array $params,array $options=null) {
      $build_list = false;
      if (count($params)) {
         foreach ($params as $key=>$val) {
            if (empty($key) || is_numeric($key)) {
               $build_list = true;
               break 1;
            }
         }
      }
      if ($build_list) {
         $i=0;
         $pval = array();
         foreach ($params as $key=>$val) {
            $pval[$i] = $val;
            $i++;
         }
         $params = array();
         $i=0;
         foreach ($this as $key=>$val) {
            if (!isset($pval[$i])) break 1;
            $params[$key] = $pval[$i];
            $i++;
         }
      }
      $this->list_to_properties($params,$options);
   }
}
