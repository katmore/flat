<?php
namespace flat\tmpl;
class data implements \ArrayAccess {
   private $_arr = [];
   private $_obj = [];
   public function getAsArray() {
      return $this->_arr;
   }
   public function getAsObject() {
      return $this->_obj;
   }
   public function __get($name) {
      if (isset($this->_obj[$name])) return $this->_obj[$name];
   }
   public function __set($name,$value) {
      $this->_obj[$name]=$value;
   }
   public function __isset($name) {
      return isset($this->_obj[$name]);
   }
   public function __unset($name) {
      if (isset($this->_obj[$name])) unset($this->_obj[$name]);
   }
   
   public function __construct($data) {
      if (is_scalar($data)) {
         $this->_arr = $this->_obj = ['data'=>$data];
      } else
      if (is_object($data)) {
         $this->_arr = $this->_obj = (array) $data;
      } else
      if (is_array($data)) {
         $this->_arr = $data;
         $this->_obj = [];
         foreach ($data as $k=>$v) {
            $this->_setObj($k,$v);
         }
      }
   }
   private function _setObj($k,$v) {
      $kcmp = (int) sprintf("%d",$k);
      if ($kcmp === $k) {
         $this->_obj["_".$k]=$v;
      } else {
         $this->_obj[$k]=$v;
      }      
   }
   private function _unsetObj($k) {
      $kcmp = (int) sprintf("%d",$k);
      if ($kcmp === $k) {
         unset($this->_obj["_".$k]);
      } else {
         unset($this->_obj[$k]);
      }
   }   
   public function offsetSet($offset, $value) {
      if (is_null($offset)) {
         $this->_arr[] = $value;
         $this->_setObj(count($this->_arr)-1, $value);
      } else {
         $this->_arr[$offset] = $value;
         $this->_setObj($offset,$value);
      }
   }
   
   public function offsetExists($offset) {
      return isset($this->_arr[$offset]);
   }
   
   public function offsetUnset($offset) {
      unset($this->_arr[$offset]);
      $this->_unsetObj($offset);
   }
   
   public function offsetGet($offset) {
      return isset($this->_arr[$offset]) ? $this->_arr[$offset] : null;
   }
}