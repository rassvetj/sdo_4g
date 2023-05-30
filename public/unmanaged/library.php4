<?
/* ver 1.0     */
/* init     */

/* include  */
// require_once('phplib.php4');
   require_once('1.php');
   require_once('news.lib.php4');

// if (empty($HTTP_COOKIE_VARS['userMID'])) $top1=true;
// print_r($HTTP_COOKIE_VARS);

   define('MAX_UPLOAD_FILE_SIZE','10000000');   
  
   if (isset($_GET['deletefile']) && !empty($_GET['deletefile']) && ($teach || $dean || $admin)) {
   
       $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.$_GET['deletefile'];
       
       if (file_exists($filename)) @unlink($filename);
       
   }


   if (!isset($_POST['make'])) echo show_tb();
   else
   {
?>
<HTML>
<head>
<META content="text/html; charset=<?php echo $GLOBALS['controller']->lang_controller->lang_current->encoding;?>" http-equiv="Content-Type">
<script language="Javascript" type="text/javascript">
<!--
    if(navigator.appName.indexOf("Netscape") != -1){
      alert("<?=_("Используйте IE версии 5.0  или выше!")?>");
      close();
        }
    if (navigator.userAgent.indexOf("Opera") != -1){
      alert("<?=_("Используйте IE версии 5.0  или выше!")?>");
      close();
    }
// -->
</script>
<SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
<title>eLearning Server 3000</title>
<link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
</head>
<BODY  class=cPageBG leftmargin=0 rightmargin=0 marginwidth=0 topmargin=0 marginheight=0>
<?

   }

/* run      */

function getCourceTitle($CID){
   return getField($coursestable,"Title","CID",$CID);
}

function getValue($v){
        echo (isset($v)) ?  $v : "" ;
}

function update_book($id,$Name,$Author,$Year,$Izdatel,$Description,$Url,$CID)
   {
   global $_POST;
   if ((!empty($Name)) && (!empty($Author)) && (!empty($Year)) && (!empty($Izdatel)) && (!empty($Url)))
   {
      
       $i=1;
       
       if (isset($_FILES['uploadfile1'])) {
          
              $path_parts = pathinfo($_FILES['uploadfile1']['name']);          
              $file_ext = $path_parts['extension'];
          
      }

      @sql("UPDATE Knigi
      SET Name='".$Name."',Author='".$Author."',Izdatel='".$Izdatel."',Year='".$Year."',Description='".$Description."',
      CID='".$CID."',Url='".$Url."',access='".trim(strip_tags($_POST['access']))."',file_active='".$_POST['active']."' WHERE KID='".(int) $id."'") or die("Couldnt exec Query Add book");
      
//      $id = sqllast();
      
//      if ($id) {
          for($i=1;$i<=5;$i++) {
          if(isset($_FILES["uploadfile{$i}"]) && !empty($_FILES["uploadfile{$i}"]['name'])) {
          
          $file = $_FILES["uploadfile{$i}"];
          
          if ($file['size'] <= MAX_UPLOAD_FILE_SIZE) {
          
              $path_parts = pathinfo($file['name']);
              $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $id."_{$i}.".$path_parts['extension'];
              @unlink($filename);
              if (move_uploaded_file($file['tmp_name'], $filename)) {
                  
                  // проверки или обработки файла книги ???
                  
              } else echo"<span align='center'>"._("Файл не найден")."<span><br>";
              
          } else echo"<span align='center'>"._("Размер файла превышает лимит размера загружаемого файла")."<span><br>";
          
//          $i++;
                    
      }
          }

//      }
      
//      echo"<span align='center'>Книга добавлена<span><br>";
      
   }else{
       
         echo _("Не все поля заполнены. Пожалуйста заполните все необходимые поля.")."<br>";

     }
   }

function insert_book($Name,$Author,$Year,$Izdatel,$Description,$Url,$CID)
   {
   global $_POST;
   if ((!empty($Name)) && (!empty($Author)) && (!empty($Year)) && (!empty($Izdatel)) && (!empty($Url)))
   {
      
       $i=1;
       
       if (isset($_FILES['uploadfile1'])) {
          
              $path_parts = pathinfo($_FILES['uploadfile1']['name']);          
              $file_ext = $path_parts['extension'];
          
      }

      @sql("INSERT INTO Knigi (Name,Author,Izdatel,Year,Description,CID,Url,access,file_ext,file_active) 
      VALUES ('".$Name."','".$Author."','".$Izdatel."','".$Year."','".$Description."',
      '".$CID."','".$Url."','".trim(strip_tags($_POST['access']))."','".
      addslashes($file_ext)."','".$_POST['active']."')") or die("Couldnt exec Query Add book");
      
      $id = sqllast();
      
      if ($id) {
          
          while(isset($_FILES["uploadfile{$i}"])) {
          
          $file = $_FILES["uploadfile{$i}"];
          
          if ($file['size'] <= MAX_UPLOAD_FILE_SIZE) {
          
              $path_parts = pathinfo($file['name']);
              $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $id."_{$i}.".$path_parts['extension'];
              if (move_uploaded_file($file['tmp_name'], $filename)) {
                  
                  // проверки или обработки файла книги ???
                  
              } else echo"<span align='center'>"._("Файл не найден")."<span><br>";
              
          } else echo"<span align='center'>"._("Размер файла превышает лимит размера загружаемого файла")."<span><br>";
          
          $i++;
                    
      }

      }
      
      echo"<span align='center'>"._("Книга добавлена")."<span><br>";
      
   }else{
       
         echo _("Не все поля заполнены. Пожалуйста заполните все необходимые поля.")."<br>";

     }
   }


if (isset($add_book) && (isset($teach))) insert_book($Name,$Author,$Year,$Izdatel,$Description,$Url,$CID);

   if (isset($_POST['editbook']) && ($_POST['editbook']=='editbook') && ($admin || $dean || $teach)) {
       
       update_book($_POST['bid'],$Name,$Author,$Year,$Izdatel,$Description,$Url,$CID);
       
   }

function ShowForm($globCID){
   global $year,$Izdatel,$Author,$Name,$Description,$coursestable;
?>



<table BORDER="0" CELLSPACING="4" CELLPADDING="0" align='center' >
  <form method="POST" action="library.php4">
   <input type='hidden' name='form_send' value='1'>
<?
   if (isset($_POST['make']))
   {
?>
   <input type='hidden' name='make' value='1'>
<?
   }
?>
<tr>
<td>
<table BORDER="0" CELLSPACING="3" CELLPADDING="0" align='center' >
<tr><th class=brdr>&nbsp;<?=_("Название курса")?>&nbsp;</th></tr>
<tr><td class=brdr>
                    <select name="select" class=lineinput style="border: inset 1px; width: 150px;">
                    <option value="0"><?=_("Литература")?></option>
<?
         $sql=("SELECT Title,CID FROM ".$coursestable." WHERE Status>0");
         $result=sql($sql) or die("Couldnt exec Query");;
         while($row = sqlget($result))
         {
         extract($row);
            if($globCID==$CID)
               echo "<option selected value='".$CID."'>".$Title."</option>";
               else
               echo "<option value='".$CID."'>".$Title."</option>";
         }

?>

</td></tr>
<tr><th class=brdr>&nbsp;<?=_("Издательство")?>&nbsp;</th></tr>
<tr><td class=brdr><input type="text" name="Izdatel" class=lineinput style="border: inset 1px; width: 150px;" value='<?  getValue($Izdatel);?>'></td></tr>
</table>
</td><td>
<table BORDER="0" CELLSPACING="2" CELLPADDING="0" align='center'>
<tr><th class=brdr>&nbsp;<?=_("Год выпуска")?>&nbsp;</th></tr>
<tr><td class=brdr>
<input type="text" name="year" class=lineinput style="border: inset 1px; width: 150px;" value='<?  getValue($year);?>'>
</td></tr>
<tr><th class=brdr>&nbsp;<?=_("Автор")?>&nbsp; </th> </tr>
<tr><td class=brdr>
<input type="text" name="Author" class=lineinput style="border: inset 1px; width: 150px;" value='<?  getValue($Author);?>'>
</td></tr>
</table>
</td><td>
<table BORDER="0" CELLSPACING="2" CELLPADDING="0" align='center' >
<tr><th class=brdr>&nbsp;<?=_("Название книги")?>&nbsp;</th></tr>
<tr><td class=brdr>
<input type="text" name="Name" class=lineinput style="border: inset 1px; width: 150px;" value='<?  getValue($Name);?>'>
</td></tr>
<tr><th class=brdr>&nbsp;<?=_("Описание")?>&nbsp;</th> </tr>
<tr><td class=brdr>
<input type="text" name="Description" class=lineinput style="border: inset 1px; width: 150px;" value='<?  getValue($Description);?>'>
</td></tr>
</table>
</td></tr>
<tr>
   <td colspan=3 aligh=right>
        <input type="image" name="add_schedule_send"
   onMouseOver="this.src='images/send_.gif';"
   onMouseOut="this.src='images/send.gif';"
           src="images/send.gif" align="right" alt="Ok" border=0>
      </td> </tr>

</form>
</table>

<?

}

function ShowAction(){
   global $s;
?>                    <br><br>
            <table width="90%" border=0 cellspacing=0 cellpadding=0 class=shedadd align='center'>
                              <tr>
                                <td width="27" id="showtask" class=shown><a class=webdna onClick="putElem('newtask'); putElem('hidetask'); removeElem('showtask');">4</a></td>
                                <td width="27" id="hidetask" class=hidden><a class=webdna onClick="createtask1.innerText='<?=_("добавление нового ресурса")?>'; removeElem('newtask'); putElem('showtask'); removeElem('hidetask');">6</a></td>
                                <td>&nbsp;<span id="createtask1"><?=_("добавление нового ресурса")?></span>&nbsp;</td>
                              </tr>
                        <!-- /добавить задание  -->
                        <tr>
                         <td><img src="images/spacer.gif" alt="" width=1 height=4 border=0></td>
                        </tr>
                      </table>
                      <table id=newtask width=90% class=hidden border="0" cellspacing="0" cellpadding="0" align='center'>
                        <tr>
                          <td>
                            <!--  /добавить  -->
         <form method="POST" action="library.php4" enctype="multipart/form-data">
            <input type='hidden' name='add_book' value='1'>
<?
   if (isset($_POST['make']))
   {

?>
   <input type='hidden' name='make' value='1'>
<?
   }
   
?>
                            <table width=100% border=0 cellspacing=0 cellpadding=1 class=brdr align=center>
                              <tr>
                                <td class=questt >
                                  <table width=100% border=0 cellspacing=0 cellpadding=0   class=cHilight>
                  <tr><td colspan=2><img src=images/spacer.gif width=148 height=14></td></tr>
                  <tr><td class="shedaddform"><?=_("курс")?></td>
                                      <td class="shedaddform" align='center'><select name="CID" class=lineinput style="border: inset 2px; width: 400px;">
<option value='0'><?=_("Литература")?></option>
<?
   $sql="SELECT Courses.Title,Courses.CID FROM Courses,Teachers WHERE Teachers.CID = Courses.CID AND Teachers.MID='".$s['mid']."'";
   echo "$sql<br>";
   $result=sql($sql) or die("Ne pashet sql zapros - '$sql'");
      while($row = sqlget($result)){
         extract($row);
         echo "<option value='$CID'>$Title</option>";
      }
                        ?>
                      </select></td></tr>
                  <tr><td class="shedaddform"><?=_("Название книги")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Name" class=lineinput style="width: 400px; "></td></tr>
                                      <tr><td class="shedaddform"><?=_("авторы")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Author" class=lineinput style="width: 400px; "></td></tr>
<!--                                      <tr><td height=7><img src="images/spacer.gif" width=1 height=1></td></tr>
-->
                                      <tr><td class="shedaddform"><?=_("Издательство")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Izdatel" class=lineinput style="width: 400px; "></td></tr>
                                      <tr><td class="shedaddform"><?=_("Год издания")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Year" class=lineinput style="width: 400px; "></td></tr>
                                      <tr><td class="shedaddform"><?=_("Описание")?></td>
                                      <td class="shedaddform" align='center'><textarea name="Description" size='8' style="width: 400px; "></textarea></td></tr>
                                      <tr><td class="shedaddform">URL</td>
                                      <td class="shedaddform" align='center'><input type="text" name="Url" class=lineinput value='http://' style="width: 400px; "></td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 1:
                                      <input type="radio" name="active" value="1" checked>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile1" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 2:
                                      <input type="radio" name="active" value="2">
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile2" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 3:
                                      <input type="radio" name="active" value="3">
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile3" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 4:
                                      <input type="radio" name="active" value="4">
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile4" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 5:
                                      <input type="radio" name="active" value="5">
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile5" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Уровень доступа")?></td>
                                      <td class="shedaddform" align='center'>
                                      <select name="access" style='width: 400px;'>
<?php
    for($i=0;$i<=10;$i++) echo "<option value=\"".(int) $i."\"> ".(int) $i."</option>";
?>                                      
                                      </select>
                                      </td></tr>                                      
                                      <tr><td colspan=2><img src=images/spacer.gif width=148 height=14></td></tr>
                                  </table>
                                </td>
                               </tr>
                              </table>
               <tr><td><img src="images/spacer.gif" width=1 height=5></td></tr>
                        <tr><td align="right"><input type="image" name="add_schedule_send"
               onMouseOver="this.src='images/send_.gif';"
               onMouseOut="this.src='images/send.gif';"
                         src="images/send.gif" align="right" alt="Ok" border=0><br></td></tr>

                                    </form>
                             </table>
<?
}



function ShowNiz($CID,$NumberKnigi,$a){
   global $s; 
   global $admin, $stud, $dean, $teach;
   global $iAmTeacher;
?>
<form action='library.php4' method='POST'>
<table width="82%" border="0" cellspacing="1" cellpadding="2" align='center'>
 <hr size='1'class=br  noshade>

 <tr>
    <input type='hidden' name='BOOK_ID' value='<?=$a["KID"]?>'>
    <td>&nbsp;</td>
    <td CLASS='cHilight' width='20px'><?
  if($iAmTeacher){
    ?><span class=wing align='right'style='cursor:hand' title="<?=_("удалить")?>"
      onclick='if(confirm("<?=_("Вы уверены, что хотите удалить ресурс")?> <?=$a["Name"]?>?"))submit();'>
      <? echo getIcon("x"); ?></span><?
  }
?></td>
  </tr>
 <tr>
    <td CLASS=schedule style="padding: 1px;"><?=$NumberKnigi?>.<b><?=$a["Name"]?></b>
<?php  if($iAmTeacher){
    echo " &nbsp; <a href=\"{$sitepath}library.php4?action=edit&bid={$a['KID']}\">"._("редактировать")." >></a>";
    
}
?>
    
    </td>
    <td CLASS=schedule style="padding: 1px;">&nbsp;</td>
  </tr>
  <tr>
    <td CLASS=schedule style="padding: 1px;"><?=$a["Author"]?></td>
    <td CLASS=schedule style="padding: 1px;">&nbsp;</td>
  </tr>
  <tr>
    <td CLASS=schedule style="padding: 1px;"><?=$a["Izdatel"]?>, <?=$a["Year"]?></td>
    <td CLASS=schedule style="padding: 1px;">&nbsp;</td>
  </tr>
    <tr>
    <td><font size="2" face="Arial"><i><?=$a["Description"]?></i></font></td>
    <td CLASS=schedule style="padding: 1px;"></td>
  </tr>
<?php
if (isset($s['user']['meta']['access_level']) && ($a['access'] >= $s['user']['meta']['access_level'])) {
?>
  <tr>
    <td CLASS=schedule style="padding: 1px;"><?="<a href=".$a["Url"]." target='_blank'>".$a["Url"]."</a>"?></td>
    <td CLASS=schedule style="padding: 1px;"></td>
  </tr>
<?php
    if ($a['file_ext']) {
        
       $i=1;
        
       $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $a['KID']."_{$i}.".$a['file_ext'];

       while (file_exists($filename)) {
           
           if (($admin || $teach || $dean) || ($stud && ($a['file_active']==$i))) {
        
?>
  <tr>
    <td CLASS=schedule style="padding: 1px;"><?="<a href=\"{$sitepath}library/{$a['KID']}_{$i}.{$a['file_ext']}\" target='_blank'>"._("скачать файл")." {$i} [".date("d.m.Y",filemtime($filename))."]</a>"?>
<?php
 if ($a['file_active']==$i) {
     echo "&nbsp; <img height=11 src=\"{$sitepath}images/icons/ok.gif\">";
 }
?>    
    </td>
    <td CLASS=schedule style="padding: 1px;">
<?php if($iAmTeacher) { ?>
    <a href="<?php echo $sitepath?>library.php4?deletefile=<?php echo $a['KID'].'_'.$i.'.'.$a['file_ext']; ?>" title="<?=_("удалить")?>"
    onclick='if(confirm("<?=_("Вы уверены, что хотите удалить файл ресурса?")?>")) return true; else return false;'>
    <? echo getIcon("x"); ?></a>
<?php } ?>    
    </td>
  </tr>
<?php

       }

       $i++;
       $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $a['KID']."_{$i}.".$a['file_ext'];
       }
    }
    
}
?>
</table>
</form>
<?
}

?>    <table border=0 cellpadding=0 cellspacing=0 align=center valign=top width="540">
                <!-- заголовок -->
                <tr valign=top>
                  <td class=skip valign=top>
                    <table border=0 cellpadding=0 cellspacing=0 align=center valign=top width="100%" class=cHilight>
                      <tr valign=top><td height=20 width="100" class="cMainBG" valign=top colspan=3><img src="images/spacer.gif"></td></tr>
                      <tr valign=top>
                        <td valign=top class=shedtitle ><?=_("Библиотека")?></td>
                        <td width="100%" background="images/schedule/back.gif"><img src="images/spacer.gif"></td>
                        <td height=19 valign=bottom nowrap><b class=wingna style="font-size: 14px">&#224;</b></div></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr><td><img src="images/spacer.gif" alt="" width=1 height=20 border=0></td></tr>
                <tr><td><?
    echo show_info_block( 0, "[ALL-CONTENT]", "-~lib~-"  );// выводит информацию блоками
?>
</td></tr>
<?
if(!empty($BOOK_ID)) {
    
   $sql = "SELECT file_ext FROM Knigi WHERE KID='".(int) $BOOK_ID."'"; 
   $res = sql($sql);
   if (sqlrows($res)) $row = sqlget($res);
    
   @sql("DELETE FROM Knigi WHERE KID=$BOOK_ID");
   
   /**
   * Удаления файла
   */
   if (isset($row['file_ext'])) {
       
       $i=1;
       $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $BOOK_ID."_$i.".$row['file_ext'];
       while (file_exists($filename)) {
           
            @unlink($filename);
        
            $i++;   
            $filename = $_SERVER['DOCUMENT_ROOT'].'/library/'.(int) $BOOK_ID."_$i.".$row['file_ext'];
       }
       
   }
   
}

if(isset($select))
   ShowForm($select);
else
   ShowForm(-1);

$iAmTeacher=$teach;
if($iAmTeacher)   ShowAction();

if(!empty($year) || !empty($Description)|| !empty($Izdatel) || !empty($Name) || !empty($Author) || (isset($select) /*&& $select!=0*/)){
   $myWhere="";
   if(!empty($year))
         $myWhere="year='$year'";

   if(!empty($Description)){
      if($myWhere!="")$myWhere.=" AND";
//         $myWhere.=" Description='$Description'";
      $myWhere.=" LOCATE('$Description',Description)";
   }

   if(!empty($Izdatel)){
      if($myWhere!="")$myWhere.=" AND";
           $myWhere.=" LOCATE('$Izdatel',Izdatel)";
   }

   if(!empty($Name)){
      if($myWhere!="")$myWhere.=" AND";
           $myWhere.=" LOCATE('$Name',Name)";
   }

   if(!empty($Author)){
      if($myWhere!="")$myWhere.=" AND";
           $myWhere.=" LOCATE('$Author',Author)";
   }

        if(isset($select)){
      if($myWhere!="")$myWhere.=" AND";
      $myWhere.=" CID=$select";
        }
   if($myWhere!="")
      $myWhere="WHERE ".$myWhere;

   $sql="SELECT * FROM Knigi $myWhere";
   //echo "---'$sql'---<br>";
   $result=sql($sql) or die("Ne pashet sql zapros");

   if($row = sqlget($result)){

      $i=0;
      do{
         $my_a[$i]=$row;
         $my_array_index[$i]=$row["Name"];
         $i++;
      } while($row = sqlget($result));

      asort($my_array_index);
      reset($my_array_index);
      $NumberKnigi=1;
      while (list($k,$value) = each($my_array_index)) {
         ShowNiz(0,$NumberKnigi,$my_a[$k]);
         $NumberKnigi++;
      }

   }else{
      echo "<h4 align='center'>"._("Ничего не найдено. Попробуйте изменить условия поиска")."</h4><br>";
   }
}else{
   if(isset($form_send)){
//      if ($select==0){
//         echo "<h4 align='center'>Необходимо задать еще один критерий поиска</h4>";
//      }
   }
}
// ====================================================================================
   if (isset($_GET['action']) && ($_GET['action'] == 'edit') &&
      isset($_GET['bid']) && ($_GET['bid'] > 0)) {
          
          $sql = "SELECT * FROM Knigi WHERE KID='".(int) $_GET['bid']."'";
          $res = sql($sql);
          
          $row = sqlget($res);
   
?>
                      <table id=newtask width=90% border="0" cellspacing="0" cellpadding="0" align='center'>
                        <tr>
                          <td>
                            <!--  /добавить  -->
         <form method="POST" action="library.php4" enctype="multipart/form-data">
<?php
   if (isset($_GET['action']) && ($_GET['action'] == 'edit')) echo " <input type='hidden' name='editbook' value='editbook'>";
   if (isset($_GET['bid']) && ($_GET['bid'] > 0)) echo " <input type='hidden' name='bid' value='{$_GET['bid']}'>";
?>
                            <table width=100% border=0 cellspacing=0 cellpadding=1 class=brdr align=center>
                              <tr>
                                <td class=questt >
                                  <table width=100% border=0 cellspacing=0 cellpadding=0   class=cHilight>
                  <tr><td colspan=2><img src=images/spacer.gif width=148 height=14></td></tr>
                  <tr><td class="shedaddform"><?=_("курс")?></td>
                                      <td class="shedaddform" align='center'><select name="CID" class=lineinput style="border: inset 2px; width: 400px;">
<option value='0'><?=_("Литература")?></option>
<?
   $sql="SELECT Courses.Title,Courses.CID FROM Courses,Teachers WHERE Teachers.CID = Courses.CID AND Teachers.MID='".$s['mid']."'";
   echo "$sql<br>";
   $result=sql($sql) or die("Ne pashet sql zapros - '$sql'");
      while($row2 = sqlget($result)){
         extract($row2);
         echo "<option value='$CID' ";
         if ($CID==$row['CID']) echo "selected";
         echo ">$Title</option>";
      }
                        ?>
                      </select></td></tr>
                  <tr><td class="shedaddform"><?=_("Название книги")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Name" class=lineinput style="width: 400px; " value="<?php echo $row['Name']?>"></td></tr>
                                      <tr><td class="shedaddform"><?=_("авторы")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Author" class=lineinput style="width: 400px; " value="<?php echo $row['Author']?>"></td></tr>
<!--                                      <tr><td height=7><img src="images/spacer.gif" width=1 height=1></td></tr>
-->
                                      <tr><td class="shedaddform"><?=_("Издательство")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Izdatel" class=lineinput style="width: 400px; " value="<?php echo $row['Izdatel']?>"></td></tr>
                                      <tr><td class="shedaddform"><?=_("Год издания")?></td>
                                      <td class="shedaddform" align='center'><input type="text" name="Year" class=lineinput style="width: 400px; " value="<?php echo $row['Year']?>"></td></tr>
                                      <tr><td class="shedaddform"><?=_("Описание")?></td>
                                      <td class="shedaddform" align='center'><textarea name="Description" size='8' style="width: 400px; "><?php echo $row['Description']?></textarea></td></tr>
                                      <tr><td class="shedaddform">URL</td>
                                      <td class="shedaddform" align='center'><input type="text" name="Url" class=lineinput value='http://' style="width: 400px; " value="<?php echo $row['Url']?>"></td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 1:
                                      <input type="radio" name="active" value="1" <?php if ($row['file_active'] == 1) echo "checked";?>>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile1" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 2:
                                      <input type="radio" name="active" value="2" <?php if ($row['file_active'] == 2) echo "checked";?>>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile2" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 3:
                                      <input type="radio" name="active" value="3" <?php if ($row['file_active'] == 3) echo "checked";?>>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile3" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 4:
                                      <input type="radio" name="active" value="4" <?php if ($row['file_active'] == 4) echo "checked";?>>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile4" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Загрузить файл")?> 5:
                                      <input type="radio" name="active" value="5" <?php if ($row['file_active'] == 5) echo "checked";?>>
                                      </td>
                                      <td class="shedaddform" align='center'><input type="file" name="uploadfile5" class=lineinput style="width: 400px; ">
                                      </td></tr>                                      
                                      <tr><td class="shedaddform"><?=_("Уровень доступа")?></td>
                                      <td class="shedaddform" align='center'>
                                      <select name="access" style='width: 400px;'>
<?php
    for($i=0;$i<=10;$i++) {
        
        echo "<option value=\"".(int) $i."\" ";
        if ($i==$row['access']) echo "selected";
        echo "> ".(int) $i."</option>";
        
    }
?>                                      
                                      </select>
                                      </td></tr>                                      
                                      <tr><td colspan=2><img src=images/spacer.gif width=148 height=14></td></tr>
                                  </table>
                                </td>
                               </tr>
                              </table>
               <tr><td><img src="images/spacer.gif" width=1 height=5></td></tr>
                        <tr><td align="right"><input type="image" name="add_schedule_send"
               onMouseOver="this.src='images/send_.gif';"
               onMouseOut="this.src='images/send.gif';"
                         src="images/send.gif" align="right" alt="Ok" border=0><br></td></tr>

                                    </form>
                             </table>
<?php
   
   }


   if (!isset($_POST['make'])){
     echo show_tb();

   }else {

?>
</body>
</html>
<?
   }

   
?>