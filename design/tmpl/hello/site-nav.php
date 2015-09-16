<?php
use flat\app\asset\hello\url;
?>
<hr>
<ul>
   <li><a href="<?=url::asset("home")?>">home</a></li>
   <li><a href="<?=url::asset("routing")?>">how flat routing works</a></li>
   <li><a href="<?=url::asset("about")?>">about</a></li>
</ul>