<?php
/**
 * \flat\core\event class 
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
/**
 * low level event class. events are objects (typically accumulated within a collection)
 *    the collection is passed along to different controllers that desire to handle same group the events.
 *    
 * @see \flat\core\event::call() events can be called. a child of \flat\core\event can use via 
 * @see \flat\core\event\triggerable events can be triggered 
 *       ...some outside thing can cause event to occur via \flat\core\event\triggerable::trigger()
 * 
 * @see \flat\core\event\listener events can be listened to...
 *    multiple listeners can accumulate on a single event. when event occurs
 * 
 * @see \flat\core\event\factory events are ideally controlled through 
 *    extending the event factory class
 * 
 * 
 * @package    flat\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * @abstract
 */
abstract class event extends \flat\core {

   /**
    * @var string $name name of event
    */
   private $name;
   
   /**
    * @var callable[] $listener any listeners attached event
    */
   protected $listener;

   /**
    * @var callable $handler function to invoke for event occurrance
    */
   private $handler;

   /**
    * @var mixed $data optional data to pass to event handler and listeners
    */
   private $data;
   
   /**
    * @var array $keyval keyval storage
    */
   private $_keyval;
   
   /**
    * sets a data keyval
    * 
    * @param string $key data key to set
    * @param mixed $val data value
    * @param bool $no_overwrite if true, will only set value to key 1 time
    * 
    * @return void
    * @throws \flat\core\event\exception\invalid_key\is_not_string if key not string
    * @throws \flat\core\event\exception\invalid_key\cannot_be_empty if key empty
    */
   final public function set_data($key,$val,$no_overwrite=false) {
      if (!is_string($key)) throw new event\exception\invalid_key\is_not_string();
      if (empty($key)) throw new event\exception\invalid_key\cannot_be_empty();
      if (!is_array($this->_keyval)) $this->_keyval = array();
      if ($no_overwrite) if (isset($this->_keyval[$key])) return;
      $this->_keyval[$key] = $val;
   }
   
   /**
    * retrieve name of event
    * 
    * @return string
    * 
    * @see $name
    * @see \flat\core\event::set_params()
    * @final
    */
   final public function get_name() {
      return $this->name;
   }
   /**
    * add listener to event. multiple listeners can attach to a single event
    *    simply by calling this method multiple times.
    * 
    * @param callable $listener function invoked after event occurance
    * @return void
    * @see $listener
    * @final
    */
   final public function add_listener(callable $listener) {
      
      if (!is_array($this->listener)) $this->listener = array();
      
      $this->listener[] = $listener;
      
   }
   /**
    * set handler for event. only the last handler set will be invoked for 
    *    event occurrance with event::call(). if the handler returns bool false on event occurance
    *    the event is considered to have failed and exception will be thrown.
    * 
    * @param callable $handler
    * @return void
    * 
    * @final
    * @see \flat\core\event\exception\failure
    * @see \flat\core\event::call()
    */
   final public function set_handler(callable $handler) {
      $this->handler = $handler;
   }
   /**
    * invoke event occurrance
    * @param mixed $call_data (optional) data associated with a to particular 
    *    occurance to pass to event handler and any listeners
    * @return void
    * 
    * @throws \flat\core\event\exception\failure if handler returns false
    * 
    * @see $handler if handler set, handler is first invoked
    * @see $listener if listeners exist, these are invoked after event handler
    * @see $data data associated with event is passed to handler and listeners
    * 
    * @final
    */
   final protected function call($call_data=NULL) {
      /**
       * @var array $arr fuction arguments to pass to handler and listeners
       * @see call_user_func_array()
       */
      $arr = array();
      if ($call_data!=NULL) $arr['call'] = $call_data; 
      if ($this->data != NULL) $arr['event'] = $this->data;
      if ($this->_keyval !=NULL) $arr['data'] = (object) $this->_keyval;
      if (count($arr)>1) $arr = array('data'=>$arr);
      
      /*
       * invoke handler first as appropriate
       */
      $h_data = NULL;
      if (is_callable($this->handler)) {
         $h_data = call_user_func_array($this->handler, $arr);
         if (false === $h_data) {
            throw new event\exception\failure($this);
         }
      }
      //var_dump($h_data);echo "dump: flat/core/event ".$this->get_name();
      /*
       * invoke listeners after preparing data in priority order
       *    if handler provides data, it will be all that is passed
       */      
      if($this->listener) {
         $arr = array();
         if ($call_data!=NULL) $arr = array( $call_data ); 
         if ($this->data != NULL) $arr = array( $this->data );
         if ($this->_keyval !=NULL) $arr = array( (object) $this->_keyval );
         if (!empty($h_data)) $arr = array( $h_data );
         foreach ($this->listener as $listener) call_user_func_array($listener, $arr);
      }
   }
   /**
    * convenience function for child classes to set some minimal event parameters
    * 
    * @final
    * @return void
    * @param string $event_string name of event
    * @param callable $handler (optional)
    * @param mixed $event_data (optional)
    * 
    * @see $name
    * @see $data
    * @see $handler
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    */
   final protected function set_params($event_name,callable $handler=NULL,$event_data=NULL) {
      if (empty($event_name)) throw new event\exception\invalid_name\cannot_be_empty();
      
      if (!is_string($event_name)) throw new event\exception\invalid_name\is_not_string();
      
      $this->name = $event_name;
      $this->data = $event_data;
      $this->handler = $handler;
      
   }



}