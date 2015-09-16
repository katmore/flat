<?php
/**
 * \flat\core\xml\dom\nodelist class 
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
namespace flat\core\xml\dom;
/**
 * provides list of DOM nodes
 * 
 * @package    flat/xml
 * @author     D. Bird <retran@gmail.com> Adapated for the flat framework.
 * @author     Peter Cowburn <petercowburn@gmail.com> 2010-10-27 Peter Cowburn, Bloxx Ltd. Edinburgh, Scotland. 
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * @link https://github.com/salathe/spl-examples/wiki/RecursiveDOMIterator derived from github project as retrieved 2014-12-02
 */
class nodelist extends \RecursiveIteratorIterator implements \RecursiveIterator {
  
  public function __construct (\DOMNodeList $nodeList) {
    
    $this->_position = 0;
    $this->_nodeList = $nodeList;
    
    self::__construct($this,\RecursiveIteratorIterator::SELF_FIRST);
    
  }
  
  
  public function get($item) {
     if (is_int($item)) {
        return $this->_nodeList->item($item);
     }
  }
   /**
     * Current Position in DOMNodeList
     * @var Integer
     */
    protected $_position;

   

    /**
     * The DOMNodeList with all children to iterate over
     * @var DOMNodeList
     */
    protected $_nodeList;
    /**
     * Returns the current DOMNode
     * @return DOMNode
     */
    public function current()
    {
        return $this->_nodeList->item($this->_position);
    }

    /**
     * Returns an iterator for the current iterator entry
     * @return RecursiveDOMIterator
     */
    public function getChildren()
    {
        return new self($this->current());
    }

    /**
     * Returns if an iterator can be created for the current entry.
     * @return bool
     */
    public function hasChildren()
    {
        return $this->current()->hasChildNodes();
    }

    /**
     * returns node name if not empty
     *    --otherwise-
     * returns position
     * @return scalar
     */
    public function key()
    {
       $node = $this->current();
       if($node->nodeType === \XML_ELEMENT_NODE) {
          if (!empty($node->nodeName)) {
             return $node->nodeName;
          }
       }
        return $this->_position;
    }

    /**
     * Moves the current position to the next element.
     * @return void
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * Rewind the Iterator to the first element
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Checks if current position is valid
     * @return bool
     */
    public function valid()
    {
        return $this->_position < $this->_nodeList->length;
    }    
  
}