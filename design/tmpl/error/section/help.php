<?php
$event = '\flat\app\event\meta\app';
$app=NULL;
if (class_exists($event) && is_a($event, 'flat\event',true)) {
   
   $event::trigger(function($data=NULL) use( & $app) {
      if (!empty($data)) {
         
         if (is_a($data,'flat\app\meta\app')) {
            $app = $data;
         }

      }
   });
}
?>
<?php
if ($app) {
?>

<ul help="<?=$app->name?>">
   <?php
   if (!empty($app->sitemap)) {
   ?>
   <li><a href="<?=$app->contact_link?>">Contact Us</a></li>
   <?php
   }
   ?>
   <?php
   if (!empty($app->sitemap)) {
   ?>
   <li><a href="<?=$app->sitemap?>">Sitemap</a></li>
   <?php
   }
   ?>
</ul><!--/application-wrap-->
<?php
}
?>