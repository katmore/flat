<?php
use flat\app\asset\hello\url;
use flat\app\tmpl\hello as tmpl;
?>
<html>
   <head>
      <title><?=tmpl::get_site_title()?></title>
   </head>
   <body>
      <h1>Hello World</h1>
      This is a trivial example of using the flat framework templating system to create a website.<br>
      Curious about how it worked? See the <a href="<?=url::asset("route_profile")?>">route profile</a>.<br>
      As this "Hello World" lacks an implementation of most of the more useful features it should not be looked
      upon as a practical use-case example.
      <?=tmpl::display("site-nav")?>
   </body>
</html>