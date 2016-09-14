<?php
namespace flat\core\collection;
trait controller {
   private $member;
   private $index; //assoc array of 'key's with integer value of array $member[$index[$key]]
   private $count=0;

   /**
    * part of \Iterator interface
    * @final
    */
   final public function rewind()
   {
      if ($this->member) {
         reset($this->member);
      }
   }
   
   /**
    * part of \Iterator interface
    * @final
    */
   final public function current()
   {
      // $var = current($this->member);
      // return $var;
      if ($this->member) {
         
         return current($this->member)->get_data();
      
      }
   }
   
   /**
    * part of \Iterator interface
    * @final
    */
   final public function key()
   {
      // $var = key($this->member);
      // return $var;
      if (!is_array($this->index)) return;
      if ($this->member) {
         return array_search(current($this->member),$this->index,true);
      }
   }
   
   /**
    * part of \Iterator interface
    * @final
    */
   final public function next()
   {
      // $var = next($this->member);
      // return $var;
      if ($this->member) {
         return next($this->member);
      }
   }
   
   /**
    * part of \Iterator interface
    * @final
    */
   final public function valid()
   {
      if (!$this->member) return false;
      $key = key($this->member);
      $var = ($key !== NULL && $key !== FALSE);
      return $var;
   }
    
   /**
    * part of \Iterator interface
    * @final
    */
   final public function count() {
      return $this->count;
   }
    
   /**
    * get a range of members
    *    returns array containing range of collection members.
    *    void if callback is given.
    *
    * @param int $limit (optional) default 0. amount of members for range. less than
    * @param int $start (optional) default 0. position starting at 0 within collection to begin range
    * @param callable $callback (optional) callback invoked for each member
    *    within range. callback function pattern:
    *    my_callback($member,$position,$member_key)
    *
    * @return \flat\core\collection\member[]|void
    * @throws \flat\core\collection\exception\out_of_range on bad $start position
    * @final
    */
   final public function range($limit=0,$start=0,callable $callback=NULL) {
      /**
       * sanitize arguments
       * @var int $limit should be no more than member count
       *    and if 0 defaults to member count.
       * @var int $start should be 0 or no more than member count minus one
       */
      $limit = (int) sprintf("%d",$limit);
      if ($limit<1) $limit = $this->count();
      if ($limit>$this->count) $limit = $this->count;
      $start = (int) sprintf("%d",$start);
      if ($start > ($this->count-1)) {
         throw new collection\exception\out_of_range(
               "start",
               "position:$start is out of range"
               );
      }
      if ($start < 0) $start = 0;
   
      /**
       * as appropriate...
       * return array of members indicated by range params
       *    --OR--
       * invoke callback with member data
       *
       * @var array $arr array of members to return
       */
      if (!$callback) {
         $arr = array();
         for($i=$start;$i<$limit;$i++) {
            $arr[] = $this->member[$i];
         }
         return $arr;
          
      } else {
         for($i=$start;$i<$limit;$i++) {
            $member = $this->member[$i];
            call_user_func_array(
                  $callback,
                  array(
                     $member,
                     $i,
                     array_search($i,$this->index,true)
                  )
                  );
   
         }
      }
   }
    
   /**
    * bool true if member exists associated by given key. false otherwise.
    * @param string $key
    *
    * @return bool
    * @final
    */
   final public function is_exist($key) {
      if (isset($this->member[$this->index[$key]])) return true;
      return false;
   }
    
   /**
    * provides \flat\core\collection\member instance if one exists as
    *    associated by given key. otherwise returns null.
    *
    * @param string $key
    *
    * @return \flat\core\collection\member()|null
    * @final
    */
   final public function get($key) {
      if (isset($this->member[$this->index[$key]])) return $this->member[$this->index[$key]];
      //if (is_int($key)) if (isset($this->member[$key])) return $this->member[$key];
      return NULL;
   }
    
   /**
    * @var \flat\core\collection\constrainer\controller $_constrainer
    * @uses _validate_data()
    */
   private $_constrainer = NULL;
    
   /**
    * @var \flat\core\collection\constrainer\controller $_validator
    * @uses _validate_data()
    */
   private $_validator = NULL;
    
   /**
    * @var callable $_filter_call
    * @uses _validate_data()
    * @uses set_data_filter()
    */
   private $_filter_call = NULL;
    
   /**
    * @var callable $_validator_call
    * @uses _validate_data()
    * @uses set_data_validator()
    */
   private $_validator_call = NULL;
    
   /**
    * set validation callback function.
    *    if returns false, member with given data will not be added.
    *
    * @param callable $callable
    * @see \flat\core\collection::_validate_data()
    */
   public function set_data_validator(callable $callable) {
      $this->_validator_call = $callable;
   }
    
   /**
    * set filter callaback function.
    *    whatever is returned is set as added member's data.
    * @param callable $callable
    * @see \flat\core\collection::_validate_data()
    */
   public function set_data_filter(callable $callable) {
      $this->_filter_call = $callable;
   }
    
   /**
    * validation logic when adding member to collection.
    *    whatever this function returns is the data used for added member.
    *    if it returns \flat\core\collection\member\ignore, no member will be added.
    *
    * child class can overload this function.
    *
    * @param mixed $data data of member proposed to be added to collection
    * @see \flat\core\collection\member\constrainer\class_constraint
    * @see \flat\core\collection\member\constrainer\interface_constraint
    * @see \flat\core\collection\member\validator
    * @see \flat\core\collection::set_data_validator()
    * @see \flat\core\collection::set_data_filter()
    * @see \flat\core\collection\member\ignore
    * @throws \flat\core\collection\exception best practice for
    *    child classes overloading _validate_data() is to use a child of this
    *    exception class when throwing validation failures.
    * @throws \Exception other exception types possible depending on
    *    behavior of child class interfaces and validation callbacks
    * @throws \flat\core\collection\member\constrainer\exception
    *    class or interface constraint failure
    * @throws \flat\core\collection\member\validator\exception
    *    validation failure
    */
   protected function _validate_data($data) {
      /**
       * data validation logic:
       *
       * @var \flat\core\collection\member\constrainer\controller $this->_constrainer
       *
       *    1. check and apply any class/interface constraints for $data
       *
       * @var \flat\core\collection\member\validator\controller $this->_validator
       *    2. check for validator interface, and apply $data against it
       *
       * @var callable $this->_validator_call
       *
       *    3. check and invoke validator_call callback if exists
       *
       * @var callable $this->_filter_call
       *    4. check and invoke filter_call callback if exists
       */
      /*
       * only need to create constrainer controller first time
       */
      if ($this->_constrainer === NULL) {
   
         $this->_constrainer = new \flat\core\collection\member\constrainer\controller($this);
          
         if (!$this->_constrainer->has_active_checks()) $this->_constrainer = false;
          
      }
   
      /*
       * if constrainer interface applies, check and transform data
       */
      if ($this->_constrainer) $data = $this->_constrainer->check_member($data);
   
      /*
       * only need to create validator constrainer controller first time
       */
      if ($this->_validator === NULL) {
         $this->_validator = new \flat\core\collection\member\validator\controller($this);
         if (!$this->_validator->has_active_checks()) $this->_validator = false;
      }
   
      /*
       * if validator interface applies, check and transform data
       */
      if ($this->_validator) $data = $this->_validator->check_member($data);
   
      /*
       * invoke validator callback if set
       */
      if ($this->_validator_call) {
         $call = $this->_validator_call;
         $call($data);
      }
   
      /*
       * invoke filter callback if set
       */
      if ($this->_filter_call) {
         $call = $this->_filter_call;
         $data = $call($data);
      }
      return $data;
   }
   /**
    * add member to collection with given data.
    *    optionally associate a key for added member.
    *    returns true if successfully added. null if not.
    *
    * @return true|void
    * @throws \flat\core\collection\member\constrainer\exception
    *    class or interface constraint failure
    * @throws \flat\core\collection\member\validator\exception
    *    validation failure
    *
    * @param mixed $add data of member. data is validated and potentially
    *    transformed with collection::_validate_data().
    * @param int|string $key key to associate with member
    *
    * @see \flat\core\collection::_validate_data()
    */
   final public function add($data,$key=NULL) {
   
      $data = $this->_validate_data($data);
   
      if (is_a($data,"\\flat\\core\\collection\\member\\ignore")) return null;
   
      if (is_callable($this->_validator_call)) {
         $validator = $this->_validator_call;
         $validator($data);
      }
   
      if (is_callable($this->_filter_call)) {
         $validator = $this->_filter_call;
         $data = $validator($data);
      }
   
      if (is_a($data,"\\flat\\core\\collection\\member\\ignore")) return null;
   
      /*
       * add element to $this->index if $key argument given
       */
      if ((is_scalar($key)) && (!empty($key))) $this->index[$key] = $this->count;
   
      /*
       * add member object that contains data and perhaps some metadata
       * increment count
       */
      $this->member[$this->count] = new \flat\core\collection\member($data);
      $this->count++;
   
      /*
       * if validator applies, invoke success method
       */
      if ($this->_validator) $this->member_success($data);
   
      return true;
   
   }
}
