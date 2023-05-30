<?php

debug_yes("include",$include);
error_reporting(2039);
require_once "../metadata.lib.php";
$accepted_fieldnames = array("MID", "LastName", "FirstName",'Patronymic', "Login", "Password", "EMail", "Access_Level", "blocked");
$accepted_fieldname_aliases = array("MID" => "ID", "LastName" => _("Фамилия"), "FirstName" => _("Имя"),'Patronymic' => _("Отчество"), "EMail" => "E-mail", "Information" => _("Дополнительная информация"), "last" => _("Последнее посещение"), "countlogin" => _("Всего посещений"), "invalid_login" => _("Неверный пароль"), "Login" => _("Логин"), "Password" => _("Пароль"), 'Access_Level'=>_("Уровень доступа к материалам библиотеки"),'mid_external'=>_("Табельный номер"), 'blocked' => _('Статус'), 'Status'=>_("Роль"));

if (!isset($include)) require ("setup.inc.php");
istest();
if (!$admin) login_error();
function get_connect()
{
    //	$connect=@mysql_connect(DB_HOST,DB_USER,DB_PASSWD);
    //	return $connect;
} // function get connect -adm_fun.php4
function get_mysql_base()
{
    //	$connect=get_connect();
    //	return @mysql_select_db (DB_DB,$connect);
} //function get_mysql_base - all scripts
function sql_query($type,$param="")
{
    global $adodb;

    debug_yes("min",$param->min);
    debug_yes("max",$param->max);
    switch ($type)
    {
        //	$sql="SELECT ".$value." FROM ".$table." where ".$col."='".$param."' ".$sort;
        case -5  : $sql="UPDATE OPTIONS SET value=$param  where name='version'"; break; // design.php4,personal.php4,arhiv.php4
        case 0  : if ($param!="") $sql="SELECT * FROM ".$param." "; break; // sp.php4
        case 1  : $sql="SELECT value FROM OPTIONS where name='template'"; break; // style.php4
        case 2  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='template'"; break; // style.php4
        case 3  : $sql="SELECT value FROM OPTIONS where name='adminPass'"; break; //pass.php4
        case 4  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='adminPass'"; break; //pass.php4
        case 5  : $sql="SELECT value FROM OPTIONS where name='version'"; break; // design.php4,personal.php4,arhiv.php4
        case 6  : if ($param!="") $sql="SELECT value FROM OPTIONS where name='dekan".$param."'"; break; //dekan.php4
        case 7  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='dekanPass'"; break; //dekan.php4
        case 8  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='dekanLogin'"; break; //dekan.php4
        case 9  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='dekanName'"; break; //dekan.php4
        case 10  : if ($param!="") $sql="UPDATE OPTIONS SET value='".$param."' WHERE name='dekanEMail'"; break; //dekan.php4
        case 11  : if ($param!="") $sql="DELETE FROM ".$param; break; //sp.php4
        case 12  : if ($param!="") $sql="INSERT INTO personal ( FIO, work, tel, email, type) VALUES ( '".$param['FIO']."', '".$param['work']."' , '".$param['tel']."', '".$param['email']."', '".$param['type']."')"; break; //sp.php4
        case 14  : if ($param!="") $sql="UPDATE personal SET FIO='".$param['FIO']."', work='".$param['work']."', tel='".$param['tel']."', email='".$param['email']."', type='".$param['type']."' WHERE PID='".$param['PID']."'"; break; //sp.php4
        case 15 :
            $sql = "SELECT DISTINCT Teachers.MID FROM Teachers";
            $arr=array(0);
            $res = sql($sql);
            while ($row = sqlget($res)){
                $arr[] = $row[MID];
            }
            if ($param!="") {
                $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).") ORDER BY ".$param." ASC";
            } else {
                $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).")";
            }
            break;
        case 35 : $sql="SELECT * from OPTIONS where name='free_add_course'"; break;
        case -35 : $sql="INSERT INTO OPTIONS (value) values ($param) where name='free_add_course'"; break;
        case 16 :
            $sql = "SELECT DISTINCT Students.MID FROM Students";
            $res = sql($sql);
            $arr=array(0);
            while ($row = sqlget($res)){
                $arr[] = $row[MID];
            }
            if ($param!=""){
                $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).") ORDER BY ".$param." ASC";
            } else {
                $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).")";
            }
            break;
        case 17 :
            $sql = "SELECT DISTINCT graduated.MID FROM graduated";
            $res = sql($sql);
            $arr=array(0);
            while ($row = sqlget($res)){
                $arr[] = $row[MID];
            }
            if ($param!="") {
                $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).") ORDER BY ".$param." ASC";
            } else {
                if (is_array($arr)) $sql="SELECT * from People WHERE People.MID IN (".implode(", ",$arr).")";
            }
            break;
        case 18 :
            if ($param!=""){
                $sql="select People.* from People, claimants  WHERE claimants.MID=People.MID AND claimants.Teacher=0 ORDER BY ".$param." ASC";
            } else {
                $sql="select * from People, claimants  WHERE claimants.MID=People.MID AND claimants.Teacher=0";
            }
            break;
        case 19 : if ($param!="") $sql="select * from People WHERE MID='".$param."'"; break;
        case 20 : $sql="SHOW FIELDS FROM People"; break;
        case 21 : if ($param!="") $sql="DELETE FROM People WHERE MID='".$param."'"; break;
        case 22 : if ($param!="") $sql="DELETE FROM Teachers WHERE MID='".$param."'"; break;
        case 24 : if ($param!="") $sql="DELETE FROM Students WHERE MID='".$param."'"; break;
        case 25 : if ($param!="") $sql="DELETE FROM claimants WHERE MID='".$param."'"; break;
        case 26 : if ($param!="") $sql="DELETE FROM graduated WHERE MID='".$param."'"; break;
        case 27 :
            $order = isset($param['order']) ? "ORDER BY People.{$param['order']} ASC" : "";
            $sql = "SELECT
				People.MID as MID,
				People.mid_external as mid_external,
				People.LastName as LastName,
				People.FirstName as FirstName,
				People.Registered as Registered,
				People.Course as Course,
				People.EMail as EMail,
				People.Phone as Phone,
				People.Information as Information,
				People.Patronymic as Patronymic,
				People.Address as Address,
				People.Fax as Fax,
				People.Password as Password,
				People.javapassword as javapassword,
				People.Login as Login,
				People.BirthDate as BirthDate,
				People.CellularNumber as CellularNumber,
				People.ICQNumber as ICQNumber,
				People.Age as Age,
				People.last as last,
				People.countlogin as countlogin,
				People.rnid as rnid,
				People.Position as Position,
				People.PositionDate as PositionDate,
				People.PositionPrev as PositionPrev,
				People.invalid_login as invalid_login,
				People.isAD as isAD,
				People.polls as polls,
				People.Access_Level as Access_Level
			FROM People ";
            switch($param['status']) {
                // Претенденты
                case "1": $sql .= "INNER JOIN claimants ON claimants.MID = People.MID"; break;
                // Выпускники
                case "2": $sql .= "INNER JOIN graduated ON graduated.MID = People.MID"; break;
                // Обучаемые
                case "3": $sql .= "INNER JOIN Students ON Students.MID = People.MID"; break;
                // Преподаватели
                case "4": $sql .= "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
                // Учебная администрация
                case "5": $sql .= "INNER JOIN deans ON deans.MID = People.MID"; break;
                // Администраторы
                case "6": $sql .= "INNER JOIN admins ON admins.MID = People.MID"; break;
                // Без статуса
                case "7": $sql .= "LEFT OUTER JOIN claimants ON claimants.MID = People.MID LEFT OUTER JOIN graduated ON graduated.MID = People.MID LEFT OUTER JOIN Students ON Students.MID = People.MID LEFT OUTER JOIN Teachers ON Teachers.MID = People.MID LEFT OUTER JOIN deans ON deans.MID = People.MID LEFT OUTER JOIN admins ON admins.MID = People.MID WHERE claimants.MID IS NULL AND graduated.MID IS NULL AND Students.MID IS NULL AND Teachers.MID IS NULL AND deans.MID IS NULL AND admins.MID IS NULL"; break;
                // Все
                case "0": default: break;
            }
            $sql .= " {$order}";
            break;
        case 23 :
            if ($param!="")
            if($param['Password'] != "") {
                $Password = $param['Password'];
                mailTostud("change", $param['MID'], 0, "");
                $tmp = "Password=PASSWORD('".$param['Password']."'),";
            } else {
                $tmp = "";
            }
            $sql=
            "UPDATE People
			 SET LastName=".$adodb->Quote($param['LastName']).",
			     FirstName=".$adodb->Quote($param['FirstName']).",
			     Registered=".$adodb->Quote($param['Registered']).",
			     Course=".$adodb->Quote($param['Course']).",
			     EMail=".$adodb->Quote($param['EMail']).",
			     Phone=".$adodb->Quote($param['Phone']).",
			     Information=".$adodb->Quote($param['Information']).",
			     Patronymic=".$adodb->Quote($param['Patronymic']).",
			     Address=".$adodb->Quote($param['Address']).",
			     Fax=".$adodb->Quote($param['Fax']).",$tmp
			     Login=".$adodb->Quote($param['Login']).",
			     BirthDate=".$adodb->Quote($param['BirthDate']).",
			     CellularNumber=".$adodb->Quote($param['CellularNumber']).",
			     ICQNumber=".$adodb->Quote($param['ICQNumber']).",
			     Age=".$adodb->Quote($param['Age']).",
			     invalid_login=".$adodb->Quote($param['invalid_login']).",
			     Access_Level=".(USE_CMS_INTEGRATION ? $adodb->Quote($param['Access_Level']) : 'Access_Level')./*",
			     mid_external=".$adodb->Quote($param['mid_external']).*/"
			 WHERE MID='".$param['MID']."'"; break;
        case 30 :
            if ($param!="")
            if($param['Password'] != "") {
                $Password = $param['Password'];
                $Pass = "PASSWORD('".$param['Password']."'),";
            } else {
                $Pass = "";
            }
            $sql=
            "INSERT INTO People (
                LastName,
			    FirstName,
			    Registered,
			    Course,
			    EMail,
			    Phone,
			    Information,
    	        Patronymic,
		        Address,
			    Fax,
			    Login,
			    ".($Password ? 'Password,' : '')."
			    CellularNumber,
    		    ICQNumber,
    		    Age,
    		    invalid_login,
    		    Access_Level
                )
			   VALUES (
			     {$adodb->Quote($param['LastName'])},
			     {$adodb->Quote($param['FirstName'])},
			     {$adodb->Quote((int)$param['Registered'])},
			     {$adodb->Quote((int)$param['Course'])},
			     {$adodb->Quote($param['EMail'])},
			     {$adodb->Quote($param['Phone'])},
			     {$adodb->Quote($param['Information'])},
			     {$adodb->Quote($param['Patronymic'])},
			     {$adodb->Quote($param['Address'])},
			     {$adodb->Quote($param['Fax'])},
			     {$adodb->Quote($param['Login'])},
			     $Pass
			     {$adodb->Quote($param['CellularNumber'])},
			     {$adodb->Quote((int)$param['ICQNumber'])},
			     {$adodb->Quote((int)$param['Age'])},
			     {$adodb->Quote((int)$param['invalid_login'])},
			     {$adodb->Quote((int)$param['Access_Level'])}
			   )";
            break;
            //ORDER BY ".$param['type']." LIMIT ".$param['min'].",".$param['max']
        default : exit();
    }
    debug_yes("SQL",$sql);
    $result=@sql($sql,"errTRE");
    //	}else
    //	{       $result=array(0);
    //	}
    return $result;
} // function sql_query look up
/**
 * clear_temp_dir()
 *
 * @return empty dir temp;
 * @use - images.php4
*/
function clear_temp_dir()
{
    $d="temp/";
    if (!isset($d)) $d=realpath("./")."/";
    if ($d[strlen($d)-1]!="/") $d.="/";
    if (isset($files)) unset($files);
    $di=@dir($d);
    while ($name=$di->read()) {
        if ($name=="." || $name=="..") continue;
        if (@is_dir($d.$name)) $files["1 $name"]=$name;
        else $files["2 $name"]=$name;
        $ftype[$name]=@filetype($d.$name);
    } //while
    $di->close();
    if (isset($files))
    {
        if(count($files)!=0)
        {
            ksort($files);
            foreach ($files as $k=>$v) {
                $name=$d.$v;
                switch($ftype[$v]) {
                    case "file":
                        unlink("temp/".urlencode($v));
                        break;
                } // swith --
            } //foreach --
        } //if count $files = 0
    } //if set $files
} //clear_temp_dir() images.php4
/**
 * list_all_images()
 *
 * @param $d - dir
 * @return - list of dir images
 * @use - images.php4
 */
function list_all_images($d)
{
    if ($d[strlen($d)-1]!="/") $d.="/";
    $d=str_replace("\\","/",$d);
    if (!realpath($d)) die("Error path. <a href=$PHP_SELF>Click here for start</a>.");
    $di=@dir($d);
    if (!$di) exit("<font color=red><b>access denied</b></font>");
    if(isset($files)) unset($files);
    while ($name=$di->read()) {
        if ($name=="." || $name=="..") continue;
        if (@is_dir($d.$name)) $files["1 $name"]=$name;
        else $files["2 $name"]=$name;
        $ftype[$name]=@filetype($d.$name);
    } //while $name - read dir
    $di->close();
    if (isset($files))
    {
        if (count($files)!=0)
        {
            ksort($files);
            if ($d[strlen($d)-1]!="/") $d.="/";
            foreach ($files as $k=>$v)
            {
                $name=$d.$v;
                switch($ftype[$v]) {
                    case "dir":
?>
					<tr align="center">
						<td  class=questt>
<?php
echo "<b>".substr($v,0,48)."</b>";
?>
						</td>
					</tr>
<?php
list_all_images($d.substr($v,0,48));
break;
                    case "file":
?>
					<tr align="center">
						<td  bgcolor="white">
<?php
echo "<a  onclick=\"window.returnValue='".$d.substr($v,0,48)."'; window.close()\"><img src=\"".$d.substr($v,0,48)."\" onmouseover=\"oElement = window.event.srcElement; if (oElement == '[object]') oElement.style.cursor = 'hand';\"></a>";
?>
						</td>
					</tr>
<?php
break;
                    default:
                        break;
                } //switch
                flush();
            } //foreach $files
        } //if isset $files
    } //if count $files=0
} //list_all_images() images.php4

/**
 * show_personal_table()
 *
 * @param $res - mysql resurses
 * @return table of studium personal
 * @use - sp.php4
 */
function show_personal_table($res,$st)
{
    $i=0;
?>
	<table width=100% class=main cellspacing=0>
		<tr align="center">
			<th width="0">
				<b><?=_("Удалить")?></b>
			</th>
			<th width="0" >
				<b><?=_("Редактировать")?></b>
			</th>
			<th width="0" >
				<a href="sp.php4?sn=PID&st=<?=$st?>"><b>iD</b></a>
			</th>
			<th width="250px">
				<a href="sp.php4?sn=FIO&st=<?=$st?>"><b><?=_("Полное имя")?></b></a>
			</th>
			<th width="250px">
				<a href="sp.php4?sn=work&st=<?=$st?>"><b><?=_("Должность")?></b></a>
			</th>
			<th width="150px">
				<a href="sp.php4?sn=tel&st=<?=$st?>"><b><?=_("Телефон")?></b></a>
			</th>
			<th width="200px">
				<a href="sp.php4?sn=email&st=<?=$st?>"><b>e-mail</b></a>
			</th>
			<th width="20px">
				<a href="sp.php4?sn=type&st=<?=$st?>"><b><?=_("Тип")?></b></a>
			</th>
		</tr>
<?php
while ($result=@sqlget($res))
{
    $color=($i%2) ? "class=questt"  : "bgcolor=\"white\""; //set row color
    $i++;
?>
		<tr align="center">
			<td width="0"  <?=$color?>>
				<a href="sp.php4?del=1&pid=<?=$result['PID']?>" onclick="if (!confirm('<?=_("Вы действительно желаете удалить?")?>')) return false;"><?=_("Удалить")?></a>
			</td>
			<td width="0"  <?=$color?>>
				<a href="sp.php4?edit=1&pid=<?=$result['PID']?>"><?=_("Редактировать")?></a>
			</td>
			<td width="0"  <?=$color?>>
				<?=$result['PID']?>
			</td>
			<td width="250px" <?=$color?>>
				<?=$result['FIO']?>
			</td>
			<td width="250px" <?=$color?>>
				<?=$result['work']?>
			</td>
			<td width="150px" <?=$color?>>
				<?=$result['tel']?>
			</td>
			<td width="200px" <?=$color?>>
				<?=$result['email']?>
			</td>
			<td width="20px"  <?=$color?>>
				<?=$result['type']?>
			</td>
		</tr>
<?php
} // while result
?>
	</table>
<?php
$GLOBALS['controller']->captureFromOb(TRASH);
?>
	<br />
	<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
		<tr align="center">
			<td class=questt>
				<b><a href="sp.php4?edit=1"><?=_("Добавить")?></a></b>
			</td>
		</tr>
	</table>
<?php
$GLOBALS['controller']->captureStop(TRASH);
} //function show pesonal table - sp.php4

/**
 * show_edit_table()
 *
 * @param $param - Personal Atribut
 * @return table of one person
 * @use sp.php4
 */
function show_edit_table ($param=0)
{
?>
	<form action="sp.php4" method=get target="_self" name="person">
		<table align="left" border="0" width="80%" class="br">
			<tr align="center">
				<td width="50%" bgcolor="white">
					<?=get_red_text(_("Полное имя"),$param['FIO'])?>
				</td>
				<td width="50%" bgcolor="white">
					<input name="FIO" style="width:100%" value="<?=$param['FIO']?>">
				</td>
			</tr>
			<tr align="center">
				<td width="50%" class=questt>
					<?=get_red_text(_("Должность"),$param['work'])?>
				</td>
				<td width="50%" class=questt>
					<input name="work" style="width:100%" value="<?=$param['work']?>">
				</td>
			</tr>
			<tr align="center">
				<td width="50%" bgcolor="white">
					<?=get_red_text(_("Контактный телефон"),$param['tel'])?>
				</td>
				<td width="50%" bgcolor="white">
					<input name="tel" style="width:100%" value="<?=$param['tel']?>">
				</td>
			</tr>
			<tr align="center">
				<td width="50%" class=questt>
					<?=get_red_text(_("Контактный email"),$param['email'])?>
				</td>
				<td width="50%" class=questt>
					<input name="email" style="width:100%" value="<?=$param['email']?>">
				</td>
			<tr align="center">
				<td width="50%" bgcolor="white">
					<?=get_red_text(_("Тип"),$param['type'])?>
				</td>
				<td width="50%" bgcolor="white">
					<input name="type" style="width:100%" value="<?=$param['type']?>">
				</td>
			</tr>
			<tr align="center">
				<td width="50%" class=questt>
<?php
//				echo($param);
echo "<input type=hidden name=\"PID\" value=\"".$param['PID']."\">\n";
?>
					<input type=submit name="ok" value="<?=_("Сохранить")?>">
				</td>
				<td width="50%" class=questt>
					<input type=reset name="ok" value="<?=_("Сбросить")?>">
				</td>
			</tr>
		</table>
	</form>
<?php
} //function show_edit_table - sp.php4

/**
 * get_red_text()
 *
 * @param $text
 * @param $param
 * @return red if $param-empty , default if $param-not empty
 * @use adm_fun.php4
 */
function get_red_text($text,$param)
{
    echo (empty($param)) ? "<font color=red>" : "";
    echo($text);
    echo (empty($param)) ? "<font color=red>" : "";
} //function get_red_text

/**
 * generate_table()
 *
 * @param $link - page
 * @param $res - mysql query
 * @param $st - sort type
 * @return generate table of MySQl query
 * @use students.php4 teachers.php4 arhiv.php4 abitur.php4
 */
function generate_table($link,$res,$st=0)
{
    global $s;
    global $accepted_fieldname_aliases;
    $status = "&Status={$_GET['Status']}";
    $return=0;
    if($link=="people.php") {
        echo "
			<form action='{$link}' method=\"POST\" onSubmit=\"var select = document.getElementById('action'); if (select && (select.value=='delete')) {if (confirm('"._("Вы действительно желаете удалить учетные записи пользователей из системы? Восстановление будет невозможно!")."')) return true; else return false;} else return true; \">
				<script language=\"javascript\" type=\"text/javascript\">
				// <!--
					function select_all_items(elm_prefix,checked) {
						var i=1;
						elm = document.getElementById(elm_prefix+'_'+(i++));
						while (elm) {
							elm.checked = checked;
							elm = document.getElementById(elm_prefix+'_'+(i++));
						}
					}
				//-->
				</script>
		";
    }
      echo "<br>
        <div style='padding-bottom: 5px;'>
            <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
            <div><a href='{$GLOBALS['sitepath']}admin/people.php?MID=0&edit=1' style='text-decoration: none;'>"._("создать учетную запись")."</a></div>
        </div>
      ";
    echo "
		<table width=100% class=main cellspacing=0>
			<tr>".(($link=="people.php") ? "
				<th width='0'>
					<input type=\"checkbox\" id=\"select_all\" onClick=\"select_all_items('people',this.checked);\">
				</th>" : "")."";
    if(($link == "people.php")||($link == "teachers.php4")||($link == "students.php4")||($link == "abitur.php4")) {
        $accepted_fieldname = array("MID", "Login", "name", "last", "countlogin",/*"invalid_login","blocked",'mid_external'*/);

        if (defined('NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL') && !NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
            if (in_array('EMail', $accepted_fieldname)) {
                unset($accepted_fieldname[array_search('EMail', $accepted_fieldname)]);
            }
        }
    }
    elseif ($link == "arhiv.php4") {
        $accepted_fieldname = array("MID", "LastName", "FirstName", "EMail", "Information", "last", "countlogin", "invalid_login");
    }
    else {
        $accepted_fieldname = array();
    }
    $name = array();
    foreach ($accepted_fieldname as $fieldname) {
        $strFieldName = get_case_sensetive_field("People", $fieldname);
        if(in_array($strFieldName, $name)) continue;
        if((!in_array($strFieldName, $accepted_fieldname))&&(!empty($accepted_fieldname))) {
            continue;
        }
        if($strFieldName == "name") {
            echo "<th><b><a href='{$link}?".(isset($_GET['Name']) ? "Name={$_GET['Name']}&" : "").(isset($_GET['Metadata']) ? "Metadata={$_GET['Metadata']}&" : "")."order=LastName,FirstName,Patronymic'>" . _('ФИО') . "</a></th>";
        } else
        echo "<th>
									<b><a href='{$link}?".(isset($_GET['Name']) ? "Name={$_GET['Name']}&" : "").(isset($_GET['Metadata']) ? "Metadata={$_GET['Metadata']}&" : "")."order=".$fieldname."{$status}'>".$accepted_fieldname_aliases[$strFieldName]."</a></b>
								</th>";
        $name[]=$strFieldName; // tacke all field names on the array
    } //while fetch field - generate top names
    echo "
	<th width='0'>
		<b>"._("Действия")."</b>
	</th>
    </tr>";
    $i=0;
    if (count($name)) debug_yes("array",$name);
    while ($field = sqlget($res)) {
        $color=($i%2) ? "class='odd'"  : "class='even'"; //set row color
        $i++;
        $style = '';
        if ($field['blocked']) $style = " style=\"background: #EEE\" ";
        echo "<tr valign='top' $color>";
        if($link=="people.php") {
            echo "<td><input type=\"checkbox\" id=\"people_{$i}\" name=\"people[]\" value=\"{$field['MID']}\"></td>";
        }
        if(is_array($name)) {
            reset($name);
            while(list($key,$value) = each($name)) {

                if (($value == 'EMail') && defined('NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL') && !NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
                    continue;
                }

                $strAlign = "";
                switch ($value){
                    case 'Information':{
                        $arrBlocks = explode(";", REGISTRATION_FORM);
                        $strData = "";
                        foreach ($arrBlocks as $block) {
                            $strData .= view_metadata_as_text(read_metadata (stripslashes( $field[$value]), $block), $block);
                        }
                        $dsc = (strlen($strData)) ? "
						<span id='plus_{$i}' style=\"display: block\" onClick=\"putElem('new_{$i}'); removeElem('plus_{$i}'); putElem('minus_{$i}');\" >
							<span class=\"cDisabled\"><span title='"._("показать")."' class=webdna style='cursor:pointer;'>&#8594;</span></span>
						</span>
						<span id='minus_{$i}' style=\"display: none;\" onClick=\"removeElem('new_{$i}'); removeElem('minus_{$i}'); putElem('plus_{$i}');\" >
							<span class=cDisabled><span title='"._("убрать")."' class=webdna style='cursor:pointer;'>&#8595;</span></span>
						</span>
						<span id='new_{$i}' style=\"display: none;\"><br>{$strData}</span>
					" : "";
                        break;
                    }
                    case 'Address':$dsc=view_metadata_as_text(read_metadata (stripslashes($field[$value]), "address" ),"address");break;
                    case 'last':
                        $timest= date($field[$value]);
                        if($timest) {
                            $dsc = substr($timest, 6, 2)."-";
                            $dsc .= substr($timest, 4, 2)."-";
                            $dsc .= substr($timest, 0, 4)."<br />";
                            $dsc .= substr($timest, 8, 2).":";
                            $dsc .= substr($timest, 10, 2).":";
                            $dsc .= substr($timest, 12, 2);
                        } else {
                            $dsc = "-";
                        }
                        break;
                    case 'Login':
                        $dsc = "<a href=\"javascript:wopen('{$GLOBALS['sitepath']}userinfo.php?mid={$field['MID']}','userinfo{$field['MID']}',450,180);\">".$field[$value].'</a>';
                        break;
                    case 'blocked':
                        $dsc = ($field[$value] == 0) ? _('Активен') : _('Заблокирован');
                        break;
                    case 'name':
                        $dsc = "{$field['LastName']} {$field['FirstName']} {$field['Patronymic']}";
                        break;
                    default:
                        $dsc=$field[$value];
                        break;
                }
                echo "
					<td $style>
						$dsc
					</td>";
            } // while put value in the all cell on row
        } // if is_array
        echo "<td $style align='center'>";
        echo "<a href='$link?MID=".$field['MID']."&edit=1";
        if ($GLOBALS['pageID']) echo "&pageID=".(int) $GLOBALS['pageID'];
        echo "'>".getIcon("edit",_("Редактировать учетную запись") )."</a>";
        if($s['mid'] != $field['MID']) {
            $str_message = ($link == "people.php") ? _("Вы действительно желаете удалить учетную запись пользователя из системы? Восстановление будет невозможно!") : _("Вы действительно желаете удалить?");
            echo "<a href='$link?MID=".$field['MID']."&del=1";
            if ($GLOBALS['pageID']) echo "&pageID=".(int) $GLOBALS['pageID'];
            echo "' onclick=\"if (!confirm('{$str_message}')) return false;\">
					".getIcon("delete",_("Удалить учетную запись") )."</a>";
        } else {
        	echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('people_del_self');
            echo "<span style='visibility: hidden;'>".getIcon("delete",_("удалить") )."</span>";
        }
        echo "</td></tr>";
        $return=1;
    }// while $field fethch array
    echo "</table>";
    if($link=="people.php") {
        echo "
    <script>
    function check_action_select(action) {
        if(action == 'block') {
            document.getElementById('block').style.display = 'inline';
        }
        else {
            document.getElementById('block').style.display = 'none';
        }
    }
    </script>
    <table width=100% class=main cellspacing=0>
        <tr>
            <td align=right nowrap>"._("Выполнить действие")."
                <select id=\"action\" name=\"action\" onchange='javascript:check_action_select(this.value);'>";
        $actions = array(_("удалить") => "delete", _("заблокировать") => "block", _("разблокировать") => "unblock");
        foreach ($actions as $act_t => $act_v) {
            echo "<option value='{$act_v}'>{$act_t}</option>";
        }
        echo "</select>&nbsp;&nbsp;<span id='block' style='display:none;'>&nbsp;&nbsp;"._("Причина блокировки").":&nbsp;<input type=text name='reason' size=27>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('people_block') . "</span></td></tr></table><br>";
        echo okbutton();
        echo "</form>";
    }
    return $return;
} //function generate_table($link,$res,$st=0)

function edit_table($link,$MID) {
    global $accepted_fieldname_aliases;
    $return=0;
    //if ($MID==0) die("Invalid MID");
    $fields = getTableFields("People");
    if(($link == "people.php")||($link == "teachers.php4")||($link == "students.php4")||($link == "abitur.php4")) {
        $accepted_fieldname = $GLOBALS['accepted_fieldnames'];
    } else {
        $accepted_fieldname = array();
    }
    foreach($fields as $key => $value) {
        $strFieldName = get_case_sensetive_field("People", $key);
        if((!in_array($strFieldName, $accepted_fieldname))&&(!empty($accepted_fieldname))) {
            continue;
        }
        debug_yes("array",$field);
        $name[$strFieldName] = $strFieldName; // tacke all field names on the array
        $type[$strFieldName] = $value->type;  // tacke all field types on the array
        $i++;
    } //while fetch field - generate top names
    if ($MID) {
    $res = sql_query(19,$MID);
    $field = sqlget($res);
    }else {
        $field = Array (
            'MID' => 0,
            'mid_external' => '',
            'LastName' => '',
            'FirstName' => '',
            'Patronymic' => '',
            'Registered' => 0,
            'Course' => 0,
            'EMail' => '',
            'Phone' => '',
            'Information' => $TODOMeta,
            'Address' => '',
            'Fax' => '',
            'Login' => '',
            'Password' => substr(md5(mt_srand(microtime(true)*1000)), 0, 8),
            'javapassword' => '',
            'BirthDate' => '0000-00-00',
            'CellularNumber' => '',
            'ICQNumber' => 0,
            'Age' => 0,
            'last' => time(),
            'countlogin' => 6,
            'rnid' => 0,
            'Position' => '',
            'PositionDate' => '0000-00-00',
            'PositionPrev' => '',
            'invalid_login' => 0,
            'isAD' => 0,
            'polls' => '',
            'Access_Level' => 0,
            'rang' => 0,
            'preferred_lang' => 0,
            'blocked' => 0,
            'block_message' => ''
        );

    }
    if (count($field)>1) {
        $return = 1;
        echo "<form action='$link";
        if ($GLOBALS['pageID']) echo "?pageID=".(int) $GLOBALS['pageID'];
        echo "' method='POST' enctype='multipart/form-data'>
			<table width=100% class=main cellspacing=0>
			<tr><th colspan=3>" . _('Основная информация') . "</th></tr>";
        debug_yes("array",$field);
        reset($field);
        reset($name);
        reset($type);
        $i=0;

        uksort($name, 'people_fields_order');
        $name['Status'] = "Status";

        foreach ($name as $key => $value) {
            $Ftype = $type[$key];
            $Flen= 40;
            if ($Flen < 4) {
                $fieldsize = $maxlength = 4;
            } else {
                $fieldsize = (($Flen > 40) ? 40 : $Flen);
                $maxlength = $Flen;
            }
            if (($value == 'EMail') && !NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
                continue;
            }
            if ($value == 'blocked') {
                continue;
            }
            if (!USE_CMS_INTEGRATION) {
	            if ($value == 'Access_Level') {
	                continue;
                }
            }
			if ($value == 'Status' && $MID) {
			    continue;
			}
            $color=($i%2) ? "class='odd'"  : "class='even'"; //set row color
            $i++;
            
            if (isset($_SESSION['people_edit']) && count($_SESSION['people_edit'])) {
                $field[$value] = $_SESSION['people_edit'][$value];
            }
            
                echo "
						<tr>
							<td width='22%' valign='top'>
								{$accepted_fieldname_aliases[$value]} ".(in_array($value, array("LastName", "Login", "FirstName", "EMail")) ? getIcon("*") : "")."
							</td>
							<td $color>";
				if($value == "Password") {
                    echo "
                        <input type='password' id='pass_input' name='$value' maxlength='".$maxlength."' size='".$fieldsize."' disabled>
                        <br>
                        <input type='checkbox' onChange='fillIfDisabled();' id='change_pass' onClick=\"var s = getElementById('pass_input'); if(s.disabled) s.disabled = false; else s.disabled = true; fillIfDisabled(); \"> 
                        <label for='change_pass'>"._("Изменить пароль")."</label>&nbsp;&nbsp;" . 
                        $GLOBALS['tooltip']->display('people_edit_password') . 
                        "<script type='text/javascript'>
                            fillIfDisabled = function () {
                                var obj = $('#pass_input');
                                if (obj.attr('disabled')) {
                                    obj.val('012345678901234567890');
                                }else {
                                    obj.val('');
                            }
                            };
                            $(fillIfDisabled);
                        </script>";
                    
				}elseif($value == 'Status') {
				    if (!$MID) {
				        $roles = array(_("без роли"), _("слушатель"), _("преподаватель"), _("уч. администратор"), _("администратор"));
                        $statusSelectBox = "<select name='Status' style='width: 275px'>";
                        foreach ($roles as $k=>$v) {
                            $statusSelectBox .= "<option value='$k'>$v</option>";
                        }
                        echo $statusSelectBox .= "</select>";
                    }                    
                } elseif($value=='Access_Level') {
                    echo "<select name='$value'>";
                    for($i=1;$i<=10;$i++) {
                        echo "<option value='$i'";
                        if ($i==$field[$value]) echo "selected";
                        echo ">$i</option>";
                    }
                    echo "</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('people_edit_access_level');
                } elseif ($value == 'blocked') {
                    echo $field[$value] == 0 ? _('Активен') : _('Заблокирован');
                    /*echo "<select name='$value'><option value='0' ";
                    if ($field[$value] == 0) echo "selected";
                    echo ">"._('Активен')." </option><option value='1' ";
                    if ($field[$value] == 1) echo "selected";
                    echo ">"._('Заблокирован')." </option></select>";*/
                } else {
                    $type_input = "text";
                    echo "<INPUT type='$type_input' name='$value' value=\"".$field[$value]."\" maxlength='".$maxlength."' size='".$fieldsize."'".($value=="MID"? "readonly" : "").">";
                }
	            echo "</td>";

                if ($value == "MID") {
			        $query_photo = "SELECT * FROM filefoto WHERE mid = ".$MID;
			        $result_photo = sql($query_photo,"errrGFRE");
			        $photography = getPhoto($MID, 1, 300, 300, 1);
			        echo "<td rowspan=99 valign=top><table>
								<tr>
									<td>$photography</td>
								</tr>
								<tr>
									<td>
										<input type='file' id='photo_input' name='photo' disabled='disabled' size='7' /><br><input type='checkbox' id='photo_input_checkbox' onClick='javascript: changeDisabledPhoto();'/> <label for='photo_input_checkbox'>"._("Изменить фото")."</label>
									</td>
								</tr>
							</table>
						<script language='javascript'>
							function changeDisabledPhoto() {
								var s = document.getElementById('photo_input');
								s.disabled = !s.disabled;
							}
						</script></td>";
                }

			echo "</tr>";
        }
        echo '</table><br>';

        unset($_SESSION['people_edit']);
        
        $reg_block = explode(";", REGISTRATION_FORM);
        if (strlen(REGISTRATION_FORM) && (count($reg_block) > 0)) {
            echo "<table width=100% class=main cellspacing=0><th colspan=2>" . _('Дополнительная информация') . "</th>";
            foreach($reg_block as $sub_value) {
                echo "<tr>";
                echo "	<td width='22%' >".get_reg_block_title($sub_value)."</td><td>".edit_metadata(read_metadata($field['Information'],$sub_value))."</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<br />";
        }
        echo "<table border='0' align='right'>
                <tr>
                    <td>".okbutton()."</td>
                    <td>".button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath']."admin/people.php")."</td>
                </tr>
              </table>
        ";        
        echo "</form>";
    }
    return $return;
} //function generate_table($link,$res,$st=0)








function delete_from_teachers($MID)
{
    return sql_query(22,$MID);
}

function delete_from_people($MID)
{
    if ($MID==$GLOBALS['s']['mid']) {
        $GLOBALS['controller']->setMessage(_("Пользователь не может удалить самого себя!"));
        return;
    }

    $return=0;

    $qq = "DELETE FROM money WHERE mid={$MID}";
    $rr = sql($qq, "err234gr");
    $qq = "DELETE FROM groupuser WHERE mid={$MID}";
    $rr = sql($qq, "err234gr");
    $qq = "DELETE FROM tracks2mid WHERE mid={$MID}";
    $rr = sql($qq, "err234gr");
    $qq = "DELETE FROM permission2mid WHERE mid={$MID}";
    $rr = sql($qq, "err234gr");
    $qq = "DELETE FROM Students WHERE MID={$MID}";
    $rr = sql($qq, "err234gr");
    $qq = "DELETE FROM Teachers WHERE MID={$MID}";
    $rr = sql($qq, "erry654");
    $qq = "DELETE FROM deans WHERE MID={$MID}";
    $rr = sql($qq,"err34fs");
    $qq = "DELETE FROM admins WHERE MID={$MID}";
    $rr = sql($qq, "errt45");
    $qq = "DELETE FROM scheduleID WHERE MID={$MID}";
    $rr = sql($qq, "errt45");

    $res=sql_query(21,$MID);
    //$return=mysql_affected_rows();
    //debug_yes("Num Rows People",$return);
    //return $return;
}

function block_people($MIDS, $MESSAGE) {
    $arr = array(0);
    foreach ($MIDS as $mid) {
        if ($mid!=$GLOBALS['s']['mid']) {
            $arr[] = intval($mid);
        }
    }
    if (count($arr)<2 && in_array($GLOBALS['s']['mid'], $MIDS)) {
        $GLOBALS['controller']->setMessage(_('Невозможно заблокировать свою учётную запись'));
    }
    $query = "UPDATE People SET blocked = '1', block_message = " . $GLOBALS['adodb']->Quote($MESSAGE) . " WHERE MID IN (" . implode(", ", $arr) . ")";
    return sql($query);
}

function unblock_people($MIDS) {
    $arr = array(0);
    foreach ($MIDS as $mid) {
        $arr[] = intval($mid);
    }
    $query = "UPDATE People SET blocked = '0', block_message = '' WHERE MID IN (" . implode(", ", $arr) . ")";
    return sql($query);
}

function delete_from_students($MID)
{
    return sql_query(24,$MID);
}

function delete_from_abiturs($MID)
{
    $return=0;
    $res=sql_query(25,$MID);
    //    $return=sqlget($res);
    $return=mysql_affected_rows();
    $return=delete_from_people($MID);
    debug_yes("Num Rows",$return);
    return $return;
}

function delete_from_arhiv($MID)
{
    return sql_query(26,$MID);
}

function people_fields_order($item1, $item2){
	$index1 = array_search($item1, $GLOBALS['accepted_fieldnames']);
	$index2 = array_search($item2, $GLOBALS['accepted_fieldnames']);
	return ($index1 < $index2) ? -1 : 1;
}

?>