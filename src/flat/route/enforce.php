<?php
/**
 * \flat\route\enforce interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\route;
/**
 * if implemented on a resovled class the route controller will use this
 *    interface to determine enforcement status.
 * 
 * @package    flat\route
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface enforce extends \flat\core\controller\route\enforce {}