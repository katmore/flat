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
namespace flat\db\driver;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class rabbitmq extends \flat\db implements 
   \flat\db\driver,
   \flat\core\publisher 
{
   /**
    * establishes rabbitmq connection
    * 
    * @param AbstractConnection $param['host'] required
    * @param string $param['port'] (optional)
    * @param string $param['user'] (optional)
    * @param bool $param['password'] (optional)
    * @param string $param['vhost'] (optional)
    * @param bool $param['insist'] (optional)
    * @param string $param['login_method'] (optional)
    * @param null $param['login_response'] (optional)
    * @param string $param['locale'] (optional)
    * @param int $param['connection_timeout'] (optional)
    * @param int $param['read_write_timeout'] (optional)
    * @param null $param['context'] (optional)
    * @param bool $param['keepalive'] (optional)
    * @param int $param['heartbeat'] (optional)
    * 
    * @return void
    */
   public function connect(array $param=NULL) {
      if ($this instanceof \flat\db\driver\rabbitmq\connection_params) {
         
         $param = self::_param_to_conn_param();
         $conn = $this->get_rabbitmq_connection_params();
         //var_dump($conn);die('is connection params');
         if (is_array($conn)) {
            foreach ($param as $k=>&$v) {
               if (isset($conn[$k])) {
                  $v = $conn[$k];
               }
            }
         }
      }
      
      $this->_param = $param;
      
      return $this->get_connection();
   }
   /**
    * @param string $param['publish'] @see rabbitmq::publish()
    */
   public function command(array $param=NULL) {
      if (isset($param['publish'])) {
         return $this->publish($param['publish']);
      }
   }
   /**
    * @return \PhpAmqpLib\Channel\AMQPChannel
    */
   public function get_channel() {
      return self::_load_channel($this->_param);
   }
   

   /**
    * params for declaring queue
    *
    * @param string $param['queue']
    * @param bool $param['passive']
    * @param bool $param['durable']
    * @param bool $param['exclusive']
    * @param bool $param['auto_delete']
    * @param bool $param['nowait']
    * @param null $param['arguments']
    * @param null $param['ticket']
    * 
    * @return array
    * 
    * @see \PhpAmqpLib\Channel\AMPQChannel::queue_declare()
    */
   private static function _param_to_queue_param(array $param=NULL) {
      $queue = [
         'queue' =>'',
         'passive' =>false,
         'durable' =>false,
         'exclusive' =>false,
         'auto_delete' =>true,
         'nowait' =>false,
         'arguments' =>null,
         'ticket' =>null
      ];  
      foreach ($queue as $k=>&$v) {
         if (isset($param[$k])) $v = $param[$k];
      }
   }
   
   /**
    * Publishes a message. Returns the message body that was used.
    * 
    * @param string|array $param queue name or assoc array of parameters: 
    *    string|array $param['queue'] queue name or AMPQChannel::queue_declare() parameters. ignored if $param['routing_key'] is set.
    *    string $param['msg'] (optional) message body, ignored if $param['AMPQMessage'] is set.
    *    AMQPMessage $param['AMPQMessage'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    string $param['exchange'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    string $param['routing_key'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    bool $param['mandatory'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    bool $param['immediate'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    null $param['ticket'] (optional) @see PhpAmqpLib\Channel\AMPQChannel::basic_publish()
    *    
    * @param string $msg_body message body
    * 
    * @return string
    * 
    * @see \flat\core\publisher part of the publisher interface
    */
   public function publish($param=NULL,$msg_body=NULL) {
      //var_dump($param);die( "flat/db/driver");
      if (isset($param['AMPQMessage'])) {
         $msg = $param['AMPQMessage'];
      } else {
         if (empty($param['msg'])) {
            if (!empty($msg_body)) {
               $msg = new AMQPMessage($msg_body); 
            } else {
               throw new rabbitmq\exception\bad_param("msg","cannot be empty");
            }
         } else {
            $msg = new AMQPMessage($param['msg']);
         }
      }
      
      $chan = $this->get_channel();
      
      if (!is_array($param)) {
         $routing_key = $param;
      } else {
         if (isset($param['routing_key'])) {
            $routing_key  = $param['routing_key'];
         } elseif (!isset($param['queue'])) {
            throw new rabbitmq\exception\bad_param("queue","must be a string or array");
         } else {
            if (is_array($param['queue'])) {
               $queue_param = self::_param_to_queue_param($param['queue']);
            } else {
               $queue_param = self::_param_to_queue_param(['queue'=>$param['queue']]);
            }
            $chan->queue_declare(
               $queue_param['queue'],
               $queue_param['passive'],
               $queue_param['durable'],
               $queue_param['exclusive'],
               $queue_param['auto_delete'],
               $queue_param['nowait'],
               $queue_param['arguments'],
               $queue_param['ticket']
            );
            $routing_key = $queue_param['queue'];
         }
      }
      //var_dump($msg);die('flat/db/driver/rabbitmq');
      $chan->basic_publish(
        $msg,
        (empty($param['exchange']))? '' : $param['exchange'],
        $routing_key,
        (empty($param['mandatory']))? false : $param['mandatory'],
        (empty($param['immediate']))? false : $param['immediate'],
        (empty($param['ticket']))? null : $param['ticket']
      );
      
      return $msg->body;
   }
   
   /**
    * provides a connection object based on previously obtained connection parameters.
    *    uses a cached connection, or establishes new connection as needed.
    *
    * @return \PhpAmqpLib\Connection\AMQPConnection
    *
    *
    * @throws \flat\db\driver\rabbitmq\exception\missing_connection_params
    */
   public function get_connection() {
      if (!$this->_param) throw new \flat\db\driver\rabbitmq\exception\missing_connection_params();
      return self::_load_conn($this->_param);
   }
   
   private $_param;
   
   /**
    * @var \PhpAmqpLib\Connection\AMQPConnection[] array of cached connection objects,
    *    cached by hash of connection parameters
    */
   private static $_conn;
   private static function _param_to_hash(array $param=NULL) {
      if (!$param) return md5("");
      return md5(json_encode(self::_param_to_conn_param($param)));
   }
   private static $_chan;
   /***
    * @return array
    */
   final public static function params_from_config_ns($configns) {
      $param = self::_param_to_conn_param([]);
      foreach ($param as $k=>&$v) {
         try {
            $v = \flat\core\config::get($configns."/$k");
         } catch (\flat\core\config\exception\key_not_found $e) {
            
         }
      }
      return $param;
   }
   final protected static function _load_channel(array $param=NULL) {
      
      /*
       * map parameters
       */
      $conn = self::_param_to_conn_param($param);
      
      /*
       * compute param hash
       */
      $hash = self::_param_to_hash($conn);
      
      /*
       * use established channel object if present
       */
      if (isset(self::$_chan[$hash])) return self::$_chan[$hash];
       
      /*
       * load connection object
       */
      $conn = self::_load_conn($param);
      
      /*
       * establish and return channel object
       */
      return self::$_chan[$hash] = $conn->channel();
      
   }
   /**
    * @param AbstractConnection $param['host'] required
    * @param string $param['port'] (optional)
    * @param string $param['user'] (optional)
    * @param bool $param['password'] (optional)
    * @param string $param['vhost'] (optional)
    * @param bool $param['insist'] (optional)
    * @param string $param['login_method'] (optional)
    * @param null $param['login_response'] (optional)
    * @param string $param['locale'] (optional)
    * @param int $param['connection_timeout'] (optional)
    * @param int $param['read_write_timeout'] (optional)
    * @param null $param['context'] (optional)
    * @param bool $param['keepalive'] (optional)
    * @param int $param['heartbeat'] (optional)
    */
   final protected static function _load_conn(array $param=NULL) {
      /*
       * map parameters
       */
      $conn = self::_param_to_conn_param($param);
      
      /*
       * compute param hash
      */
      $hash = self::_param_to_hash($conn);
      
      /*
       * enforce 'host' parameter exists
       */
      if (empty($conn['host'])) throw new rabbitmq\exception\missing_connection_params('host');
      
      /**
       * return already established connection
       * @todo perform test on connection to see if timed or errored out
       */
      if (isset(self::$_conn[$hash])) return self::$_conn[$hash];
      //var_dump($conn);die('db driver rabbitmq (die)');
      $connection = self::$_conn[$hash] = new AMQPConnection(
         $conn['host'],
         $conn['port'],
         $conn['user'],
         $conn['password'],
         $conn['vhost'],
         $conn['insist'],
         $conn['login_method'],
         $conn['login_response'],
         $conn['locale'],
         $conn['connection_timeout'],
         $conn['read_write_timeout'],
         $conn['context'],
         $conn['keepalive'],
         $conn['heartbeat']
      );
   }
   /**
    * @see \flat\db\driver\rabbitmq::_load_conn
    */
   private static function _param_to_conn_param(array $param=NULL) {
      //'localhost', 5672, 'guest', 'guest'
      $conn = array(
         'host'=>'',
         'port'=>5672,
         'user'=>'guest',
         'password'=>'guest',
         'vhost'=>'/',
         'insist'=> false,
         'login_method'=>'AMQPLAIN',
         'login_response'=>null,
         'locale'=>'en_US',
         'connection_timeout'=>3,
         'read_write_timeout'=>3,
         'context'=> null,
         'keepalive'=>false,
         'heartbeat'=>0,
      );
   
      if ($param) foreach ($conn as $key=>&$val) {
         if (isset($param[$key])) {
            if (is_string($param[$key])) {
               $val  = trim($param[$key]);
            } else {
               $val=$param[$key];
            }
         }
      }
   
      return $conn;
   }
}