<?php
/**
 * \flat\core\md class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * BY ACCESSING THE CONTENTS OF THIS SOURCE FILE IN ANY WAY YOU AGREE TO BE 
 * BOUND BY THE PROVISIONS OF THE "SOURCE FILE ACCESS AGREEMENT", A COPY OF 
 * WHICH CAN IS FOUND IN THE FILE 'access-agreement.txt'.
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\md\convert;
/**
 * converts markdown file into HTML
 * 
 * @package    flat\core\md
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @uses \flat\vendor\erusev\parsedown\Parsedown uses Emanuil Rusev's better markdown parser
 */
class file extends \flat\core\md\convert {
   /**
    * @param string $source path to markdown file
    */
   public function __construct($source) {
      if (false ===($text = file_get_contents($source))) {
         throw new convert\exception\invalid_source(
            "path '$source' could not be read"
         );
      }
      //var_dump($text);die('flat core md convert file die');
      parent::__construct($text);
   }
}
















