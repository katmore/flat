<?php
use flat\app\asset\hello\url;
use flat\app\tmpl\hello as tmpl;
$title = "Routing Explation";
?>
<html>
   <head>
      <title><?=$title?> - <?=tmpl::get_site_title()?></title>
   </head>
   <body>
      <h1><?=$title?></h1>
      Routing in flat starts with a "resource" and proceeds to attempt to "resolve" the
      resource into one or more class instances.<br>
      <br>
      The rules for resolving a resource should be defined by route factory classes placed in the flat\app\route namespace.<br>
      To facilitate routing a website, see 'examples/web-service/html-route.php' which is the "html route entry point".
      the "flat\deploy\html" bootloader class which initializes the class autoloaders, flat's config system, 
      and optionally flat's special error handling.<br>
      <br>
      The bootloader then creates a routing controller \flat\core\controller\route instance, and instructs it to apply the rules defined in<br>
      &nbsp;&nbsp;flat\app\route\html class (the 'route factory' for html routes) for a configured "resource".<br>
      What the value of the "resource" is depends on the value of the 'resource' array index is defined in the 
      config file flat/deploy/config.php. Out of the box, it is set to the value of $_SERVER ['PATH_INFO'], 
      should that superglobal exist. It could easily be configured to be the value of a query var (such as with $_GET['p']), 
      an argument value (like $argv[1]), or any other available superglobal, constant, or string literal. <br>
      Most documentation and examples work on the assumption the resource is $_SERVER['PATH_INFO'] or something equivelent.<br> 
      <br>
      The out-of-box definition for html routes is to look for a class with a name corresponding to the resource,
      by appending the resource name to flat\app\tmpl, and seeing if an instantiatable class exists. If no such
      class exists, it has a rule to return a template class flat\app\notfound, which is a template that refers to 
      flat\tmpl\error\minimal template definition. 
      <br>
      Again, this is all just an explanation of the out-of-box definitions and configuration, which are of course 
      meant to be modified.
      <?=tmpl::display("site-nav")?>
   </body>
</html>