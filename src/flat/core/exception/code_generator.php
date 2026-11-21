<?php
namespace flat\core\exception;
interface code_generator {
   public function _value_to_code($value);
   public function _derive_code($code_offset=900000000);
}