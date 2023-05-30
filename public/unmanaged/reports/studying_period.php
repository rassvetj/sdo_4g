<?php
require("../1.php");
include_once("schedule.lib.php");
include_once("ved_class.php");
include_once("People.class.php");
require_once('Schedules.class.php');

if (!$s[login]) {
    exitmsg("Пожалуйста, авторизуйтесь","/?$sess");
}
if ($s[perm]<3) {
    exitmsg("Доступ к странице имеют представители учебной администрации, или администраторы","/?$sess");
}

if (isset($_POST['date']['from']['Month'])) {
    $sec_from = mktime(0,0,0,$_POST['date']['from']['Month'],$_POST['date']['from']['Day'],$_POST['date']['from']['Year']);
    $sec_to = mktime(0,0,0,$_POST['date']['to']['Month'],$_POST['date']['to']['Day'],$_POST['date']['to']['Year']);
}
//
$arr = getWeek($cid, $weeks);
$sec_from = $arr[0];
$sec_to = $arr[1];
//
if($_POST['view']) {
    if($sec_from > $sec_to){
              echo "<h3 style='color: black'>Неверно задан временной диапазон.</h3>";
              die();
    }
    
    $smarty_tpl = new Smarty_els;
    
    $week[0] = $sec_from;
    $week[1] = $sec_to;
    $strWeekStart = date("Y-m-d", $week[0]);
    $strWeekEnd = date("Y-m-d H:i", $week[1]);
    $strWeekStartHuman = date("d.m.Y", $week[0]);
    $strWeekEndHuman = date("d.m.Y", $week[1]);
    $dates = "{$strWeekStartHuman} - {$strWeekEndHuman}";
    $strSql = "
        SELECT
          people_students.MID as student_mid,
          people_students.LastName as student_lastname,
          WEEKDAY(schedule.begin) AS week_day,
          schedule.period as period_id,
          schedule.title as schedule_title,
          rooms.name as room_title,
          people_teachers.MID as teacher_mid,
          scheduleID.V_STATUS as mark,
          alt_mark.`char` as alt_mark,
          schedule.SHEID as SHE_ID
        FROM
          schedule
          INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
          INNER JOIN People as people_students ON (scheduleID.`MID` = people_students.`MID`)
          INNER JOIN People as people_teachers ON (schedule.createID = people_teachers.`MID`)
          LEFT OUTER JOIN alt_mark ON (scheduleID.V_STATUS = alt_mark.id)
          LEFT OUTER JOIN rooms ON (schedule.rid = rooms.rid)
        WHERE
          schedule.begin >= '{$strWeekStart}' AND 
          schedule.end <= '{$strWeekEnd}' AND 
          schedule.CID = '{$_POST['cid']}'
        ORDER BY
          student_lastname, student_mid, week_day, period_id
    ";
    $res = sql($strSql);

    $tabledays = 0;
    for($sec_from; $sec_from<=$sec_to; $sec_from+=60*60*24) {
        $arrDays[] = $strWeekStartHuman = date("d.m.Y", $sec_from);
        $tabledays++;
    }
    $arrPeriods = Schedules::get_all_periods();
    $arrJournal['students'] = Schedules::get_all_students($_POST['cid'], ONLY_CID);//ONLY_CID
    while($arrRecord = sqlget($res)) {
        
        $intFirstSpace = min(strpos(ltrim($arrRecord['schedule_title'] . " ")," "), 15); 
        $arrSchedule['title'] = $arrRecord['schedule_title'];
//      $arrSchedule['kindnum'] = get_kindnum_by_sheid($arrRecord['SHE_ID']);
        $arrSchedule['teacher'] = getpeoplename($arrRecord['teacher_mid']);//get_people_military_info($arrRecord['teacher_mid'], SHOW_PLAIN, USE_BR);
        $arrSchedule['room'] = $arrRecord['room_title'];
        $arrJournal['schedules'][$arrRecord['week_day']][$arrRecord['period_id']] = $arrSchedule;
    //echo "<pre>"; var_dump($arrSchedule); 
//      $arrJournal['students'][$arrRecord['student_mid']]['name'] = get_people_military_info($arrRecord['student_mid'], SHOW_PLAIN);
        $arrJournal['students'][$arrRecord['student_mid']]['name'] = $arrRecord['student_lastname'];
        $arrJournal['students'][$arrRecord['student_mid']]['marks'][$arrRecord['week_day']][$arrRecord['period_id']] = ($arrRecord['mark'] == "-1") ? $arrRecord['mark']/*$arrRecord['alt_mark']*/ : $arrRecord['mark'];
    }
    
    //echo "<pre>";
    //print_r($arrJournal);
    //echo "</pre>";
    
    $smarty_tpl->assign("program", get_course_title($_POST['cid']));
//  $smarty_tpl->assign("group", get_groupnumber_cid($_POST['cid']));
//  $smarty_tpl->assign("group_leader", get_grouppid_cid($_POST['cid']));
    $smarty_tpl->assign("week", $_GET['week']);
    $smarty_tpl->assign("dates", $dates);   
    $smarty_tpl->assign("days", $arrDays);
    $smarty_tpl->assign("periods", $arrPeriods);
    $smarty_tpl->assign("journal", $arrJournal);
    $smarty_tpl->assign("tabledays", $tabledays);
    
//    echo "<pre>";
//    print_r($arrJournal);
//    echo "</pre>";
    
    $arr = get_blank($s['mid']);
    foreach ($arr as $k=>$v)
    {
        $smarty_tpl->assign($k, $v);
    }   
    
    echo $smarty_tpl->fetch('studying_period_print.tpl');
    
    die();
}


echo show_tb();
echo ph("Статистика обучения");

$smarty_tpl = new Smarty_els;
$smarty_tpl->assign("cids", getCoursesList());
//
$smarty_tpl->assign("weeks", selWeeks($cid, 0));
$smarty_tpl->assign("cid", $cid);
//
echo $smarty_tpl->fetch('studying_period_select.tpl');

echo show_tb();

function getCoursesList() {
    $r_a = array();
    $q = "SELECT CID, Title FROM Courses";
    $r = sql($q);
    while($row = sqlget($r)) {
        $r_a[$row['CID']] = $row['Title'];
    }
    return $r_a;
}

?>