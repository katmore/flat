<?php
namespace flat\data;
class mapper_cfg {
   /**
    * default or_empty field return value
    * @uses \flat\data::or_empty()
    * @uses \flat\data::or_empty_value()
    */
   const default_empty_value = 'unknown';
   /**
    * default html tags allowed for strip() strip_tags()
    * @uses \flat\data::strip()
    * @uses \flat\data::strip_tags()
    */
   const default_allowed_tags = '<p><br><span><i><b><strong><div>';
   
   const htmlentities_unknown = "??";
   const htmlentities_null = '';
}