<?php
namespace flat\app\tmpl;

use \flat\app\event\hello as event;

class hello extends \flat\tmpl 
implements 
   \flat\tmpl\design,
   \flat\tmpl\design_base
{
   const tmpl_base='\flat\design\tmpl';
   const design_base = 'hello';
   public static function get_design_base() {
      return self::tmpl_base . "\\" . self::design_base;
   }
   
   public function get_design() {
      
      $instance = new \ReflectionClass($this);
      
      if ($instance->getShortName()==self::design_base) {
         $design = "home"; 
      } else {
         $design = $instance->getShortName();
      }
      
      return self::get_design_base() . "\\" . $design;
   }
   
   public static function get_site_title() {
      return "Hello World";
   }

}










