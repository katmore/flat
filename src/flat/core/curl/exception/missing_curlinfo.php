<?php
/**
 * class definition
 *
 * PHP version >=7.1
 *
 * Copyright (c) 2012-2017 Doug Bird.
 *    All Rights Reserved.
 *
 * @license The MIT License (MIT) http://opensource.org/licenses/MIT
 * @license GNU General Public License, version 3 (GPL-3.0) http://opensource.org/licenses/GPL-3.0
 * @link https://github.com/katmore/flat
 */
namespace flat\core\curl\exception;
/**
 * curl_getinfo() returned no transfer information for a completed request.
 */
class missing_curlinfo extends \flat\core\curl\exception {
   public function __construct() {
      parent::__construct("curl_getinfo returned no transfer information");
   }
}
