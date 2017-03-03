<?php
/**
 * class definition 
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
namespace flat\event;

use \flat\db\driver\rabbitmq as driver;

abstract class alert 
   implements 
      \flat\event\triggerable,
      \flat\event\listenable
{
   /**
    * 
    * @return \flat\db\driver\mongo\crud
    */
   protected static function _get_crud() {
      $r = new \ReflectionClass(get_called_class());
      $crud = NULL;
      if ($r->implementsInterface('\flat\event\alert\mongo_crud_provider')) {
         $crud = static::get_alert_mongo_crud();
      }
      if (empty($crud)) {
         $param = crud::params_from_config_ns(get_called_class()."/alert");
         $crud = new crud($param);
          
      }
      return $crud;
   }
   
   /**
    * provides rabbitmq driver connection
    * @return \flat\db\driver\rabbitmq
    */
   protected static function _get_driver() {
      $r = new \ReflectionClass(get_called_class());
      $driver = NULL;
      if ($r->implementsInterface('\flat\event\alert\driver_provider')) {
         $driver = static::get_alert_driver();
      }
      if (empty($driver)) {
         
         $param = driver::params_from_config_ns(get_called_class()."/alert");
         $driver = new driver($param);
         
      }
      return $driver;
   }
   final public static function get_queue($sub_chan=null) {
      return static::_get_queue($sub_chan);
   }
   /**
    * provides alert queue name
    */
   protected static function _get_queue($sub_chan=NULL) {
      $r = new \ReflectionClass(get_called_class());
      $chan = NULL;
      if ($r->implementsInterface('\flat\event\alert\queue_provider')) {
         $chan = static::get_alert_queue();
      }
      if (empty($chan)) {
         $chan = get_called_class();
         $chan = str_replace("flat\\event\\",'',$chan);
         $chan = str_replace("flat\\app\\event\\",'',$chan);
      }
      if (!empty($sub_chan)) {
         $chan .= ".$sub_chan";
      }
      return $chan;
   }
   
   /**
    * triggers alert event
    *    sends message on {queuename}
    *    sends message on {queuename}.listener
    *    
    * @param mixed $trigger_data (optional) data passed to handler and listeners
    * @param array $callback (optional) assoc array of event callbacks:
    *    callable $callback['success'] invoked after successful alert publish
    *    callable $callback['handler'] sets event handler like \flat\core\factory::set_handler().
    *    callable $callback['listener'] adds listener like \flat\core\factory::add_listener().
    * @return event uuid
    */
   public static function trigger($alert_data=NULL,array $callback=NULL) {
      if (!empty($callback['listener']) && is_callable($callback['listener'])) {
         self::add_listener($callback['listener']);
      }
      $success = null;
      if (!empty($callback['success']) && is_callable($callback['success'])) {
         $success = $callback['success'];
      }
      
      $uuid = \flat\core\uuid::get_v4();
      $data = [
         'alert'=>$alert_data,
         'uuid'=>$uuid,
         'meta'=>$meta,
         'iso_timestamp'=>date("c"),
         'unix_timestamp'=>microtime(true)
      ];
      $payload = json_encode($data);
      
      /*
       * find alert subscriptions
       *    determine queue's necessary to publish to
       */
      $driver->publish(self::_get_queue("subscription:$uuid"),$payload);
      
      /*
       * find alert listeners
       *    determine queue's necessary to publish to
       */
      $driver->publish(self::_get_queue("listener:$uuid"),$payload);
      
      /*
       * 
       */
   }
   
   /**
    * creates an alert subscription
    * 
    * @param int|bool $ttl (optional) defaults to infinite. 
    *    subscription time-to-live in seconds. bool false means infinite.
    *    ignored unless value is unsigned integer or bool false.
    * 
    * @return \flat\event\alert\subscription_data
    */
   public static function subscribe($ttl=false) {
      $crud = self::_get_crud();
      if (is_int($ttl) && $ttl>0) {
         $expires = time() + $ttl;
      } else {
         $expires = false;
      }
      $crud->create($subscription = new alert\subscription_data([
         'alert_type'=>str_replace("flat\\app\\event\\", "", get_called_class()),
         'uuid'=>\flat\core\uuid::get_v4(),
         'expires'=>$expires,
      ]),['sub_collection'=>'subscription']);
      return $subscription;
   }
   
   public static function get_subscription_log($uuid) {
      
   }
   
   public static function get_log() {
      
   }
   
   /**
    * infinitely blocking, invokes callback each time alert is triggered.
    */
   public static function add_subscription_listener($uuid,callable $callback) {
      
   }
   
   const listener_ttl = 180; //should always be greater than alert::listener_timeout
   const listener_timeout = 120;
   /**
    * infinitely blocking, invokes callback each time alert is triggered.
    */
   public static function add_listener(callable $callback) {
      $timeout = self::listener_timeout;
      $ttl = self::listener_ttl;
      
      $crud = self::_get_crud();
      $expires = time() + $ttl;
      
      $crud->create($listener = new alert\listener_data([
         'alert_type'=>str_replace("flat\\app\\event\\", "", get_called_class()),
         'uuid'=>\flat\core\uuid::get_v4(),
         'expires'=>$expires,
      ]),['sub_collection'=>'listener']);
      
      $queue = self::_get_queue("listener:".$listener->uuid);
      
      $chan = self::_get_driver()->get_channel();
      $chan->queue_declare($queue,false,false,false,false);
      
      $chan->basic_consume(
         $queue, '', false, true, false, false, 
         function($msg) use($callback) {
            $data = json_decode($msg->body);
            //var_dump($payload);die('flat/event/alert (die)');
            $callback($data);
      });
      
      while(count($chan->callbacks)) {
         try {
            $chan->wait(null,false,$timeout);
         } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
            /*
             * freshen expiration
             */
            $expires = time() + $ttl;
            $crud->update(['uuid'=>$listener->uuid],['$set'=>['expires'=>$expires]]);
            continue;
         }
      }
   }
//    final public static function set_handler(callable $handler) {
//       self::_enforce_deps();
//       $driver = self::_get_driver();
//       $chan = $driver->get_channel();
//       $chan->queue_declare(self::_get_queue(),false,false,false,false);
//       $chan->basic_consume(self::_get_queue(), '', false, true, false, false, function($msg) use($handler) {
//          $data = json_decode($msg->body);
//          //var_dump($payload);die('flat/event/alert (die)');
//          $handler($data);
//       });
//       while(count($chan->callbacks)) {
//          $chan->wait();
//       }
//    }
   
}


















