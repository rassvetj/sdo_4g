<?php

        require_once("dir_set.inc.php4");
        require_once("manage_course.lib.php4");

        $controller->setView('DocumentPopup');
        
        function get_all_pod_mod_param($mcid,$mod)
        {

        global $mod_cont_table;

        $sql="SELECT * FROM ".$mod_cont_table." WHERE McID='".$mcid."' AND ModID='".$mod."'";
        $sql_result=sql($sql);
        if (sqlrows($sql_result)<0) return 0;
        return $sql_result;
        }

        function update_pod_mod_prop($title,$type,$mod,$mcid,$filename="",$filetmpname="",$cid)
        {
        global $mod_cont_table,$sitepath;

        $error['ModID']=!ereg("^[0-9]+$",trim($mod));
        $error['McID']=!ereg("^[0-9]+$",trim($mcid));
        $title=return_valid_value($title);
        $type=return_valid_value($type);
        $filename=str_replace(" ","",$filename);
        $filename=str_replace("#","",$filename);
        $filename=normal_filename($filename);
        
        $sql="SELECT mod_l FROM ".$mod_cont_table." WHERE McID='".$mcid."' AND ModID='".$mod."'";
        $sql_result=sql($sql);
        if (sqlrows($sql_result)<0) return 1;
        $res=sqlget($sql_result);
        $name=basename($res['mod_l']);
        $tmppath="COURSES/course".$cid."/mods/".$mod."/";
        $path=$GLOBALS['wwf']."/".$tmppath;
        $sitepath.=$tmppath;
        if(empty($error['ModID']) && empty($error['McID']))
        if ($filename!="")
        {
        if (@is_file($path.$name)) @unlink($path.$name);
        if (@move_uploaded_file($filetmpname,$path.$filename))
                        {                            
                                $sql="UPDATE ".$mod_cont_table." SET Title='".$title."', type='".$type."', mod_l='".$tmppath.$filename."' WHERE McID='".$mcid."' AND ModID='".$mod."'";
                                @chmod($path.$filename,0777);
                        }

        }
        else
        {
                        $sql="UPDATE ".$mod_cont_table." SET Title='".$title."', type='".$type."' WHERE McID='".$mcid."' AND ModID='".$mod."'";
        }
        if ($sql_result=sql($sql)) return 0;

        return $error;
        }

$refer="";

if (isset($_SERVER["HTTP_REFERER"])) $refer=$_SERVER["HTTP_REFERER"];
$pos = strpos($refer, $sitepath);
if ($pos === false)
        {
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
<title>eLearn Server 3000</title>
<link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
</head>
<body>
<?php
        if (isset($_POST['make']))
        if ($_POST['make']=="editpodMod"){
        if ($sql_result=get_all_pod_mod_param($_POST['McID'],$_POST['ModID']))
        {
                $close=1;
        if ($res=sqlget($sql_result))
        {

        $controller->captureFromOb(CONTENT);
        	      ?>

        <table width=100% class=main cellspacing=0>
<?
        $controller->captureFromOb(TRASH);

?>
        
        <tr>
              <td>
                                        <table width=100% class=brdr  cellspacing="0" cellpadding="0">
                                              <tr>
                                                <th><span id=createtest class=brdr> &nbsp;&nbsp;<?=_("изменить")?></span></th>
                                                </tr>
                                           </table>
                                        </td>
          </tr>
<?
        $controller->captureStop(TRASH);

?>

          <form action="<?=$sitepath?>teachers/podmod_properties.php4" target="_self" method="POST" id="add" enctype="multipart/form-data">
                                <input type="hidden" name="ModID" value="<?=$_POST['ModID']?>">
                                <input type="hidden" name="CID" value="<?=$_POST['CID']?>">
                                <input type="hidden" name="McID" value="<?=$_POST['McID']?>">
                                <input type="hidden" name="make" value="editpodModcomplete">
                                <tr>
                                  <td>
                                                                <table width=100% class=brdr  cellspacing="1" cellpadding="5">
                                                                <tr class="questt">
                                                                        <td class="shedaddform" width="30%"><?=_("тип")?><br>
                                                                        <select name="type" style="width:100%; border: solid 1px">
                                                                                <option value="html" <? if ($res['type']=='html') echo "selected";?> ><?=_("Изучить")?></option>
                                                                                <option value="file" <? if ($res['type']!='html') echo "selected";?>><?=_("Загрузить")?></option>
                                                                        </select>
                                                                        </td>
                                                                        <td class="shedaddform" width="70%"><?=_("Название")?><br>
                                                                        <input name="title" value="<?=$res['Title']?>" type="text" style="width:100%; border: solid 1px">
                                                                        </td>
                                                                </tr>
                                                                <tr class="questt">
                                                                        <td class="shedaddform" width="100%" colspan="2">
                                                                                <input type="file" name="userfile" style="width:100%; border: solid 1px"><br>
                                                                                <input type="text" readonly name="lastfile" style="width:100%; border: solid 1px" value="<?
                                                                                $name=basename($res['mod_l']);
                                                                                $path="../COURSES/course".$_POST['CID']."/mods/".$_POST['ModID']."/".$name;
                                                                                if (@is_file($path)) echo $name;
                                                                                else echo _("Файл не найден на сервере");?>">
                                                                        </td>
                                                                </tr>
                                                                </table>
                                        </td>
                                </tr>
                                <tr>
                                        <td align="right" class="shedaddform">
<?=okbutton()?>                                        
</td>
                                </tr>
                                </form>
                                </table>
        <?

        $controller->captureStop(CONTENT);
        
        }
        }
        }elseif ($_POST['make']=="editpodModcomplete") {
        ?>
        <h2>Please wait</h2>
        <?
        $filename=$_FILES['userfile']['name'];
        $filetmpname=$_FILES['userfile']['tmp_name'];
        if ($error=update_pod_mod_prop($_POST['title'],$_POST['type'],$_POST['ModID'],$_POST['McID'],$filename,$filetmpname,$_POST['CID']))
        {
                $close=1;
                echo _("Ошибка при изменении данных!");
                //while(list($key,$value)=each($error)) echo $key." - ".$value;
        }
        }

        if(!isset($close))
        {
            $controller->setView('DocumentBlank');
        	$controller->setMessage(_("Данные изменены успешно"), JS_GO_URL,'javascript:window.close();');
        	$controller->terminate();
        	exit();
        ?>
        <script>window.close()</script>
        <?
        }
?>
</BODY>
</HTML>
<?
       $controller->terminate();
?>