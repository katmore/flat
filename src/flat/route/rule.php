<?php
/**
 * \flat\route\data class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * BY ACCESSING THE CONTENTS OF THIS SOURCE FILE IN ANY WAY YOU AGREE TO BE 
 * BOUND BY THE PROVISIONS OF THE "SOURCE FILE ACCESS AGREEMENT", A COPY OF 
 * WHICH CAN IS FOUND IN THE FILE 'LICENSE.txt'.
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\route;
/**
 * route configuration data
 * 
 * @package    flat\core
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @uses \flat\core\controller\route
 */
class rule extends \flat\data {
   /**
    * @var string $ns used by resolver to create a path. if 'ignore_resource' is true,
    *    $ns is the same as the path, otherwise, the 'resource' provided to the resolver
    *    is appended to the $ns.
    */
   public $ns;
   
   /**
    * @var bool $ignore_resource if false, controller will not append resource to $ns for resolving route
    */
   public $ignore_resource=false;
   
   /**
    * @var int $weight resolver will only resolve for a single single rule for same path and weight.
    */
   public $weight;
   
   /**
    * @var bool $traverse if true, router will examine each section of the route
    *    to resolve the first matching object.
    */
   public $traverse = true;
   
   /**
    * @var bool $iterate if true, router will examine each section of the route
    *    and resolves each matching object.
    */
   public $iterate = true;
   
   /**
    * @var int $cycles number of times for controller to resolve route
    */
   public $cycles=1;
   /**
    * @var callable | null
    *    the return value of callable function will become the resource for the route
    */
   public $transform;
}











