<?php
namespace flat\app\route;
class api extends \flat\route\factory implements \flat\core\app,\flat\route\base {
   public function get_base() {
      return "/";
   }
   public function __construct() {
      
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\meta\app',
            'weight'=>0,
            'iterate'=>false,
            'traverse'=>true,
         ))
      );
      

      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\helper\api',
            'weight'=>1,
            'iterate'=>true,
         ))
      );
      

      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\app\api',
            'weight'=>2,
            'iterate'=>false,
            'traverse'=>false,
         ))
      );
      $this->add_route(
         new \flat\route\rule(array(
            'ns'=>'\flat\api\response\forbidden\any_method',
            'weight'=>2,
            'iterate'=>false,
            'ignore_resource'=>true
         ))
      );
   }
}












