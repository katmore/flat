<?php
namespace flat\app\api;
class hello extends \flat\api 
   implements 
      \flat\api\method\GET,
      \flat\api\method\POST
{
   public function GET_method(\flat\input $input) {
      return new \flat\api\response\ok([
         'msg'=>'hello world! this is my response to a GET request',
         'input'=>$input
      ]);
   }
   public function POST_method(\flat\input $input) {
      return new \flat\api\response\ok([
         'msg'=>'you made a POST, see the input',
         'input'=>$input
      ]);
   }
}