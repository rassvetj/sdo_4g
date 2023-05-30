<?php

//defines

$include=TRUE ;

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");
require ("error.inc.php4");


$connect=get_mysql_base();
debug_yes("array",$HTTP_COOKIE_VARS);

define("MIN_INVALID_LOGIN", 1);
define("MAX_INVALID_LOGIN", 99);

$arrAlert = array();
switch($_POST['hid_act']) {
        case "set_options":
                $intMaxInvalidLoginNew = ($_POST['ch_mode']) ? (int)$_POST['txt_number'] : 0;
                if ($_POST['ch_mode']) {
                        if (($intMaxInvalidLoginNew < MIN_INVALID_LOGIN) || ($intMaxInvalidLoginNew > MAX_INVALID_LOGIN)) {
                                $intMaxInvalidLoginNew = MAX_INVALID_LOGIN;
                        }
                } else {
                        $r = sql("UPDATE People SET invalid_login='0'");
                }
                setOption("max_invalid_login", $intMaxInvalidLoginNew);
                break;
        default:
                break;
}

$intMaxInvalidLogin = getOption('max_invalid_login');
if ($intMaxInvalidLogin) {
        $strMaxInvalidLogin = (string)$intMaxInvalidLogin;
        $strChecked = "checked";
        $strDisabled = "";
} else {
        $strMaxInvalidLogin = $strChecked = "";
        $strDisabled = "disabled";
}



$GLOBALS['controller']->captureFromOb("m020401");
?>
<center>
<table width=80% class=main cellspacing=0>
<form action="<?=$SERVER['PHPH_SELF']?>" method="post" name="form_allow" id="form_allow">
   <tr>
      <td class=questt>
  <input name="ch_mode" type="checkbox" id="ch_mode" value="1" onClick="javascript:document.getElementById('txt_number').disabled=!(this.checked)" <?=$strChecked?>>
  <?=_("блокировать учетную запись после")?>
  <input name="txt_number" type="text" id="txt_number" value="<?=$strMaxInvalidLogin?>" size="2" maxlength="2" style="width:20px;height:18px;font-size:11px;" <?=$strDisabled?>>
  <?=_("неудачных попыток")?>
  <input name="hid_act" type="hidden" id="hid_act" value="set_options">
          </td>
   </tr>
   <tr>
      <td>
<?
echo okbutton();
?>
          </td>
   </tr>
</form>
  </table>
<?php
$GLOBALS['controller']->captureStop("m020401");
//$GLOBALS['controller']->captureFromOb(TRASH);
?>
<br>
<table width=80% class=main cellspacing=0>
   <tr align="center">
      <td class=questt>
        <b><a href="javascript:;"><?=_("Заблокированные учетные записи")?></a></b>
          </td>
   </tr>
  </table>
<?
//$GLOBALS['controller']->captureStop(TRASH);
$GLOBALS['controller']->captureFromOb("m020402");
// generate table
$arrFlush = $_POST["arr_flush"];
$arrMail = $_POST["arr_mail"];
if (isset($_POST['flush']))
{
        if (is_array($arrFlush)) {
                foreach ($arrFlush as $key => $val) {
                        $q = "UPDATE People SET invalid_login=0 WHERE MID='{$key}'";
                        $r = sql($q);
                }
        }
        if (is_array($arrMail)) {
                foreach ($arrMail as $key => $val) {
                        mailToSimple("invalid_login_flush", $key);
                }
        }
}
?>
<form name="form1" method="post" action="<?=$_SERVER['PHP_SELF']?>">
 <table width=80% class=main cellspacing=0>
   <tr align="center">
      <td class=questt>
            <b><?=_("Логин")?></b>
      </td>
      <td class=questt>
            <b><?=_("Имя")?></b>
      </td>
      <td class=questt>
            <b><?=_("Разблокировать")?></b>
      </td>
      <td class=questt>
            <b><?=_("Уведомить")?></b>
      </td>
   </tr>
<?
          $intMaxInvalidLogin = getOption('max_invalid_login');
          $sqlExtraWhere = ((integer)$intMaxInvalidLogin > 0) ? "invalid_login>=".$intMaxInvalidLogin : "";
          //$q = "SELECT MID, Login, CONCAT(LastName, ' ', FirstName) as name, invalid_login FROM People WHERE {$sqlExtraWhere} ORDER BY MID";
          if (!empty($sqlExtraWhere)) $sqlExtraWhere = " WHERE {$sqlExtraWhere} "; else $sqlExtraWhere= " WHERE 0=1 ";
          $q = "SELECT MID, Login, ".$adodb->Concat("LastName","' '","FirstName")." as name, invalid_login FROM People {$sqlExtraWhere} ORDER BY MID";
//          var_dump($q);

          $r = sql($q);
          while ($a = sqlget($r)){
              $color=($i%2) ? "class=questt"  : "bgcolor=\"white\""; //set row color
              $i++;
?>
   <tr align="center">
      <td <?=$color?>><?=$a['Login']?></td>
      <td <?=$color?>><?=$a['name']?></td>
      <td <?=$color?>><input type="checkbox" name="arr_flush[<?=$a['MID']?>]" id="arr_flush<?=$a['MID']?>" value="1" onClick="javascript:document.getElementById('arr_mail<?=$a['MID']?>').disabled=!this.checked;"></td>
      <td <?=$color?>><input type="checkbox" name="arr_mail[<?=$a['MID']?>]" id="arr_mail<?=$a['MID']?>" value="1" disabled></td>
      </tr>
<?
          }
          if (!sqlrows($r)){
?>
          <tr><td colspan="4"><?=_("Список пуст")?>
          </td></tr>
<?
          }
?>
          <tr><td colspan="4">
<?
echo okbutton();
?>
          </td></tr>
</table>
<input name="flush" type="hidden" value="1">
</form>
</center>
<?php


//require_once("adm_b.php4");
$GLOBALS['controller']->captureStop("m020402");
echo show_tb();
?>