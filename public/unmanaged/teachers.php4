<? // require_once('phplib.php4');
require_once('1.php');

//$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

$s[user][tssort]=(isset($s[user][tssort])) ? $s[user][tssort] : 2;
$s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;
$s[user][tfull]=(isset($s[user][tfull])) ? $s[user][tfull] : 1;
$tfull=2;
if ($tfull) $s[user][tfull]=$tfull;
$tfull=$s[user][tfull];
$tssort=(isset($_GET['tssort'])) ? intval($_GET['tssort']) : "";
if ($tssort==$s[user][tssort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
if ($tssort) $s[user][tssort]=$tssort;

function tSort() {
        global $s;
        $ret="Courses.Title ";
        if (2==$s[user][tssort]) $ret=" LastName ";
        if (2==$s[user][corder]) $ret.=" DESC";
        else $ret.=" ASC";
        $ret=" ORDER BY ".$ret;
        return $ret;
}

function tImgSort($num) {
        global $s,$sitepath;
        $imgpath="";
        if ($s[user][tssort]==$num) $imgpath="<img src='".$sitepath."images/sort_".((2==$s[user][corder]) ? "up" : "down").".gif' border=0>";
        return  $imgpath;
}

$CID=(isset($_GET['CID'])) ? $_GET['CID'] : 0;

istest();
if (!$dean) login_error();

echo show_tb();
if (isset($HTTP_POST_VARS['countstud']))
{
        for($i=1; $i<=$countstud; $i++) {
                $cid="c".$i;
                $mid="m".$i;
                $tt="t".$i;
                $mes="mes".$i;
                if (!empty($$tt)) {
                        $sql = "SELECT SID FROM claimants WHERE MID='".(int) $$mid."' AND CID='".(int) $$cid."' AND Teacher='1'";
                        $res = sql($sql);
                        if (!sqlrows($res)) {
                        $query = "INSERT INTO $claimtable (MID,CID,Teacher) VALUES (".$$mid.",".$$cid.",1)";
                        @sql($query, "eer334");
                        $query = "delete from $teacherstable where MID=".$$mid." AND CID=".$$cid;
                        $result=sql($query,"err545");
                        if ($result) mailToteach("remteach",$$mid,$$cid);
                        }
                        if (!is_teacher((int) $$mid)) {
                        $sql = "SELECT permission_groups.pmid 
                                FROM permission_groups 
                                INNER JOIN permission2mid ON (permission2mid.pmid=permission_groups.pmid)
                                WHERE permission2mid.mid='".(int) $$mid."' AND permission_groups.type='teacher'";
                        $res = sql($sql);
                        while($row = sqlget($res)) {
                            sql("DELETE FROM permission2mid WHERE mid='".(int) $$mid."' AND pmid='".(int) $row['pmid']."'");
                        }
                        }
                        
                }

                if (!empty($$mes)) {
                        mailToteach("elmes",$$mid,$$cid,$mail_mes);
                }
        }

}

if (isset($HTTP_POST_VARS['countabitur']))
{
        for($i=1; $i<=$countabitur; $i++)
        {       $cid="c".$i;
        $mid="m".$i;
        $del="del".$i;
        $tt="t".$i;
        if (!empty($$tt))
        {
                $strPass = randString(7);
                $q = "UPDATE {$peopletable} SET Password=PASSWORD('{$strPass}') WHERE mid='{$$mid}'";
                $r=sql($q, "err3434");
                $arrMore['Password'] = $strPass;

                $query = "INSERT INTO $teacherstable (MID,CID) VALUES (".$$mid.",".$$cid.")";
                $result=sql($query, "err343");
                add_teacher_to_default_groups($$mid);
                $query = "DELETE FROM $claimtable WHERE MID=".$$mid." AND CID=".$$cid;
                @sql($query, "err234");
                if ($result) mailToteach("toteach",$$mid,$$cid,$arrMore);
        }
        if (!empty($$del))
        {
                mailToteach("del",$$mid,$$cid,$arrMore);
                $query = "DELETE FROM $claimtable WHERE MID=".$$mid." AND CID=".$$cid;
                $result=sql($query, "errgfdgdfgZ");
                $query = "DELETE FROM $teacherstable WHERE MID=".$$mid." AND CID=".$$cid;                
                $result=sql($query, "err234");
                
        }

        }

}

if (isset($HTTP_POST_VARS['alltoteach']))
{
        for($i=1; $i<=$alltoteach; $i++)
        {       $cid="c".$i;
        $mid="m".$i;
        $tt="t".$i;
        $dd="d".$i;
        if (!empty($$tt))
        {
                $query = "INSERT INTO $teacherstable (MID,CID) VALUES (".$$mid.",".$$cid.")";
                $result=sql($query, "errgfdg334");
                add_teacher_to_default_groups($$mid);
                $tmp['Password'] = _("не изменился, используйте старый пароль")." (not updated)";
                mailToteach("toteach",$$mid,$$cid, $tmp);
                $r = sql("DELETE FROM claimants WHERE MID='{$$mid}' AND CID='{$$cid}'","er234gf");
        }
        if (!empty($$dd)) {
                $r = sql("DELETE FROM claimants WHERE MID='{$$mid}' AND CID='{$$cid}'","err34jff3");
        }

        }
}

if (isset($_POST['hid_act'])) {
        switch ($_POST['hid_act']) {
                case "add_teacher":

                $error = "";
                if ($strLogin=validateEmail($_POST['txt_add_email'])) {
                        $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'","err354fd");
                        if (sqlrows($r)) {
                                $strLogin .= "_".randString(3);
                                $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'","err343fd");
                                if (sqlrows($r)) {
                                        $error .= _("Невозможно создать преподавателя: попробуйте другой")." e-mail<br>\n";
                                }
                        }
                } else {
                        $error.=_("Неверный")." e-mail.<br>\n";
                }
                if (!strlen($error)) {
                        $Password = randString(7);
                        $r = sql("INSERT INTO People (Login, Password, EMail) values
                                ('{$strLogin}', PASSWORD('{$Password}'), '{$_POST['txt_add_email']}')","err234dsfe3");
                        $idPeople = sqllast();
                        $r = sql("INSERT INTO Teachers (MID, CID) values ('{$idPeople}', '{$_POST['sel_add_to_course']}')","err324fsdf");
                        if (isset($_POST['ch_mail'])) {
                                mailToteach("forced_new", $idPeople, $_POST['sel_add_to_course']);
                        }
                        add_teacher_to_default_groups($idPeople);
                }
                break;
                case "add_teacher_from_smb":
                $CID = $_POST['sel_add_to_course'];
                if (isset($CID)) {
                		$q = "SELECT mid FROM Teachers WHERE MID='{$_POST['add']}' AND CID='{$CID}'";
                		$r = sql($q);
                		if (!sqlrows($r)) {
	                        $q = "INSERT INTO Teachers (MID, CID) values ('{$_POST['add']}', '{$CID}')";
	                        $r = sql($q,"err234");
	                        if(isset($_POST['ch_mail'])) {
	                                mailToteach("forced", $_POST['add'], $CID);
	                        }
	                        add_teacher_to_default_groups($_POST['add']);
                		}
                		sql("DELETE FROM claimants WHERE MID='".(int) $_POST['add']."' AND CID='".(int) $CID."' AND Teacher='1'");
                }
                break;
                default:
                break;
        }
}

?>
               <table valign=top border=0  cellspacing="0" cellpadding="0">
<tr><td rowspan=3 width=10 ><img src="images/spacer.gif"></td><td height=10 width=100% colspan=3><img src="images/spacer.gif"></td></tr>

<tr><TD CLASS="schedule" style="padding: 1" align=left><span class=shedtitle>&nbsp;&nbsp;<?=_("курс")?></span></TD>
         <td ></td>
<td width=9 ><select id=ChCourse name=ChCourse onChange="navigate('teachers.php4?CID='+this.value);" class=lineinput style="border: inset 1px; width: 350px;">
                <option value=''><?=_("Все курсы")?></option>
                <? $resultt=sql("select CID,Title from $coursestable ORDER BY Title","err234ghf");                
                while ($course=sqlget($resultt)){
                        if (!$courseFilter->is_filtered($course['CID'])) continue;
                        echo "<option value='".$course['CID']."'";
                        @print ($course['CID']==$CID)? " selected":"";
                        echo ">".$course['Title']."</option>";
                        if ($GLOBALS['controller']->enabled) $controller_options[$course['CID']]=$course['Title'];                        
                } ?>
               </select></TD></tr></table><br>

<?
if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->addFilter(_("курс"), 'CID',$controller_options,$CID);

echo ph(_("Преподаватели"));
$GLOBALS['controller']->captureFromOb("m120401");

if (!empty($CID))
$query = "select * from $peopletable, $teacherstable, $coursestable where $teacherstable.CID=$CID AND $coursestable.CID=$teacherstable.CID AND $teacherstable.MID=$peopletable.MID ".tSort();
//                $query = "select * from $peopletable, $teacherstable where $teacherstable.CID=$CID AND $teacherstable.MID=$peopletable.MID";
else
$query = "select * from $peopletable, $teacherstable, $coursestable where $teacherstable.MID=$peopletable.MID AND $coursestable.CID=$teacherstable.CID ".tSort();
//                $query = "select * from $peopletable, $teacherstable where $teacherstable.MID=$peopletable.MID ORDER BY $teacherstable.CID";
$result=sql($query,"errrt34");
if (sqlrows($result)==0) {
        echo "<table border=0 cellspacing=1>\n";
        echo "<tr><td>&nbsp;</td><td class=schedule>&nbsp;"._("Нет преподавателей")."</td></tr></table>";
}
else {
        echo "<table border=0 align=center width=80%><tr><td><form method=post action='teachers.php4?CID=$CID' name='teach1'>";
        echo "<input type=hidden name=countstud value=".sqlrows($result).">";
        echo "<table border=0 cellspacing=1 class=br width=100%>\n";
        echo "<tr><th>&nbsp;N</th><th>&nbsp;<a href=\"".$sitepath."teachers.php4?".$sess."tssort=1&CID=".$CID."\">"._("курс")."</a> ".tImgSort(1)."</th>
                           <th>&nbsp;<a href=\"".$sitepath."teachers.php4?".$sess."tssort=2&CID=".$CID."\">"._("ФИО")."</a>".tImgSort(2)."</th><th>&nbsp;"._("уволить")."</th>
                           <th>&nbsp;"._("послать сообщение")."</th></tr>";
        $i=0;
        while ($row=sqlget($result)) {
                if (!$courseFilter->is_filtered($row['CID'])) continue;
                
                    $courseTitle=getField($coursestable,"Title","CID",$row['CID']);
                    $courseCreateby=getField($coursestable,"createby","CID",$row['CID']);
                    echo "<tr bgcolor=#FFFFFF><td class=schedule align=right>".++$i."</td><td width=152 class=schedule>&nbsp;&nbsp;$courseTitle</a></td>\n";
                    echo "<td width=152 class=schedule>";
                    echo ($row['EMail']==$courseCreateby) ? "<b>" : "";
                    echo "<a href='reg.php4?showMID=".$row['MID']."'>".$row['LastName']." ".$row['FirstName']." ". $row['Patronymic'] . "</a>";
                    echo ($row['EMail']==$courseCreateby) ? "</b>" : "";
                    echo "</td>\n";
                    echo "<td class=schedule>";
                    echo "<input type=hidden name='c$i' value='".$row['CID']."'>";
                    echo "<input type=hidden name='m$i' value='".$row['MID']."'>";
                    echo "<input type=checkbox name='t$i'></td>\n";
                    echo "<td align='center'><input type=checkbox name='mes$i'></td></tr>\n";
        }
        echo "";
        echo "";
        echo "<tr><td bgcolor='#E6DED1' colspan=5><textarea style=\"width:100%; border:1px solid black\" name='mail_mes' rows=5>"._("Ваше сообщение для преподавателей")."</textarea></td></tr>";
        echo "</table><br>";
        if ($GLOBALS['controller']->enabled) echo okbutton();
        else
        echo "<input type=image name='send' onMouseOver=\"this.src='images/send_.gif';\" onMouseOut=\"this.src='images/send.gif';\" src='images/send.gif' align='right' alt='"._("Зарегистрировать")."' border=0 vspace=10>";
        echo "</form></td></tr></table>";
}
$GLOBALS['controller']->captureStop("m120401");
echo ph(_("Добавить учетную запись преподавателя"));
if (strlen($error)) {
        echo "<p align='center'><font color=red><b>{$error}</b></font></center></p>";
        $GLOBALS['controller']->setMessage(strip_tags(str_replace("\n",'',$error)));
}
$GLOBALS['controller']->captureFromOb("m120402");
echo "<table border=0 align=center width=80%><tr><td><form method=post action='teachers.php4?CID=$CID' name='form_add_teacher'>
       <input type=hidden name=countstud value=".sqlrows($result).">
       <table width=100% class=main cellspacing=0>\n
        <!--<tr>
         <th align='center' class='th-center'>&nbsp;"._("курс")."</th>
         <th align='center' class='th-center'>&nbsp;e-mail</th>
         <th align='center' class='th-center'>&nbsp;"._("послать уведомление")."</th>
        </tr>-->
        <tr>
         <td align='left'>
          "._("Курс:")." </td><td>
          <select name=sel_add_to_course style='width:50%'>";
$resultt=sql("select CID,Title from $coursestable ORDER BY Title","err234gf");
while ($course=sqlget($resultt)){
        if (!$courseFilter->is_filtered($course['CID'])) continue;
        echo "<option value='".$course['CID']."'>".$course['Title']."</option>";
}
echo "   </td>
        </tr>
<tr> 
         <td align='left'>
          <input type='hidden' name='hid_act' value='add_teacher_from_smb' />
          "._("Пользователь:")."  
          </td><td>
          <select name='add' id='add'>".
//peopleSelect_('',0, 'Students', true)."
peopleSelect_2(array("Teachers", "deans", "admins"), 0)."
          </select>
          <!--<input name='ch_mail' type='checkbox' id='ch_mail' value='checkbox' checked>
          <input name='hid_act' type='hidden' id='hid_act' value='add_teacher'>-->
         </td>
        </tr>
        <tr>
         <td colspan=2>
          <input type='checkbox' name='ch_mail' id='ch_mail' checked /> "._("Послать уведомление")."
                ".okbutton()."
         </td>
        </tr>
       </table></form>";
/*
echo"
<table align='center' border='0' cellspacing='1' cellpadding='5' width='100%' style='font-size:13px'>
<tr><th>Добавить из уже имеющихся учетных записей</th></tr>
<tr><td class='questt' align=right>
<form action='teachers.php4?CID={$CID}' method=post>
<select name='add' style='width:100%'>";

echo peopleSelect('',0, 'deans', false);

echo "</select>
<input name='hid_act' type='hidden' id='hid_act' value='add_teacher_from_smb'>
</td>
</tr>
<tr><td align='right'>";
echo okbutton();
echo "</form>
</td></tr>
</table>"; */



echo "</td></tr></table>";
$GLOBALS['controller']->captureStop("m120402");
echo ph(_("Претенденты"));
$GLOBALS['controller']->captureFromOb("m120403");
if ($tfull>1) {
        if((isset($_GET['CID']))&&(!empty($_GET['CID']))) {
                $add_condition = " $coursestable.CID = ".$_GET['CID']." AND";
        }
        else {
                $add_condition = "";
        }
        $query = "select * from $peopletable, $claimtable, $coursestable  where $claimtable.Teacher=1 AND $add_condition $claimtable.MID=$peopletable.MID AND $coursestable.CID=$claimtable.CID ".tSort();
        //echo $query."<br />";
        $result=sql($query, "err543gn");

        $query = "select * from $peopletable, $teacherstable, $coursestable where $teacherstable.MID=$peopletable.MID AND $coursestable.CID=$teacherstable.CID ".tSort();
        $result2=sql($query,"err234gf");

        $num=sqlrows($result)+sqlrows($result2);

        echo "<form method=post action='teachers.php4?CID=$CID'>";
        echo "<input type=hidden name=alltoteach value=".$num.">";
        echo "<table width=80% class=main cellspacing=0>\n";
        echo "<tr><th>&nbsp;N</th><th>&nbsp;<a href=\"".$sitepath."teachers.php4?".$sess."tssort=1&CID=".$CID."\">"._("курс")."</a>".tImgSort(1)."</th>
                        <th>&nbsp;<a href=\"".$sitepath."teachers.php4?".$sess."tssort=2&CID=".$CID."\">"._("ФИО")."</a>".tImgSort(2)."</th>
                        <th>&nbsp;"._("назначить")."</th>
                        <th>&nbsp;"._("удалить")."</th>
                                </tr>
                        <!--<tr><td colspan=5><b>"._("Преподаватели-претенденты")."</b></td></tr>-->
                        ";

        $i=0;
        while ($row=sqlget($result)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
                $courseTitle=getField($coursestable,"Title","CID",$row['CID']);
                $courseCreateby=getField($coursestable,"createby","CID",$row['CID']);
                echo "<tr bgcolor=#FFFFFF><td class=schedule align=right>".++$i."</td><td width=152 class=schedule>&nbsp;&nbsp;$courseTitle</a></td>\n";
                echo "<td width=152 class=schedule>";
                echo ($row['EMail']==$courseCreateby) ? "<b>" : "";
                echo "<a href='reg.php4?showMID=".$row['MID']."'>".$row['LastName']." ".$row['FirstName']." " . $row['Patronymic'] . "</a>";
                echo ($row['EMail']==$courseCreateby) ? "</b>" : "";
                echo "</td>\n";
                echo "<td class=schedule>";
                echo "<input type=hidden name='c$i' value='".$row['CID']."'>";
                echo "<input type=hidden name='m$i' value='".$row['MID']."'>";
                echo "<input type=checkbox name='t$i'></td>\n";
                echo "<td class=schedule><input type=checkbox name='d$i'></td>";
        }

        /*echo "<tr><td colspan=5><b>Преподаватели</b></td></tr>";
        while ($row=sqlget($result2)) {
        $courseTitle=getField($coursestable,"Title","CID",$row['CID']);
        $courseCreateby=getField($coursestable,"createby","CID",$row['CID']);
        echo "<tr bgcolor=#FFFFFF><td class=schedule align=right>".++$i."</td><td width=152 class=schedule>&nbsp;&nbsp;$courseTitle</a></td>\n";
        echo "<td width=152 class=schedule>";
        echo ($row['EMail']==$courseCreateby) ? "<b>" : "";
        echo "<a href='reg.php4?showMID=".$row['MID']."'>".$row['LastName']." ".$row['FirstName']."</a>";
        echo ($row['EMail']==$courseCreateby) ? "</b>" : "";
        echo "</td>\n";
        echo "<td class=schedule>";
        echo "<input type=hidden name='c$i' value='$CID'>";
        echo "<input type=hidden name='m$i' value='".$row['MID']."'>";
        echo "<input type=checkbox name='t$i'></td>\n";
        echo "<td class=schedule>&nbsp;</td>";
        }*/
        echo "<tr><td colspan=5>";
        if ($GLOBALS['controller']->enabled) echo okbutton();
        else
        echo "<input type=image name=\'send' onMouseOver=\"this.src='images/send_.gif';\" onMouseOut=\"this.src='images/send.gif';\" src='images/send.gif' align='right' alt='"._("Зарегистрировать")."' border=0>";
        echo "</td></tr>";
        echo "</table>";
        if ($GLOBALS['controller']->enabled) echo "</form>";
}
$GLOBALS['controller']->captureStop("m120403");
?>
<?//  include('bottom.php4');
s_timeprint();
echo show_tb();


?>