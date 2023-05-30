<?php
require("../1.php");
require("schedule.lib.php");
require('departments.lib.php');
//require('fun_lib.inc.php4');


if (!$s[login]) {
    exitmsg("Пожалуйста, авторизуйтесь","/?$sess");
}
if ($s[perm]<3) {
    exitmsg("Доступ к странице имеют представители учебной администрации, или администраторы","/?$sess");
}

$arr = getWeek($cid, $weeks);
$sec_from = $sec_to = $arr[0];

/*if (isset($_POST['date']['from']['Month'])) {
    $sec_from = mktime(0,0,0,$_POST['date']['from']['Month'],$_POST['date']['from']['Day'],$_POST['date']['from']['Year']);
    $sec_to = mktime(0,0,0,$_POST['date']['to']['Month'],$_POST['date']['to']['Day'],$_POST['date']['to']['Year']);
}*/

if($_POST['view']) {
    if($sec_from > $sec_to){
              echo "<h3 style='color: black'>Неверно задан временной диапазон.</h3>";
              die();
    }
    
    $smarty_tpl = new Smarty_els;
    foreach (getCoursesList() as $cid => $value) {
            $week_schedule = new WeekSchedule;
            $week_schedule->init_by_begin_week(date("Y-m-d", $sec_from));
            $week_schedule->set_course($cid);
            $week_schedule->set_cids(array((int)$cid));
            $week_schedule_for_smarty = $week_schedule->get_as_array();
            
            $smarty_array[$cid]["groupname"] = $week_schedule->get_groupname();
            $smarty_array[$cid]["begin_day"] = substr($week_schedule->begin_week,8,2).".".substr($week_schedule->begin_week,5,2).".".substr($week_schedule->begin_week,0,4);
            $smarty_array[$cid]["end_day"] = substr($week_schedule->end_week,8,2).".".substr($week_schedule->end_week,5,2).".".substr($week_schedule->end_week,0,4); 
            $smarty_array[$cid]["week_schedule"] = $week_schedule_for_smarty;
            
//          $smarty_array[$cid]["groupnumber"] = get_groupnumber_cid($cid);
//          $smarty_array[$cid]["st"] = get_grouppid_cid($cid);
    }
    
        $smarty_tpl->assign("smarty_array", $smarty_array);
        $arr = get_blank($s['mid']);
        foreach ($arr as $k=>$v)
        {
            $smarty_tpl->assign($k, $v);
        }                
                
                echo $smarty_tpl->fetch("schedule_groups_print.tpl");   
    
    die();
}


echo show_tb();
echo ph("Расписание групп");

$smarty_tpl = new Smarty_els;
$cids = getCoursesList();
foreach ($cids as $k => $v) {
    $smarty_tpl->assign("weeks", selWeeks($k, 0));
    $smarty_tpl->assign("cid", $k);
    break;
}
echo $smarty_tpl->fetch('schedule_groups_select.tpl');

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