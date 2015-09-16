<?php
/**
 * class definition 
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
namespace flat\data;
class scalar extends \flat\data implements \flat\core\serializer {
   /**
    * default value on empty
    */
   const default_value="";
   /**
    * @var boolean | integer | float | string | \flat\data\scalar $value
    */
   public $value;
   public function gettype() {
      if (is_object($this->value)) return get_class($this->value);
      return gettype($this->value);
   }
   public function display(array $encoding=NULL) {
      $value = $this->value;
      if (in_array('html',$encoding)) $value = htmlentities($value);
      if (in_array('html_comment',$encoding)) echo "<!-- begin display ".get_called_class();
      echo $value;
      if (in_array('html_comment',$encoding)) echo " end display -->\n";
   }
   public static function serialize($data,array $options=NULL) {
      return (string) $data->value;
   }
   public static function unserialize($data,array $options=NULL) {
      return new static($data);
   }
   public function __construct($value,$value_on_empty=self::default_value) {
      /*
       * attempt to make it compatible with \flat\data::__construct()
       */
      if (empty($value)) {
         $value = $value_on_empty;
      } else if (
         is_scalar($value) || 
         ( is_object($value) && is_a($value,'\flat\data\scalar') )
      ) {
         parent::__construct(array('value'=>$value));
         
      } else
      if (is_array($value) && isset($value['value']) && is_scalar($value['value'])) {
         parent::__construct(array('value'=>$value['value']));
      } else
      if (is_object($value) && is_a($value,'\flat\data\rules') && is_scalar($value->data)) {
         parent::__construct(array('value'=>$value->data));
      } else
      if (is_object($value) && 
         is_a($value,'\flat\data\rules') && 
         is_object($value->data) && 
         isset($value->data->value) &&
         is_scalar($value->data->value)
      ) {
         parent::__construct(array('value'=>$value->data->value));
      } else 
      if (is_object($value) && 
         is_a($value,'\flat\data\rules') && 
         is_array($value->data) && 
         isset($value->data['value']) &&
         is_scalar($value->data['value'])
      ) {
         parent::__construct(array('value'=>$value->data['value']));
      } else throw new scalar\exception\bad_value();
   }

}














