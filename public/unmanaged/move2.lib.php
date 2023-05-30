<?php

require_once("schedule.lib.php");

function getSort($add) {
    global $s;

    $ret="Courses.Title ";
    if (2==$s[user][assort] && $add=="Students.SID") $ret="Students.cgid ";
    if (3==$s[user][assort]) $ret=" lname ";

    //      $ret.=$add;

    if (2==$s[user][corder]) $ret.=" DESC";
    else $ret.=" ASC";

    $ret=" ORDER BY ".$ret;
    return $ret;
}

function abiturList($CID) {
    global $alltr,$s;
    $sel="";
    $n=1;
    $all=array();
    $sql="SELECT DISTINCT claimants.CID as cid, People.Login, People.MID as mid, People.Patronymic, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM claimants, People, Courses
          WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'
          ";
    if ($CID) $sql.=" AND claimants.CID='".$CID."'";
    else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";
    //$sql.=" ORDER BY Courses.CID, claimants.SID ASC";
    //$sql.=getSort("claimants.SID");
    // echo $sql;
    if ($CID) {

        $res=sql($sql.' ORDER BY People.LastName',"abiturEr01");
    }
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        //      $all['move']   ="<input type=\"checkbox\" name=\"abtost['".$n."']\" value=\"tost\">";
        //      $all['remove'] ="<input type=\"checkbox\" name=\"abdelete['".$n."']\" value=\"delete\">";
        //      $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        //      $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
        //       $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid[]\" value=\"{$row['mid']}\">";
        //       $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    return $sel;
}

function abiturList_Chain($CID) {
    global $s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");

//    dirty hack begin
	$search = <<<E0D
   <td valign="top" width="15%" align="center" bgcolor="#FFFFFF">[AB-days]</td>
   <td valign="top" width="15%" align="center" bgcolor="#FFFFFF">[AB-rest]</td>
E0D;
	$alltr = str_replace($search, '', $alltr);
//    dirty hack end

    $sel="";
    $n=1;
    $all=array();
    $sql="SELECT DISTINCT claimants.CID as cid, People.Login, People.Patronymic, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM claimants, People, Courses
          WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";
//	  чтобы сделать обязательным фильтр на странице согласования; иначе - очень длинный список студентов.
//    if ($CID)
	$sql.=" AND claimants.CID='".$CID."'";
    //$sql.=getSort("claimants.SID");
    $res=sql($sql.' ORDER BY People.LastName',"abiturEr01");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['chainFilter']->is_filtered($row['cid'],$row['mid'])
            || !$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];

        $crntStatus = false;
        if (is_array($chainInfo = CChainLog::get_as_array($row['cid'],$row['mid']))) {
            foreach ($chainInfo as $crntAgreement) {
                if ($crntAgreement['subject'] == $s['mid']) {
                    $crntStatus = '<font color="green">'._("согласовано").'</font>';
                }
            }
        }
        $crntStatus = $crntStatus?$crntStatus:'<font color="red">'._("ожидание").'</font>';

        $all['status'] ="<a href='[PATH]orders.php?search=*&MID={$row['mid']}' target='_blank'>$crntStatus</a>";
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    return $sel;
}

function abiturList_mid($cid, $mids) {
    global /*$alltr,*/ $s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
    $sel="";
    $n=1;
    $all=array();
    $sql="SELECT DISTINCT structure_of_organ.name, claimants.CID as cid, People.Login, People.MID as mid,
          People.Patronymic, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM claimants, People LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID), Courses
          WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";

    if ($cid) $sql.=" AND claimants.CID='".$cid."'";
    else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";

    $sql.=" AND People.MID IN (".implode(", ", $mids).")";

    $res=sql($sql.' ORDER BY People.LastName',"abiturEr01");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
        else
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    $GLOBALS['n_list'] = $n;
    return $sel;
}

function abiturList_not_in_org($cid, $mids) {
    global $alltr, $s;
    $sel="";
    $n=$GLOBALS['n_list'];
    $all=array();
    $sql="SELECT  claimants.CID as cid, People.Patronymic, People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
    FROM claimants, People LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID), Courses
    WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";

    if ($cid) $sql.=" AND claimants.CID='".$cid."'";
    else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";

    $sql.=" AND People.MID NOT IN (".implode(", ", $mids).")
            AND structure_of_organ.mid IS NULL";

    $res=sql($sql.' ORDER BY People.LastName',"abiturEr01");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    return $sel;
}

function studListOther($CID) {
    // д®а¬ЁагҐв бЇЁб®Є ўбҐе бвг¤Ґ­в®ў ¤«п § зЁб«Ґ­Ёп
    global $alltr,$s;
    $alltr=loadtmpl("abitur-ottr.html");
    $sel="";
    $n=1;
    $all=array();

    $sql="SELECT People.MID as mid, People.FirstName as fname, People.LastName as lname, People.Patronymic, Courses.Title as title, Students.CID as cid, cgname.name as cgr FROM People, Courses, Students LEFT JOIN cgname ON cgname.cgid=Students.cgid WHERE Courses.CID=Students.CID AND Students.MID=People.MID";
    $sql.=" AND Students.CID<>'".$CID."'";
    //            else $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";
    //         $sql.=" ORDER BY Courses.CID, Students.SID ASC";
    //$sql.=getSort("Students.SID");

    $res=sql($sql.' ORDER BY People.LastName',"abiturEr02");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['cgr'] =$row['cgr'];
        $all['move']   ="<input type=\"checkbox\" name=\"tost['".$n."']\" value=\"tost\">";
        $all['remove'] ="";
        $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$CID."\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }

    $sql="SELECT  claimants.CID as cid, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title FROM claimants, People, Courses WHERE Courses.CID=claimants.CID AND claimants.MID=People.MID AND claimants.Teacher='0'";
    $sql.=" AND claimants.CID<>'".$CID."'";
    //            else $sql.=" AND claimants.CID IN (".implode(",",$s[tkurs]).")";*/
    //$sql.=" ORDER BY Courses.CID, claimants.SID ASC";
    //$sql.=getSort("claimants.SID");
    // echo $sql;
    $res=sql($sql.' ORDER BY People.LastName',"abiturEr01");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        $all['name']="<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['move']   ="<input type=\"checkbox\" name=\"tost['".$n."']\" value=\"tost\">";
        $all['remove'] ="";
        $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$CID."\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }


    return $sel;
}

//   function studSearchList($cid ) {
// поиск по условиям


//  }


function studList($CID, $gr=-1) {

    global $alltr,$s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
//    dirty hack begin
	$search = <<<E0D
   <td valign="top" width="40%" align="center" bgcolor="#FFFFFF">[AB-status]</td>
E0D;
	$alltr = str_replace($search, '', $alltr);
	//    dirty hack end


    $sel="";
    $n=1;
    $all=array();
//    if ($gr != -1) {
        switch ($gr[0]) {
            case "d":
            $cgid = (int)substr($gr, 1);
            $gr_name = "cgname";
            break;
            case "g":
            $gid = (int)substr($gr, 1);
            $gr_name = "groupname";
            break;
            default:
            $gr_name = "groupname";
        }

//            SELECT DISTINCT People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title, Students.CID as cid,  cgname.name as cgname, groupname.name as groupname
//                LEFT JOIN cgname ON cgname.cgid=Students.cgid,
//                LEFT JOIN groupuser ON groupuser.mid=People.MID
//                LEFT JOIN groupname ON groupuser.gid=groupname.gid
        $tmstamp = time();
		$sql="
            SELECT DISTINCT Students.CID as cid, {$tmstamp} - UNIX_TIMESTAMP(Students.time_registered) as seconds_from_registration, People.Login, People.MID as mid, People.Patronymic, People.FirstName as fname, People.LastName as lname, Courses.Title as title, Courses.longtime
            FROM
                Courses, Students, People
            WHERE
                Courses.CID=Students.CID AND Students.MID=People.MID";
        if ($CID)
        $sql.=" AND Students.CID='".$CID."'";
        //else
        //$sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";

        /*
        if ($cgid) {
            $sql .= " AND cgname.cgid='{$cgid}'";
        }
        if ($gid) {
            $sql .= " AND groupuser.gid='{$gid}'";
        }
        */

//      $sql.=getSort("Students.SID");
        $sql.= " ORDER BY People.LastName";

        //echo "<pre>".$sql;
        if ($CID)
        $res=sql($sql,"abiturEr02");
        $ii=1;
        while ($row=sqlget($res)) {
            if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
            $all['num']=$n;
            $ii=intval($row['mid']);
            $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");

            {

                if ($GLOBALS['s']['perm']!=4)
                $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
                else
                $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
            }

            $all['ctitle'] =$row['title'];
            $all['cgr'] =$row[$gr_name];
            //$all['cgr'] =$row['groupname'].$row['cgname'];
            $all['login'] =$row['Login'];
            //               $all['move']   ="<input type=\"checkbox\" name=\"sttoab['".$n."']\" value=\"toab\">";
            //               $all['remove'] ="<input type=\"checkbox\" name=\"sttogr['".$n."']\" value=\"togr\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
            //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
            //               $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid[]\" value=\"{$row['mid']}\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
            $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";

            $all['days'] = sprintf(_("%d-й"), max(floor($row['seconds_from_registration']/86400 + 1), 1));
            $all['rest'] = (($num = $row['longtime'] - $all['days']) >= 0) ? ($num==0?_('последний день обучения'):$num) : _('время обучения по курсу закончилось');

            $sel.=words_parse($alltr,$all,"AB-");
            $n++;
        }

/*    }
    else {
        $sql="
            SELECT People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title, Students.CID as cid
            FROM
                Courses, Students, People
            WHERE
                Courses.CID=Students.CID AND Students.MID=People.MID";
        if ($CID)
        $sql.=" AND Students.CID='".$CID."'";
        else
        $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";

//      $sql.=getSort("Students.SID");
        $sql.= " ORDER BY People.Login";

        //echo "<pre>".$sql;

        $res=sql($sql,"abiturEr02");
        $ii=1;
        while ($row=sqlget($res)) {
            $all['num']=$n;
            $ii=intval($row['mid']);
            $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");
            {
                $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">".$row['lname']." ".$row['fname']."</a>";
            }

            $all['ctitle'] =$row['title'];
            $all['cgr'] =$row[$gr_name];
            //$all['cgr'] =$row['groupname'].$row['cgname'];
            $all['login'] =$row['Login'];
            //               $all['move']   ="<input type=\"checkbox\" name=\"sttoab['".$n."']\" value=\"toab\">";
            //               $all['remove'] ="<input type=\"checkbox\" name=\"sttogr['".$n."']\" value=\"togr\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
            //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
            //               $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid[]\" value=\"{$row['mid']}\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
            $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
            $sel.=words_parse($alltr,$all,"AB-");
            $n++;
        }

    }   */

    return $sel;
}

function studList_Chain($CID, $gr=-1) {

    global $s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
    //dirty hack
    $alltr = str_replace('<td valign="top" width="40%" align="center" bgcolor="#FFFFFF">[AB-status]</td>','',$alltr);
    //dirty hack

    $sel="";
    $n=1;
    $all=array();
        switch ($gr[0]) {
            case "d":
            $cgid = (int)substr($gr, 1);
            $gr_name = "cgname";
            break;
            case "g":
            $gid = (int)substr($gr, 1);
            $gr_name = "groupname";
            break;
            default:
            $gr_name = "groupname";
        }
        $tmstamp = time();
        $sql="
            SELECT DISTINCT Students.CID as cid, 
                            People.Login, 
                            People.MID as mid, 
                            People.Patronymic, 
                            People.FirstName as fname, 
                            People.LastName as lname, 
                            Courses.Title as title, 
                            {$tmstamp} - UNIX_TIMESTAMP(Students.time_registered) as seconds_from_registration, 
                            Courses.longtime
            FROM
                Courses, 
                Students, 
                People
            WHERE
                Courses.CID=Students.CID AND 
                Students.MID=People.MID  AND 
                Students.CID='{$CID}'
            ORDER BY People.LastName";

        $res=sql($sql,"abiturEr02");
        $ii=1;
        while ($row=sqlget($res)) {
            if (!$GLOBALS['chainFilter']->is_filtered($row['cid'],$row['mid'])
            && !$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
            $all['num']=$n;
            $ii=intval($row['mid']);
            $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");

            {

                if ($GLOBALS['s']['perm']!=4)
                $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
                else
                $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
            }

            $all['ctitle'] =$row['title'];
            $all['cgr'] =$row[$gr_name];
            $all['login'] =$row['Login'];
            $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";

            $all['days'] = sprintf(_("%d-й"), max(floor($row['seconds_from_registration']/86400 + 1), 1));
            $all['rest'] = (($num = $row['longtime'] - $all['days']) > 0) ? $num : _('время обучения по курсу закончилось');

            $sel.=words_parse($alltr,$all,"AB-");
            $n++;
        }

    return $sel;
}

function studList_mid($CID, $mids, $gr=-1) {

    global $alltr,$s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
    $sel="";
    $n=1;
    $all=array();
/*
    if ($gr != -1) {
        switch ($gr[0]) {
            case "d":
            $cgid = (int)substr($gr, 1);
            $gr_name = "cgname";
            break;
            case "g":
            $gid = (int)substr($gr, 1);
            $gr_name = "groupname";
            break;
            default:
            $gr_name = "groupname";
        }
    }
        $sql="
            SELECT structure_of_organ.name , People.Login, People.MID as mid, People.FirstName as fname,
            People.LastName as lname, Courses.Title as title, Students.CID as cid,  cgname.name as cgname,
            groupname.name as groupname
            FROM
                Courses, Students
                LEFT JOIN cgname ON cgname.cgid=Students.cgid,
                People
                LEFT JOIN groupuser ON groupuser.mid=People.MID
                LEFT JOIN groupname ON groupuser.gid=groupname.gid
                LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID)
            WHERE
                Courses.CID=Students.CID AND Students.MID=People.MID";
*/

        $sql="
            SELECT DISTINCT structure_of_organ.name , People.Login, People.MID as mid, People.FirstName as fname,
            People.LastName as lname, Courses.Title as title, Students.CID as cid
            FROM
                Courses, Students, People
                LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID)
            WHERE
                Courses.CID=Students.CID AND Students.MID=People.MID";
        if ($CID)
            $sql.=" AND Students.CID='".$CID."'";
        else
            $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";

        $sql.=" AND People.MID IN (".implode(", ", $mids).")";

        /*
        if ($cgid) {
            $sql .= " AND cgname.cgid='{$cgid}'";
        }
        if ($gid) {
            $sql .= " AND groupuser.gid='{$gid}'";
        }
        */
        //echo "<pre>".$sql;

        $res=sql($sql.' ORDER BY People.LastName',"abiturEr02");
        $ii=1;
        while ($row=sqlget($res)) {
            if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
            $all['num']=$n;
            $ii=intval($row['mid']);
            $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");

            {

                if ($GLOBALS['s']['perm']!=4)
                $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
                else
                $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
            }

            $all['ctitle'] =$row['title'];
            $all['cgr'] =$row[$gr_name];
            //$all['cgr'] =$row['groupname'].$row['cgname'];
            $all['login'] =$row['Login'];
            //               $all['move']   ="<input type=\"checkbox\" name=\"sttoab['".$n."']\" value=\"toab\">";
            //               $all['remove'] ="<input type=\"checkbox\" name=\"sttogr['".$n."']\" value=\"togr\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
            //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
            //               $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid[]\" value=\"{$row['mid']}\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
            $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";

            $all['days'] = '';
            $all['rest'] = '';

            $sel.=words_parse($alltr,$all,"AB-");
            $n++;
        }
    $GLOBALS['n_list'] = $n;
    return $sel;
}

function studList_not_in_org($CID, $mids, $gr=-1) {

    global $alltr,$s;
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
    $sel="";
    $n=$GLOBALS['n_list'];
    $all=array();

    if ($gr != -1) {
        switch ($gr[0]) {
            case "d":
            $cgid = (int)substr($gr, 1);
            $gr_name = "cgname";
            break;
            case "g":
            $gid = (int)substr($gr, 1);
            $gr_name = "groupname";
            break;
            default:
            $gr_name = "groupname";
        }
    }

        $sql="
            SELECT People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title, Students.CID as cid,  cgname.name as cgname, groupname.name as groupname
            FROM
                Courses, Students
                LEFT JOIN cgname ON cgname.cgid=Students.cgid,
                People
                LEFT JOIN groupuser ON groupuser.mid=People.MID
                LEFT JOIN groupname ON groupuser.gid=groupname.gid
                LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID)
            WHERE
                Courses.CID=Students.CID AND Students.MID=People.MID";
        if ($CID)
        $sql.=" AND Students.CID='".$CID."'";
        else
        $sql.=" AND Students.CID IN (".implode(",",$s[tkurs]).")";

        $sql.=" AND People.MID NOT IN (".implode(", ", $mids).")
                AND structure_of_organ.mid is NULL";

        if ($cgid) {
            $sql .= " AND cgname.cgid='{$cgid}'";
        }
        if ($gid) {
            $sql .= " AND groupuser.gid='{$gid}'";
        }


        //echo "<pre>".$sql;

        $res=sql($sql.' ORDER BY People.LastName',"abiturEr02");
        $ii=1;
        while ($row=sqlget($res)) {
            if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
            $all['num']=$n;
            $ii=intval($row['mid']);
            $img=sqlval("SELECT * FROM filefoto WHERE mid=$ii ","errRE428");

            {

                if ($GLOBALS['s']['perm']!=4)
                $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
                else
                $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
            }

            $all['ctitle'] =$row['title'];
            $all['cgr'] =$row[$gr_name];
            //$all['cgr'] =$row['groupname'].$row['cgname'];
            $all['login'] =$row['Login'];
            //               $all['move']   ="<input type=\"checkbox\" name=\"sttoab['".$n."']\" value=\"toab\">";
            //               $all['remove'] ="<input type=\"checkbox\" name=\"sttogr['".$n."']\" value=\"togr\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
            //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
            //               $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid[]\" value=\"{$row['mid']}\">";
            //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
            $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";

            $all['days'] = '';
            $all['rest'] = '';

            $sel.=words_parse($alltr,$all,"AB-");
            $n++;
        }

    return $sel;
}

function gradList($CID) {
    global $alltr,$s;
    $sel="";
    $n=1;
    $all=array();

    $sql="SELECT DISTINCT graduated.CID as cid, People.Login, People.Patronymic, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM graduated, People, Courses WHERE Courses.CID=graduated.CID AND graduated.MID=People.MID";
    if ($CID) $sql.=" AND graduated.CID='".$CID."'";
    else $sql.=" AND graduated.CID IN (".implode(",",$s[tkurs]).")";
    //         $sql.=" ORDER BY Courses.CID, graduated.SID ASC";
    //$sql.=getSort("graduated.SID");
    if ($CID)
    $res=sql($sql.' ORDER BY People.LastName',"abiturEr03");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        //               $all['move']   ="<input type=\"checkbox\" name=\"grtost['".$n."']\" value=\"tost\">";
        //               $all['remove'] ="<input type=\"checkbox\" name=\"grdelete['".$n."']\" value=\"delete\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }

    return $sel;
}

function gradList_mid($CID, $mids) {
    global $alltr,$s;
    $sel="";
    $n=1;
    $all=array();

    $sql="SELECT DISTINCT structure_of_organ.name, graduated.CID as cid, People.Patronymic, People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM graduated, People LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID), Courses
          WHERE Courses.CID=graduated.CID AND graduated.MID=People.MID";
//  $sql.=" AND graduated.CID='".$CID."'";
    if ($CID) $sql.=" AND graduated.CID='".$CID."'";
    else $sql.=" AND graduated.CID IN (".implode(",",$s[tkurs]).")";
    $sql.=" AND People.MID IN (".implode(", ", $mids).")";

    $res=sql($sql.' ORDER BY People.LastName',"abiturEr03");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
        else
        $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']} (".$row['name'].")</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        //               $all['move']   ="<input type=\"checkbox\" name=\"grtost['".$n."']\" value=\"tost\">";
        //               $all['remove'] ="<input type=\"checkbox\" name=\"grdelete['".$n."']\" value=\"delete\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    $GLOBALS['n_list'] = $n;
    return $sel;
}

function gradList_not_in_org($CID, $mids) {
    global $alltr,$s;
    $sel="";
    $n=$GLOBALS['n_list'];
    $all=array();

    $sql="SELECT graduated.CID as cid, People.Patronymic, People.Login, People.MID as mid, People.FirstName as fname, People.LastName as lname, Courses.Title as title
          FROM graduated, People LEFT JOIN structure_of_organ ON (structure_of_organ.mid=People.MID), Courses
          WHERE Courses.CID=graduated.CID AND graduated.MID=People.MID";
//  $sql.=" AND graduated.CID='".$CID."'";
    if ($CID) $sql.=" AND graduated.CID='".$CID."'";
    else $sql.=" AND graduated.CID IN (".implode(",",$s[tkurs]).")";
    $sql.=" AND People.MID NOT IN (".implode(", ", $mids).")
            AND structure_of_organ.mid IS NULL";

    $res=sql($sql.' ORDER BY People.LastName',"abiturEr03");
    while ($row=sqlget($res)) {
        if (!$GLOBALS['peopleFilter']->is_filtered($row['mid'])) continue;
        $all['num']=$n;
        if ($GLOBALS['s']['perm']!=4)
        $all['name']="<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        else
        $all['name']=$mid."<a href=\"[PATH]reg.php4?[SESSID]showMID=".$row['mid']."\">{$row['lname']} {$row['fname']} {$row['Patronymic']}</a>";
        $all['ctitle'] =$row['title'];
        $all['login'] =$row['Login'];
        //               $all['move']   ="<input type=\"checkbox\" name=\"grtost['".$n."']\" value=\"tost\">";
        //               $all['remove'] ="<input type=\"checkbox\" name=\"grdelete['".$n."']\" value=\"delete\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"pm['".$n."']\" value=\"".$row['mid']."\">";
        //               $all['hidden'].="<input type=\"hidden\" name=\"pc['".$n."']\" value=\"".$row['cid']."\">";
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        //               $all['hidden'] ="<input type=\"hidden\" name=\"arr_hid_cid[{$row['mid']}]\" value=\"{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }

    return $sel;
}

function moveStud($pm,$pc) {


    $sablist=(isset($_POST['sttoab'])) ? $_POST['sttoab'] : array();
    $sgrlist=(isset($_POST['sttogr'])) ? $_POST['sttogr'] : array();

    foreach ($sablist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) toab($pm[$k],$pc[$k]);
    }
    foreach ($sgrlist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) togr($pm[$k],$pc[$k]);
    }
}

function moveSimple() {

    global $arrTargets;
    global $arrActions;

    $arrMidCids = (isset($_POST['arr_ch_mid_cid'])) ? $_POST['arr_ch_mid_cid'] : array();
    //                $arrCids = (isset($_POST['arr_hid_cid'])) ? $_POST['arr_hid_cid'] : array();
    $strTarget = (isset($_POST['hid_target'])) ? $_POST['hid_target'] : '';
    $strAction = (isset($_POST['sel_action'])) ? $_POST['sel_action'] : '';
    $intCidAction = (isset($_POST['sel_course_action'])) ? $_POST['sel_course_action'] : 0;
    if (count($arrMidCids) && in_array($strTarget, $arrTargets) && (in_array($strAction, array_keys($arrActions)) || ($strAction=='accept'))) {
        foreach ($arrMidCids as $strMidCid) {
            list($intMid, $intCid) = explode("_", $strMidCid);
            if ($intCidAction) {
                tost($intMid, $intCidAction, 1,0,1);
            } else {
                $strAction($intMid,$intCid);
            }
        }
    }
    return true;
}

function moveAbit($pm,$pc) {
    $sstlist=(isset($_POST['abtost'])) ? $_POST['abtost'] : array();
    $sdelist=(isset($_POST['abdelete'])) ? $_POST['abdelete'] : array();

    foreach ($sstlist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
    }
    foreach ($sdelist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) del($pm[$k],$pc[$k]);
    }
}

function moveGrad($pm,$pc) {
    $sstlist=(isset($_POST['grtost'])) ? $_POST['grtost'] : array();
    $sdelist=(isset($_POST['grdelete'])) ? $_POST['grdelete'] : array();
    foreach ($sstlist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
    }
    foreach ($sdelist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) del($pm[$k],$pc[$k]);
    }


}

/**
* Согласовать
*/
function accept($mid,$cid) {
    if (getField('Courses','chain','CID',(int) $cid) == 0) {
        CChainLog::erase($cid,$mid);
        return tost($mid,$cid);
    }
    $GLOBALS['chainFilter'] = new CChainFilter();
    $GLOBALS['chainFilter']->init($cid,$GLOBALS['s']['mid']);
    $GLOBALS['chainFilter']->accept($cid,$mid,$_POST['comment']);
}

function moveToStud($pm,$pc) {
    $sstlist=(isset($_POST['tost'])) ? $_POST['tost'] : array();
    foreach ($sstlist as $k=>$val) {
        if (isset($pm[$k]) && isset($pc[$k])) tost($pm[$k],$pc[$k]);
    }
}

function toab( $mid,$cid ) {
    $query = "SELECT * FROM claimants WHERE MID=".$mid." AND CID='".(int) $cid."'";
    $res=sql($query,"abiturToab01");
    if (sqlrows($res)<1) {
        $query = "INSERT INTO claimants (MID,CID,Teacher) VALUES (".$mid.",".$cid.",0)";
        sql($query,"abiturToab02");
        $query = "DELETE FROM Students WHERE MID=".$mid." AND CID=".$cid;
        sql($query,"abiturToab03");
        $query = "DELETE FROM graduated WHERE MID=".$mid." AND CID=".$cid;
        sql($query,"abiturToab04");
        mailTostud( "toab", $mid, $cid, "");
        if (!sqlrows(sql("SELECT * FROM Students WHERE MID={$mid}"))) {
        	sql("INSERT INTO Students (MID,CID) VALUES({$mid},0)");
        }
    }
}

function setAsAb( $mid,$cid ) {
    //  устанавливает абитуриаентов если чел только в people зарегистрирован
    //      $query = "SELECT * FROM claimants WHERE MID=".$mid." AND CID=".$cid;
    //      $res=sql($query,"abiturToab01");
    //      if (sqlrows($res)<1) {
    $query = "INSERT INTO claimants (MID,CID) VALUES (".$mid.",".$cid.")";
    $res=sql($query,"abiturToab02");
    //            $query = "DELETE FROM Students WHERE MID=".$mid." AND CID=".$cid;
    //            sql($query,"abiturToab03");
    //            $query = "DELETE FROM graduated WHERE MID=".$mid." AND CID=".$cid;
    //            sql($query,"abiturToab04");
    //            mailTostud("toab",$mid,$cid,"");
    //            }
    return( $res );
}


function togr($mid,$cid, $boolMail = true) {
    $query = "SELECT * FROM graduated WHERE MID=".$mid." AND CID=".$cid;
    $res=sql($query,"abiturTogr01");
    if (sqlrows($res)<1) {
        $query = "INSERT INTO graduated (MID,CID) VALUES (".$mid.",".$cid.")";
        sql($query,"abiturTogr02");
        if ($boolMail) mailTostud("togr",$mid,$cid,"");
    }
    $query = "DELETE FROM claimants WHERE MID=".$mid." AND CID=".$cid;
    sql($query,"abiturTogr03");
    $query = "DELETE FROM Students WHERE MID=".$mid." AND CID=".$cid;
    sql($query,"abiturTogr04");
    if (!sqlrows(sql("SELECT * FROM Students WHERE MID={$mid}"))) {
    	sql("INSERT INTO Students (MID,CID) VALUES({$mid},0)");
    }
}


function findInClaimants( $mid ){
    $query = "SELECT * FROM claimants WHERE MID=".$mid;
    $res=sql( $query, "find abiturTost03");
    while( $r=sqlget($res)){
        $cids[]=$r[cid];
    }
    return( $cids );
}

function tost( $mid, $cid, $boolMail = true, $return = false, $boolNotFirst = false ) {

    $query = "SELECT * FROM Students WHERE MID='".$mid."'";
    $res=sql($query,"abiturTost01");
    if ($arrStudent = sqlget($res)) {
        $intCgid = $arrStudent['cgid'];
    } else {
        $intCgid = 0;
    }

    $query = "SELECT * FROM Students WHERE MID='".$mid."' AND CID=".$cid;
    $res=sql($query,"abiturTost01");
    $arrRegInfo = array('', '');
    if (sqlrows($res)<1) {

        // echo "<H1>ЗАНОСИМ В СТУДЕНТЫ</H1> "
        if (strtolower(dbdriver) == 'mysql') {
            $query = "INSERT INTO Students ( MID, CID, Registered, cgid) VALUES (".$mid.",".$cid.",".time().", '{$intCgid}')";
        } else {
            $query = "INSERT INTO Students ( MID, CID, Registered, cgid, time_registered ) VALUES (".$mid.",".$cid.",".time().", '{$intCgid}', ".$GLOBALS['adodb']->DBDate(time()).")";
        }
        sql($query,"abiturTost02");
        //            echo "$query<P>";
        $query = "DELETE FROM claimants WHERE MID=".$mid." AND CID=".$cid;
        sql($query,"abiturTost03");
        $query = "DELETE FROM graduated WHERE MID=".$mid." AND CID=".$cid;
        sql($query,"abiturTost04");

        $count=reset_mid_shedule( $mid, $cid );
        // добавить человеку в расписание занятия, которые он должен пройти по программе

        if($count>0) $mess=_("В программе обучения назначено занятий")." $count.";
        //            echo $mess;
        $strMode = ($boolNotFirst) ? "tost" : "tost_first";
        if ($boolMail) $arrRegInfo = mailTostud( $strMode, $mid, $cid, $mess, true, $return );

        //            echo shedule_count( $mid, $cid );

        /**
        * Установка ролей по умолчанию
        */
        CRole::add_mid_to_role($mid,CRole::get_default_role('student'));

    }

    if ($return) return $arrRegInfo;
    //      $count=shedule_count( $mid, $cid );
    //      echo "$count ЗАНЯТИЙ<BR>";
    //      if( $count == 0 ) // если нет ни одного занятия у человека на данном курсе
}

function del($mid,$cid,$mail=true) //[che 24.10.2003]
{
    //      echo "<HR>$mid..<BR>";
    //      echo "отправка сообщения..<BR>";
    if ($mail) {
        mailTostud("del",$mid,$cid,"");
    }

    require_once($GLOBALS['wwf'].'/lib/classes/Chain.class.php');
    CChainLog::erase($cid,$mid);

    //      echo "удаление из студентов..<BR>";
    $query = "DELETE FROM Students WHERE MID=".$mid." AND CID=".$cid;
    sql($query,"abiturDel01");

    //      echo "удаление из выпускников..<BR>";
    $query = "DELETE FROM graduated WHERE MID=".$mid." AND CID=".$cid;
    sql($query,"abiturDel02");

    //      echo "удаление из абитуриентов..<BR>";
    $query = "DELETE FROM claimants WHERE MID=".$mid." AND CID=".$cid;
    sql($query,"abiturDel03");

    //      echo "удаление из групп..<BR>";
    $query = "DELETE FROM claimants WHERE MID=".$mid." AND CID=".$cid;
    //      sql($query,"abiturDel03");

    //      echo "удаление со специальности..<BR>";
    $query = "DELETE FROM claimants WHERE MID=".$mid." AND CID=".$cid;
    //      sql($query,"abiturDel03");

    //      echo "удаление всех данных..<BR>";
    //      $query = "DELETE FROM People WHERE MID=".$mid;
    //      sql($query,"abiturDel04");
    //      echo "выполнено..<BR>";
}

function erase( $mid ) //[dk - che 24.10.2003]
{
    mailTostud("del",$mid,$cid,"");

    //echo "удаление из студентов..<BR>";
    $query = "DELETE FROM Students WHERE MID=".$mid;
    sql($query,"abiturDel01");

    //echo "удаление из абитуриентов..<BR>";
    $query = "DELETE FROM claimants WHERE MID=".$mid;
    sql($query,"abiturDel03");

    //echo "удаление из групп..<BR>";
    $query = "DELETE FROM groupuser WHERE MID=".$mid;
    sql($query,"abiturDel03");

    //echo "удаление со специальности..<BR>";
    $query = "DELETE FROM logseance WHERE mid=".$mid;
    sql($query,"abiturDel03");

    $query = "DELETE FROM logseance WHERE MID=".$mid;
    sql($query,"abiturDel03");

    $query = "DELETE FROM money WHERE mid=".$mid;
    sql($query,"abiturDel03");
    $query = "DELETE FROM scheduleID WHERE MID=".$mid;
    sql($query,"abiturDel03");
    $query = "DELETE FROM seance WHERE MID=".$mid;
    sql($query,"abiturDel03");
    $query = "DELETE FROM teachNotes WHERE MID=".$mid;
    sql($query,"abiturDel03");
    $query = "DELETE FROM testcount WHERE mid=".$mid;
    sql($query,"abiturDel03");
    $query = "DELETE FROM tracks2mid WHERE MID=".$mid;

    //echo "удаление всех данных..<BR>";
    $query = "DELETE FROM People WHERE MID=".$mid;
    sql($query,"abiturDel04");
    //echo "выполнено..<BR>";
}

function registration2course( $Course, $mid, $teacher=0, $redirect_url="", $message = "" ){

    global $studentstable;
    global $peopletable;
    global $optionstable;
    global $coursestable;
    global $teacherstable;
    global $claimtable;
    global $_POST;
    global $Login;
    global $Password;
    $result=sql("SELECT * FROM $studentstable WHERE (MID=$mid AND CID=$Course)","errREG524");
    $result0=sql("SELECT * FROM $claimtable WHERE (MID=$mid AND CID=$Course)","errREG524");
    $result1=sql("SELECT * FROM $teacherstable WHERE (MID=$mid AND CID=$Course)","errREG525");
    $result2=sql("SELECT * FROM People INNER JOIN Students ON (People.MID=Students.MID) WHERE (People.MID=$mid)","errREG524");
    $LastName = $_POST['LastName'];
    $FirstName = $_POST['FirstName'];

    if (sqlrows($result)>0 && sqlrows($result1)>0) {
        /* Является студентом и преподавателем */
        $redirect_url = "course_structure.php?CID={$Course}";
    	$message = _("Вы уже зарегистрированы на этом курсе!");
    }elseif (sqlrows($result0)>0) {
        /* Является претендентом */
        $redirect_url = "order.php";
        $message = _("Ваша заявка уже находится на рассмотрении");
    }
    else {
        $CourseTitle=getField($coursestable,"Title","CID",$Course);
        $from=getField($optionstable,"value","name","dekanEMail");
        $fromname=getField($optionstable,"value","name","dekanName");
        $headers = "From: $fromname<$from>\n";
        $headers .="Content-type: text/html; Charset={$GLOBALS['controller']->lang_controller->lang_current->encoding}\n";
        $headers .= "X-Sender: <$from>\n";
        $to=getField( $peopletable,"EMail","MID",$mid);
        if ( $teacher==1 && sqlrows($result1)==0) {
            $query = "INSERT INTO $claimtable (MID,CID,Teacher) VALUES ($mid,$Course,1)";
            sql($query,"errREG518");
            mailToteach("regcourse", $mid, $Course);
            mailToelearn("about_reg_teacher", $mid, $Course, $more);

            /* Является претендентом на роль преподавателя  */
            $redirect_url = "index.php";
            $message = _("Ваша заявка на курс принята к рассмотрению. О результате Вам сообщат по e-mail");
        }
        elseif (sqlrows($result)==0) {
            $typeDes = getField($coursestable,"TypeDes","CID",$Course);
            if ($typeDes<0) $typeDes = getField($coursestable,"chain","CID",$Course);
            if ($typeDes==0) {
                $boolAlreadyExists = sqlrows($result2);
                tost( $mid, $Course, 1, 0, $boolAlreadyExists);
                mailToelearn("about_reg_student", $mid, $Course, $more);

                /* Является студентом */
                //$redirect_url = "course_structure.php?CID={$Course}";
                $redirect_url = "order.php";
                $message = _("Вы зачислены на курс")." (".cid2title($Course).")";
            }
            else {
                $sql = "SELECT SID FROM claimants WHERE MID = '$mid' AND CID = '$Course' AND Teacher = '0'";
                $res = sql($sql);
                if (!sqlrows($res)) {
                    $query = "INSERT INTO $claimtable (MID,CID,Teacher) VALUES ($mid,$Course,0)";
                    sql($query,"errREG517");

                    mailTostud("reg_stud", $mid, $Course, "");
                    mailToelearn("about_reg_student", $mid, $Course, $more);
                    CChainLog::email($Course, $mid);

                    /* Является претендентом на роль студента */
                    $redirect_url = "index.php";
                    $message = _("Ваша заявка на курс принята к рассмотрению. О результате Вам сообщат по e-mail");
                }
            }
        }
        else {
            /* Является студентом или преподавателем */
            $redirect_url = "course_structure.php?CID={$Course}";
            $message = _("Вы уже зарегистрированы на этом курсе!");
        }
    }
    return true;
}

function selectOpenCourses( $name="Course", $none=false){
    global $coursestable;


    $tmp="<select name='$name' class=lineinput>";
    if ($none) {
        $tmp .= "<option value='0'> нет</option>";
    }

    // вывод перечня курсов
    $query = "select CID, Title from $coursestable WHERE status>0 ORDER BY Title";
    $result1=sql($query,"errREG530");
    $tr=getTracksIdList();
    $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
    while ($row=sqlget($result1)){
        if (!$courseFilter->is_filtered($row['CID'])) continue;
        //        if( !isInTrackList( $tr, $row->CID) ){
        $courses.="<option value='".$row['CID']."'>".$row['Title']."</option>";
        //        }
    }
    $tmp.=$courses."</select>";
    return( $tmp );
}

function selectOpenTracks($name="Track")
{
     $tmp = "<select name='$name' class=lineinput>";
     $query = "SELECT trid, name FROM tracks WHERE status>0 ORDER BY name";
     $result1 = sql($query, "errSelOpTr");
     while($row = sqlget($result1))
     {
         $tmp .= "<option value='" . $row['trid'] . "'>" . $row['name'] . "</option>";
     }
     $tmp .= "</select>";
     return $tmp;
}

function isGroup( $name, $mode=1 ){
    if( $mode ){
        $q="SELECT * FROM cgname";
    }else{
        $q="SELECT * FROM groupname";// WHERE cid=-1";
    }

    $res=sql( $q ,"errFM18335");

    while( $r = sqlget( $res ) ){
        if( trim($r[name])==trim($name)){
            if( $mode )
            $cgid=$r[ cgid ];
            else
            $cgid=$r[ gid ];
            break;
        }
    }
    sqlfree($res);
    return( $cgid );
}

function makeNewGroup( $name, $mode=1 ){
    if (!empty($name)){
        if( $mode )
        $res=sql("INSERT INTO cgname (name) values (".$GLOBALS['adodb']->Quote($name).")","errFM185");
        else
        $res=sql("INSERT INTO groupname (name, cid) values (".$GLOBALS['adodb']->Quote($name).", '-1')","errFM185");
    }
    sqlfree($res);
    $gid=sqllast(); // !!!!!!!!!!!!!

    return( $gid );
}

function add2group( $che, $cgid, $mode=1 ){

    //         echo "<H1>$mode!!</H1>";

    if( $mode ){
        if (is_array($che) && count($che)) {
            $rq="UPDATE `Students` SET cgid=$cgid WHERE MID IN (".implode(", ",$che).")";
            //      echo "ДОБАЛЯЕМ СПИСКОМ";
        }else{
            if( !empty( $che ) ){
                $rq="UPDATE `Students` SET cgid=$cgid WHERE MID=$che";
                //      echo "ДОБАЛЯЕМ ОДНОГО";
            }
            else echo " "._("НЕ УКАЗАН ЧЕЛОВЕК")." ";
        }
    }else{
        // $res=sql("DELETE FROM groupuser WHERE gid=$gid AND cid=$cid","errGR136");
        // sqlfree($res);
        if (is_array($che) && count($che)) {
            $rq="INSERT INTO groupuser (gid,cid,mid) VALUES ";
            foreach ($che as $k=>$v) {
                $rq.="($cgid,-1,".intval($v)."),";
            }
            $rq=substr($rq,0,-1);
        } else {
            $_q = "SELECT * FROM groupuser WHERE gid = {$cgid} AND cid = -1 AND mid = {$che}";
            $_r = sql($_q);
            if (sqlrows($_r)) {
                $rq = "SELECT 1";
            }
            else {
                $rq="INSERT INTO groupuser (gid,cid,mid) VALUES ($cgid,-1, $che)";
            }
        }
        //     echo $rq;
    }

    $res=sql($rq,"errGR139:$rq");
    if( !$res )
    echo " "._("НЕУДАЧНО")." ";

    sqlfree($res);
    return( $res );
}

function checkPeople( $Login, $LastName, $FirstName, $Patronymic, $BirthDay ){
    $rq="SELECT * FROM People WHERE Login='$Login' OR LastName='$LastName' AND FirstName='$FirstName' AND Patronymic='$Patronymic'"; //BirthDay=$BirthDay
    $res=sql( $rq, "err1C89");
    if( $r=sqlget($res) ) $ret=$r[MID];  else  $ret=0;
    return( $ret );
}

function delfromabitur($mid,$cid) {

    CChainFilter::deny($cid,$mid);

/*    $sql = "DELETE FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
    sql($sql);

    sql("DELETE FROM chain_agreement WHERE mid='".(int) $mid."' AND cid='".(int) $cid."'");
*/
}

function delfromgrad($mid,$cid) {
    $sql = "DELETE FROM graduated WHERE MID='".(int) $mid."' AND CID='".(int) $cid."'";
    sql($sql);
}

function get_people_who_study_on_cid($cid) {
    $alltr=loadtmpl("abitur-sttr".$GLOBALS['tmpl'].".html");
    $sel="";
    $n=1;
    $sql = "SELECT DISTINCT People.Login, People.MID as mid, People.Patronymic, People.FirstName as fname, People.LastName as lname
            FROM People INNER JOIN Students ON (Students.MID=People.MID) WHERE Students.CID='".(int) $cid."'";
    $res = sql($sql);
    while ($row=sqlget($res)) {
        $all['num']=$n;
        $all['name']=$mid."<a onClick=\"wopen('[PATH]userinfo.php?[SESSID]mid=".(int) $row['mid']."','',600,425)\" href=\"javascript:void(0);\">".$row['lname']." ".$row['fname']." ".$row['Patronymic']."</a>";
        $all['login'] =$row['Login'];
        $all['select'] ="<input type=\"checkbox\" name=\"arr_ch_mid_cid[]\" value=\"{$row['mid']}_{$row['cid']}\">";
        $sel.=words_parse($alltr,$all,"AB-");
        $n++;
    }
    return $sel;
}
?>