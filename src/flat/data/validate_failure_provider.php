<?php
namespace flat\data;
interface validate_failure_provider {
   /**
    * @return \flat\data\validate_failure[]
    */
   public function get_validate_failure();
}