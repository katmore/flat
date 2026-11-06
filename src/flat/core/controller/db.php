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
namespace flat\core\controller;
abstract class db {
   final public static function load_read(array $param=NULL) {
      if (self instanceof \flat\db\op\read) {
         /* CONSERVATIVELY DISABLED unreachable read body on PHP 7.2.
          * The guard tests the literal constant self, not an object or the
          * called class. PHP 7.2 constants cannot hold objects. Preserve the
          * guard's existing undefined-constant warning and NULL result until
          * the read-versus-op_read contract is settled. Original body:
          *          $q = array();
          *          if (!empty($param['key'])) {
          *             $q['key'] = $param['key'];
          *          }
          *          $read = new static($q);
          *          $data = $read->op_read();
          *          return $data;
          */
      }
      return NULL;
   }
   final public function __construct(array $param=NULL) {
      /*
       * find what kind of operation
       * 
       * find what object to map to
       */
      if ($this instanceof \flat\db\driver) {
         /*
          * connect if driver
          */
         $this->connect($param);
      }
      //\flat\db\driver\operation
      
      
   }
}