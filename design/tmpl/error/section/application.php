<?php
$event = '\flat\app\event\meta\app';
$encoder = '\flat\core\html\encode';
$app=NULL;
$info="";
if (class_exists($event) && is_a($event, 'flat\event',true)) {
   
   $event::trigger(function($data=NULL) use( & $app, & $info,  & $encoder) {
      if (!empty($data)) {
         $app = $data;
         if (class_exists($encoder) && is_a($app,'flat\app\meta\app')) {
            $info = $encoder::serialize($data);
         } else {
            ob_start();
            var_dump($data);
            $info = htmlentities(ob_get_clean());
         }
      }
   });
}
?>
<?php
if ($app) {
?>

<div data-application="<?=$app->name?>">
   application:
<div data-application-info >
<?=$info?>
</div><!--/application-info-->
</div><!--/application-wrap-->
<?php
}
?>