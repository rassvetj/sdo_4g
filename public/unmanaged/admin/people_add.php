<?php

//defines

$include=TRUE ;
define("MAX_AUTO_ACCOUNTS", 50);
define("MIN_PWD_LENGTH", 3);
define("MAX_PWD_LENGTH", 10);
define("NO_MAIL_ECHO", false);

require ("setup.inc.php");
require ("../tracks.lib.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");
require_once ("../fun_lib.inc.php4");
require ("error.inc.php4");
require_once('move2.lib.php');
switch ($_POST['hid_act']) {
        case "gen":
                $strAlert = array();
                if (!isset($_POST['txt_number']) || empty($_POST['txt_number']) || (isset($_POST['txt_number']) && ($_POST['txt_number']>999))) {
                        $arrAlert[] = _("Не задано количество учетных записей или превышен лимит в 999 записей")."<br>";
                }
                elseif (($_POST['txt_number'] < 0) && ($_POST['txt_number'] > MAX_AUTO_ACCOUNTS)) {
                        $arrAlert[] = _("Недопустимое количество учетных записей")."<br>";
                }
                if (($_POST['txt_length'] < MIN_PWD_LENGTH) || ($_POST['txt_length'] > MAX_PWD_LENGTH)) {
                        $arrAlert[] = _("Недопустимая длина пароля")."<br>";
                }
                if (!strlen($_POST['txt_login'])) {
                        $_POST['txt_login'] = "user";
                }
                if (($_POST['sel_status']<2) && (isset($_POST['ch_ingroup'])) && ($_POST['sel_group']==0) && !strlen($_POST['txt_group'])) {
                        $arrAlert[] = _("Не выбрана группа")."<br>";
                }
                //if ((($_POST['sel_status']==1) && !($_POST['sel_course'] || $_POST['sel_spec'])) || (($_POST['sel_status']==2) && !$_POST['sel_course'])) {
                //        $arrAlert[] = "Не выбраны курс или специальность<br>";
                //}
                $arrStorePasswords = array();
                if (!count($arrAlert)) {

                        if (isset($_POST['ch_ingroup']) && !empty($_POST['txt_group'])) {
                            $groupname = trim(strip_tags($_POST['txt_group']));
                            $res = sql("INSERT INTO groupname (cid,name) VALUES ('-1',".$GLOBALS['adodb']->Quote($groupname).")");
                            if ($res) $_POST['sel_group'] = sqllast();
                        }

//                        if (isset($_POST['ch_ingroup'])) {
                                /*
                                if (strlen($_POST['txt_group'])) {
                                        $q = "INSERT INTO cgname (name) values ('".mysql_escape_string($_POST['txt_group'])."')";
                                        $r = sql($q, "err23nd");
                                        $intGroupId = sqllast();
                                } else {
                                        $intGroupId = $_POST['sel_group'];
                                }
                                */
//                        } else {
                        $intGroupId = 0;
//                        }

                        $intCount = 0;


                        //for ($i = 1; $i <= $_POST['txt_number']; $i++) {
                        $num_inserted = 0;
                        $i = 1;
                        $mail_body = _("Добавлены учетные записи:")." <br /><br />
                         <table border='1'>
                         <tr><th>"._("Логин")."</th><th>"._("Пароль")."</th></tr>";

                        while($intCount < $_POST['txt_number']) {

                                $strNum = str_pad((string)$i,3,"0",STR_PAD_LEFT);
                                $strPassword = randString($_POST['txt_length']);
                                $strLogin = $_POST['txt_login'].$strNum;

                                $q = "SELECT * FROM People WHERE Login=".$GLOBALS['adodb']->Quote($strLogin);
                                $r = sql($q, "eerrgfg44");
                                if(sqlrows($r) == 0) {
                                   $q = "INSERT INTO People (Login, Password) values (".$GLOBALS['adodb']->Quote($strLogin).", PASSWORD('".$strPassword."'))";
                                   $r = sql($q, "err234gf");
                                   $arrStorePasswords[$strLogin] = $strPassword;
                                   $intCount++;
                                   $mail_body .= "<tr><td>".htmlspecialchars($strLogin, ENT_QUOTES)."</td><td>".htmlspecialchars($strPassword, ENT_QUOTES)."</td></tr>";
                                }
                                else {
                                         $i++;
                                         continue;

                                }
                                $intPeopleId = sqllast();
                                if ($_POST['sel_group'] && $intPeopleId) {
                                    sql("INSERT INTO groupuser (mid,cid,gid) VALUES ('".(int) $intPeopleId."','-1','".(int) $_POST['sel_group']."')");
                                }
                                //$intDefaultPmid = getDefaultPmGroup("dean");
                                $q = "";
                                $type = "";
                                switch ($_POST['sel_status']) {
                                        case 1:
                                                //$q = "INSERT INTO Students (MID, CID, cgid) values ({$intPeopleId}, '{$_POST['sel_course']}', {$intGroupId})";
                                                sql("INSERT INTO Students (MID, CID) VALUES ('".(int) $intPeopleId."','0')");
                                                if ($_POST['sel_course']) {
                                                    assign_person2course($intPeopleId, $_POST['sel_course'], false);
                                                    //tost($intPeopleId,$_POST['sel_course'],false);
                                                    $q = "UPDATE Students SET cgid='".(int) $intGroupId."' WHERE MID='".(int) $intPeopleId."' AND CID='".(int) $_POST['sel_course']."'";
                                                }
                                                if ($_POST['sel_spec']) registration2track($_POST['sel_spec'], $intPeopleId, 0, NO_MAIL_ECHO);
                                                $type = "student";
                                                break;
                                        case 2:
                                                //if ($_POST['sel_course']) {
                                                    $q = "INSERT INTO Teachers (MID, CID) values ({$intPeopleId}, {$_POST['sel_course']})";
                                                    add_teacher_to_default_groups($intPeopleId);
                                                //}

                                                $type = "teacher";

                                                break;
                                        case 3:
                                                $q = "INSERT INTO deans (MID) values ({$intPeopleId})";

                                                $type = "dean";
                                                //$r = sql("INSERT INTO permission2mid (pmid, mid) values ('{$intDefaultPmid}', '{$intPeopleId}')", "err3432vf");

                                                break;
                                        case 4:
                                                $q = "INSERT INTO admins (MID) values ({$intPeopleId})";
                                                $type = "admin";
                                                break;
                                }
                                if (!empty($q))
                                    $r = sql($q, "errhtr3");
                                if (!empty($type))
                                    CRole::add_mid_to_role($intPeopleId,CRole::get_default_role($type),$type);

                        }
                        $mail_body .= '</table>';
                        mailToelearn("import", 0, (int) $_POST['sel_course'], array("msg" => $mail_body));
                        $arrAlert[] = _("Добавлено записей:")." {$intCount}<br><a href={$sitepath}admin/people_print.php target=_blank>"._("Распечатать список")."</a>";
                        if (count(arrStorePasswords)) {
                                session_register("arrStorePasswords");
?>

        <script>
         window.open("people_print.php");
        </script>
        <?
                        }
                }
                break;
        case "del":
                if (isset($_POST['txt_pattern_del']) && strlen($_POST['txt_pattern_del'])) {
                        $_POST['txt_pattern_del'] = str_replace("*", "%", $_POST['txt_pattern_del']);
                        $q = "SELECT * FROM People WHERE Login LIKE '{$_POST['txt_pattern_del']}' AND MID<>'".$_SESSION['s']['mid']."'";
                        $r = sql($q, "err465sd");
                        $intCount = sqlrows($r);
                        while ($a = sqlget($r)) {
                                if ($a['MID']==$_SESSION['s']['mid']) {
                                    continue;
                                }
                                $qq = "DELETE FROM money WHERE mid={$a['MID']}";
                                $rr = sql($qq, "err234gr");
                                $qq = "DELETE FROM groupuser WHERE mid={$a['MID']}";
                                $rr = sql($qq, "err234gr");
                                $qq = "DELETE FROM tracks2mid WHERE mid={$a['MID']}";
                                $rr = sql($qq, "err234gr");
                                $qq = "DELETE FROM permission2mid WHERE mid={$a['MID']}";
                                $rr = sql($qq, "err234gr");
                                $qq = "DELETE FROM Students WHERE MID={$a['MID']}";
                                $rr = sql($qq, "err234gr");
                                $qq = "DELETE FROM Teachers WHERE MID={$a['MID']}";
                                $rr = sql($qq, "erry654");
                                $qq = "DELETE FROM deans WHERE MID={$a['MID']}";
                                $rr = sql($qq,"err34fs");
                                $qq = "DELETE FROM admins WHERE MID={$a['MID']}";
                                $rr = sql($qq, "errt45");
                                $qq = "DELETE FROM scheduleID WHERE MID={$a['MID']}";
                                $rr = sql($qq, "errt45");
                        }
                        $q = "DELETE FROM People WHERE Login LIKE '{$_POST['txt_pattern_del']}' AND MID<>'".(int) $_SESSION['s']['mid']."'";
                        $r = sql($q, "err344gf");
                        $arrAlert[] = _("Удалено записей:")." {$intCount}<br>";
                } else {
                        $arrAlert[] = _("Пустой шаблон")."<br>";
                }
                break;
}
if (is_array($arrAlert) && count($arrAlert)) {

        foreach ($arrAlert as $val) {
                echo "<b>{$val}</b><br>";
                $GLOBALS['controller']->setMessage($val);
        }

}elseif(isset($_POST['hid_act']) && ($_POST['hid_act'] == "gen")) {

?>
   <a href="people_print.php" target="_blank"><?=_("Распечатать учетные записи")?></a><br><br>
<?

}
?>

<table border=0 cellpadding=0 cellspacing=0 align=center width="100%">
           <tr>
                         <td width=100% class=tabheader>
                                <table width=100% class=th2 cellpadding=0 cellspacing=0>
                                        <tr>
                                         <td width=27 valign=top class=shown id=plusmain1>
                                                <a title="<?=_("показать")?>" href=# onClick="putElem('gen'); removeElem('plusmain1'); putElem('minusmain1');" ><span class=cDisabled><span class=webd>4</span></span></a>
                                         </td>
                                         <td width=27 valign=top class=hidden2 id=minusmain1>
                                                <a title="<?=_("убрать")?>" href=# onClick="removeElem('gen'); removeElem('minusmain1'); putElem('plusmain1');" ><span class=cDisabled><span class=webd>6</span></span></a>
                                         </td>
                                         <td width=100%>
                                                &nbsp;<span id=createtest><?=_("сгенерировать учетные записи")?></span>
                                         </td>
                         </tr>
                                </table>
                         </td>
                        </tr>

                         <tr>
                         <td width=100%>
                                <img src="images/spacer.gif" alt="" width=1 height=5 border=0>
                         </td>
                        </tr>
                </table>
<?php
$GLOBALS['controller']->captureFromOb("m020201");
$sel_course_onchange = (USE_SPECIALITIES) ? 'onChange="javascript:document.getElementById(\'sel_spec\').value=0;"' : '';
$sel_status_onchange = (USE_SPECIALITIES) ? 'document.getElementById(\'sel_spec\').disabled=(this.value!=1);' : '';
?>
<form name="form_gen" method="post" action="">
<table id="gen" width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>

<table width=100% class=main cellspacing=0>
  <tr>
    <td width="100"> <?=_("Количество")?></td>
    <td>
      <input name='txt_number' type='text' id="txt_number" size='5' maxlength='5'>&nbsp;&nbsp;<? echo $GLOBALS['tooltip']->display('people_gen_num')?>
    </td>
  </tr>
  <tr>
    <td> <?=_("Роль")?> </td>
    <td>
        <select name="sel_status" id="sel_status" onChange="javascript:document.getElementById('ch_ingroup').disabled=(this.value!=1);<?=$sel_status_onchange?>document.getElementById('sel_course').disabled=(this.value>2);">
          <option value="1"><?=_("слушатель");?></option>
          <option value="2"><?=_("преподаватель")?> </option>
          <option value="3"><?=_("учебная администрация")?></option>
          <option value="4"><?=_("администратор")?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td> <?=_("Назначить на курс")?> </td>
    <td>
      <select name="sel_course" id="sel_course" <?=$sel_course_onchange?>>
                  <option value="0">-- <?=_("нет")?> --
<?
      //все
      echo $cselect.=selCourses($cids = get_kurs_by_status(1),$CID,false,true);
      if (is_array($cids) && count($cids)) $res = sql('SELECT CID FROM Courses WHERE TypeDes > 0 OR (TypeDes = -1 AND chain > 0) AND CID IN (' . implode(',', $cids) . ')');
      $cids_chains = array();
      while ($row = sqlget($res)){
      		$cids_chains[] = $row['CID'];
      }
      //курсы status>0 (декан)
      //echo $cselect.=selCourses($s['tkurs'],$CID,false,true);
?>
      </select>&nbsp;&nbsp;<? echo $GLOBALS['tooltip']->display_variable('people_gen_course', 'sel_course', $cids_chains)?>
    </td></tr>
<?php
if (USE_SPECIALITIES){
?>

    <tr>
    <td><?=_("или специальность:")?>    </td>
    <td width="100%">
    <select name="sel_spec" id="sel_spec"  onChange="javascript:document.getElementById('sel_course').value=0;">
                  <option value="0">-- <?=_("выберите специальность")?> --
      <?
      echo getTracksOptions();
?>
            </select></td>
  </tr>
<?php
}
?>

  <tr>
    <td> <?=_("Логин (префикс)")?></td>
    <td>
      <input name='txt_login' type='text' id="txt_login2" size='11' maxlength='11'>&nbsp;&nbsp;<? echo $GLOBALS['tooltip']->display('people_gen_login')?></td>
  </tr>
  <tr>
    <td><?=_("Длина пароля")?> </td>
    <td><input name='txt_length' type='text' id="txt_length" size='3' maxlength='3' value="7">&nbsp;&nbsp;<? echo $GLOBALS['tooltip']->display('people_gen_pwd_length')?></td>
  </tr>
  <tr>
    <td nowrap><input type="checkbox" name="ch_ingroup" id="ch_ingroup" value="1" onClick="javascript:choice=!this.checked;document.getElementById('sel_group').disabled=choice;document.getElementById('txt_group').disabled=choice;">&nbsp;<?=_("Включить в группу")?></td>
    <td>
    <select name="sel_group" id="sel_group" disabled onChange="javascript:document.getElementById('txt_group').value='';">
                  <option value="0">-- <?=_("выберите группу")?> --
<?
        echo selGroups(0);
?>
        </select>    &nbsp;</td></tr>
        <tr>
    <td nowrap><?=_("или создать новую группу:")?> </td>
    <td><input name="hid_act" type="hidden" id="act" value="gen"><input name='txt_group' type='text' id="txt_group" size='20' maxlength='20' onKeyPress="javascript:document.getElementById('sel_group').value=0;" disabled></td>
  </tr>
</table>
    </td>
  </tr>
  <tr>
    <td><br>
<?
        echo okbutton();
?>
    </td>
  </tr>
</table>
</form>
<?php
$GLOBALS['controller']->captureStop("m020201");
?>
<br>
<table border=0 cellpadding=0 cellspacing=0 align=center width="100%">

           <tr>
                         <td width=100% class=tabheader>
                                <table width=100% class=th2 cellpadding=0 cellspacing=0>
                                        <tr>
                     <td width=27 valign=top class=shown id=plusmain3>
                                                <a title="<?=_("показать")?>" href=# onClick="putElem('print'); removeElem('plusmain3'); putElem('minusmain3');" ><span class=cDisabled><span class=webd>4</span></span></a>
                                         </td>
                                         <td width=27 valign=top class=hidden2 id=minusmain3>
                                                <a title="<?=_("убрать")?>" href=# onClick="removeElem('print'); removeElem('minusmain3'); putElem('plusmain3');" ><span class=cDisabled><span class=webd>6</span></span></a>
                                         </td>
                            <td width=100%>
                                                &nbsp;<span id=createtest><?=_("удалить учетные записи")?></span>
                                         </td>
                         </tr>
                                </table>
                         </td>
                        </tr>

                         <tr>
                         <td width=100%>
                                <img src="images/spacer.gif" alt="" width=1 height=5 border=0>
                         </td>
                        </tr>
                </table>
<?php
$GLOBALS['controller']->captureFromOb("m020202");
?>
<form name="form_del" method="post" action="" onSubmit="javascript:return(confirm('<?=_("Вы действительно желаете удалить учетные записи? Восстановление будет невозможно!")?>'))">
<table id="print" width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
    <td class=tabheader>
<table width=100% class=main cellspacing=0>
  <tr>
    <td nowrap><?=_("Логин (префикс)")?></td>
    <td width="100%">
      <input name='txt_pattern_del' type='text' id="txt_pattern_del" size='15' maxlength='15'>&nbsp;&nbsp;<? echo $GLOBALS['tooltip']->display('people_del')?>
    </td>
  </tr>
</table>
    </td>
  </tr>
  <tr>
    <td><br>
	<input name="hid_act" type="hidden" id="act" value="del">
<?
        echo okbutton();
?>
    </td>
  </tr>
</table>
</form>
<?php
$GLOBALS['controller']->captureStop("m020202");
?>
<br>
<table border=0 cellpadding=0 cellspacing=0 align=center width="100%">

           <tr>
                         <td width=100% class=tabheader>
                                <table width=100% class=th2 cellpadding=0 cellspacing=0>
                                        <tr>
                                         <td width=27 valign=top class=shown id=plusmain2>
                                                <a title="<?=_("показать")?>" href=# onClick="putElem('imp'); removeElem('plusmain2'); putElem('minusmain2');" ><span class=cDisabled><span class=webd>4</span></span></a>
                                         </td>
                                         <td width=27 valign=top class=hidden2 id=minusmain2>
                                                <a title="<?=_("убрать")?>" href=# onClick="removeElem('imp'); removeElem('minusmain2'); putElem('plusmain2');" ><span class=cDisabled><span class=webd>6</span></span></a>
                                         </td>
                                         <td width=100%>
                                                &nbsp;<span id=createtest><?=_("импортировать учетные записи из csv")?></span>
                                         </td>
                         </tr>
                                </table>
                         </td>
                        </tr>

                         <tr>
                         <td width=100%>
                                <img src="images/spacer.gif" alt="" width=1 height=5 border=0>
                         </td>
                        </tr>
                </table>
<?php
$GLOBALS['controller']->captureFromOb("m020203");
?>
<form enctype="multipart/form-data" action="<?=$sitepath?>people_import.php" method="post" name="form_imp">
<table id="imp" width="100%"  border="0" cellspacing="0" cellpadding="0">
   <input type=hidden name="PHPSESSID" value="d550da1881b80e220042c67d27bab888">
   <input type=hidden name=c value="upload">
   <input type=hidden name=MAX_FILE_SIZE value=500000>
        <tr>
    <td >
<table width=100% class=main cellspacing=0>
  <tr>
    <td nowrap><?=_("CSV-файл с учетными записями")?> </td>
    <td width="100%">
      <input name=userfile type=file class=s8>
    </td>
  </tr>
  <!-- tr>
    <td valign="top"><?=_("Разделитель")?> </td>
    <td width="100%">
<input type=radio name=razd value=';' checked id=z2><label for=z2> <?=_("точка с запятой")?> [;]</label><br>
<input type=radio name=razd value=',' id=z1><label for=z1> <?=_("запятая")?> [,]</label>
    </td>
  </tr -->
  <tr>
    <td valign="top"><?=_("Назначаемая роль")?> </td>
    <td width="100%">
<input type=radio name=people_type value='stud_cid' checked id=p1><label for=p1> <?=_("слушатели на курс")?></label><br>
<?php
if (USE_SPECIALITIES){
?>
<input type=radio name=people_type value='stud_trid' id=p3><label for=p3> <?=_("слушатели на специальность")?></label><br><br>
<?
}
?>
<input type=radio name=people_type value='teac_cid' id=p2><label for=p2> <?=_("преподаватели")?></label>
    </td>
  </tr>
</table>
    </td>
  </tr>
  <tr>
    <td><br>    <input name="hid_act" type="hidden" id="act" value="imp">
<?
        echo okbutton(_('Далее').' &#8594;');
?>
    </td>
  </tr>
</table>
</form>
<?php
$GLOBALS['controller']->captureStop("m020203");
?>
<br>
<!--  IMPORT ACTIVE DIRECTORY -->
<table border=0 cellpadding=0 cellspacing=0 align=center width="100%">

           <tr>
                         <td width=100% class=tabheader>
                                <table width=100% class=th2 cellpadding=0 cellspacing=0>
                                        <tr>
                                         <td width=27 valign=top class=shown id=plusmain4>
                                                <a title="<?=_("показать")?>" href=# onClick="putElem('imp_ad'); removeElem('plusmain4'); putElem('minusmain4');" ><span class=cDisabled><span class=webd>4</span></span></a>
                                         </td>
                                         <td width=27 valign=top class=hidden2 id=minusmain4>
                                                <a title="<?=_("убрать")?>" href=# onClick="removeElem('imp_ad'); removeElem('minusmain4'); putElem('plusmain4');" ><span class=cDisabled><span class=webd>6</span></span></a>
                                         </td>
                                         <td width=100%>
                                                &nbsp;<span id=createtest><?=_("импортировать учетные записи из")?> active directory</span>
                                         </td>
                         </tr>
                                </table>
                         </td>
                        </tr>

                         <tr>
                         <td width=100%>
                                <img src="images/spacer.gif" alt="" width=1 height=5 border=0>
                         </td>
                        </tr>
                </table>
<?php
$GLOBALS['controller']->captureFromOb("m020204");
?>
<form action="<?=$sitepath?>people_import_ad.php" method="post" name="form_imp_ad">
<table id="imp_ad" width="100%"  border="0" cellspacing="0" cellpadding="0">
   <input type="hidden" name="import_from_ad" value="import_from_ad">
   <input type="hidden" name="do" value="listusers">
        <tr>
    <td class=tabheader>
<table align="center" border="0" cellspacing="0" cellpadding="5" width="100%"  style="font-size:13px" class=shedaddform>
  <tr>
    <td><?=_("Домен:")?> </td>
    <td width="100%">
      <input name="domain_name" type=text class=s8>
    </td>
  </tr>
  <tr>
    <td><?=_("Логин:")?> </td>
    <td width="100%">
      <input name="username" type=text class=s8>
    </td>
  </tr>
  <tr>
    <td><?=_("Пароль:")?> </td>
    <td width="100%">
      <input name="password" type=password class=s8>
    </td>
  </tr>
  <?php
    $sql = "SELECT name FROM OPTIONS WHERE name='ldap_host' OR name='ldap_user'";
    $res = sql($sql);
    if (sqlrows($res)==2) {
  ?>
  <tr><td colspan=2><input type="checkbox" name="use_exists_settings" value='1'> <?=_("использовать существующие настройки (небходимо указать только пароль)")?></td></tr>
  <?php
    }
  ?>
</table>
    </td>
  </tr>
  <tr>
    <td><br>
<?
        echo okbutton();
?>
    </td>
  </tr>
</table>
</form>
<!-- END IMPORT ACTIVE DIRECTORY -->
<?php
$GLOBALS['controller']->captureStop("m020204");
if (session_is_registered("arrStorePasswords")) $GLOBALS['controller']->setLink('m020205');
echo show_tb();
?>