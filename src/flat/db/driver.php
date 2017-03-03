<?php
/**
 * \flat\db\driver interface 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db;
/**
 * parent interface for db operations abstraction
 * 
 * @see \flat\db\driver\op\create
 * @see \flat\db\driver\op\read
 * @see \flat\db\driver\op\update
 * @see \flat\db\driver\op\delete
 * 
 * @package    flat\db
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface driver {
   /**
    * database connection
    * 
    * @return mixed
    */   
   public function connect(array $param=NULL);
   /**
    * 
    */
   public function command(array $param=NULL);
   // public function create(array $param=NULL);
   // public function read(array $param=NULL);
   // public function update(array $param=NULL);
   // public function delete(array $param=NULL);
}