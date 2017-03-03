<?php
/**
 * \flat\core\controller\view class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\controller;
/**
 * view controller
 * 
 * @package    flat\view
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class view implements \flat\core\resolver, \flat\core\app\event\factory\consumer,\flat\core\controller {
   /**
    * return design class
    * 
    * @return string
    */
   abstract protected function _get_design();
   /**
    * @var \flat\core\event\factory $event
    */
   private $event;
   /**
    * part of the \flat\core\app\event\factory\consumer interface
    * 
    * @param \flat\core\event\factory $event shared event factory
    * @return void
    * @uses $event
    */
   public function set_event_factory(\flat\core\event\factory &$event) {
      $this->event = $event;
   }
   /**
    * 
    *    
    * @param string $resource
    * @return void
    * @uses $event triggers event when view is resolved from given resource
    * @see \flat\core\config::get() retrieves value from config path: "design/basedir"
    * @see \flat\core\event\factory::trigger_event() triggers view_resolved event
    * @uses \flat\core]controller\view\data provides instance to view_resolved event
    * @see \flat\core\resolver interface
    */
   public function set_resource($resource) {
      /**
       * @var string[] $route route to loop backwards through to try to find corresponding design
       */
      $route = explode(
         "/",
         str_replace(
            "/flat/design",
            "",
            str_replace("\\","/",$this->_get_design())."/$resource"
         )
      );
      
      //for ($i=0;$i<count($resolved);$i++) {
      $i=0;
      while(count($route)) {
         /**
          * @var string $path concatonate full filename to check for existence
          */
         $path = \flat\core\config::get("design/basedir",array('not_found_exception'=>true)).implode("/",$route).".php";
         if (file_exists($path) && is_readable($path)) {
            /**
             * trigger view_resolved event because we found view file
             * @see $this->event
             */
            $this->event->trigger_event(
               "view_resolved",
               new view\data(
                  array(
                     'path'=>$path,
                     'resolved'=>"\\flat\\design".implode("\\",$resolved),
                     'resource'=>$resource
                  )
               )
            );
         }
         /**
          * @var string[] next check for one level less design path
          */
         array_pop($route);
         $i++;
      }
   }
}