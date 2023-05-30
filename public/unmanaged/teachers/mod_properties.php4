<?php
require_once("dir_set.inc.php4");
require_once("manage_course.lib.php4");

$controller->setView('DocumentPopup');   

function update_mod_prop($mod,$title,$status,$descr,$theme,$CID) {
   global $mod_list_table;
   $error['ModID']=!ereg("^[0-9]{1,4}$",$mod);
   $error['CID']=!ereg("^[0-9]{1,4}$",$CID);
   $error['Status']=!ereg("^[0-9]{1}$",$status);
   if (empty($status)) $error['Status']=$status;
   $title=return_valid_value($title);
   $theme=return_valid_value($theme);
   $descr=return_valid_value($descr);
   if (!is_dir("../COURSES/course".$CID."/mods/".$mod))
      mkdir("../COURSES/course".$CID."/mods/".$mod, 0700);
   chmod("../COURSES/course".$CID."/mods/".$mod,0777);
   if(empty($error['ModID']) && empty($error['Status'])) {
      $sql="UPDATE ".$mod_list_table." SET 
            Title=".$GLOBALS['adodb']->Quote($title).", 
            Pub='".$status."', 
            Num=".$GLOBALS['adodb']->Quote($theme).", 
            Descript=".$GLOBALS['adodb']->Quote($descr)." 
            WHERE ModID='".$mod."'";
      if ($sql_result=sql($sql))
         return 0;
   }
   return $error;
}

$refer="";

if (isset($_SERVER["HTTP_REFERER"]))
   $refer=$_SERVER["HTTP_REFERER"];
$pos = strpos($refer, $sitepath);
if ($pos === false) {
   header("location: ".$sitepath);
   exit();
}
echo "<!-- aoutirized -->";
?>
<HTML>
 <head>
  <META content="text/html; charset=windows-1251" http-equiv="Content-Type">
  <TITLE>eLearning Server 3000</TITLE>
  <SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
  <SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
  <SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
  <title>eLearning Server 3000</title>
  <link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
 </head>
 <body>
<?php
$GLOBALS['controller']->captureFromOb(CONTENT);
$_METHOD=$_GET;
if (isset($_METHOD['make']))
   if ($_METHOD['make']=="editModp") {
      if ($sql_result=get_all_mod_param($_METHOD['PID'],$_METHOD['CID'],$_METHOD['ModID'])) {
         $close=1;?>
  <form action="<?=$sitepath?>teachers/mod_properties.php4" target="_self" id="self" method="GET">
   <input type='hidden' name='ModID' value='<?=$_METHOD['ModID']?>'>
   <input type='hidden' name='CID' value='<?=$_METHOD['CID']?>'>
   <table width=99% border=0 cellspacing="3" cellpadding="0">
<?
$GLOBALS['controller']->captureFromOb(TRASH);
?>
    <tr>
     <td>
      <table width=99% border=0 cellspacing="0" cellpadding="0" class=brdr>
       <th width=100%><a> &nbsp;<span id=createtest><?=_("Править свойства модуля")?></span></a></th>
       </tr>
      </table>
     </td>
    </tr>
<?
$GLOBALS['controller']->captureStop(TRASH);
?>
    <tr>
     <td>
      <table width=99% border=0 cellspacing="1" cellpadding="5" class="brdr"><?
         while ($res=sqlget($sql_result)) {?>
       <tr class=questt>
        <td class="shedaddform"><?=_("Название")?>
        </td>
        <td ><input name="title" type="text" value="<?=stripslashes($res['mod_name'])?>" style="width:75%">
        </td>
       </tr>
       <tr class=questt>
        <td class="shedaddform"><?=_("Статус")?>
        </td>
        <td>
         <select name="status">
          <option value="" <? if (empty($res['pub'])) echo "selected";?>><?=_("Не опубликован")?></option>
          <option value="1" <? if (!empty($res['pub'])) echo "selected";?>><?=_("Опубликован")?></option>
         </select>
        </td>
       </tr>
       <tr class=questt>
        <td class="shedaddform"><?=_("Тема")?>
        </td>
        <td><input type="text" value="<?=stripslashes($res['theme'])?>" style="width:75%" name="theme">
        </td>
       </tr>
       <tr class=questt>
        <td class="shedaddform" valign='top'><?=_("Описание")?>
        </td>
        <td><?
            echo "<input type='text' name='descr' style='width:75%' value=\"".stripslashes($res['descript'])."\">";
            $close=0?>
      	<input type='hidden' name='make' value='editModcomplete'>            
        </td>
       </tr><?php 
         }?>
      </table>
     </td>
    </tr>
   </table>
<?
echo okbutton();
?>   
  </form>
<?php
      }
   }
   elseif ($_METHOD['make']=="editModcomplete") {?>
      <h2>Please wait</h2><?
      if ($error=update_mod_prop($_METHOD['ModID'],$_METHOD['title'],$_METHOD['status'], $_METHOD['descr'], $_METHOD['theme'],$_METHOD['CID'])) {
         $close=1;
         echo _("Ошибка при изменении данных!");
      }
   }
   if(!( isset($close))) {?>
      <script>
      opener.location.reload();
      window.close();
      </script>
<? 
   }
$GLOBALS['controller']->captureStop(CONTENT);
$controller->terminate();   
?>
 </BODY>
</HTML>