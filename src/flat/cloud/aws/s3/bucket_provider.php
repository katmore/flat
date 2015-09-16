<?php
/**
 * \flat\cloud\aws\client_provider interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud\aws\s3;
/**
 * indicates to controller this class can provide an AWS client
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface bucket_provider extends \flat\cloud\aws\client_provider {
   public function get_s3_bucket();
}