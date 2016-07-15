<?php
namespace flat\core\encode;
class csv 
   implements \flat\core\serializer
{
   /**
    * transforms an array into a comma-separated-value string; 
    *    non-scalar values are replaced with empty strings.
    *    
    * @param array $fields array to transform into a csv
    *    
    * @static
    * @return string
    */
   public static function fields2csv(array $fields) {
      $fval = [];
      foreach ($fields as $k=>$v) {
         if (is_scalar($v)) {
//             if (is_bool($v)) {
//                $fval[] = ($v)?'true':'false';
//             } else {
//                $fval[] = $v;
//             }
            $fval[] = $v;
         } else {
            $fval[]="";
         }
      }
      $f = fopen('php://memory', 'rw');
      fputcsv($f, $fval);
      fseek($f, 0);
      return fread($f,array_slice(fstat($f), 13)['size']);
   }
   /**
    * serializes data into a csv set
    * 
    */
   public static function serialize($data,array $options=null) {
      if (is_array($data)) {
         
      }
   }
   /**
    * 
    * @todo complete this method \flat\core\encode\csv::unserialize()
    */
   public static function unserialize($data,array $options=null) {
      
   }
}

























