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
namespace flat\asset;
abstract class twitter extends \flat\asset 
implements 
   \flat\asset\base,
   \flat\asset\resource\transform
{
   const twitter_url = "https://twitter.com";
   /**
    * retrieves twitter username, needed for forming twitter url
    * 
    * @return string
    */
   abstract protected function _get_twitter_username();
   
   /**
    * Retrieves the base for resource resolution. Part of the asset base 
    *    template. 
    * @see \flat\asset\base 
    */
   public function _get_base() {
      return self::twitter_url."/".$this->_get_twitter_username();
   }
   /**
    * the return value (as long as it is a string) will be set as the asset's resource
    * 
    * @return string
    * @see \flat\asset\resource\transform part of the resource transform interface
    * @uses \flat\core\controller\asset::__construct()
    */
   public function get_resource_transform($resource) {
      return "";
   }
}