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
namespace flat\event\alert;
class listener extends \Thread {
   private $_channel;
   private $_client;
   private $_destroy;
   public function __construct($channel_name,$rabbitmq_client) {
      $this->_channel = $channel_name;
      $this->_client = $rabbitmq_client;
   }
   public function run() {
      /*
       * connect to "this->channel_name" using this->rabbitmq_client
       */
      
      /*
       * in infinite loop?...
       *    if destroy flag given, disconnect from chan
       *       and let thread be 'done'
       */
      if ($this->_destroy) {
         /*
          * disconnect
          */
         $this->synchronized(function($thread){
            $thread->done = true;
            $thread->notify();
         }, $this);         
      }
   }
   
   public function __destruct() {
      /** cause this thread to wait **/
      $this->synchronized(function($thread){
         if (!$thread->done) {
            $this->_destory = true;
            $thread->wait();
         }
      }, $this);
   }
}