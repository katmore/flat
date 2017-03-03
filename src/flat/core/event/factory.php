<?php
/**
 * \flat\core\event\factory definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\event;
/**
 * fevent actory
 * 
 * @package    flat\event
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class factory extends \flat\core\factory {
   
   private $event;
   /**
    * sets a data keyval for given event
    * 
    * @param string $key data key to set
    * @param mixed $val data value
    * @param bool $no_overwrite if true, will only set value to key 1 time
    * 
    * @return void
    * @throws \flat\core\event\exception\invalid_key\is_not_string if key not string
    * @throws \flat\core\event\exception\invalid_key\cannot_be_empty if key empty
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    */
   public function set_data($event_name,$key,$value,$no_overwrite=false) {
      $event = $this->get_event_or_add($event_name);
      $event->set_data($key,$value,$no_overwrite);
   }
   /**
    * adds callback function for given $event_name, invoked after any handler.
    * multiple listeners can exist for given $event_name.
    * 
    * @param string $event_name event name
    * @param callable $listener callback performed when event is called (triggered)
    * @return void
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    */
   public function add_listener($event_name,callable $listener) {
      
      $event = $this->get_event_or_add($event_name);
      $event->add_listener($listener);
   }
   
   /**
    * sets callback function for given $event_name, overriding any existing handler.
    * 
    * @param string $event_name event name
    * @param callable $handler callback performed when event is called (triggered)
    * @return void
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    */
   public function set_handler($event_name,callable $handler) {
      if ($event = $this->get_event_or_add($event_name))
         $event->set_handler($handler);
   }
   /**
    * triggers an event
    * 
    * @param string $event_name
    * @param mixed $trigger_data (optional) data passed to handler and listeners
    * @param array $callback (optional) assoc array of event callback:
    *    callable $callback['handler'] sets event handler like \flat\core\factory::set_handler(),
    *    callable $callback['listener'] adds listener like \flat\core\factory::add_listener()
    * @return void
    */
   public function trigger_event($event_name,$trigger_data=NULL,array $callback=NULL) {
      if (isset($callback['listener'])) {
         //var_dump("listener");
         if (is_callable($callback['listener'])) $this->add_listener($event_name, $callback['listener']);
      }
      if (isset($callback['handler'])) {
         if (is_callable($callback['handler'])) $this->set_handler($event_name, $callback['handler']);
      }
      
      $event = $this->get_event($event_name);

      if (is_a($event,"\\flat\\core\\event\\triggerable")) {
         $event->trigger($trigger_data);
         
      }
   }
   
   private function _set_event($event_name,\flat\core\event $event) {
      
      if (!is_array($this->event)) $this->event = array();
      $this->event[$event_name] = $event;
      
   }
   
   private function get_event_or_add($event_name) {
      if (!isset($this->event[$event_name] )) {
         $this->add_event($event_name);
      }
      return $this->event[$event_name];
   }
   
   private function get_event($event_name) {
      if (isset($this->event[$event_name] )) return $this->event[$event_name];
   }
   
   /**
    * Function: add_event
    * Purpose: add event with handler
    * 
    * @param string $event_name name for the event
    * @param callable $options['handler'] OPTIONAL callback when event occurs
    * @param mixed $options['data'] OPTIONAL data that will be passed to event handler
    * @param \flat\event $options['type'] OPTIONAL type defaults to \flat\event\triggerable
    * @param bool $options['ignore_if_exists'] OPTIONAL wheather to throw exception if event with same name already exists
    * 
    * @return bool TRUE if successful, otherwise FALSE
    * 
    * @throws \flat\event\already_exists when event already exists and $options['ignore_if_exists'] is not bool TRUE
    * @throws \flat\event\type_not_found when type given is bad
    * @throws \flat\core\event\exception\invalid_name\is_not_string if event name not string
    * @throws \flat\core\event\exception\invalid_name\cannot_be_empty if event name empty
    * 
    */
   public function add_event($event_name,array $options=NULL) {
      $option = (object) array(
         'handler'=>NULL,
         'data'=>NULL,
         'type'=>"\\flat\\core\\event\\triggerable",
         'ignore_if_exists'=>false
      );
      foreach ($option as $key=>&$val) if (isset($options[$key])) $val = $options[$key];
      
      //echo $event_name."\n";var_dump($option);
      if ($event = $this->get_event($event_name)) {
      
            if ($option->{'ignore_if_exists'}!==true) {
               throw new factory\exception\already_exists(
                  $event_name
               );
            } else {
               return false;
            }
         
      } else {
         
         $event_class = $option->{'type'};
         
      }
      
      if (!class_exists($event_class)) {
         throw new factory\exception\type_not_found(
            $option->{'type'},
            $event_class
         );
      }         
      
      $this->_set_event(
         $event_name,
         new $event_class($event_name, $option->handler, $option->data)
      );
      
      return true;
   }

}