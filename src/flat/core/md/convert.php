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
namespace flat\core\md;
/**
 * vendor alias
 */
use \flat\vendor\erusev\parsedown;
/**
 * converts markdown text to html 
 * 
 * @package    flat\core\md
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @uses \flat\vendor\erusev\parsedown\Parsedown uses Emanuil Rusev's better markdown parser
 */
class convert {
   /**
    * converts text into html
    * 
    * @return string
    * 
    * @param string $text markdown text
    */
   public static function string_to_html($text) {
      $parse= new parsedown\extra();
      return $parse->text($text);
   }
   /**
    * @var string $_html
    */
   private $_html;
   
   /**
    * __toString() magic method: retrieves HTML as processed from markdown text
    * @uses get_html()
    * @return string
    */
   public function __toString() {
      if (empty($this->_html)) return "";
      return $this->_html;
   }
   
   /**
    * retrieves HTML as processed from markdown text
    * @return string
    */
   public function get_html() {
      return $this->_html;
   }
   /**
    * @param string $text markdown text to convert
    * @uses string_to_html()
    */
   public function __construct($text) {
      if (!is_string($text)) throw new convert\exception\invalid_source(
         "source must be string"
      );
      //var_dump($text);die('flat core md convert die');
      $this->_html= self::string_to_html($text);
      //var_dump($html);die('flat core md convert die');
   }
}































