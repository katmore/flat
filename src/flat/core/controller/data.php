<?php
namespace flat\core\controller;
abstract class data extends \flat\core\mappable\constructor 
   implements 
   \flat\core\app,
   \flat\core\controller,
   \flat\core\mappable\property\ignore_non_existing_set 
{
   use \flat\data\mapper;
   /**
    * serialize this object to assoc array
    * 
    * @param null $object IGNORED for \flat\data object. 
    * 
    * @param null $get_private_properties IGNORED for \flat\data object.
    * 
    * @return Array
    */
   public function get_as_assoc($object=null,$get_private_properties=false) {
      $object = $this;
      if (is_object($object) && ($object instanceof \flat\data)) {
         return (array) json_decode(json_encode($object));
      }
   }
}
