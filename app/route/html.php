<?php
/**
 * \flat\app\route\html class 
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
/**
 * namespace
 */
namespace flat\app\route;
/**
 * Applys a route configuration by running a \flat\route\factory; this is
 * an ideal way to configure and deploy a flat application (\flat\app) as a 
 * website (\flat\tmpl).
 *  
 * It is designed be instanitated by an 'entry point' controller, such as those 
 * in \flat\deploy. Note that there is nothing limiting an 'entry point' controller
 * (whatever instantiates this class) from being outside the flat framework.
 * 
 * Also, note that there is nothing requiring an 'entry point' controller to use
 * a route factory (such as this class) to initiate and deploy a flat application.
 * 
 * Lastly, note that usage of \flat\deploy is itself optional, as it's provided
 * as a convenience (and idealized model) to group deployment configuration,
 * 'entry points', and error handling in one place.  
 * 
 * @see \flat\deploy\html
 * 
 * @package    flat\appExample
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class html extends \flat\route\factory implements \flat\core\app,\flat\route\base {
   public function get_base() {
      return "/";
   }
      /**
       *  
       * summary of the HTML routing:
       * 
       * explained in further detail below
       * @uses \flat\route\rule
       * @uses \flat\core\controller\route
       * @uses \flat\core\controller\html
       */
   public function __construct() {
      
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\meta\app',
            'weight'=>0,
            'iterate'=>false,
            'traverse'=>true,
         ))
      );
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\meta\tmpl',
            'weight'=>1,
            'iterate'=>false,
            'traverse'=>true,
         ))
      );
      
      /*
       * WEIGHT 2: (single rule)
       * always iterate through all matching helpers:
       * 
       *    resource is typically the part of the URL after flat.php
       *       for example, given the following senerio:
       *          \flat\route\rule
       *             ->ns = '\flat\app\helper\tmpl',
       *             ->iterate = true,
       *             ->weight = 0,
       *             ->ignore_resource != true
       *          browser url: 'http://example.com/flat.php/mysite/page/something'
       *       the resource would be: "mysite/page/something"
       *    the router tries to find and instantiate the class named:
       *       '\flat\app\helper\mysite\page\something'
       *    even if it finds it, because 'iterate' is true... it will also try to find and 
       *    instantiate class named:
       *       '\flat\app\helper\mysite\page' 
       *          --and also--
       *       '\flat\app\helper\mysite'
       *    (but will not try to find and instantiate '\flat\app\helper' because
       *    'ignore_resource' != true)
       *        
       *       
       *       
       */
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\helper\tmpl',
            'weight'=>2,
            'iterate'=>true,
            'traverse'=>true,
            'cycles'=>2
         ))
      );
      
      /* WEIGHT 3: rule with priority 0
       * try to find a matching template (tmpl)
       *    because tmpl weight is 1, and helper above is 0... route controller
       *    will try and resolve the template ns, even if a helper class was found. 
       *       for example, given the following senerio:
       *          \flat\route\rule
       *             ->ns = '\flat\app\tmpl',
       *             ->iterate = false,
       *          browser url: 'http://example.com/flat.php/mysite/page/something'
       *       the resource would be: "mysite/page/something"
       *       the router tries to find and instantiate the class named:
       *          '\flat\app\tmpl\mysite\page\something'
       *       because 'iterate' is false... it WOULD NOT try to find and 
       *       instantiate class named:
       *          '\flat\app\tmpl\mysite\page' (or any further up route heirarchy) 
       */
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\tmpl',
            'weight'=>3,
            'iterate'=>false,
            'traverse'=>false,
         ))
      );
      
      /* WEIGHT 3: rule with priority 1
       * because the same weight as prior route entry, 
       *    it will be ignored unless no class was resolved (ie: not found matching tmpl class).
       * 
       * also, because "ignore_resource" is true, this route will resolve
       * exactly to a class with same name as the route's 'ns' value:
       *    "\\flat\\app\\tmpl\\notfound"
       * 
       */
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\tmpl\notfound',
            'weight'=>3,
            'iterate'=>false,
            'traverse'=>false,
            'ignore_resource'=>true
         ))
      );
   }
}













