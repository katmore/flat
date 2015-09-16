<?php
$event = '\flat\app\event\error';
$message=NULL;
if (class_exists($event) && is_a($event, 'flat\event',true)) {
   $event::trigger(function($data=NULL) use( & $message) {
      if (!empty($data->message) && is_string($data->message)) {
         $message = $data->message;
      }
   });
}
if (!$message && !empty($data->description)) $message = $data->description;
?>

<?php
if ($message) {
?>
<span message><?=$message?></span><!--/message-wrap-->
<?php
}
?>