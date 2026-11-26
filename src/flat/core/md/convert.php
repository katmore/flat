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
    * By default the text is treated as untrusted: raw HTML in it is escaped (shown as text), links and images with
    * unsafe addresses (javascript:, data:, vbscript:) lose them, and on* attributes are dropped (Parsedown's safe
    * mode). Pass ['safe' => false] only for text you wrote yourself, to let raw HTML through.
    * 
    * @return string
    * 
    * @param string $text markdown text
    * @param array $options optional: 'safe' (bool, default true) escapes raw HTML and unsafe addresses
    * 
    * @throws \InvalidArgumentException for an option that does not exist
    */
   public static function string_to_html($text, array $options = []) {
      foreach (array_keys($options) as $name) {
         if ($name !== 'safe') {
            throw new \InvalidArgumentException("unknown option '$name'");
         }
      }
      $parse= new parsedown\extra();
      $parse->setSafeMode(!isset($options['safe']) || (bool) $options['safe']);
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
    * @param array $options as for string_to_html()
    * @uses string_to_html()
    */
   public function __construct($text, array $options = []) {
      if (!is_string($text)) throw new convert\exception\invalid_source(
         "source must be string"
      );
      //var_dump($text);die('flat core md convert die');
      $this->_html= self::string_to_html($text, $options);
      //var_dump($html);die('flat core md convert die');
   }
}































