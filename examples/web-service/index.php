<?php
//
// -- this file originally from flat/examples/web-service/index.php
//
/* 
 * The flat framework is copyrighted free software.
 * Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * See the full license and copyright:
 * https://github.com/katmore/flat/LICENSE.txt
 */
/**
 * placeholder index for flat web-services
 *
 * PHP version >=7.0.0
 */
return (function() {
/**
 * @var string $reporoot_url
 *    URL to flat framework source repo
 */
$reporoot_url = "https://github.com/katmore/flat";
/**
 * @var string $path_href_prefix
 *    href prefix for resources documented in index.
 *    default configuration is to the path containing this script when $_SERVER['REQUEST_URI'] is available,
 *       or to the value "./" string literal when $_SERVER['REQUEST_URI'] not available.
 */
$path_href_prefix = (isset($_SERVER['REQUEST_URI'])) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : "./";
?>
<html>
   <head>
      <title>flat framework web-service index</title>
      <style>
      .footer-info { font-size: 0.80em; }
      </style>
      <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
   </head>
   <body>
      <h1>flat framework web-service index</h1>
      
      The 'web-service' examples are useful for implementing template based websites (with flat/app/tmpl/ defined classes) and 
         RESTful API services (with flat/app/api/ defined classes).<br>
      <br>
      Please note the full contents of the flat package should not reside within a publically accessible web root.<br>
      See <a href="<?=$reporoot_url?>/examples/web-service/README.txt">README.txt</a> and <a href="<?=$reporoot_url?>/INSTALL.md">INSTALL.md</a> for install and config instructions.
      
      <ul>
         <li>The following resources should reside INSIDE the publically accessible web root:
            <ul>
            <?php
            foreach([
               [
                  'type'=>'file',
                  'name'=>'api-route.php',
                  'repo_dir'=>'/examples/web-service',
                  'purpose'=>'routes RESTful API service requests as configured in flat/app/route/api.php',
               ],
               [
                  'type'=>'file',
                  'name'=>'html-route.php',
                  'repo_dir'=>'/examples/web-service',
                  'purpose'=>'routes website requests as configured in flat/app/route/html.php',
               ],
               [
                  'type'=>'dir',
                  'name'=>'asset',
                  'repo_dir'=>'',
                  'purpose'=>'contains static resources that may be required by flat application; such as: javascript, css, image, and video files.',
               ],
            ] as $path) {
            ?>
               <li>
                  <a href="<?=$path_href_prefix.$path['name']?>"><?=$path['name']?></a> <?=$path['type']?> should start as a copy of:
                  <ul><li><?=$reporoot_url?><?=$path['repo_dir']?>/<?=$path['name']?></ul>
                  It <?=$path['purpose']?>.
               </li>
            <?php
            }
            ?>
            </ul>
         </li>
         <li> The following flat package directories should be copied to the system OUTSIDE the publically accessible web root:
            <ul>
               <?php
               foreach(['app','deploy','design','src/flat'
                  ] as $dir) {
               ?>
               <li>flat/<?=$dir?>/*
               <?php
               }
               ?>
            </ul>
         </li>
      </ul>
      <hr>
         <span class="footer-info">
         The flat framework. 
         Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.<br>
         The flat framework is free software. See <a href="<?=$reporoot_url?>/LICENSE.txt">LICENSE.txt</a><br>
         Full source is available at <a href="<?=$reporoot_url?>"><?=str_replace(["http://","https://"],"",$reporoot_url)?></a>.
         </span>
   </body>
</html>
<?php
})();