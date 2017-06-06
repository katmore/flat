<?php
namespace flat\data;

use \flat\data;
use \flat\core\xml\encode as xmlencode;
use \flat\core\html\encode as htmlencode;
use \flat\core\util\validate;

trait mapper {
   use \flat\core\mapper;
   /**
    * magic method __call
    *    Assumes intent to return value of a data field, optionally
    *    apply some operation to affect field value as returned (without
    *    affecting actual value in object) per supplied $arg's.
    */
   public function __call($field,array $arg) {
      
      if ($this->is_field_exist($field)) {
         $param = array();
         $on_empty=NULL;
         $value = $this->$field;
         if (isset($arg[0]) && is_array($arg[0])) {
            $param=$arg[0];
            /*
             * save on_empty for last
             */
            if (isset($param['on_empty'])) $on_empty=$param['on_empty'];
            /*
             * apply 'strip' operation
             */
            if (isset($param['strip'])||in_array('strip',$param)) {
               $allowed=NULL;
               if (isset($param['strip']) && is_string($param['strip'])) {
                  $allowed=$param['strip'];
               }
               if (NULL!==($strip = self::strip_tags_value($this->$field,$allowed))) {
                  //var_dump($strip);die('flat data strip dump');
                  $value = $strip;
               }
            }
         } else {
            if (isset($arg[0]) && is_scalar($arg[0]) ) {
               $on_empty = $arg[0];
            }
         }
         
         /*
          * on empty should be last operation applied
          */
         if (!empty($on_empty)) return $this->or_empty($field,$on_empty);
         return $value;
      }
      //throw new \flat\data\exception\not_field($field);
      return null;
   }
   
   /**
    * provides data dump
    * 
    * @return \flat\data\dump
    * @param string $type (optional) defaults to 'human'. types include: 'human', and 'var'.
    *    'human': human readable dump, like print_r(),
    *    'var': full object dump detailing native php types, like var_dump().
    * @see print_r() 'human' type dump
    * @see var_dump() 'var' type dump
    */
   public function dump($type='human',array $flags=NULL) {
      if ($flags===NULL) $flags=array();
      if ($type=='var') {
         ob_start();
         var_dump($this);
         return new \flat\data\dump(ob_get_clean());
      } else
      if ($type = 'human') {
         $dump=\flat\core\util\deepcopy::data($this);
         if (in_array('htmlencode',$flags)) {
            $dump->htmlentities();
         }
         //$dump->remove_non_visible();
         return new \flat\data\dump(print_r($dump,true));
      }
   }



   /**
    * checks if given field exists, throws exception if it does not.
    * 
    * @return void
    * 
    * @static
    * 
    * @param string $field field name (public property of \flat\data child)
    * 
    * @throws \flat\data\exception\not_field field does not exist
    * @throws \flat\data\exception\bad_field if given field name is not string
    */
   public function check_field_exist($field) {
      if (!$this->is_field_exist($field)) throw new data\exception\not_field($field);
   }
   
   /**
    * returns boolean true if given field exists, false if it does not.
    * 
    * @return bool
    * 
    * @param string $field field name (public property of \flat\data child)
    * 
    * @throws \flat\data\exception\bad_field if given field name is not string
    */
   public function is_field_exist($field) {
      if (is_string($field)) {
         $r = new \ReflectionClass(get_called_class());
         try {
         $rp=$r->getProperty($field);
            if ($rp->isPublic()) return true;
         } catch (\ReflectionException $e) {
            
         }
         return false;
      }
      throw new data\exception\bad_field("must be string");
   }
   


   /**
    * returns value if determined not to be empty value,
    *    otherwise returns $default value as specified.
    * 
    * @static
    * @param mixed $value
    * @param mixed $default value to return if given field's value evaluates empty
    */
   public static function or_empty_value($value,$default=mapper_cfg::default_empty_value) {
      if (!empty($value)) return $value;
      return $default;
   }

   /**
    * returns value of given field if determined not to have an empty value.
    *    otherwise returns $default value as specified.
    * 
    * @return mixed
    * 
    * @param string $field
    * @param mixed $default value to return if given field's value evaluates empty
    * 
    * @uses \flat\data::or_empty_value()
    * 
    * @throws \flat\data\exception\not_field field does not exist
    * @throws \flat\data\exception\bad_field if given field name is not string
    */
   public function or_empty($field,$default=mapper_cfg::default_empty_value) {
      $this->check_field_exist($field);
      return self::or_empty_value($this->$field,$default);
   }
   

   
   /**
    * strip_tags() convenience wrapper:
    *    if specified value is scalar; supplies to strip_tags(), optionally passing $allowed_tags.
    *    returns NULL if field value is not scalar.
    * 
    * @static 
    * @return string | NULL
    * 
    * @param scalar $value string to evaluate
    * @param string $allowed_tags (optional) defaults \flat\data::default_allowed_tags,
    *    string of tags to allow, ie: "<p><br><b>". ignored unless string value.
    *  
    */
   public static function strip_tags_value($value,$allowed_tags=mapper_cfg::default_allowed_tags) {
      if (validate\value::is_stringable($value)) {
         $value = (string) $value;
         $allowed=mapper_cfg::default_allowed_tags;
         if (is_string($allowed_tags)) $allowed=$allowed_tags;
         return strip_tags($value,$allowed);
      }
      return NULL;
   }
   
   /**
    * Alias of strip_tags()
    * 
    * @see \flat\data::strip_tags()
    */
   public function strip($field,$allowed_tags=mapper_cfg::default_allowed_tags) {
      return $this->strip_tags($field,$allowed_tags);
   }
   
   /**
    * strip_tags() convenience wrapper:
    *    supplies given field value strip_tags(), passing $allowed_tags as appropriate.
    * 
    * @return string
    * 
    * @param string $field name of \flat\data public property
    * @param string $allowed_tags (optional) defaults \flat\data::default_allowed_tags,
    *    string of tags to allow, ie: "<p><br><b>". ignored unless string value.
    * 
    * @uses \flat\data::strip_tags_value()
    * 
    * @throws \flat\data\exception\not_field field does not exist
    * @throws \flat\data\exception\bad_field if given field name is not string
    */
   public function strip_tags($field,$allowed_tags=mapper_cfg::default_allowed_tags) {
      //var_dump($field);
      $this->check_field_exist($field);
      return self::strip_tags_value($this->$field,$allowed_tags);
   }
   /**
    * @see \flat\data::get_string()
    */
   public function __toString() {
      try {
         return $this->get_string();
      } catch (\Exception $e) {
         trigger_error(
            "exception while converting \\flat\\data to string: ".
            $e->getCode().": ".$e->getMessage(), 
            E_USER_NOTICE
         );
         return "";
      }
   }
   /**
    * invokes given callback on each data field (public properties of \flat\data child), or subset of data fields.
    *    if the return value of callback is not NULL, then return value
    *    will be set as field value.
    * 
    * @return void
    * 
    * @param callable $callback callback function invoked with signature:
    *    function($field_value,$field_name)
    * @param string[] $fields (optional) limits to data fields in this array (fields are public properties of \flat\data child)
    */
   final public function each_field(callable $callback,array $fields=NULL) {
      $this->each_property($callback,$fields);
   }
   
   /**
    * converts all data fields where value is string type value to \flat\data\scalar
    *    optionally casts all strings with numeric value into
    *    float or int.
    * 
    * @param boolean $conver_numeric wheather to convert strings that
    *    evaluate numerically into an or int | float respecitvely
    */
   public function strings_to_scalar($convert_numeric=false) {
      if ($convert_numeric) {
         $this->each_property(function($value,$name) {
            if (is_string($value)) {
               /*
                * test if int... if evaluates to same when
                *    cast as int... assume an int (i think)
                */
               if ( (int) sprintf("%d",$value) == $value) {
                  return new data\scalar((int) $value);
               }
               
               /*
                * test if float.. if evaluates to same when cast as float...
                *    assume a float
                */
               if ( (float) sprintf("%f",$value) == $value) {
                  return new data\scalar((float) $value);
               }
               
               /*
                * not float or int by this point.. just string
                */
               return new data\scalar($value);
            }
         });
      } else {
         $this->each_property(function($value,$name) {
            if (is_string($value)) {
               return new data\scalar($value);
            }
         });
      }
   }
   
   /**
    * converts all data fields where value is integer type value to \flat\data\scalar
    */
   public function floats_to_scalar() {
      $this->each_property(function($value,$name) {
         if (is_float($value)) return new data\scalar($value);
      });
   }
   
   /**
    * converts all data fields where value is integer type value to \flat\data\scalar
    */
   public function ints_to_scalar() {
      $this->each_property(function($value,$name) {
         if (is_int($value)) return new data\scalar($value);
      });
   }
   
   public function null_to_emptystring(array $fields=NULL) {
      $this->each_property(function($value,$name) {
         if ($value===NULL) return "";
      });
   }

   /**
    * creates clone of data object and recursively changes all data fields
    *    values to be encoded with htmlentities().
    * 
    * @return \flat\data
    * 
    * @param int $flags same as php htmlentities() doc 
    * @param string $encoding same as php htmlentities() doc
    * @param bool $double_encode same as htmlentities() doc
    * @param string[] $fields (optional) limits to data fields in this array
    *    (fields are public properties of \flat\data child)
    * 
    * @see http://php.net/htmlentities htmlentities() doc
    * @see htmlentities()
    * 
    * @uses \flat\data::htmlentities_unknown constant is
    *    used when unable to determine how to encode a value.
    * @uses \flat\data::htmlentities_null constant is
    *    used when value is NULL.
    */
   public function htmlentities($flags = NULL,$encoding=NULL,$double_encode=NULL,array $fields=NULL) {
      $data = clone $this;
      $data->each_field(function($value,$name) use(& $flags,& $encoding,& $double_encode){
         return self::_recursive_htmlentities($value,$flags,$encoding,$double_encode);
      },$fields);
      return $data;
   }

   private static function _recursive_htmlentities($value,$flags = NULL,$encoding=NULL,$double_encode=NULL) {
      if (!$flags) $flags = ENT_COMPAT | ENT_HTML401;
      if (!$encoding) $encoding = ini_get("default_charset");
      if ($double_encode===true) {
         $double_encode = true;
      } else {
         $double_encode = false;
      }
      if (is_string($value) || is_int($value) || is_float($value)) return htmlentities($value,$flags,$encoding,$double_encode);
      if (is_bool($value)) {
         if ($value) return "true";
         return "false";
      }
      if (is_object($value) && is_a($value,"\\flat\\data")){
         return $value->htmlentities($flags,$encoding,$double_encode);
      } else 
      if (is_object($value) || is_array($value)) {
         foreach ($value as $prop=>&$val) {
            $val = self::_recursive_htmlentities($val,$flags,$encoding,$double_encode);
         }
         return $value;
      }
      if ($value===NULL) return mapper_cfg::htmlentities_null;
      return mapper_cfg::htmlentities_unknown;
   }
   
   /**
    * fixes all data fields (public properties of \flat\data child) that 
    *    currently have string values that have nested utf8 encoding (nested
    *    character encoding is common cause for garbage characters inside 
    *    a string)
    * 
    * @return void
    * 
    * @param string[] $fields (optional) limits to data fields in this array
    *    (fields are public properties of \flat\data child)
    * 
    * @see \flat\core\util\utf8::fix()
    * 
    */
   public function utf8_fix(array $fields=NULL) {
      $this->each_property(function($value,$name) {
         if (is_string($value)) return \flat\core\util\utf8::fix($value);
      },$fields);
   }
   
   /**
    * enforce that given var is string 
    *  
    * @final
    * @param mixed $var variable to check
    * @param string $interface \flat\data\stringify interface providing string
    * @throws \flat\data\exception\not_stringable if $var is not string
    * 
    * @return string
    * 
    * @see \flat\data\stringify
    */
   final protected function _check_stringify($var,$interface) {
      if (!is_string($var)) {
         throw new data\exception\not_stringable(
            get_class($this),
            "bad string given from interface: $interface"
         );
      }
      return $var;
   }

   /**
    * transforms data field values into a string representation.
    * 
    * @final
    * @return string
    * 
    * @param string | NULL $stringify (optional) explicitly use given stringify methodology, when
    *    not given or NULL, encodes as JSON or determines an interface to apply as described below.
    * 
    * @throws \data\exception\not_stringable if data cannot be transformed 
    *    into a string for some reason 
    * 
    * @see \flat\core\serializer returns result of child::serialize($this,NULL)
    *    if inherited class implements this interface
    * 
    * @uses \flat\data\stringify\json returns json document
    *    if inherited class implements this interface
    * 
    * @uses \flat\data\stringify\xml returns XML document
    *    if inherited class implements this interface
    * 
    * @see \flat\core\xml\encode used with \flat\data\stringify\xml interface
    * 
    * @uses \flat\data\stringify\html returns HTML document (serialized with \flat\core\html\encode)
    *    if inherited class implements this interface
    * 
    * @see \flat\core\html\encode used with \flat\data\stringify\html interface
    * 
    * @uses \flat\data\stringify\text returns result of child->get_text()
    *    if inherited class implements this interface
    * 
    * @uses \flat\data\stringify\empty_string returns an empty string ("")
    *    if inherited class implements this interface
    * 
    * @uses \flat\data\not_stringable throws exception data\exception\not_stringable
    *    if inherited class implements this interface
    * 
    * @uses \flat\data::_check_stringify() when applying \flat\data\stringify interfaces
    */
   final public function get_string($stringify=NULL) {
      if ($stringify && is_string($stringify)) {
         if ($stringify=='json') {
            return json_encode($this);
         } else
         if ($stringify=='xml') {
            return xmlencode::serialize($this);
         } else
         if ($stringify=='html') {
            return htmlencode::serialize($this);
         }
      }
      if ($this instanceof \flat\data\not_stringable) {
         throw new data\exception\not_stringable(
            get_class($this),
            "has not_stringable interface"
         );
      } else
      if ($this instanceof \flat\core\serializer) {
         return $this->_check_stringify(
            $this::serialize($this),
            "core\\serializer"
         );
      } else
      if (
         (!$this instanceof \flat\data\stringify) || 
         ($this instanceof \flat\data\stringify\json)) {
         return json_encode($this);
      } else {
         if ($this instanceof \flat\data\stringify\xml) {
            return $this->_check_stringify(
               xmlencode::serialize(
                  $this,
                  $this->get_xml_serialize_options()
               ),
               "stringify\\xml"
            );
         } else
         if ($this instanceof \flat\data\stringify\html) {
            return $this->_check_stringify(
               htmlencode::serialize(
                  $this,
                  $this->get_html_serialize_options()
               ),
               "stringify\\html"
            );
         } else
         if ($this instanceof \flat\data\stringify\text) {
            return $this->_check_stringify(
               $this->get_text(),
               "stringify\\text"
            );
         } else
         if  ($this instanceof \flat\data\stringify\empty_string) {
            return "";
         } else {
            throw new data\exception\not_stringable(
               get_class($this),
               "unknown stringify interface"
            );
         }
      }
      
   }
   /**
    * transform fields/values into an XML document
    * 
    * @final
    * @return string
    * 
    * @see \flat\core\xml\encode
    */
   final public function to_xml() {
      return xmlencode::serialize($this);
   }
   /**
    * transform fields/values into a JSON document
    * 
    * @final
    * @return string
    */
   final public function to_json($options = null) {
      return json_encode($this, $options);
   }
   
   /**
    * retrieve list of data field names
    *    (public properties the \flat\data child)
    * 
    * @return null|string[]
    */
   private static $_field=NULL;
   final public static function field_list(\flat\data $instance=NULL) {

      if ($instance) {
         
      } else {
         $class = "flat\\data";
         if (substr( get_called_class(), 0, strlen($class) ) == $class) {
            return null;
         }
         /*
          * only enumerate once
          */
         if (is_array(self::$_field)) return self::$_field;
         
         $r = new \ReflectionClass(get_called_class());
         
      }
      $prop = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
      $field = array();
      foreach ($prop as $p) $field[] = $p->getName();
      
      /*
       * save list for later
       */
      self::$_field = $field;
      return $field;      
   }
   
   /**
    * determine if field(s) exist
    * 
    * returns bool if $name is string or array of strings:
    *    $name is string: returns bool true if field exists, bool false if not
    *    $name is array of strings: returns bool true if all exist, false if any do not
    *  
    * returns NULL on error
    * 
    * if $name is array, $not_field_callback is called for each name given that is not a field 
    *    function pattern: callback(string $field_name, bool|null $result)
    * 
    * @return bool|null
    * @param string|string[] $name field name
    * @param callable $not_field_callback 
    * @see \flat\data::get_field_list()
    */
   final public static function is_field($name,callable $not_field_callback=NULL) {
      
      if (is_string($name)) {
         if ($not_field_callback) return self::is_field(array($name),$not_field_callback);
         return (in_array($name,self::field_list()))?  true:false;
      } else
      if (is_array($name)) {
         if ($not_field_callback) $found_not_field = false;
         foreach ($name as $n) {
            if (!$r = self::is_field($n)) {
               if ($not_field_callback) {
                  $found_not_field = true;
                  $not_field_callback($n,$r);
               } else {
                  if ($r===false) return false;
                  return null;
               }
            }
         }
         if ($not_field_callback) {
            if ($found_not_field) return false;
            return true;
         }
         return true;
      }
      return null;
   }
   
   /**
    * creates (an optionally ongoing) assoc map from given data
    *    as specified by $key_prefix
    * 
    * @static
    * 
    * @return array
    */
   public static function map_key_prefix(array &$data,$key_prefix,array &$map=NULL) {
      if (!$map) $map = array();
      if (is_array($key_prefix)) {
         foreach($key_prefix as $prefix) {
            self::map_key_prefix($data,$prefix,$map);
         }
         return $map;
      } else 
      if (!is_string($key_prefix) && !empty($key_prefix)) throw new \flat\data\exception\bad_rule(
         'key_prefix',
         "must be string or string[] if given"
      );
      if (empty($key_prefix)) $key_prefix = "";
      
      $len = strlen($key_prefix);
      $i=0;
      foreach ($data as $k=>$v) {
         if (empty($k)) throw new \flat\core\mappable\exception\bad_property_name(
            "data must be assoc array (on data element #$i)"
         );
         if (substr($k,0,$len)==$key_prefix) {
            $map[substr($k,$len)] = $v;
         } else {
            $map[$k] = $v;
         }
         $i++;
      }
      return $map;
   }
   /**
    * provide argument(s) to map values from
    * 
    * @return void
    * 
    * @final
    * @param array $data assoc array of values to map to \flat\data object.
    * @param string | string[] $key_prefix prefixed string within $data assoc keys to 
    *    remove if found before mapping.
    * @param array $default (optional) assoc array of default values for 
    *    elements missing (by key) from $data.
    * @param array $flags (optional) array of strings for behavior to apply:
    *    'default_on_empty' if present $default param will apply if event when
    *       $data key is set but evalates empty
    * 
    * @todo implement some $flags
    * 
    * @see \flat\core\mappable\constructor for how data is mapped to object.
    * 
    * @todo usage of \flat\data\validator interface
    * 
    * @throws \flat\core\mappable\exception\bad_property_name if a key in $data
    *    element is missing or not a string.
    * 
    * function call signatures: 
    *    map(array $data,$key_prefix=NULL,array $default=NULL,array $flags=NULL)
    *       OR
    *    array('data'=>$data,'key_prefix'=>$key_prefix, etc...)
    */
   final public function map() {
      if ($this->_purged_done) {
         
         foreach($this->_purged as $k=>$v) {
            $this->$k = $v;
         }
         
      }      
      $args = func_get_args();
      //if (count($args)==1 && is_a($args[0],"\\flat\\data\\rules")) {
      if (count($args)==1 && is_object($args[0]) && ($args[0] instanceof \flat\data\rules)) {
         $rule = $args[0];
      } else {
         $rule = new \flat\data\rules($args);
      }
      
      /*
       * sanity check: data rule must exist
       */
      if (!is_array($rule->data)) throw new \flat\data\exception\bad_rule(
         "data","must exist and be assoc array"
      );
      
      /*
       * sanity check: if any of the following params exist, 
       *    they must be arrays:
       *       'key_prefix','default','flags'
       */
      foreach (array('default','flags') as $param) {
         if ($rule->$param && !is_array($rule->$param)) throw new 
         \flat\data\exception\bad_rule(
            $param,
            "must be array if given"
         );
      }

      /*
       * remove key prefix(es)
       */
      if (!empty($rule->key_prefix)) {
         $map = self::map_key_prefix($rule->data,$rule->key_prefix );
      } else $map = $rule->data;
      

      if (NULL != ($default = ($rule->default))) {
         /*
          * apply default map
          */
         foreach($prop as $k=>&$v) if (!isset($map[$k]) && isset($default[$k]) ) $v=$default[$k];
         /*
          * apply 'default_on_empty' flag
          */
         if ($flags && $default!=NULL && in_array('default_on_empty',$flags))
         foreach($map as $k=>&$v) if (empty($v) && isset($default[$k]) ) $v=$default[$k];
      }
          
      parent::__construct($map);
      $this->_apply_interfaces($map,$rule);

   }
   private function _apply_interfaces(array $data=NULL,data\rules $rules=NULL) {
      

      
      $r = new \ReflectionClass($this);
      
      /**
       * @see \flat\data\typed set $this->type as appropriate
       */
      if ($this instanceof \flat\data\typed\shortname) {
         
         $this->type = $r->getShortName();
      } else 
      if ($this instanceof \flat\data\typed\explicit) {
         $this->type = $this->get_data_type();
      } else
      if ($this instanceof \flat\data\typed\classname) {
         $this->type = get_class($this);
      }
      
      if ($this instanceof \flat\data\webview) {
         $this->utf8_fix();
         $this->htmlentities();
      }
      
      /*
       * data\transform, data\rules\consumer interfaces
       */
      if ($this instanceof \flat\data\transform) {
         $this->data_transform($data);
      }
      
      if ($this instanceof \flat\data\rules\consumer) {
         if (!$rules) $rules = new data\rules(array('data'=>$data));
         $this->set_data_rules($rules);
      }
      
      if ($this instanceof \flat\data\replace) {
         
      }
      
      /*
       * data\ready interface...
       *    should be the last interface applied
       */
      if ($this instanceof \flat\data\ready) {
         $this->data_ready();
      }
      
      //$r = new \ReflectionClass(get_class($this));
      /*
       * remove pesky protected properties ;)
       */
      
      foreach( $r->getProperties(\ReflectionProperty::IS_PROTECTED) as $rp) {
         
         if ($rp->isStatic()) continue;
         
         //unset($this->$rp->name);
         $name = $rp->name;
         //var_dump($name);die('flat data die');
         if (!$this->_purged_done) {
            $this->_purged[$name] = $this->$name;
         }
         unset($this->$name);
      }
      
      if ($this->_purged_done) {
         foreach($this->_purged as $k=>$v) {
            if (isset($this->$k)) unset($this->$k);
         }
      } else {
         $this->_purged_done = true;
      }
   }
   private $_purged_done = false;
   private $_purged=[];
   /**
    * provide argument(s) to map values from
    * 
    * @see \flat\core\mappable\constructor used when given 1 argument that is an array
    * @see \flat\core\mappable::args_to_properties() used otherwise 
    * 
    * @final
    * @param array|object|\flat\data\map $data object, assoc array, non-assoc array (
    *    assumed in order of property definitions, to map values from
    * 
    * @uses \flat\data\typed\shortname sets $this->type property to inherited class shortname
    *    if inherited class implements this interface
    * @uses \flat\data\typed\classname sets $this->type property to full inherited classname
    *    if inherited class implements this interface
    * @uses \flat\data\typed\explicit sets $this->type property to value 
    *    explicitly provided by inherited class through this interface
    * 
    * @todo usage of \flat\data\validator interface
    */
   public function __construct() {
      $args = func_get_args();
      $data = array();
      if (count($args)==1) {
         if (is_a($args[0],"\\flat\\data\\rules")) {
            //var_dump($args[0]);echo "flat data rule\n\n";
            $this->map($args[0]);
            return;
         } else
         if (is_array($args[0])) {       
            $data = $args[0];
            parent::__construct($args[0]);
            return $this->_apply_interfaces($data);
         }
      }
      if (count($args)) {
         $this->args_to_properties(
            func_get_args(),
            (array) $this,
            array(
               'set_private_property'=>false,
               'ignore_cannot_set'=>true,
            )
         );
         $data = (array) $this;
      }
      $this->_apply_interfaces($data);
   }   
}
