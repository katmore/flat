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
namespace flat\core\html;
class encode implements \flat\core\serializer {
   public static function unserialize($input, array $options=null) {
      throw new \flat\core\status\exception\feature_not_ready();
   }
   public static function serialize($input, array $options=null) {
      $param = array(
         'top_element'=>'div',
         'parent_element'=>'ul',
         'child_element'=>'li'
      );

      $html = "";
      //$html .= '<' . $param['top_element'] . ' data-meta="'.htmlspecialchars($type_info, ENT_QUOTES).'">'."\n";
      $html .= self::_data_to_html($input,$param['parent_element'],$param['child_element'],0 );
      //$html .= '</' . $param['top_element'] . '>'."\n";

      return $html;
    }
   protected static function _get_meta_value($input) {
      if (is_object($input)) {
         if ("stdClass" == ($className= get_class($input))) {
            return "object";
            //data:application/json;base64,$dump
            // $structure = json_encode(self::_get_structure( $input));
            // //return 'data:application/json;base64,'.base64_encode($structure);
            // return 'data:application/json;'.htmlspecialchars($structure,ENT_QUOTES | ENT_SUBSTITUTE);
         } else {
            return htmlspecialchars($className, ENT_QUOTES | ENT_SUBSTITUTE);
         }
      } else {
         //$type = gettype($input);
         return htmlspecialchars(gettype($input), ENT_QUOTES | ENT_SUBSTITUTE);
      }
   }
   private static function _ident($level=1,$size=3) {
      $ident="";
      for($i=0;$i<$size*$level;$i++) $ident.=" ";
      return $ident; 
   }
    private static function _data_to_html($data,$parent_element,$child_element,$indent_level=1, $indent_size=3) {
        
        if ($index = is_array($data) || is_object($data)) {
           $i=0;
            $html = self::_ident($indent_level,$indent_size)."<$parent_element data-type=\"".self::_get_meta_value($data)."\">\n";
            foreach ($data as $key=>$value) {
               $indent_level++;
                $html .= self::_ident($indent_level,$indent_size)."<$child_element ";
                if ($index) $html .= "data-index=\"$i\" ";
                $html .= "data-key=\"".htmlspecialchars($key, ENT_QUOTES)."\" data-role=\"item\">";
                if (sprintf("%d",$key)!=$key) {
                   $html .= "<span data-role=\"item-key\">".htmlspecialchars($key, ENT_QUOTES)."</span>".":&nbsp;";
                } else {
                   $html .= "&nbsp;";
                }
                $html .= 
                  "<span data-role=\"item-value\">". self::_data_to_html($value, $parent_element,$child_element,$indent_level);
                $html .= "</$child_element></span><!--/data-item: (".htmlspecialchars($key, ENT_QUOTES).")-->\n";
                $indent_level--;
                $i++;
            }
            $html .= self::_ident($indent_level,$indent_size)."</$parent_element>\n";
            return $html;
        } else {
           if (is_string($data) && $data=="") return "''";
           if (is_scalar($data) && ctype_print(str_replace(array("\n","\r"),"",(string) $data))) {
              return htmlspecialchars($data, ENT_QUOTES);
           } else {
              ob_start();
              var_dump($data);
              $dump = ob_get_clean();
              return "(dump) <br><pre>".nl2br(htmlspecialchars($dump, ENT_QUOTES))."</pre>";
           }
        }

        
    }   
}