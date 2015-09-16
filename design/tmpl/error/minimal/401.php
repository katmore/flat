<?php
$error = (object) array(
   'heading'=>'Unauthorized',
   'title'=>'Unauthorized',
   'description'=>'unauthorized request for this resource'
);
$tmpl='flat\app\tmpl\error\minimal';
if (!class_exists($tmpl) || !is_a($tmpl,'flat\tmpl',true)) $tmpl=NULL;
if ($tmpl && $tmpl::check_design('page')) {
   $tmpl::display('page',$error);
} else {
   echo $error->heading . ": ". $error->description;
}