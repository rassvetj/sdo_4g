<?php
require_once('../1.php');
require_once('lib/classes/Schedule.class.php');
require_once('lib/classes/WeekSchedule.class.php');

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= "<root none=\"Нет текущих занятий\" color1=\"".(APPLICATION_COLOR_1 ? substr(APPLICATION_COLOR_1,1) : 'F78F15')."\" color2=\"".(APPLICATION_COLOR_2 ? substr(APPLICATION_COLOR_2,1) : '3490C5')."\">";

$week_schedule = new WeekSchedule();
$week_schedule->init_by_begin_week(date("Y-m-d", time()));

$courses = array();
switch($s['perm']) {
    case 1:
        if (isset($s["skurs"]) && is_array($s["skurs"])) {
            $courses = $s["skurs"];
        }
        break;
    default:
        if (isset($s["tkurs"]) && is_array($s["tkurs"])) {
            $courses = $s["tkurs"];
        }        
        break;
}

$week_schedule->set_cids($courses);
$week_schedule_array = $week_schedule->get_as_array_for_day(date('d.m.Y'));

if (isset($week_schedule_array[0]['studies']) 
    && is_array($week_schedule_array[0]['studies']) 
    && count($week_schedule_array[0]['studies'])) {
    foreach ($week_schedule_array[0]['studies'] as $study) {
        $xml .= "<item begin=\"{$study['time']['begin']}\" end=\"{$study['time']['end']}\" lesson=\"".htmlspecialchars($study['name'])."\" url=\"{$GLOBALS['sitepath']}schedule.php4?c=go&amp;mode_frames=1&amp;sheid={$study['sheid']}\"/>";
    }
        
}

//$week_schedule->set_bad_sheids($this->_get_bad_sheids());

$xml .= "</root>";

header("Content-type: text/xml");
echo iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'utf-8', $xml);
//echo file_get_contents('student_shedule.xml');
?>