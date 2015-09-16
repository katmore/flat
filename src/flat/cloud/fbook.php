<?php
/**
 * \flat\fbook definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud;
/**
 * facebook API client application class
 * 
 * @package    flat\fbook
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
abstract class fbook extends \flat\core\data{
   abstract protected function get_client();
}