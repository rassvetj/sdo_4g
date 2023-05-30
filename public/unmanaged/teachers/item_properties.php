<?php
         require_once("dir_set.inc.php4");
        require_once("manage_course.lib.php4");

        function update_mod_prop($mod,$title,$status,$descr,$theme,$CID)
        {
        global $mod_list_table;
        $error['ModID']=!ereg("^[0-9]{1,4}$",$mod);
        $error['CID']=!ereg("^[0-9]{1,4}$",$CID);
        $error['Status']=!ereg("^[0-9]{1}$",$status);
        if (empty($status)) $error['Status']=$status;
        $title=return_valid_value($title);
        $theme=return_valid_value($theme);
        $descr=return_valid_value($descr);
        if (!is_dir("../COURSES/course".$CID."/mods/".$mod)) mkdir("../COURSES/course".$CID."/mods/".$mod, 0700);
        @chmod("../COURSES/course".$CID."/mods/".$mod,0777);
        if(empty($error['ModID']) && empty($error['Status']))
        {
                $sql="UPDATE ".$mod_list_table." SET Title='".$title."', Pub='".$status."', Num='".$theme."', Descript='".$descr."' WHERE ModID='".$mod."'";
                if ($sql_result=sql($sql)) return 0;
        }
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
        if ($_POST['make']=="editModp"){
        if ($sql_result=get_all_mod_param($_POST['PID'],$_POST['CID'],$_POST['ModID']))
        {
                $close=1;
        ?>
        <form action="<?=$sitepath?>teachers/mod_properties.php4" target="_self" id="self" method="POST">
        <input type='hidden' name='ModID' value='<?=$_POST['ModID']?>'>
        <input type='hidden' name='CID' value='<?=$_POST['CID']?>'>

        <table width=100% border=0 cellspacing="3" cellpadding="0">
        <tr><td><table width=100% border=0 cellspacing="0" cellpadding="0" class=brdr>
<th width=100%><a > &nbsp;<span id=createtest><?=_("Править свойства модуля")?></span></a></th>
</tr></table></td></tr>
                <tr>
                <td>
                <table width=100% border=0 cellspacing="1" cellpadding="5" class="brdr">

        <?
                while ($res=sqlget($sql_result))
                {
                        ?>
                        <tr class=questt>
                        <td width="50%" class="shedaddform"><?=_("Название модуля")?>
                        </td>
                        <td width="50%" class="shedaddform"><?=_("Статус модуля")?>
                        </td>
                        </tr>
                        <tr class=questt>
                        <td width="50%"><input name="title" type="text" value="<?=stripslashes($res['2'])?>" style="width:75%">
                        </td>
                        <td width="50%">
                        <select name="status">
                        <option value="" <? if (empty($res['pub'])) echo "selected";?>><?=_("Не опубликован")?></option>
                        <option value="1" <? if (!empty($res['pub'])) echo "selected";?>><?=_("Опубликован")?></option>
                        </select>
                        </td>
                        </tr>
                        <tr class=questt>
                        <td width="50%" class="shedaddform"><?=_("Тема")?>
                        </td>
                        <td width="50%" class="shedaddform"><?=_("Описание модуля")?>
                        </td>
                        </tr>
                        <tr class=questt>
                        <td width="50%"><input type="text" value="<?=get_theme($res['theme'])?>" style="width:75%" name="theme">
                        </td>
                        <td width="50%"><input type="text" value="<?=get_descr($res['descript'])?>" style="width:75%" name="descr">
                        </td>
                        </tr>
                        <?php
                }
                ?>
                </table>
                </td>
                </tr>
                <tr>
                <td align="right" bgcolor="white">
                <input type='hidden' name='make' value='editModcomplete'>
                <input type="image" id="add_shedule_send" onMouseOver="this.src='<?=$sitepath?>images/send_.gif';" onMouseOut="this.src='<?=$sitepath?>images/send.gif';" src="<?=$sitepath?>images/send.gif" align="right" alt="ok" border=0></td></tr>
        </table>
        </form>
<?php
        }
        }elseif ($_POST['make']=="editModcomplete") {
        ?>
        <h2>Please wait</h2>
        <?
        if ($error=update_mod_prop($_POST['ModID'],$_POST['title'],$_POST['status'],$_POST['descr'],$_POST['theme'],$_POST['CID']))
        {
                $close=1;
                echo _("Ошибка при изменении данных!");
                //while(list($key,$value)=each($error)) echo $key." - ".$value;
        }
        }

        if(!isset($close))
        {?>
        <script>window.close()</script>
        <?
        }
?>

</BODY>
</HTML>