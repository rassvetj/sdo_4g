<?php
$include=TRUE ;
define("FILE_LOG", "/zlog/xml/log.xml");
define("ENCODING_WESTERN", "ISO-8859-1");
define("ENCODING_RUSSIAN", "CP1251");
require ("setup.inc.php");
echo show_tb();
require ("adm_fun.php4");

if (isset($_GET['add'])) addFromDeans($_GET['add']);

if (isset($ok)) {
	if  (isset($HTTP_GET_VARS['name']) && isset($HTTP_GET_VARS['email'])) {
		if  (!empty($HTTP_GET_VARS['name']) && !empty($HTTP_GET_VARS['email']))	{
			$result=0;
			$result=sql_query(9,return_valid_value($_GET['name']));
			$result=sql_query(10,return_valid_value($_GET['email']));
			if (!$result) 
				echo "<font color='red'><b>New Information not added</b></font>";
			else 
				echo "<b>Save succes</b>";
		} //empty

	} // if all not empty
	else {
		$res=sql_query(6,"Name");
		$result=@sqlget($res);
		$name=$result['value'];
		$res=sql_query(6,"EMail");
		$result=@sqlget($res);
		$email=$result['value'];
	}
} // if isset $ok-yes
else {
	$res=sql_query(6,"Name");
	$result=@sqlget($res);
	$name=$result['value'];
	$res=sql_query(6,"EMail");
	$result=@sqlget($res);
	$email=$result['value'];
}

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("result",$result);
debug_yes("login",$login);
debug_yes("name",$name);
debug_yes("pass",$pass);
debug_yes("email",$email);

switch ($_POST['hid_act']) {
	case "add_pmgroup":
		$r = sql("SELECT * FROM permission_groups WHERE name='{$_POST['txt_pmname']}'");
		if (!sqlrows($r)) {
			$r = sql("INSERT INTO permission_groups (name, type) values ('{$_POST['txt_pmname']}', 'student')");
		}
	break;
	case "edit_pmgroup":
		$r = sql("DELETE FROM permission2act WHERE pmid!='2' AND type = 'student'", "err12354");
		if (is_array($_POST['ch_pmgroups'])) {
			foreach ($_POST['ch_pmgroups'] as $acid => $arrPmid) {
				foreach ($arrPmid as $pmid => $blabla) {
					$r = sql("INSERT INTO permission2act (acid, pmid, type) values ('{$acid}', '{$pmid}', 'student')","err738");
				}
			}
			
		}
	break;
	case "add_to_pmgroup":
		if (is_array($_POST['sel_pmgroup'])) {
                       $arrMids = array_keys($_POST['sel_pmgroup']);
                        $strMids = implode(", ", $arrMids);
                        foreach ($_POST['sel_pmgroup'] as $mid => $pmid) {
/*                                $q = "SELECT
                              ".$adodb->Concat("People.LastName", "' '",  "People.FirstName")." as full_name,
                        permission_groups.name as pm_name
                      FROM
                        permission2mid
                        INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
                        INNER JOIN People ON (permission2mid.`mid` = People.`MID`)
                      WHERE
                        permission2mid.`mid`='{$mid}'";*/
                                $q = "
                                        SELECT
                                          ".$adodb->Concat("People.LastName","' '","People.FirstName")." as full_name,
                                          permission_groups.name as pm_name
                                        FROM
                                          permission2mid
                                          INNER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
                                          INNER JOIN People ON (permission2mid.`mid` = People.`MID`)
                                        WHERE
                                          permission2mid.`mid`='{$mid}'
                                ";                        
                                $r = sql($q, "err45");
				$a = sqlget($r);
				$r = sql("SELECT * FROM permission_groups WHERE pmid='{$pmid}'","err66");
				$aa = sqlget($r);
				
				$query = "SELECT * FROM permission_groups WHERE type='student'";
				$result = sql($query,"err");
				
				$in_condition = "(";
				while($row = sqlget($result)) {
					$in_condition .= $row['pmid'].",";
				}
				
				$in_condition = trim($in_condition, ",").")";
				
								
				$r = sql("DELETE FROM permission2mid WHERE mid='{$mid}' AND pmid  IN $in_condition", "errrrr");
				
				$q = "INSERT INTO permission2mid (pmid, mid) values ('{$pmid}', '{$mid}')";
				$r = sql($q,"err56gf");
				if ($r && ($a['pm_name'] != $aa['name'])) 
				    if((float) PHP_VERSION < 5)
					logPmAlteration($a['full_name'], $a['pm_name'], $aa['name']);
			}
		}
	break;
	default:
	break;
}

if (isset($_GET['del'])) {
	$intDefaultPmid = getDefaultPmGroup("student");
	$r = sql("SELECT * FROM permission_groups WHERE pmid={$_GET['del']} AND `default`='0'");
	while ($a = sqlget($r)) {
		$r = sql("UPDATE permission2mid SET pmid='{$intDefaultPmid}' WHERE pmid={$_GET['del']}");
		$r = sql("DELETE FROM permission2act WHERE pmid={$_GET['del']}");
	}
	$r = sql("DELETE FROM permission_groups WHERE pmid={$_GET['del']} AND `default`='0'");
}

?>

<center>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
	<tr align="center">
    	<td class=questt>
        	<b><a href="students.php"><?=_("Обучаемые")?></a></b>
        </td>
    </tr>
</table>
<br />

<table width=80% class=main cellspacing=0>
	
	<form action="" method="POST" target="_self">
	<tr>
		<th><?=_("Логин")?></th><th><?=_("Имя")?></th><th><?=_("Фамилия")?></th><th><?=_("Группа")?></th></tr>
	<?echo studentsList();?>
  	  <td colspan=5><br>
    	  <input name="hid_act" type="hidden" id="hid_act" value="add_to_pmgroup">
	  	<?echo okbutton();?>
      </td>
  	</tr>
	</form>
</table>
<br>
<table border=0 cellpadding=0 cellspacing=0 align=center width="80%">
	<tr>
    	<td width=100% class=tabheader>
        	<table width=100% class=th2 cellpadding=0 cellspacing=0>
            	<tr>
                	<td width=27 valign=top class=shown id=plusmain1>
                    	<a title="<?=_("показать")?>" href='#add_pmgroup' onClick="putElem('gen'); removeElem('plusmain1'); putElem('minusmain1');" ><span class=cDisabled><span class=webd>4</span></span></a>
                    </td>
                    <td width=27 valign=top class=hidden id=minusmain1>
                    	<a title="<?=_("убрать")?>" href="javascript:removeElem('gen'); removeElem('minusmain1'); putElem('plusmain1');" ><span class=cDisabled><span class=webd>6</span></span></a>
                    </td>
                    <td width=100%>
                    	&nbsp;<span id=createtest><?=_("Добавить группу с ограниченными правами")?></span>
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

<table id="gen" width="80%"  border="0" cellspacing="0" cellpadding="0" class="hidden">
  <form name="form_add" method="post" action="">
  <tr class=questt>
  	<td class=tabheader>
  		<table align="center" border="0" cellspacing="0" cellpadding="5" width="100%" style="font-size:13px" class=shedaddform>
  			<tr class=questt>
    			<td nowrap> <?=_("Имя группы:")?></td>
    			<td width="100%">
      				<input name='txt_pmname' type='text' id="txt_pmname" size='15' maxlength='15'>
      				<input name="hid_act" type="hidden" id="hid_act" value="add_pmgroup">
    			</td>
  			</tr>
		</table>
    </td>
  </tr>
  <tr>
    <td>
    	<br /><?echo okbutton();?>
    </td>
  </tr>
  </form>
</table>
<br />

<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
        <b><a href="javascript:;"><?=_("Группы с ограниченными правами доступа")?></a></b>
      </td>
   </tr>
</table>
<br />

<form name="form_edit" method="post" action="">
	<table width="80%" border="0" cellspacing="1" cellpadding="5" bgcolor="#225CA3">
		
<?
		$arrTablePermissions = get_permissions_all(PERMISSIONS_STUDENT);

        $intNumCols = (count($arrTablePermissions)+1);
        $intWid = intval(100/$intNumCols);

        echo "<tr><th>&nbsp;</th>";
        foreach ($arrTablePermissions as $key => $val) {
                echo "<th width='{$intWid}%'><center><a style='color:white' href=\"javascript: void checkBoxes({$key}, arrTriggers[{$key}]); chState({$key})\">{$val}</a></center></th>";
        }
        echo "</tr>";
        $i = 0;

        $arrTableActions = get_actions(PERMISSIONS_STUDENT);
        
        foreach ($arrTableActions as $acid => $act_name) {
                $act_name = (ord($act_name)<128) ? ((defined($act_name)) ? strtolower(constant($act_name)) : _("кафедры")) : $act_name;
                $strColor = ((++$i)%2) ? "class=questt" : "bgcolor=#FFFFFF";
                echo "<tr {$strColor}><td nowrap>{$act_name}</td>";
                foreach ($arrTablePermissions as $pmid => $title) {
                        $strDisabled = (!isset($strDisabled)) ? "disabled" : "";
                        $ch = (check_permissions_group($acid, $pmid)) ? "checked" : "";
                        echo "<td align='center'><input type='checkbox' {$strDisabled} name='ch_pmgroups[{$acid}][{$pmid}]' value=1 $ch /></td>\n";
                }
                unset($strDisabled);
//                foreach ($arrTableChecked[$acid] as $pmid => $ch) {
//                        $strDisabled = ($arrTablePermissionsDefault[$pmid]) ? "disabled" : "";
//                        //if ($pmid == )
//                        echo "<td align='center'><input type='checkbox' {$strDisabled} name='ch_pmgroups[{$acid}][{$pmid}]' value=1 $ch /></td>\n";
//                }
                echo "</tr>";
        }
        echo "<tr bgcolor='#FFFFFF'><td>&nbsp;</td>";
        foreach ($arrTablePermissions as $key => $val) {
                $strIcon = getIcon("delete", _("Удалить группу"));
                $strDel = (!$arrTablePermissionsDefault[$key]) ? "<a href='{$sitepath}admin/students.php?del={$key}'>{$strIcon}</a>" : "";
                echo "<td align='center'>{$strDel}</td>";
        }
        echo "</tr></table>";
?>


<table width="80%" border="0" cellspacing="1" cellpadding="0">
  <tr>
    <td>
    	<input name="hid_act" type="hidden" id="hid_act" value="edit_pmgroup"><br>
		<?echo okbutton();?>
    </td>
  </tr>
</table>
</form>
</center>
<script language="JavaScript">
arrActions = new Array();
arrTriggers = new Array();
i=0;
<?
foreach ($arrTableActions as $key => $val) {
	?>
	arrActions[i] = <?=$key?>;
	arrTriggers[i++] = true;
	<?
}
?>
function checkBoxes(id, checked) {
	for (i = 0; i < arrActions.length; i++) {
		str = "ch_pmgroups[" + arrActions[i] + "][" + id + "]";
		obj = eval("document.getElementById('" + str + "')");
		obj.checked = checked;
	}
}

function chState(key) {
	arrTriggers[key]=!arrTriggers[key];
}

</script>
<?php
echo show_tb();

/*#####################################################################################################
####     ........FUNCTIONS.......        ##############################################################
#####################################################################################################*/

function logPmAlteration($strPerson, $strPrev, $strNext) {
	$strCharsetPerson = (ord($strPerson) <= 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;
	$strCharsetPrev= (ord($strPrev) <= 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;
	$strCharsetNext = (ord($strNext) <= 192) ? ENCODING_WESTERN : ENCODING_RUSSIAN ;
	$strPerson = iconv($strCharsetPerson, "UTF-8", $strPerson);
	$strPrev = iconv($strCharsetPrev, "UTF-8", $strPrev);
	$strNext = iconv($strCharsetNext, "UTF-8", $strNext);
	$strPath = $_SERVER['DOCUMENT_ROOT'].FILE_LOG;
	if ($xml = domxml_open_file($strPath)) {
		$log = $xml->document_element();
		$permissions = $xml->create_element("permissions");
		$permissions->set_attribute("date", date("Y-m-d H:i:s"));
		$log->append_child($permissions);
		$person = $xml->create_element("person");
		$person->set_content($strPerson);
		$permissions->append_child($person);
		$ch_from = $xml->create_element("ch_from");
		$ch_from->set_content($strPrev);
		$permissions->append_child($ch_from);
		$ch_to = $xml->create_element("ch_to");
		$ch_to->set_content($strNext);
		$permissions->append_child($ch_to);
		$xml->dump_file($strPath, false, true);
	}
}
?>