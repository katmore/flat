<?php
namespace flat\asset;

trait deprecated_methods_trait {
   
   abstract public function print_tag(int $tag_type = null): void;
   
   /**
    * @return void
    * @deprecated
    */
   public function script_tag() {
      $this->print_tag();
   }
   
   /**
    * @return void
    * @deprecated
    */
   public function style_link() {
      $this->print_tag();
   }
   
   /**
    * @return void
    * @deprecated
    */
   public function print_script($always=false) {
      $this->print_tag();
   }
   
   /**
    * @return void
    * @deprecated
    */
   public function print_style($always=false) {
      $this->print_tag();
   }
}