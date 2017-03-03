<?php
/**
 * \flat\core\controller\route class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\core\controller;
/**
 * parent class which resolves a given resource into objects based on rules defined in 
 *    \flat\app\route also, each object may have various processes invoked 
 *    according to a resolved object's defined interfaces. 
 * 
 * @package    flat\route
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * 
 */
class route implements \flat\core\controller, \flat\core\collectable {
   
   /**
    * resolves application object(s) from given resource.
    * 
    * @param String $resource resource to resolve
    * @param array $input (optional) input to provide to resolved objects
    * @param callable $restful_status_callback (optional) function to invoke 
    *    when resolved object has a restful status. callback signature: function($status);
    *    
    * 
    * 
    * @throws \flat\core\controller\route\exception\no_route if 
    *    \flat\app\route does not have route factory correlating to the 
    *    namespace shortName of this \flat\core\controller\route\__NAMESPACE_SHORTNAME__
    *    (shortName is name of the class extending the \flat\core\controller\route)
    * @throws \flat\core\exception child of \flat\core\exception depending on the error
    * 
    * @uses \flat\core\routable interface indicates to \flat\core\controller\route that 
    *    class is a candidate to be resolved from a resource. 
    * 
    * @uses \flat\core\resolver will pass the $resource 
    *    arg value to a resolved object that implements this interface.
    * 
    * @uses \flat\core\app\event\factory\consumer will pass 
    *    shared \flat\core\app\event\factory object to any resolved objects 
    *    that implements this interface.
    * 
    * @uses \flat\core\input\consumer will pass the $input 
    *    arg value to any resolved objects that implements this interface.
    * 
    * @uses \flat\core\controller\route\restful_status will call 
    *    'restful_status_callback' to any resolved objects that implements this interface.
    * 
    */
   public function __construct(array $params) {
      $r = new \ReflectionClass($this);
      //\flat\core\debug::dump($r->getShortName(),"short name");
      $param = (object) $params;
      if (empty($param->resource)) throw new route\exception\bad_resource(
         "cannot be empty"
      );
      $orig_resource = str_replace("/","\\",$param->resource);
      $resource = implode("\\",array_filter(explode("/",$param->resource)));

      if (empty($param->route_factory)) {
         //$param->route_factory = "\\flat\\app\\route\\".$r->getShortName();
         throw new route\exception\missing('route_factory');
      }
      
      if (is_object($param->route_factory)) {
         if (!$param->route_factory instanceof \flat\route\factory) {
            throw new route\exception\bad_resource(
               "route_factory must be (string) ".
               "{\\flat\\route\\factory child class name} OR ".
               "(\\flat\\route\\factory) {instance}"
            );
         }
         $route = $param->route_factory;
      } else
      if (is_string($param->route_factory)) {
         
         $route = array_filter(explode("\\",$param->route_factory."\\$resource"));
         
         //\flat\core\debug::dump($route,"route first");
   
         //for($i=0;$i<count($route);$i++) {
         $i=0;
         while(count($route)) {
            // \flat\core\debug::dump("\\".implode("\\",$route),"route_class:$i");
            // \flat\core\debug::dump($route,"route arr:$i");
            if (class_exists($route_className="\\".implode("\\",$route))) break 1;
            array_pop($route);
            $i++;
         }
         if (!is_a($route_className,"\\flat\\route\\factory",true)) {
            throw new route\exception\no_route($resource,$route_className);
         }
         $route = new $route_className();
         //\flat\core\debug::dump($route,"route");
      }
      /*
       * create an event factory for all the \flat\app controllers to use
       */
      $event = new \flat\core\app\event\factory();
      //var_dump( $param );
      /*
       * apply the routes by priority order
       */
      $i=0;
      $appcol = new \flat\core\app\collection();
      $last_weight_added=-1;
      foreach ($route->get_route() as $route_rule) {
         if ($last_weight_added==$route_rule->weight) continue;
         
         if (!$route_rule->ignore_resource) {
            $transform = null;
            if (!empty($route_rule->transform) && is_callable($route_rule->transform)) {
               $transform = call_user_func_array($route_rule->transform, [$resource]);
               //die($transform);
            }
            if (empty($transform)) {
               $ns = $route_rule->ns."\\$resource";
            } else {
               $ns = $route_rule->ns."\\$transform";
            }
         } else {
            $ns = $route_rule->ns;
         }
         //echo "next level\n";
         $cycle=array();
         //var_dump($route_rule);die('wtf');
      
         for($c=0;$c<$route_rule->cycles;$c++) {
            $app = array_filter(explode("\\",$ns));
            if (empty($transform)) {
               $appres = array_filter(explode("\\",$resource));
            } else {
               $appres = array_filter(explode("\\",$transform));
            }            
            $appres = array_filter(explode("\\",$resource));
            $check_next_for_resolver = false;
            $app_count = count($app);
            //echo "$c\n";
            for($a=0;$a<$app_count;$a++) {
               if ($a!=0) {
                  array_pop($app);
                  array_pop($appres);
               }
               $app_className="\\".implode("\\",$app);
               //echo $app_className."($c $a)\n";
               $app_resource = implode("\\",$appres);
               if (class_exists($app_className)) {
                  //echo $app_className."($c) exists\n";
                  $r = new \ReflectionClass($app_className);
                  if ($r->implementsInterface("\\flat\\route\\cycle\\demander")) {
                     $demand = $app_className::get_cycle_demand();
                     if (is_scalar($demand) && $demand!=$c) continue;
                     if (is_array($demand) && !in_array($c,$demand)) continue;
                  }
                  //echo $app_className."($c $a) checkin\n";
                  if ($r->implementsInterface("\\flat\\route\\ignore")) {
                     continue;
                  }
                  
                  if ($check_next_for_resolver) {
                     
                     if (
                        (!$r->implementsInterface("\\flat\\core\\resolver")) && 
                        (!$r->implementsInterface("\\flat\\core\\resolver\\unresolved_consumer"))// &&
                        //\flat\route\resolution
                        //(!$r->implementsInterface("\\flat\\route\\resolution\\resolved"))
                     ) {
                        break 1;
                        
                     }
                  }
                  if (
                     !$r->implementsInterface("\\flat\\route\\cycle\\multiple_ok") && 
                     !$r->implementsInterface("\\flat\\route\\cycle\\demander") &&
                     ($c!=0)
                  ) {
                     //echo $app_className."($c)xx\n";
                     continue;
                  } else {
                     //echo $app_className."($c)good\n";
                  }
                  if ($r->implementsInterface("\\flat\\core\\debug\\suppress")) {
                     \flat\core\debug::set_suppress_on();
                  }
                  
                  if ($r->isInstantiable() && $r->implementsInterface("\\flat\\core\\routable")) {
                     
                     /*
                      * run each class (they'll be factories doing events or something...)
                      */
                     $app_object = new $app_className();
                     if ($app_object instanceof \flat\core\app\event\factory\consumer) {
                        $app_object->set_event_factory($event);
                     }               
                     if ($app_object instanceof \flat\core\resolver) {
                        $app_object->set_resource($resource);
                     }
                     
                     if ($app_object instanceof \flat\core\resolver\unresolved_consumer) {
                        $app_object->set_unresolved(
                           trim(str_replace($app_resource,"",$resource),"\\")
                        );
                     }
                     
                     if ($app_object instanceof \flat\core\input\consumer) {
                        $app_object->set_input(
                           new \flat\input\map($param->input)
                        );
                     }
                     
                     if ($app_object instanceof \flat\route\ignorable) {
                     
                        if ($app_object->is_route_ignored()===true) {
                           continue;
                        }
                     }                     
                     
                     if ($app_object instanceof \flat\core\resolver\prepared) {
                        $app_object->set_prepared_on();
                     }
                     

  
                     $appcol->add( $app_object  );
                     $last_weight_added = $route_rule->weight;
                     
                     if ($app_object instanceof \flat\core\controller\route\restful_status) {
                        //\flat\core\debug::dump("restful_status");
                        
                        
                        if (isset($param->restful_status_callback) && is_callable($param->restful_status_callback)) {
                           $h = $param->restful_status_callback;

                           $status = new route\restful_status\callback_data(
                              array(
                                 'app'=>get_class($app_object),
                                 'code'=>$app_object->get_status_code(),
                                 'string'=>$app_object->get_status_string()
                              )
                           );
                           $ret = $h($status);
                        }
                     }
                     
                     /*
                      * determine if resource is considered resolved:
                      *    default is true (since we found an app object...)
                      *    but if the app object indicates it's not a resolution
                      *    the resolver must go on!
                      */
                     $resolved = true;
                     
                     /*
                      * apply the 'unresolved' interface
                      */
                     if ($app_object 
                        instanceof 
                        \flat\route\resolution\unresolved) $resolved = false;
                     
                     /*
                      * apply the '\flat\route\resolution\provider' interface
                      */
                     if ( $app_object 
                        instanceof 
                        \flat\route\resolution\provider) $resolved = $app_object->is_resolved();
                     
                     if ($resolved) {
                        if (!$route_rule->iterate) break 1;
                        //if (!$route_rule->traverse) break 1;
                     }
                  }
               } else {
                  //echo "$app_className not exists\n";
                  
                  //if (!$route_rule->iterate) {
                  if (!$route_rule->iterate && !$route_rule->traverse) {
                     //if ($check_next_for_resolver) break 1;
                     $check_next_for_resolver = true;
                     //echo "checking next...\n";
                  } 
               }
   
   
            }/*end for each app*/
         
         }/*end for each route cycle*/
         $i++;
      }/*end for each route*/

      $this->_col = $appcol;
   }

   /**
    * returns number of resolved objects
    * 
    * @return int
    */
   public function get_resolve_count() {
      return $this->_col->count();
   }
   
   /**
    * @var \flat\core\app\collection $_col stores all resolved objects
    * @access private
    */
   private $_col;
   
   /**
    * retrieves collection of resolved objects
    *    as implementation of \flat\core\collectable 
    * 
    * @return \flat\core\app\collection
    */
   public function get_collection() {
      return $this->_col;
   }

}

















