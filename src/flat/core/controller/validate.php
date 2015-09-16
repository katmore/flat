<?php
/**
 * class definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (C) 2012-2015  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat\core\controller;
class validate extends \flat\core\collection implements \flat\core\input\consumer {
   public function set_input(\flat\input $input) {
      //$col = $this->__col;
      $mbr = array();
      foreach ( $this->_col->range() as $validate ) {
         if (isset($input->{$validate->data})) {
            
         }
      }
   }
   private $_col;
   public function __construct() {
      $param = new \flat\core\util\map(
         func_get_args(),
         array(
            'input',
            'collection'
         )
      );
      /*
       * type enforcement for paramters
       */
      if (!is_a($param->collection,"\\flat\\core\\collection")) 
         throw new validate\exception\bad_param(
            "collection",
            "must be \\flat\\core\\collection"
         );

      if (!is_a($param->input,"\\flat\\input"))
         throw new validate\exception\bad_param(
            "input",
            "\\flat\\input"
         );

      $this->_col = $param->collection;
      $this->set_input($param->input);
   }
}