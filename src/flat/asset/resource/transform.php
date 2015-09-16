<?php
/**
 * \flat\asset\resource\transform definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\asset\resource;
/**
 * facilitates setting resource value of \flat\asset object outside constructor
 * 
 * @package    flat\asset
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface transform {
   /**
    * the return value (as long as it is a string) will be set as the asset's resource
    * 
    * @return string
    * @see \flat\asset\resource\transform part of the resource transform interface
    * @uses \flat\core\controller\asset::__construct()
    */
   public function get_resource_transform($resource);
}