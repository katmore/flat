<?php
/**
 * class definition 
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
namespace flat\core\html\encode;
class form implements \flat\core\serializer {
   public static function unserialize($input, array $options=null) {
      throw new \flat\core\status\exception\feature_not_ready();
   }
   public static function serialize($input, array $options=null) {
      $params = [ 'type_map'=>[] ];
      if ($options) foreach($options as $k=>$v) {
         if (isset($params[$k])) $params[$k] = $v;
      }
      self::data_to_form($input, $params['type_map']);
   }
   
   public static function keyval_to_input($key,$val,$type=NULL) {
      if (empty($type) || !is_string($type)) $type = "text";
      $label = $key;
      $class_name = preg_replace("/[^a-zA-Z]/", "", $key);
      $input_class = "flat-html-encode-form-input flat-html-encode-form-input-$type flat-html-encode-form-input-name-$class_name";
      $label_class = "flat-html-encode-form-input-label";
      if ($type=="text") {
         ob_start();
         ?>
<div class="<?=$input_class?>">
   <div class="<?=$label_class?>"><?=$label?></div>
   <input name="<?=htmlentities($key)?>" type="text" value="<?=htmlentities($val)?>">
</div>
         <?php
         return trim(ob_get_clean());
      } else
      if ($type=="textarea") {
         ob_start();
         ?>
<div class="<?=$input_class?>">
   <div class="<?=$label_class?>"><?=$label?></div>
   <textarea name="<?=htmlentities($key)?>"><?=htmlentities($val)?></textarea>
</div>
         <?php
         return trim(ob_get_clean());
      }
   }
   
   public static function data_to_form($data,array $type_map) {
      
      $form="<!--START flat/core/html/encode/form::data_to_form-->\n";
      $form .= "<!--data\n";
      ob_start();
      var_dump($data);
      $form .= ob_get_clean();
      $form .= "-->";
      //if (is_object($type_map)) $type_map = (array) $type_map;
      if (is_object($data) || is_array($data)) {
         foreach ($data as $k=>$v) {
            if (is_object($v) || is_array($v)) {
               $map = [];
               if (isset($type_map[$k])) $map = (array) $type_map[$k]; 
               $form .= self::data_to_form($v, $map);
            } else {
               $type = NULL;
               if (isset($type_map[$k])) {
                  $type = $type_map[$k];
               }
               $form .= "\n".self::keyval_to_input($k, $v,$type);
            }
         }
      }
      $form .= "\n<!--END flat/core/html/encode/form::data_to_form-->";
      return $form;
   }
}




















