<?php
#####################################################################
#
#   c - export видимо пременная отвечающая что делать
#   qar - скорей всего question array список вопросов для экспорта
#   ok_x, ok_y координаты на кнопке где кликнули мышкой.
#
#####################################################################
#####################################################################
/*
echo "<b>POST</b><br>";
foreach($_POST as $key=>$value) {
  echo $key." - ".$value."<br>";
}
echo "<br><br>";

echo "<b>GET</b><br>";

foreach($_GET as $key=>$value) {
  echo $key." - ".$value."<br>";
}
*/
#####################################################################



   include_once("1.php");
   include_once("test.inc.php");
   include_once($wwf."/template_test/1-v.php");
   include_once($wwf."/template_test/2-v.php");
   include_once($wwf."/template_test/3-v.php");
   include_once($wwf."/template_test/4-v.php");
   include_once($wwf."/template_test/5-v.php");
   include_once($wwf."/template_test/6-v.php");
   include_once($wwf."/template_test/7-v.php");
   include_once($wwf."/xml2/xml.class.php4");
   require_once($wwf."/lib/PEAR/Archive/Zip.php");

   if (!$s[login])
     exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

   if ($s[perm]<2)
     exitmsg(_("К этой странице могут обратится только: преподаватели, учебная администрация, администраторы"),"/?$sess");

   if (count($s[tkurs])==0)
     exitmsg(_("Хоть вы и преподаватель, но в данное время не владеете ни одним курсом."),"/?$sess");

   if (!isset($qar) || !is_array($qar) || !count($qar)) {
      //backbutton();
      exitmsg(_("Вы не передали вопросы, которые хотите экспортировать. Вернитесь назад и отметьте необходимые для экспорта вопросы."));
   }

function check_access_qar() {
   global $qar,$s;
   $out=array();
   foreach ($qar as $v) {
      if (!preg_match("!^([0-9]{1,9})-!s",$v,$ok)) continue;
      if (!isset($s[tkurs][$ok[1]]) && $s[perm]<3)
         exit(backbutton()."errEX202: ACCESS DENIED - "._("Вы не можете экспортировать
         вопрос")." ID=[".strip_tags($v)."], "._("т.к. нет прав читать чужие вопросы. Вы
         можете экспортировать только вопросы своего курса. Администраторы и учебная администрация могут
         экспортировать любые вопросы, со всех курсов. Преподаватели - только
         вопросы своих курсов."));
      $out[]=$v;
   }
   $qar=$out;
}

switch ($c) {
   case "export":
   $allxml = prepare_xml($qar);   
   break;
}

/**
* Архивирование и выдача результата
*/
$tmpdir = tempdir($wwf.'/temp/');
mkdirs($tmpdir.'/files');

if (is_dir($tmpdir.'/files')) {

$filelist = prepare_files($tmpdir.'/files', $qar);    
    
if ($fp = fopen($tmpdir.'/course.xml','w')) {
    fwrite($fp,$allxml);
    $filelist[] = $tmpdir.'/course.xml';    
}
fclose($fp);

$tmpname = tempnam($wwf.'/temp/','els');

if ($zip = new Archive_Zip($tmpname)) {
    $zip->create($filelist, array('remove_path' => $tmpdir.'/'));
    if (file_exists($tmpname) && is_readable($tmpname) && ($content = file_get_contents($tmpname))) {
        $allxml = $content;
        unset($content);
    }
}
@unlink($wwf.'/temp/course.xml');
@unlink($tmpname);
@deldir($tmpdir);
}

while(ob_get_contents()) ob_end_clean();
header("Content-type: application/unknown");
header("Content-disposition: attachment; filename=\"questions.zip\";");
echo $allxml;
exit();

function mkdirs($dir, $mode = 0777, $recursive = true) {
    
  if( is_null($dir) || $dir === "" ) {
      return FALSE;
  }  
  if( is_dir($dir) || $dir === "/" ) {
      return TRUE;
  }
  if( mkdirs(dirname($dir), $mode, $recursive) ) {
      $oldumask = umask(0);
      $ret = mkdir($dir, $mode);
      umask($oldumask);
      return $ret;
  }  
  return FALSE;
}

function deldir($dir){
    $d = dir($dir);
    while($entry = $d->read()) {
        if ($entry != "." && $entry != "..") {
            if (Is_Dir($dir."/".$entry))
                deldir($dir."/".$entry);
            else
                unlink ($dir."/".$entry);
        }
    }
    $d->close();
    @rmdir($dir);
}

/*
switch ($send) {
       case 1:
          header("Content-type: application/unknown");
          header("Content-disposition: attachment; filename=\"elearn.xml\";");
          echo $allxml;
          break;
       case 2:
          echo $allxml;
          break;
       case 3:
          echo "<pre>".html($allxml)."</pre>";
          break;
       case 4:
          echo "<textarea name=x cols=40 rows=15 style='width:100%; height:95%'>".html($allxml)."</textarea>";
          break;
}
*/
?>