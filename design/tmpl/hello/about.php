<?php
use flat\app\asset\hello\url;
use flat\app\tmpl\hello as tmpl;
$title = "About";
$reporoot_url = "https://github.com/katmore/flat";
?>
<html>
   <head>
      <title><?=$title?> - <?=tmpl::get_site_title()?></title>
   </head>
   <body>
      <h1><?=$title?></h1>
         <b>The flat framework.</b><br>
         Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.<br>
         The flat framework is free software. See <a href="<?=$reporoot_url?>/LICENSE.txt">LICENSE.txt</a><br>
         Full source is available at <a href="<?=$reporoot_url?>"><?=str_replace(["http://","https://"],"",$reporoot_url)?></a>.
      <?=tmpl::display("site-nav")?>
   </body>
</html>