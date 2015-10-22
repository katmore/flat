<?php
/**
 * File:
 *    encode.php
 * 
 * Purpose:
 *    create XML document from given data, and vice versa
 * 
 * Dependancies:
 *    NO DEPENDANCIES to serialize data into XML
 *       encode::serialize()
 * 
 *    \flat\core\xml\dom and SPL dom extension (\DOMDocument) to unserialize XML into an object
 *       encode::unserialize()
 * 
 * Usage:
 *    static example 1:
 *       //serialize $data into XML and display
 *       $data = array('data example'); //$data can be an object, array, or scalar
 *       $xml_string = \flat\core\xml\encode::serialize($data);
 *       echo $xml_string;
 * 
 *    static example 2:
 *       //serialize $data into XML and retrieve as \DOMDocument class
 *       $data = array('data example'); //$data can be an object, array, or scalar
 *       $document = \flat\core\xml\encode::DOMDocument($data);
 *       $document->appendChild(
 *          $document->createElement('hello DOM','I am a value')
 *       );
 *       var_dump($document);
 * 
 *    instantiated object example:
 *       //create /flat/core/xml/encode object from $data
 *       $data = array('data example'); //$data can be an object, array, or scalar
 *       $xmlencode = new \flat\core\xml\encode($data);
 * 
 *       //get xml as string
 *       $str1 = $xmlencode->get_xml();
 *       echo '<hr>$str1 dump (get xml as string)<hr>';
 *       var_dump($str1);
 * 
 *       //access object as string
 *       $str2= '<!--begin $xmlencode as string-->'. $xmlencode.'<!--end $xmlencode as string-->';
 *       echo '<hr>$str2 dump (access object as string)<hr>';
 *       var_dump($str2);
 * 
 *       //get DOMDocument
 *       $document = $xmlencode->get_DOMDocument();
 *       $document->appendChild(
 *          $document->createElement('hello DOM','I am a value')
 *       );
 *       $str3 = $document->saveXML();
 *       echo '<hr>$str3 dump (get DOMDocument)<hr>';
 *       var_dump($str3);
 * 
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.. All works herein are considered to be trade secrets, and as such are afforded 
 * all criminal and civil protections as applicable to trade secrets.
 * 
 * @package    flat/core/xml
 * @author     D. Bird <retran@gmail.com>
 * @author     Sean Barton
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @link       http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
 * @version    1.1.0 (beta release state)
 * 
 * Created:
 *    2009-03-25 by Sean Barton
 *       Published on blog. Retrieved 2014-11-20.
 *       URL: http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
 * 
 * Modified:
 *    2014-11-20 by D. Bird
 *       Adapted for the flat framework. 
 */
namespace flat\core\xml;
class encode implements \flat\core\serializer,\flat\core\status\beta {
   const flat_xml_ver="0.2";
   const xmlns = 'https://github.com/katmore/flat/wiki/xmlns';
   private $xml;
   public function __toString() {
      return $this->get_xml();
   }
   public function get_xml() {
      return $this->xml;
   }
   public function __construct($input, array $options=NULL) {
      $this->xml = self::_xml_encode($input, $options);
   }
   public static function unserialize($xml, array $options=NULL) {      
      $dom = dom::load_xml($xml);
      if (!is_a($dom,"\\flat\\core\\xml\\dom")) return;
      if ($dom->getAttr('xmlns')!=self::xmlns."/obj") return;
      return dom::dom_to_object(
          $dom,
          array(
            'assoc'=>validate\assoc::only_bool_true($options,'assoc'),
            'node_name_attr'=>'key',
            'index_name_attr'=>'index',
            'preserve_parent_value'=>false
          )
      );
      
   }
   public static function serialize($input, array $options=NULL) {
      return self::_xml_encode($input, $options);
   }
   private static function _ident($level=1,$size=3) {
      $ident="";
      for($i=0;$i<$size*$level;$i++) $ident.=" ";
      return $ident; 
   }
   protected static function _get_type($val) {
      if (is_scalar($val)) {
         return 'scalar';
      } else
      if (is_array($val)) {
         return "array";
      } else 
      if (is_object($val)) {
         if ("stdClass" == ($className= get_class($val))) {
            return "object";
         }
         return $className;
      }
   }
   protected static function _get_structure($input) {
      $structure = new \stdClass();
      
      if ('scalar'!=($structure->type = self::_get_type($input))) {
         foreach ($input as $key=>$val) {
            $structure->node = new \stdClass();
            if ('scalar'!=($structure->node->$key = self::_get_type($val))) {
               $structure->node->$key = self::_get_structure($val);
            }
         }
      }
      
      return $structure;
   }
   protected static function _get_meta_value($input) {
      if (is_object($input)) {
         if ("stdClass" == ($className= get_class($input))) {
            return;
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
   
   protected static function _xml_encode($input, array $options=NULL) {
      
      
      
      if (!is_array($options)) $options=array();
      foreach (array(
      'top_node'=>'flat',
      'default_node'=>'data',
      'indent_size'=>3,
      'dump_ok'=>false,
      'checksum'=>array('md5'),
      'meta'=>true,
      'xsi_type_detect'=>true,
      ) as $opt=>$val) if (!isset($options[$opt])) $options[$opt]=$val;
      
      $meta = "";
      $metatag = "";
      if ($options['meta']===true) {
         //htmlspecialchars($key, ENT_QUOTES& ENT_SUBSTITUTE)
         if ($meta = self::_get_meta_value($input)) {
            $meta = ' extxs:meta="'.$meta.'"';
         } else {
            $meta = " extxs:meta=\"extxs:structure\"";
            $structure = new \stdClass();
            $structure->structure = self::_get_structure($input);
            $metatag = self::_data_to_xml(
               $structure, 
               'structure',
               1,
               $options['indent_size'],
               false,
               array(),
               array(
                  'xsi_type'=>array(
                     'string'=>true,
                     'DateTime'=>true,
                     'hexBinary'=>true,
                     'base64Binary'=>true,
                     'anyURI'=>true,
                     'nil'=>true
                  ),
                  'namespace'=>'s',
                  'namespace_identifyer'=>self::xmlns.'-structure'
               )
            );
         } 
         
      } else
      if ($options['meta']!==false) {
         if (is_scalar($options['meta'])) {
            $meta=' extxs:meta="'.htmlspecialchars($options['meta'], ENT_QUOTES | ENT_SUBSTITUTE).'"';
         } else {
            if ($meta = self::_get_meta_value($input)) {
               $meta = ' extxs:meta="'.$meta.'"';
            } else {
               $meta = " extxs:meta=\"extxs:structure";
               $structure = new \stdClass();
               $structure->structure = self::_get_structure($input);               
               $metatag = self::_data_to_xml(
                  $structure, 
                  'structure',
                  1,
                  $options['indent_size'],
                  false,
                  array(),
                  array(
                     'xsi_type'=>array(
                        'string'=>true,
                        'DateTime'=>true,
                        'hexBinary'=>true,
                        'base64Binary'=>true,
                        'anyURI'=>true,
                        'nil'=>true
                     ),
                     'namespace'=>'s',
                     'namespace_identifyer'=>self::xmlns.'-structure'
                  )
               );
            } 
         }
      }      
      $xsins = "";
      if (!empty($options['xsi_type_detect']) || !empty($options['xsi_type'])) $xsins = ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:extxs="'.self::xmlns.'-extxs"';
      // if (!$input = json_encode($input)) {
         // if (!$options['dump_ok']) return false;
         // ob_start();
         // var_dump($input);
         // $dump=ob_get_clean();
         // $input = new \stdClass();
         // $input->dump = $dump;
      // }
      $checksum_attr = "";
      $checksum_data = NULL;
      foreach ($options['checksum'] as $algo) {
         
         if (empty($checksum_data)) {
            $checksum_data = json_encode($checksum_data);
         }
         if ($hash = hash($algo,$checksum_data)) {
            if (!hexdec($hash)) continue;
            $attr = preg_replace("/[^a-zA-Z0-9]+/", "", $algo);
            if (is_numeric(substr($attr,0,1))) $attr="hash_".$attr;
            $checksum_attr .= " fx:$attr=\"$hash\"";
         }
      } 
      unset($checksum_data);
      
      $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

      $xml .= '<' . $options['top_node'] . ' fx:created="'.date("c").'"'.$checksum_attr."$meta$xsins";
      $ftypeattr = " ";
      if (is_null($input)) {
         $ftypeattr .= 'xsi:nil="true"';
      } else {
         $ftypeattr .= self::_get_xsi_attr(self::_get_xsi_type($input));
      }
      $xml .=" fx:flat-xml-ver=\"".self::flat_xml_ver."\" xmlns:fx=\"".self::xmlns."\" xmlns=\"".self::xmlns."-object\"$ftypeattr>\n";
      
      
      
      $xml .= self::_data_to_xml(
         $input, 
         $options['default_node'],
         1,
         $options['indent_size'],
         $options['dump_ok'],
         $options['checksum'],
         array(
            'xsi_type'=>array(
               'string'=>true,
               'DateTime'=>true,
               'hexBinary'=>true,
               'base64Binary'=>true,
               'anyURI'=>true,
               'nil'=>true
            )
         )
      );
      $xml .= $metatag;
      $xml .= '</' . $options['top_node'] . ">";

      return $xml;
    }

    private static function _get_xsi_attr($type) {

      return 'xsi:type="'.$type.'"';
       
    }

   private static function _get_xsi_type($data,array $option=NULL) {
      
      $param = array(
         'DateTime'=>true,
         'hexBinary'=>true,
         'base64Binary'=>true,
         'anyURI'=>true,
         'string'=>true,
         'Array'=>true,
         'Object'=>true,
         'other'=>true,
      );
      if (is_array($option)) foreach($option as $key=>$val) {
         if (isset($param[$key])) $param[$key] = $val;
      }
       if (is_scalar($data)) {
          if (is_int($data)) {
             return "xs:integer";
          } else
          if (is_float($data)) {
             return "xs:decimal";
          } else
          if (is_bool($data)) {
             return "xs:boolean";
          } else
          if (is_string($data)) {
             
             if (is_numeric($data)) {
                
                if (intval($data)==$data) {
                   return "extxs:NumericStringInt";
                } elseif (floatval($data)==$data) {
                   return "extxs:NumericStringFloat";
                }
                
                return "extns:NumericString";
             }
             
             if ($param['DateTime'] ) {
                if (false !== ($ts = strtotime($data))) {
                   if (!empty($ts)) {
                        return "xs:DateTime";
                   }
                }
             }
             // if ($param['ip_addr']) {
                // if (filter_var($ip, FILTER_VALIDATE_IP)) {
//                    
                // }
             // }
             if ($param['hexBinary'] ) {
                if (ctype_xdigit($data)) return "xs:hexBinary";
             }
             // if ($analyze['base64Binary']) {
                // if (base64_decode($data)) return "base64Binary";
             // }
             if ($param['anyURI']) {
                if(filter_var($data, FILTER_VALIDATE_URL)) {
                   return "xs:anyURI";
                }
             }             
             if ($param['string']) return "xs:string";
             
             //$xsi=" ".self::_get_xsi_attr("string");
          }
       } else {
          if (is_array($data)) {
             if ($param['Array']) return "extxs:Array";
          } else
          if (is_object($data)) {
             if ($param['Object']) return "extxs:Object";
          } else {
             if ($param['other']) return "extxs:".str_replace(" ","",ucwords(gettype($data)));
          }
       }
   }

    private static function _data_to_xml($data, $default_node, $indent_level=1, $indent_size=3, $dump_ok=true,$checksum=array('md5'),array $options=null) {
        
        $ns = "";
        $nsidattr="";
        if (!empty($options['namespace'])) {
           if (preg_match('/[a-z_0-9]/i', $options['namespace'])) {
              if (!is_numeric(substr($options['namespace'],0,1))) {
                 
           
                 
                 $ns = $options['namespace'].":";

                 if (empty($options['namespace_child'])) {
                    if (empty($options['namespace_identifyer'])) {
                       $nsid = self::xmlns."-".$options['namespace'];
                    } else {
                       $nsid = htmlspecialchars($options['namespace_identifyer'],ENT_QUOTES | ENT_SUBSTITUTE);
                    }
                    $nsidattr= " xmlns:".$options['namespace']."=\"$nsid\"";
                    $options['namespace_child']=true;
                 }
                 
              }
           }
        }
         
        if ($is_arr = is_array($data) || is_object($data)) {
           // if (method_exists( $data, '__toString' )) {
              // return self::_data_to_xml($data->__toString(), $default_node,$indent_level,$indent_size,$dump_ok,$checksum);
           // }
           $i=0;
           $xml="";
            foreach ($data as $key=>$value) {
                $node = $key;
               $index = NULL;
                if (preg_match('/[^a-z_0-9]/i', $node)) {
                   $node = $default_node;
                } else {
                   if (is_numeric(substr($node,0,1))) {
                      $node = $default_node;
                   }
                }
                if (empty($node)) $node = $default_node;
                $node = strtolower($node);
                $xml .= self::_ident($indent_level,$indent_size)."<$ns$node$nsidattr";
                if ($is_arr){
                    if (is_int($key)) {
                       $index = $key;
                       //if ($key!=$i) {
                        $xml .= " extxs:index=\"$key\"";
                       //}
                    }
                       
                }
                if ($key != $node) {
                  
                  
                  if (!$keyval = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE)) {
                     if ($keyval = base64_encode($keyval)) {
                        $keyval = "data:application/octet-stream;base64,$keyval";
                     } else {
                        if($dump_ok) {
                           ob_start();
                           var_dump($key);
                           $dump = ob_get_clean();
                           if ($dump = base64_encode($dump)) {
                              $keyval = "data:application/php-object-dump;base64,$dump";
                           } else {
                              $keyval ="";
                           }
                        } else {
                           $keyval="";
                        }
                     }
                  }
                  if (!empty($keyval)) {
                     if ($keyval!=$index)
                     $xml .= " extxs:key=\"$keyval\"";
                  }
                }
                if (is_array($value) || is_object($value)) {
                   $indent_level++;
                   if (is_array($value)) {
                      $all_same_type = true;
                      $last_type = null;
                      $first_value = true;
                      foreach($value as $v) {
                         if (!$first_value && (self::_get_xsi_type($v,['string'=>true])!=$last_type)) {
                            $all_same_type = false;
                            break 1;
                         }
                         $last_type = self::_get_xsi_type($v,['string'=>true]);
                      }
                      if ($all_same_type) {
                         $array_type = $last_type;
                         $xml .= " xsi:type=\"extxs:Array\" extxs:ArrayType=\"".$array_type."[".count($value)."]\"";
                      } else {
                         $xml .= " xsi:type=\"extxs:Array\" extxs:ArrayType=\"extxs:Mixed[".count($value)."]\"";
                      }
                   } else {
                      $xml .= " xsi:type=\"extxs:Object\" extxs:ObjectType=\"".get_class($value)."\"";
                   }
                   $xml .= ">\n".
                     self::_data_to_xml($value, $default_node,$indent_level,$indent_size,$dump_ok,$checksum,$options);
                   $indent_level--;
                   $xml .= self::_ident($indent_level,$indent_size)."</$ns$node>\n";
                   
                } else {
                   if ($value===null) {
                      if (!empty($options['xsi_type']['nil'])) {
                        $xml .=' xsi:nil="true"'." ";
                      }
                      $xml .= "/>\n";
                   } else {
                      if (empty($value)) {
                         // ob_start();
                         // var_dump($value);
                         // $empty=" empty=\"".htmlspecialchars(trim(ob_get_clean()),ENT_QUOTES | ENT_SUBSTITUTE)."\"";
                         $xml .= "/>\n";
                      } else {
                         $xsi = "";
                         if (!empty($options['xsi_type'])) {
                            if ($xsitype = self::_get_xsi_type($value,$options['xsi_type'])) {
                              $xsi=" ".self::_get_xsi_attr($xsitype);
                            }
                         }

                        $xml .= "$xsi>".self::_data_to_xml($value, $default_node,$indent_level,$indent_size,$dump_ok,$checksum,$options)."</$ns$node>\n";
                      }
                   }
                }
                
                $i++;
            }
            return $xml;
            
        } else {
           if (empty($data)) return "<!--empty-->";
           if ($xml = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8')) return trim($xml);
            $checksum_attr = "";
            foreach ($checksum as $algo) {
               if ($hash = hash($algo,$input)) {
                  if (!hexdec($hash)) continue;
                  $attr = preg_replace("/[^a-zA-Z0-9]+/", "", $algo);
                  if (is_numeric(substr($attr,0,1))) $attr="hash_".$attr;
                  $checksum_attr .= " $attr=\"$hash\"";
               }
            } 
           if (!$dump_ok) {
                     
              return "<$default_node$checksum_attr><!--unserializable--></$default_node>";
           }
            $type = self::_get_xsi_type($data);
            ob_start();
            var_dump($data);
            $dump = ob_get_clean();
            $encoding = "";
            if ($data = htmlspecialchars($dump, ENT_QUOTES | ENT_SUBSTITUTE)) {
               $encoding = " extxs:encoding=\"none\"";
               $dump = $data;
            } else {
               if ($data = base64_encode($dump)) {
                  $dump = $data;
                  $encoding = " extxs:encoding=\"base64\"";
               } else {
                  //$dump = "<!--unserializable dump-->";
                  $dump = "";
                  $encoding = " extxs:encoding=\"unserializable\" /";
               }
            }
            $indent_level++;
            $xml = "\n".self::_ident($indent_level,$indent_size);
            $xml .= "<extxs:dump extxs:DumpObject=\"$type\"$checksum_attr$encoding>$dump";
            $indent_level--;
            $xml .= "</extxs:dump>\n".self::_ident($indent_level,$indent_size);            
            return $xml;
        }
    }

}

















