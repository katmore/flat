<?php
/**
 * \flat\report class definition 
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
namespace flat;
/**
 * facilitate report generation from a \flat\app\db definition
 * 
 * @package    flat\report
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @abstract
 */
abstract class report implements \flat\core\app , \flat\core\routable {
   /**
    * @var \flat\report\meta\data|null $meta
    * @see \flat\report::__construct()
    * @see \flat\report::get_meta()
    */
   private $meta;
   /**
    * retrieve report metadata
    *    returns null if no metadata exists on report
    * @return \flat\report\meta\data|null
    * @see \flat\report\meta\data
    * @see \flat\report::__construct()
    * @final
    */
   final protected function _get_meta() {
      return $this->meta;
   }
   /**
    * prepare report
    * 
    * @param string|array $source (optional) if empty, defaults to corresponding 
    *    class name in \flat\app\db. if $source is string of \flat\db 
    *    child class name, will use that as source. if $source is array, will 
    *    use that as collection by mapping it. if $source is 
    *    \flat\data\collection class, will use that as source. 
    * @final
    * @throws \flat\report\exception\invalid_source
    * 
    * @see \flat\db
    * @see \flat\report\meta\data
    */
   final public function __construct($source=null) {
      if ($this instanceof \flat\report\meta) {
         if (is_a($meta = $this->get_meta(),"\\flat\\report\\meta\\data")) $this->meta = $meta;
         $source = $this->meta->source;
      }
      // if (empty($source)) {
         // $given = false;
         // if (!empty($input)) {
            // if (is_string($input)) {
               // if (is_a($input,"\\flat\\db",true)) {
                  // $source = $input;
               // }
            // }
         // } 
         // if (empty($source)) {
            // $r = new \ReflectionClass($this);
            // $source = "\\flat\\app\\db\\".\flat\app\meta::ns."\\".$r->getShortName();
         // }
         // $this->meta = new \flat\report\meta\data(
            // array("source"=>$source)
         // );         
      // }
      if (!is_a($source,"\\flat\\db",true)) throw new report\exception\invalid_source(
         "resolved source must be \\flat\\db class"
      );

   }
}