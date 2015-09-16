<?php
/**
 * \flat\tmpl\design_base interface 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
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
namespace flat\tmpl;
/**
 * provides design base to template controller
 * 
 * @package    flat\tmpl
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
interface design_base {
   /**
    * provides design base to template controller, when templates
    *    are displayed, the return value of this function is prepended
    *    to the resource specified to the template controller.  
    * @return string
    * @see \flat\tmpl\design_base part of the template design base interface.
    */
   public static function get_design_base();
}










