<?php
require_once('1.php');
require_once('lib/classes/WeekSchedule.class.php');
require_once('lib/classes/Schedule.class.php');
require_once("test.inc.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s['perm']<2) login_error();

$GLOBALS['controller']->captureFromOb(CONTENT);

$tweek = (int) $_GET['tweek'];
$cid = (int) $_GET['cid'];
if (!$tweek) $tweek = time();

while(date("w",$tweek)!=1) {
    $tweek-=11*60*60;
}

$tweeklast=mktime(12,0,0,date("m",$tweek),date("d",$tweek)-1,date("Y",$tweek));
$tweeknext=mktime(12,0,0,date("m",$tweek),date("d",$tweek)+$sd,date("Y",$tweek));

$SCHEDULE_PERM_EDIT = (check_teachers_permissions(20, ($s['mid'] || ($s['perm']>1))));

$SCHEDULE_PERM_ADD = ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
                     || $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS));

$SCHEDULE_PERM_ALL_COURSES = ($GLOBALS['controller']->checkPermission(SHEDULE_ROOMS_PERM_ALL_COURSES));

$filter_courses = array();
$filter_courses['-1'] = _("Все курсы преподавателя");
if ($SCHEDULE_PERM_ALL_COURSES) {
    $sql = "SELECT CID, Title FROM Courses WHERE Status>0 ORDER BY Title";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $filter_courses[$row['CID']] = $row['Title'];
    }

    $sql = "SELECT DISTINCT Courses.CID, Courses.Title
            FROM Courses
            INNER JOIN Teachers ON (Teachers.CID=Courses.CID)
            WHERE Courses.Status>0
            AND Teachers.MID='".(int) $s['mid']."'
            ORDER BY Courses.Title";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $teacher_courses[$row['CID']] = $row['Title'];
    }

} else {
    $sql = "SELECT DISTINCT Courses.CID, Courses.Title
            FROM Courses
            INNER JOIN Teachers ON (Teachers.CID=Courses.CID)
            WHERE Courses.Status>0
            AND Teachers.MID='".(int) $s['mid']."'
            ORDER BY Courses.Title";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $filter_courses[$row['CID']] = $row['Title'];
        $teacher_courses[$row['CID']] = $row['Title'];
    }
}

$GLOBALS['controller']->addFilter(_("Курс"),'cid',$filter_courses,$cid,false);

$week_schedule = new WeekSchedule;
$week_schedule->init_by_begin_week(date("Y-m-d", $tweek));

switch($s['perm']) {
    default:
        if ($cid>0) {
            $week_schedule->set_cids(array($cid));
        } else {
            if ($cid==-1) {
                $week_schedule->set_cids(array_keys($teacher_courses));
            } else {
                $week_schedule->set_cids(array_keys($filter_courses));
            }
        }
    break;
}

$aWeek_schedule = $week_schedule->get_as_array();

if (is_array($aWeek_schedule) && count($aWeek_schedule)) {
    foreach(array_keys($aWeek_schedule) as $i) {
        $studies = array();
        if (isset($aWeek_schedule[$i]['studies'])
            && is_array($aWeek_schedule[$i]['studies'])
            && count($aWeek_schedule[$i]['studies'])) {

            foreach(array_keys($aWeek_schedule[$i]['studies']) as $j) {

                if ($aWeek_schedule[$i]['studies'][$j]['period_id']<=0) {
                    $aWeek_schedule[$i]['studies'][$j]['period_id'] = 0;
                }
                if ($aWeek_schedule[$i]['studies'][$j]['room_id']<=0) {
                    $aWeek_schedule[$i]['studies'][$j]['room_id']   = 0;
                } else {
                    if (getField('rooms','status','rid',$aWeek_schedule[$i]['studies'][$j]['room_id']) == 0) {
                        $aWeek_schedule[$i]['studies'][$j]['room_id'] = 0;
                    }
                }



                $aWeek_schedule[$i]['studies'][$j]['text'] =
                "<table class=main cellspacing=0>
                <tr><td>"._("Занятие").": </td><td>{$aWeek_schedule[$i]['studies'][$j]['name']}</td></tr>";

                if ($aWeek_schedule[$i]['studies'][$j]['penalty'])
                $aWeek_schedule[$i]['studies'][$j]['text'] .=
                "<tr><td colspan=2><font color=red>"._("Штраф за несвоевременную сдачу").": {$aWeek_schedule[$i]['studies'][$j]['penalty']}%</font></td></tr>";

                $aWeek_schedule[$i]['studies'][$j]['text'] .=
                "<tr><td>"._("Курс").": </td><td>{$aWeek_schedule[$i]['studies'][$j]['course_name']}</td></tr>
                <tr><td>"._("Преподаватель").": </td><td>{$aWeek_schedule[$i]['studies'][$j]['teacher']}</td></tr>
                <tr><td>"._("Время").": </td><td>{$aWeek_schedule[$i]['studies'][$j]['time']['begin']} - {$aWeek_schedule[$i]['studies'][$j]['time']['end']}</td></tr>
                <tr><td colspan=2>{$aWeek_schedule[$i]['studies'][$j]['description']}</td></tr>
                </table>";
                $aWeek_schedule[$i]['studies'][$j]['text'] = htmlspecialchars(str_replace(array("\n","\r"),'',$aWeek_schedule[$i]['studies'][$j]['text']),ENT_QUOTES);
                $studies[$aWeek_schedule[$i]['studies'][$j]['room_id']][$aWeek_schedule[$i]['studies'][$j]['period_id']]['items'][] = $aWeek_schedule[$i]['studies'][$j];
                $studies[$aWeek_schedule[$i]['studies'][$j]['room_id']][$aWeek_schedule[$i]['studies'][$j]['period_id']]['count']++;
            }

            $aWeek_schedule[$i]['studies'] = $studies;
        }
    }
}

$cid = $_GET['cid']?$_GET['cid']:0;
$rooms = getRooms($cid,false);
$rooms[] = array('rid'=>0,'name'=>_("Без аудитории"));

$periods = getallperiods();
$periods[] = array('lid'=>0,'name'=>_("Вне сетки занятий"));

$smarty = new Smarty_els();

$smarty->assign('cid',$cid);
$smarty->assign('tweeklast',$tweeklast);
$smarty->assign('tweeknext',$tweeknext);
$smarty->assign('cid',$cid);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('skin_path',$GLOBALS['controller']->view_root->skin_url);
$smarty->assign('schedule_perm_edit', $SCHEDULE_PERM_EDIT);
$smarty->assign('schedule_perm_add', $SCHEDULE_PERM_ADD);
$smarty->assign('rooms',$rooms);
$smarty->assign('periods',$periods);
$smarty->assign('width', (int) 100/(count($rooms)+1));
$smarty->assign_by_ref('week_schedule',$aWeek_schedule);
$smarty->display('schedule_rooms.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>