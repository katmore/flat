<?php
/**
 * \flat\data\rules definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\data;
/**
 * rules for mapping a \flat\data object
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class rules {
   public $data;
   public $key_prefix;
   public $default;
   public $flags;
   
   /**
    * parameters for \flat\data::__construct() to invoke \flat\data::map() with
    * 
    *    signature __construct(array $data,$key_prefix=NULL,$default=NULL,array $flags=NULL)
    *    or array('data'=>$data,'key_prefix'=>$key_prefix, etc...)
    * 
    * @see \flat\data::map()
    * @uses \flat\data::__construct()
    */
   public function __construct() {
      $param = new \flat\core\util\map(
         func_get_args(),
         (array) $this
      );
      //var_dump($param);die('die: data/rules');
      foreach($param as $p=>$v) $this->$p=$v;
   }
}
























