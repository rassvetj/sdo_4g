<?php

//require_once("../1.php");
require_once("dir_set.inc.php4");
require_once("manage_course.lib.php4");
require_once('positions.lib.php');
require_once('../lib/classes/MessageFilter.class.php');
require_once('../lib/classes/Forum.class.php');
require_once('../lib/classes/Glossary.class.php');
require_once('../lib/classes/Person.class.php');

session_unregister("goto");

$is_teacher=0;
$second_post=0;
$second_post_file=0;

if(!isset($new_win)) {
       $showfull=(isset($_REQUEST['showfull'])) ? $_REQUEST['showfull'] : 0;
       $pid=(isset($_REQUEST['PID'])) ? $_REQUEST['PID'] : 0 ;
       $CID=(isset($_REQUEST['CID'])) ? $_REQUEST['CID'] : 0 ;
       $userMID=(isset($s['mid'])) ? $s['mid'] : 0 ;
       $ModID=(isset($_REQUEST['ModID'])) ? $_REQUEST['ModID'] : 0 ;
       $title=(isset($_REQUEST['title'])) ? return_valid_value($_REQUEST['title']) : 0 ;
       $title_file=(isset($_REQUEST['title_file'])) ? return_valid_value($_REQUEST['title_file']) : 0 ;
       $mcid=(isset($_REQUEST['McID'])) ? $_REQUEST['McID'] : 0 ;
       $name=(isset($_REQUEST['att_name'])) ? $_REQUEST['att_name'] : 0 ;
       $forum_id=(isset($_REQUEST['forum_id'])) ? $_REQUEST['forum_id'] : 0 ;
       $forum_name=(isset($_REQUEST['forum_name'])) ? $_REQUEST['forum_name'] : 0 ;
       $anwbyemail=(isset($_REQUEST['anwbyemail'])) ? 1 : 0 ;
       $test_id=(isset($_REQUEST['test_id'])) ? $_REQUEST['test_id'] : 0 ;
       $test_del_id=(isset($_REQUEST['test_del_id'])) ? $_REQUEST['test_del_id'] : 0 ;
       $new_win = 1;
       $navi=1;
}
else {  // если модуль показывается в отдельном окне
       $style="";
       $showfull=(int) $_REQUEST['showfull'];
       if (!isset($_REQUEST['showfull'])) $showfull = 0;
       $navi=0;
       $pid=(isset($_REQUEST['PID'])) ? $_REQUEST['PID'] : 0 ;
}
if ($_REQUEST['popup']) $GLOBALS['controller']->setView('DocumentPopup');
//$teach = ($GLOBALS['controller']->enabled) ? $_GET['teach'] : check_teachers_permissions(19, $s['mid']);
$teach = ($GLOBALS['controller']->enabled) ? $GLOBALS['controller']->checkPermission('m130110') : check_teachers_permissions(19, $s['mid']);
if(isset($_GET['mode_frames'])) $arrProperArr = $_GET;
elseif(isset($_REQUEST['mode_frames'])) $arrProperArr = $_REQUEST;
//if ($arrProperArr['mode_frames'] == MODE_SHOW_FRAMES) {
if(($arrProperArr['mode_frames'] == MODE_SHOW_FRAMES) && !$_GET['teach']) {
       if (!is_module_published($ModID)) {
           exitmsg('Модуль не опубликован','/');
           exit();
       }

       if (!is_module_can_run($ModID)) {
           $GLOBALS['controller']->setView('DocumentBlank');
           $GLOBALS['controller']->setMessage(_('Модуль не может быть запущен'), JS_GO_URL, $GLOBALS['sitepath']);
           $GLOBALS['controller']->terminate();           
           exit();
       }
       $sql="SELECT Title,type,McID,mod_l FROM ".$mod_cont_table." WHERE ModID='".$ModID."' ORDER BY McID";
       $sql_result=sql($sql);
       if ($r = sqlget($sql_result)) {
        $strParamsMain = base64_encode("ModID=".$ModID."&McID=".$r['McID']);
        $Link = base64_encode("show_mod.php4?");
       }
       else{       
          $Link = base64_encode("../show_mod_sm.php4?");
          $strParamsMain = base64_encode("display=1");
        }
       
            $strParamsLeft = "";
            $arrProperArr['mode_frames'] = MODE_SHOW_NOFRAMES;
            $arrProperArr['showfull'] = 0;
            $arrProperArr['new_win'] = 0;
            $arrProperArr['in_frame'] = MODE_IN_FRAME;
            foreach ($arrProperArr as $k => $v) {
                     $strParamsLeft .= "{$k}={$v}&";
            }
            $boolInFrame = MODE_IN_FRAME;
            $strParamsLeft = base64_encode(substr($strParamsLeft,0,-1));
            session_register("boolInFrame");
            
            $closeSheduleWindow = true;
            session_register("closeSheduleWindow");
            
            //header("Location: show_mod_frames.php?paramsMain=".$strParamsMain."&paramsLeft=".$strParamsLeft."&Link=".$Link);
            $Link = base64_encode("show_org_metadata.php?");
            $strParamsMain = base64_encode("ModID={$ModID}");            
            $goto = "{$sitepath}teachers/show_org_metadata.php?ModID={$ModID}";
            session_register("goto");
            header("Location: show_mod_frames.php?paramsMain={$strParamsMain}&paramsLeft={$strParamsLeft}&Link={$Link}");
            exit();
}

$is_teacher=$teach;
if($is_teacher && $teach && !$second_post)
{
        if (isset($_REQUEST['add_mod_s']) && isset($_FILES['userfile']['name']))
        {
                $title=return_valid_value($_REQUEST['title']);
                $type=return_valid_value($_REQUEST['type']);
                $error['ModID']=!ereg("^[0-9]{1,4}$",$_REQUEST['ModID']);
                $name=$_FILES['userfile']['name'];
                $name=normal_filename($name);
                $xtmp=array();
                if (!is_dir("../COURSES/course".$_REQUEST['CID'])) $xtmp[]="../COURSES/course".$_REQUEST['CID'];
                if (!is_dir("../COURSES/course".$_REQUEST['CID']."/mods")) $xtmp[]="../COURSES/course".$_REQUEST['CID']."/mods";
                if (!is_dir("../COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID'])) $xtmp[]="../COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID'];
                if (count($xtmp))
                foreach ($xtmp as $v) {
                        //echo "<li>Create <b>$v</b><br>";
                        @mkdir($v);
                        @chmod($v,0777);
                }
                $path1="COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID']."/".$name;
                if (move_uploaded_file($_FILES['userfile']['tmp_name'],"../".$path1))
                {
                        @chmod("../".$path1,0777);
                        $sql="INSERT INTO ".$mod_cont_table." (Title,ModID,mod_l,type,conttype) VALUES ('".$title."','".$ModID."','".$path1."','".$type."','".$HTTP_POST_FILES['userfile']['type']."')";
                        $sql_result=sql($sql);
                        //setcookie("LastaddMod",$title,time()+120);
                }
        }
        if (isset($_REQUEST['add_mod_s']) && isset($_FILES['userfile_2']['name']) && !$second_post_file)
        {
                $title=return_valid_value($_REQUEST['title_file_2']);
                $type=return_valid_value($_REQUEST['type']);
                $error['ModID']=!ereg("^[0-9]{1,4}$",$_REQUEST['ModID']);
                $name=$_FILES['userfile_2']['name'];
                $name=normal_filename($name);
                $xtmp=array();
                // создаются каталоги для файлов модуля
                if (!is_dir("../COURSES/course".$_REQUEST['CID'])) $xtmp[]="../COURSES/course".$_REQUEST['CID'];
                if (!is_dir("../COURSES/course".$_REQUEST['CID']."/mods")) $xtmp[]="../COURSES/course".$_REQUEST['CID']."/mods";
                if (!is_dir("../COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID'])) $xtmp[]="../COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID'];
                if (count($xtmp))
                foreach ($xtmp as $v) {
                        //echo "<li>Create <b>$v</b><br>";
                        @mkdir($v);
                        @chmod($v,0777);
                }
                $path1="COURSES/course".$_REQUEST['CID']."/mods/".$_REQUEST['ModID']."/".$name;
                if (move_uploaded_file($_FILES['userfile_2']['tmp_name'],"../".$path1))
                {
                        @chmod("../".$path1,0777);
                        setcookie("LastaddMod_2",$title,time()+120);
                }
        }

        if (isset($_REQUEST['add_mod_s']) && is_uploaded_file($zipFileName = $_FILES['file_zip']['tmp_name']))
        {

                if (isset($_REQUEST['CID']) && isset($_REQUEST['ModID'])) {
                        chdir($_SERVER['DOCUMENT_ROOT']."/COURSES/course{$_REQUEST['CID']}/mods");
                        if (!is_dir($_REQUEST['ModID'])) {
                                @mkdir($_REQUEST['ModID']);
								@chmod($_REQUEST['ModID'],0777);
                        }
                        chdir($_SERVER['DOCUMENT_ROOT']."/COURSES/course{$_REQUEST['CID']}/mods/".$_REQUEST['ModID']);
                        $zip = zip_open($zipFileName);
                        if ($zip)
                        {
                                while ($zip_entry = zip_read($zip))
                                {
                                        if (zip_entry_open($zip, $zip_entry, "r"))
                                        {
                                                $intSize = zip_entry_filesize($zip_entry);
                                                $strFileName = zip_entry_name($zip_entry);
                                                if (!$intSize) {
                                                        @mkdir($strFileName);
                                                        @chmod($strFileName,0777);
                                                } else {
                                                        $strDirs= str_replace("/", "\\", dirname($strFileName));
                                                        if (is_array($arrDirs = explode("\\", $strDirs))) {
                                                                foreach ($arrDirs as $key => $val) {
                                                                        if (!is_dir($val)) {
                                                                                @mkdir($val);
                                                                                @chmod($val, 0777);
                                                                        }
                                                                        chdir($val);
                                                                }
                                                        }
                                                        chdir($_SERVER['DOCUMENT_ROOT']."/COURSES/course{$_REQUEST['CID']}/mods/".$_REQUEST['ModID']);
                                                        $buf=zip_entry_read($zip_entry, $intSize);
                                                        $fp = fopen ($strFileName, "wb+");
                                                        fwrite($fp,$buf);
                                                        fclose($fp);
                                                }
                                                zip_entry_close($zip_entry);
                                        }
                                }
                                zip_close($zip);
                        }
                }
        }

        if (isset($_GET['make'])) {
                if ($_GET['make']=="delete") {
                        $sql="DELETE FROM ".$mod_cont_table." WHERE McID='".$mcid."' AND ModID='".$ModID."'";
                        $path2=$GLOBALS['wwf']."/COURSES/course".$CID."/mods/".$ModID."/".$name;
                        $sql_result=sql($sql);
                        if (@is_file($path2)) @unlink($path2);
                }
        }
        if (isset($_REQUEST['make']))
        {
                if ($_REQUEST['make']=="add_run")
                {
                        $user=$s['user']['fname']." ".$s['user']['lname'];
                        if($forum_id=="new" && !empty($forum_name)) $forum_id=create_new_forum($forum_name,$CID,$anwbyemail,$user,$s['user']['email']);
                        if (ereg("^[0-9]{1,9}$",$forum_id) && $forum_id)
                        {
                                $sql="UPDATE ".$mod_list_table." SET forum_id='".$forum_id."' WHERE ModID='".$ModID."'";
                                $sql_result=sql($sql);
                        }
                }
                if ($_REQUEST['make']=="add_forum")
                {
                        $user=$s['user']['fname']." ".$s['user']['lname'];
                        if($forum_id=="new" && !empty($forum_name) && $forum_category_id) {
                            //$forum_id = create_new_forum($forum_name,$CID,$anwbyemail,$user,$s['user']['email']);
                            $thread = new CForumThread();
                            $thread->init(array(
                                'category' => array('int'=>$forum_category_id), 
                                'message'=> array('string'=>$forum_name), 
                                'sendmail'=> array('int'=>$anwbyemail), 
                                'name' => array('string'=>$forum_name)));
                            $forum_id = $thread->create();
                        }
                        if (ereg("^[0-9]{1,9}$", $forum_id) && $forum_id)
                        {
                                $sql="UPDATE ".$mod_list_table." SET forum_id='".$forum_id."' WHERE ModID='".$ModID."'";
                                $sql_result=sql($sql);
                        }
                }
                if ($_REQUEST['make']=="del_forum")
                {
                        if (ereg("^[0-9]{1,9}$",$ModID) && $ModID)
                        {
                                $sql="UPDATE ".$mod_list_table." SET forum_id='' WHERE ModID='".$ModID."'";
                                $sql_result=sql($sql);
                        }
                }
                if ($_REQUEST['make']=="add_test") {
                        if (ereg("^[0-9]{1,9}$",$test_id) && $test_id) {
                                $tests=$test_id;
                                $sql="SELECT test_id FROM ".$mod_list_table." WHERE ModID='".$ModID."'";
                                $sql_result=sql($sql);
                                if( sqlrows($sql_result) > 0 ) {
                                        $test_res = sqlget($sql_result);
                                        $tests_array=explode(";",$test_res['test_id']);
                                        if (!empty($test_res['test_id'])) {
                                                $tests = $test_res['test_id'];
                                        }
                                        if (!in_array($test_id,$tests_array) && $test_res['test_id']!="")
                                        $tests=$test_res['test_id'].";".$test_id;
                                }
                                //            echo "modlisttable: ".$mod_list_table;
                                $sql="UPDATE ".$mod_list_table." SET test_id='".$tests."' WHERE ModID='".$ModID."'";
                                $sql_result = sql($sql);
                        }
                }
                if (($_REQUEST['make'] == "add_new_podmod")&&($_REQUEST['type'] == "run")) {
                        $name = $adodb->qstr($_REQUEST['name']);
                        $mod_id = $_REQUEST['ModID'];
                        $path = $adodb->qstr($_REQUEST['hid_path_run_program']);
                        $query = "INSERT INTO training_run (name, path)
                     VALUES
                     (".$name.", ".$path.")";
                        $result = sql($query, "errfn123");
                        $inserted_id = sqllast($result);
                        $query = "SELECT * FROM mod_list WHERE ModID = $mod_id";
                        $result = sql($query);
                        $row = sqlget($result);
                        $run_ids = trim($row['run_id']);
                        if($run_ids == "") {
                                $updating_run_id_value = $inserted_id;
                        }
                        else {
                                $updating_run_id_value = trim($run_ids, ";").";". $inserted_id;
                        }
                        $query = "UPDATE mod_list SET run_id ='".$updating_run_id_value."' WHERE ModID = $mod_id";
                        $result = sql($query,"errfn555");
                }

                if($_REQUEST['make'] == "del_run") {
                        $query = "DELETE FROM training_run WHERE run_id = ".$_REQUEST['run_id'];
                        $result = sql($query, "errfn556");

                        $query = "SELECT * FROM mod_list WHERE ModID = ".$_REQUEST['ModID'];
                        $result = sql($query,"errfn557");
                        $row = sqlget($result);
                        $run_ids_array = explode(";", $row['run_id']);
                        $updating_value = "";
                        if(is_array($run_ids_array)) {
                                foreach($run_ids_array as $key => $run_id_from_base) {
                                        if($run_id_from_base == $_REQUEST['run_id']) {
                                                continue;
                                        }
                                        $updating_value .= $run_id_from_base.";";
                                }
                        }
                        $updating_value = trim($updating_value, ";");
                        $query = "UPDATE mod_list SET run_id ='".$updating_value."' WHERE ModID = ".$_REQUEST['ModID'];
                        $result = sql($query);
                }
                if ($_REQUEST['make']=="indexing") {
?>
<?
                }
                if ($_REQUEST['make']=="del_test") {
                        if (ereg("^[0-9]{1,9}$",$test_del_id) && $test_del_id) {
                                $sql = "SELECT test_id FROM ".$mod_list_table." WHERE ModID='".$ModID."'";
                                $sql_result = sql($sql);
                                $tests = "";
                                if (sqlrows($sql_result)>0) {
                                        $test_res = sqlget($sql_result);
                                        $tests_array=explode(";",$test_res['test_id']);
                                        if (in_array($test_del_id,$tests_array)) {
                                                $tests=str_replace($test_del_id,"",$test_res['test_id']);
                                                $tests=str_replace(";;",";",$tests);
                                                if (strlen($tests)>1) {
                                                        if ($tests[strlen($tests)-1]==";") $tests=substr($tests,0,strlen($tests)-1);
                                                        if ($tests[0]==";") $tests=substr($tests,1,strlen($tests));
                                                }
                                                else
                                                $tests=($tests==";") ? "": $tests;
                                                $sql="UPDATE ".$mod_list_table." SET test_id='".$tests."' WHERE ModID='".$ModID."'";
                                                $sql_result=sql($sql);
                                        }
                                }
                        }
                }
        }
}

//if ($showfull) {
if ($showfull  || $new_win==1) {
        echo show_tb(); //require_once($path."top.php4");
}
else{
        if (!isset($top1))
        if (empty($s['mid'])) {
                header("location:".$sitepath.""); exit();
        }else
        echo startHTML( "eLearning Server", $style );
}

$sql_result=get_all_mod_param($pid,$CID,$ModID);
if (sqlrows($sql_result)>0) {
        $res=sqlget($sql_result);

        $forum_id=$res['forum_id'];
        $test_id=$res['test_id'];

        if( $navi ) create_navigate_button( $CID, $ModID, $pid );
        //        if ($_GET['in_frame']) echo "<br style='font:4px;'><a href='/teachers/manage_course.php4?CID={$_GET['CID']}' target='_parent' class='schedule'>up to course</a><br><br style='font:4px;'>";

        $GLOBALS['controller']->captureFromOb(CONTENT);
        echo show_mod_content( $CID, $pid, $res, $ModID, $is_teacher && $showfull);       
        if(($is_teacher) && $teach) {
        $GLOBALS['controller']->captureFromOb(TRASH);
            ?>
           <BR>
           <table width=100% class=brdr  cellspacing="0" cellpadding="0">
              <tr>
                  <th>
                    <a style='cursor:hand' id=add_show onClick= "putElem('add_form');removeElem('add_show');putElem('add_hide');">
                    <span class=webd>4</span> <?=_("добавить")?></a>
                    <a style='cursor:hand' id=add_hide class=hidden onClick= "removeElem('add_form');removeElem('add_hide');putElem('add_show');">
                    <span class=webd >6</span> <?=_("добавить")?></a>
                  </th>
                  </tr>
           </table>
<?
        $GLOBALS['controller']->captureStop(TRASH);
?>           
            <br><form name='add' id='form_add' action="<?=$sitepath?>teachers/edit_mod.php4" target="_self" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="ModID" value="<?=$ModID?>">
            <input type="hidden" name="CID" value="<?=$CID?>">
            <input type="hidden" name="PID" value="<?=$pid?>">
            <input type="hidden" name="showfull" value="<?=$showfull?>">
            <input type="hidden" name="new_win" value="<?=$new_win?>">
            <input type="hidden" name="popup" value="<?=$_REQUEST['popup']?>">
            <input type="hidden" name="add_mod_s" value="add_new_podmod">
            <input type="hidden" id="make" value="add_new_podmod" name="make">

             <a width=27 id=add_form class=hidden >
             <table class=main cellspacing=0>
                        <tr class="shedaddform">
                           <th colspan="2">&nbsp;<?=_("добавить модуль")?></tр>
                        </tr>
                        <tr class="shedaddform">
                           <td>тип</td>
                           <td>
                           <select name="type" style="border: solid 1px"
                              onchange="removeElem('m1');removeElem('m2');removeElem('m3');removeElem('m4');
                                        removeElem('m5');removeElem('m6');removeElem('m7');removeElem('m8');
                                        removeElem('m9');
                                        putElem('m'+(selectedIndex+1));new_mod_make(selectedIndex+1);">
                              <option value="html"><?=_("Изучить материал")?></option>
                              <option value="file"><?=_("Загрузить файл")?></option>
                              <option value="link"><?=_("Изучить уже размещенный материал")?> </option>
                              <option value="forum"><?=_("Задать вопросы")?></option>
                              <option value="test"><?=_("Выполнить задание")?></option>
                              <option value="file_up"><?=_("Загрузить каталог файлов")?></option>
                              <option value="run"><?=_("Запустить программу с локального компьютера")?></option>
                              <option value="repo"><?=_("Изучить материал из библиотеки")?></option>
                           </select>
                           </td>
                        </tr>
                        
                        <tr><td colspan=2>
                        
                        <table id="m1" border=0 cellpadding=2 cellspacing=0 width=100%>
                        <tr class="shedaddform" >
                              <td class="shedaddform" colspan="2">
                           <table align="center" cellpadding="3" cellspacing="0" border="0">
                           <tr class="questt">
                              <td><?=_("Название:")?><br>
                              </td>
                              <td width="50%"><input name="title" value="<?=_("Название:")?>" type="text"></td>
                           </tr>
                           <tr class="questt">
                              <td nowrap><?=_("Укажите файл:")?></td>
                              <td width="100%"><input type="file" name="userfile"></td>
                           </tr>
                           </table>
                              </td>
                        </tr>
                        </table>
                        <table id="m2" border=0 cellpadding=2 cellspacing=0 width=100%>
                        <tr>
                        <td>&nbsp;
                        </td>
                        <td>
                          <input type="file" name="userfile_2" style="width:100%; border: solid 1px">
                        </td>
                        </tr>
                        </table>
                     <table id="m3" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr>
                           <td><?=_("Выберите файлы:")?></td>
                           <td><input name="Browse" value="<?=_("Обзор")?>" type="button"
                           onclick="window.open('', 'indexing', 'width=540,height=650,scrollbars=1,toolbar=1,titlebar=0,resizable=yes');document.indexing.submit();">
                           </td>
                     </tr>
                     </table>
                     <table id="m8" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr class="questt">
                        <td><?=_("Выберите материал:")?></td>
                        <td>
                           <input name="Browse" value="<?=_("Обзор")?>" type="button"
                           onclick="window.open('', 'library', 'width=540,height=650,scrollbars=1,toolbar=1,titlebar=0,resizable=yes');document.library.submit();">
                        </td>
                     </tr>
                     </table>
                     <table id="m9" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr class="questt">
                        <td><?=_("Выберите материал:")?></td>
                        <td>
                           <input name="Browse" value="<?=_("Обзор")?>" type="button"
                           onclick="window.open('', 'laws', 'width=540,height=650,scrollbars=1,toolbar=1,titlebar=0,resizable=yes');document.laws.submit();">
                        </td>
                     </tr>
                     </table>
                     <table id="m7" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr class="questt">
                      <td colspan="2">
                        <table border="0">
                        <tr>
                         <td><?=_("Укажите название:")?> &nbsp;&nbsp;&nbsp;</td>
                         <td><input type="text" name="name" size="50" value = "<?=_("Название")?>" /></td>
                        </tr>
                        <tr>
                         <td>
                          <?=_("Укажите программу:")?>
                         </td>
                         <td>
                          <input type="file" maxlength="0" id="run_program_path" onChange="javascript: document.getElementById('hid_path_run_program').value = this.value" />
                          <input type="hidden" id="hid_path_run_program" name="hid_path_run_program" />
                         </td>
                        </tr>
                        <tr>
                          <td colspan="2"><b><?=_("Внимание!")?></b> <?=_("программа должна быть установлена на каждом клиентском компьютере.")?></td>
                        </tr>
                       </table>                      
                      </td>
                     </tr>
                     </table>
                     <table id="m6" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr>
                             <td><?=_("Укажите каталог файлов")?> (.zip):</td>
                             <td><input name="file_zip" type="file"></td>
                     </tr>
                     </table>
                     <table id="m5" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr>
                                    <td><?=_("Выберите задание")?></td><td>
                                    <select name="test_id">
                                          <?=show_tests_list($CID,explode(";",$test_id));?>
                                    </select>
                                    </td>
                     </tr>
                     </table>
                     <table id="m4" border=0 cellpadding=2 cellspacing=0 width=100%>
                     <tr>
                       <td colspan="2">
                        <table cellpadding="2" cellspacing="0" border="0">
                          <tr>
                            <td><?=_("Выберите тему:")?>
                            </td>
                            <td>
                            <select name="forum_id">
                                <option value="new"> --<?=_("создать новую тему")?>--</option>
                                <?= show_forum_list($CID); ?>
                            </select>
                            </td>
                          </tr>
                          <tr class="questt">
                            <td ><?=_("Название новой темы:")?></td>
                            <td>
                            <select name="forum_category_id">
                            <?php
                                $cats = CForumCategory::get_as_array();
                                if (is_array($cats) && count($cats)) {
                                    foreach($cats as $v) {
                                        if (in_array($v['cid'],array(0,$CID))) {
                                            echo "<option value=\"{$v['id']}\"> {$v['name']}</option>";
                                        }
                                    }
                                }
                            ?>
                            </select>&nbsp;
                            <input type="text" name="forum_name" value="<?=$res['mod_name']?>">
                            </td>
                          </tr>
                          <tr class="questt">
                            <td colspan="2">
                              <input type="checkbox" name="anwbyemail" value="1">
                              <?=_("присылать сообщения на почту")?> </td>
                          </tr>
                        </table>                       
                        </td>
                    </tr>
                    </table>
                    </td></tr>
                    
                    <tr><td colspan="2"><?=okbutton()?></td></tr>
            
            </table>
            </form>
   <script>

   removeElem('m2');
   removeElem('m3');
   removeElem('m4');
   removeElem('m5');
   removeElem('m6');
   removeElem('m7');
   removeElem('m8');
   removeElem('m9');
   //   removeElem('m7');
   </script>

         <?
         // }else
         // {

        } // КОНЕЦ ФОРМЫ ДОБАВЛЕНИЯ

      ?>




            <form action="<?=$sitepath?>teachers/create_index.php4" target="indexing" method="POST" id="index" name="indexing">
            <input type="hidden" name="ModID" value="<?=$ModID?>">
            <input type="hidden" name="CID" value="<?=$CID?>">
            <input type="hidden" name="PID" value="<?=$pid?>">
            <input type="hidden" name="showfull" value="<?=$showfull?>">
            <input type="hidden" name="make" value="indexing">
            </form>

            <form action="<?=$sitepath?>lib.php" target="library" method="GET" id="library" name="library">
            <input type="hidden" name="ModID" value="<?=$ModID?>">
            <input type="hidden" name="CID" value="<?=$CID?>">
            </form>
            
            <form action="<?=$sitepath?>teachers/file_up.php4" target="file_up" method="GET" id="file_up" name="file_up">
            <input type="hidden" name="ModID" value="<?=$ModID?>">
            <input type="hidden" name="CID" value="<?=$CID?>">
            <input type="hidden" name="make" value="get_key">
            </form>

<?
} else {
        echo _("Ссылка на несуществующий учебный модуль либо ошибка индетификации пользователя.");
}
// including page Bottom
$GLOBALS['controller']->setHeader(_("Редактирование учебного материала"));
$GLOBALS['controller']->captureStop(CONTENT);
if ($showfull) {
        echo show_tb(); //require_once($path."bottom.php4");
}
else {
        echo stopHTML("");
}

?>