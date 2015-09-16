<?php
$page = (object) array(
   'title'=>'error',
   'heading'=>'error',
   'description'=>'we are experiencing difficulties'
);
$show['application'] = false;
$show['description'] = false;
$show['message'] = true;

$tmpl='\flat\app\tmpl\error';
if (!class_exists($tmpl) || !is_a($tmpl,'flat\tmpl',true)) $tmpl=NULL;
if (is_object($data))
foreach ($page as $prop=>&$val) if (isset($data->$prop)) $val=$data->$prop;
?>
<!doctype html>
<title><?=$page->title?></title>
<style>
h1 { font-size: 2.5em; }
body { font-family: Constantia, 'Hoefler Text',  "Adobe Caslon Pro", Baskerville, Georgia, Times, serif; color: black; text-shadow: 5pt 5pt 5pt rgba(200, 200, 200, 0.5); }
a { color: blue; text-decoration:none; }
a:hover { color: black ; text-shadow: 5pt 5pt 5pt gray; text-decoration: underline; }
[data-frown] { transform: rotate(90deg); display:inline-block;}
[data-timestamp] { font-size: 0.75em;}

<?php
foreach($show as $section=>$enabled) {
   if (isset($_GET) && isset($_GET["enable_".$section]) || isset($_GET) && isset($_GET["enable-".$section])) {
      if (filter_var($_GET["enable_".$section],FILTER_VALIDATE_BOOLEAN)) {
         $enabled = true;
         //echo "enabled....\n";
      }
      //die("enabled $section");
   }
   if ($enabled) {
   ?>
   [data-<?=$section?>]:after {content: "\a"; white-space: pre;} 
   <?php
   } else {
   ?>
   [data-<?=$section?>] {display: none;}
   <?php
   }
}
?>

hr {margin: 0.5em 0 0 0;}
</style>
<h1><?=$page->heading?></h1>
<div data-error-info><span data-frown>:(</span>&nbsp;<span data-description>error: <?=$page->description?></span>
<?=($tmpl)?$tmpl::format('section/message',array('silent_fail'),array('data'=>$page)):""?>
<?=($tmpl)?$tmpl::format('section/application',array('silent_fail'),array('data'=>$page)):""?>
<?=($tmpl)?$tmpl::format('section/help',array('silent_fail'),array('data'=>$page)):""?>
</div>
<hr>
<span data-timestamp="<?=date("c")?>"><?=date("F j, Y g:ia")?></span>