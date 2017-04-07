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
namespace flat\core\text;
class encode implements \flat\core\serializer {
   public static function unserialize($input, array $options=null) {
      throw new \flat\core\status\exception\feature_not_ready();
   }
   public static function serialize($input, array $options=null) {
      $param = array(
         'newline'=>"\n",
         'ident_level'=>-1,
         'ident_size'=>3,
         'ident_char'=>" ",
      );
      if ($options) {
         foreach($options as $k=>$v) if (isset($param[$k])) $param[$k] = $v;
      }
      $nl = $param['newline'];
      if (is_object($input)) {
         $type = str_replace('actvp','',get_class($input));
      } else {
         $type = gettype($input);
      }
      $text = "";
      //$text = "$type:$nl";
      $text .= self::data_to_text($input,$param['newline'],$param['ident_level'],$param['ident_size'],$param['ident_char'] );
      return trim($text.$nl);
    }
   private static function _ident($level=1,$size=3,$char=" ") {
      $ident="";
      for($i=0;$i<$size*$level;$i++) $ident.=$char;
      return $ident; 
   }
    public static function data_to_text($data,$nl="\n",$indent_level=1, $indent_size=3, $ident_char=" ") {
        $text = "";
        if ($index = is_array($data) || is_object($data)) {
           $i=0;
            //$text = self::_ident($indent_level,$indent_size)."<$parent_element>\n";
            
            foreach ($data as $key=>$value) {
               $indent_level++;
               if (is_numeric($key)) $key = "#$key";
               $text .= $nl;
                $text .= self::_ident($indent_level,$indent_size,$ident_char)."$key:";
                $text .= self::data_to_text($value, $nl,$indent_level,$indent_size,$ident_char);
                //if (substr($text,-1)!=$nl) $text .= $nl;
                $text .= self::_ident($indent_level,$indent_size,$ident_char);
                $indent_level--;
                $i++;
            }
            
            return $text;
        } else {
           if (is_string($data) && $data=="") return " ''";
           if (is_bool($data)) {
              if ($data) {
                 return " true";
              } else {
                 return " false";
              }
           }
           if (is_null($data)) {
              return "";
           } else
           if (is_scalar($data) && ctype_print(str_replace(array("\n","\r"),"",(string) $data))) {
              return " ".$data;
           } else {
              $text = json_encode($data);
              if ($text===NULL) {
                 return " (unprintable value)";
              } else {
                 return " ".str_replace("'","",str_replace('"',"",$text));
              }
           }
        }

        
    }   
}