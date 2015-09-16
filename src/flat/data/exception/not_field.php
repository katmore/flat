<?php
/**
 * \flat\data\exception\not_field class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * BY ACCESSING THE CONTENTS OF THIS SOURCE FILE IN ANY WAY YOU AGREE TO BE 
 * BOUND BY THE PROVISIONS OF THE "SOURCE FILE ACCESS AGREEMENT", A COPY OF 
 * WHICH CAN IS FOUND IN THE FILE 'LICENSE.txt'.
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\data\exception;
/**
 * thrown when some method is provided data field that does not exist
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class not_field extends \flat\data\exception {
   /**
    * provides name of non-existant field causing the error
    * @return string
    */
   public function get_field_name() {
      return $this->_field;
   }
   private $_field;
   /**
    * @param string $field name of field causing the error
    */
   public function __construct($field) {
      $this->_field= $field;
      parent::__construct("data field not found: $field");
   }
}
















