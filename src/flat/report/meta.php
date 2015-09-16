<?php
/**
 * \flat\report\meta definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\report;
/**
 * report controller looks for report metadata (most importantly, the source) 
 *    in class definition; ignore source argument passed to the constructor 
 *    and not attemping to resolve a \flat\db source.
 * 
 * @package    flat\report
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface meta extends \flat\core\data {
   public function get_meta();
}