<?php
/**
 * \flat\route\resource\consumer interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\route\resource;
/**
 * controller will provide a resolved class with original resource
 *    or something like that
 * 
 * @package    flat\route
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface consumer {
   public function set_resource($resource);
}