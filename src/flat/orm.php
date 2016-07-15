<?php
namespace flat;
abstract class orm {
   
   use \flat\core\mapper;
   
   protected $_create_done;
   protected function _before_create_record() {}
   protected function _create() {}
   public function create() {
      
      if ($this->_exists || $this->_create_done) return;
      
      if (!$this instanceof orm\crud\create) return;
      
      $this->_before_create_record();
      $ret = $this->_create();
      if ($ret===false) {
         throw new orm\crud_error('create');
      } elseif (is_string($ret)) {
         throw new orm\crud_error('create',$ret);
      }
      $this->_exists = true;
      $this->_create_done = true;
   }
   
   protected $_read_done;
   protected function _before_read_record() {}
   protected function _read() {}
   public function read() {
      
      if ($this->_read_done) return;
      
      if (!$this instanceof orm\crud\read) return;
      
      $this->_before_read_record();
      $ret = $this->_read();
      if ($ret===false) {
         throw new orm\crud_error('read');
      } elseif (is_string($ret)) {
         throw new orm\crud_error('read',$ret);
      }
      $this->_exists = true;
      $this->_read_done = true;
      
      return $this;
   }  
   
   protected $_update_done;
   protected function _before_update_record(){}
   protected function _update() {}
   public function update() {
   
      if (!$this instanceof orm\crud\update) return;
   
      $this->_before_update_record();
      $ret = $this->_update();
      if ($ret===false) {
         throw new orm\crud_error('update');
      } elseif (is_string($ret)) {
         throw new orm\crud_error('update',$ret);
      }
      $this->_exists = true;
      $this->_update_done = true;
   }   
   
   protected $_delete_done;
   protected function _before_delete_record() {}
   protected function _delete() {}
   /**
    * @return void
    */
   public function delete() {
      
      if ($this->_read_done) return;
      
      if (!$this instanceof orm\crud\delete) return;
       
      $this->_before_delete_record();
      $ret = $this->_delete();
      if ($ret===false) {
         throw new orm\crud_error('delete');
      } elseif (is_string($ret)) {
         throw new orm\crud_error('delete',$ret);
      }
      $this->_exists = false;
      $this->_delete_done = true;
   }
   /**
    * @var bool existence status of this object's key/value set.
    */
   protected $_exists;
   /**
    * Called when the _exists property has a null value.
    *    The result declares a record with this object's key/value set
    *    existence status.
    */
   protected function _exists() {}
   /**
    * Provides the existence status of this object's key/value set.
    * @return bool
    */
   public function exists() {
      if ($this->_exists===null) {
         if (null!==($exists = $this->_exists())) {
            if ($exists) {
               $this->_exists = true;
            } else {
               $this->_exists = false;
            }
         }
      }
      return $this->_exists;
   }
   
   protected function _apply_map(array $map) {
      
      if (!is_array($keylist = $this->_get_key_list())) {
         $keylist=[];
      }
      foreach(
         (new \ReflectionClass(get_called_class()))->getProperties(\ReflectionProperty::IS_PUBLIC) as $rp
      ) {
         if (array_key_exists($rp->name,$map)) {
            $this->{$rp->name} = $map[$rp->name];
         }
      }
      
   }
   
   protected static function _keyval2map($keylist,$keyvalue) {
      
      $keylist = json_decode(json_encode($keylist));
      $fieldlist=[];
      if (is_scalar($keylist)) {
         if (is_string($keylist)) {
            if (property_exists(get_called_class(), $keylist)) {
               $map[$keylist] = null;
               $fieldlist[]=$keylist;
            }
         }
      }
      if (is_array($keylist)) {
         foreach($keylist as $k) {
            if (property_exists(get_called_class(), $k)) {
               $map[$k] = null;
               $fieldlist[]=$k;
            }
         }
      }
      
      $map = [];
      if (is_scalar($keyvalue)) {
//          var_dump($fieldlist);
//          \flat\core\debug::kill('scalar...');
         foreach($fieldlist as $k) {
            $map[$k]=$keyvalue;
         }
         
      } elseif (is_array($keyvalue) || is_object($keyvalue)) {
         $map = self::_data2map((array) $keyvalue,$fieldlist);
      }
      
      //\flat\core\debug::kill($map);
      return $map;
   }
   
   protected static function _data2map(array $data,array $fieldlist=null) {
      $map = [];
      if (!is_array($fieldlist)) {
         $fieldlist=[];
         foreach(
               (new \ReflectionClass(get_called_class()))->getProperties(\ReflectionProperty::IS_PUBLIC) as $rp
         ) {
            $fieldlist[]=$rp->name;
         }
      }
      if (count($data)) {
         $mapArg=null;
         if ((count($data)==1) && (is_array(current($data) || is_object(current($data))))) {
            $mapArg = json_decode(json_encode(current($data)));
         } else {
            $mapArg = json_decode(json_encode($data));
         }
         if (is_array($mapArg)) {
            foreach(
                  $fieldlist as $field
            )
            {
               if (!list($k,$v)=each($mapArg)) break 1;
               $map[$field] = $v;
            }
         } elseif (is_object($mapArg)) {
            foreach( $fieldlist as $field) {
               if (property_exists($mapArg, $field)) {
                  $map[$field] = $mapArg->$field; 
               }
            }
         }
      }
      
      return $map;
   }
   
   public function set_data(array $data) {
      $this->_apply_map(
         static::_data2map($data)
      );
   }
   
   abstract protected function _get_key_list();
   
   public function __construct($keyvalue,...$data) {
      
      $this->set_data($keymap = self::_keyval2map($this->_get_key_list(), $keyvalue));
      
      $this->set_data($data);
      
   }
}

























