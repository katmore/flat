<?php
/*
 * Purpose: curl convenience class
 *    loads everything into DOM and SimpleXML
 *    has xpath query
 */
namespace flat\core;
class curl {
   
   /**
    * curl execution
    * 
    * @param string | curl\config $config:
    *    string $config['url'] URL to visit.
    *    string $config['request_method'] (optional) HTTP request method, defaults to "GET". 
    *    callable | array $config['callback'] (optional) callback data as defined below:
    *    if callable: callback function is invoked upon success 
    *       (response gives http status code 200/ok)
    *    if assoc array:
    *       callback with assoc key = 'success' invoked if http status code is 200 (OK)
    *       callback with assoc key = 'fail' invoked if http status code is 4XX or 5XX
    *       callback with assoc key = 'always' invoked for any response 
    *       callback with an (int) assoc key correlating to a 
    *          responses http status code will be invoked...
    *          for example:
    *             $callback[200] will be invoked if http status code 200 (OK).
    *             $callback[500] will be invoked if http status code 500 (Internal Server Error).
    *             $callback[404] will be invoked if http status code 404 (Not Found).
    *             etc...
    */
   public static function exec($config, array $flags=array('use_robots'), $cookie_file=NULL) {
      if (!$config instanceof curl\config ) {
         if (is_string($config)) {
            $config = new curl\config(
               new curl\request($config),
               'GET',
               'https://github.com/katmore/flat'
            );
         }
      }
      $success_callback=NULL;
      $always_callback=NULL;
      $fail_callback=NULL;
      $status_callback = array();
      if (is_callable($callback)) {
         $success_callback = $callback;
      } else
      if (is_array($callback)) {
         if (isset($callback['success']) && is_callable($callback['success'])) {
            $success_callback = $callback['success'];
         }
         if (isset($callback['always']) && is_callable($callback['always'])) {
            $always_callback = $callback['always'];
         }
         if (isset($callback['fail']) && is_callable($callback['fail'])) {
            $fail_callback = $callback['fail'];
         }
         if (isset($callback['status']) && is_array($callback['status'])) {
            foreach ($callback['status'] as $code=>$val) {
               $http_code = (int) sprintf("%d",$code);
               if (!empty($http_code) && is_callable($val)) {
                  $status_callback[$http_code] = $val;
               }
            }
         }
      }
      
      $page = new \flat\core\curl\response(
         $config,
         $flags
      );
      
      return $page;
   }
   
}









































