<?php
/**
 * \flat\cloud\aws\bad_client class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud\aws;
/**
 * controller unable to get AWS client object
 * 
 * @package    flat\cloud\aws
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class bad_client extends \flat\cloud\aws\exception {
   
   public function __construct($details="") {
      if (!empty($details)) $details = ": $details";
      parent::__construct("bad AWS client".$details,$this->_value_to_code($details));
   }
}






