<?php
require("1.php");
require_once("metadata.lib.php");
require_once("Schedule.class.php");
require_once("Schedules.class.php");
require_once("Teacher.class.php");
require_once("Teachers.class.php");
require_once("Course.class.php");

if(!isset($_POST['CID'])) {
        $cid = 1;
}

if(isset($_POST['date'])) {
   $from = $_POST['date']['from']['year']."-".$_POST['date']['from']['month']."-".$_POST['date']['from']['day']." 00:00:00";
   $to = $_POST['date']['to']['year']."-".$_POST['date']['to']['month']."-".$_POST['date']['to']['day']." 00:00:00";
   
   $from_for_smarty = month_name((integer)$_POST['date']['from']['month']) . " " . $_POST['date']['from']['year'];
   $to_for_smarty = month_name((integer)$_POST['date']['to']['month']) . " " . $_POST['date']['to']['year'];
}


//Определяем всех пиплов для которых будем выводить нагрузку
if($_POST['mid'] == 0) {
   //Если отчет выводится для всех
   $mids_array = Teachers::get_mids_array($cid);
   $is_report_for_all_people = true;
}
else {
   //Если отчет выводится для конкретного человека
   $mids_array = array($_POST['mid']);
   $is_report_for_all_people = false;
}

//Определяем все занятия для которых будем выводить нагрузку(все занятия на данном курсе)
$course = new Course;
$course->init($cid);
$sheids_array = $course->get_sheids_array_at_period($from, $to);

//Набиваем массив с двумя индексами mid и sheid а значение количество часов этого занятия
//если человек не ведет этого занятия то пусто
//и заодно заполняем массив занятий для шаблона smarty
$count_hours = array();
$types_of_studies_for_smarty = array();
foreach($sheids_array as $sheid) {
        $schedule = new Schedule;
        $schedule->init($sheid);
        if($is_report_for_all_people) {
           //Если для всех пиплов то месяцы нас не интерсуют
           $count_hours[$schedule->get_teacher_mid()][$schedule->get_type_id()] += $schedule->get_count_of_hours();
        }
        else {
           //Если для конкретного человека то нас интересует еще и месяцы, просто smarty отдадим немного другой массив
           $temp_date = explode(".", $schedule->get_date());
           $year_number = $temp_date[2];
           $month_number = trim($temp_date[1], "0");
           $count_hours[$year_number][$month_number][$schedule->get_type_id()] += $schedule->get_count_of_hours();
        }
//        $types_of_studies_for_smarty[$schedule->get_type_id()] = array("name" => $schedule->get_type());
        unset($schedule);
}
// все типы занятий, даже пустые
$types_of_studies_for_smarty = Schedules::get_all_types();

//Набиваем массив пиплов для smarty
$teachers_for_smarty = array();
foreach($mids_array as $mid) {
        $teacher = new Teacher;
        $teacher->init($mid);
        $teachers_for_smarty[$mid] = array("first_name" => $teacher->get_first_name(),
                                           "last_name" =>  $teacher->get_last_name(),
                                           "rank" => $teacher->get_rank());
}

$course_for_smarty = array("name" => $course->get_name(),
                           "department" => $course->get_department());

$smarty_tpl = new Smarty_els;
$smarty_tpl->assign("course", $course_for_smarty);
$smarty_tpl->assign("types_of_studies", $types_of_studies_for_smarty);
$smarty_tpl->assign("count_of_types", count($types_of_studies_for_smarty));
if($is_report_for_all_people) {
   $smarty_tpl->assign("teachers", $teachers_for_smarty);
}
else {
   $smarty_tpl->assign("teacher", $teachers_for_smarty[$_POST['mid']]);
}
$smarty_tpl->assign("count_hours", $count_hours);

if(!$is_report_for_all_people) {
    //здесь определяется массив который будет выводится в правой колонке
    //массив временных точек
    $temp = explode(" ", $from);
    $from_date_array = explode("-", $temp[0]);
    $temp = explode(" ", $to);
    $to_date_array = explode("-", $temp[0]);
    $time_marks_for_smarty = array();
    if($from_date_array[0] == $to_date_array[0]) {
          for($i = $from_date_array[1]; $i <= $to_date_array[1]; $i++) {
              $time_marks_for_smarty[$from_date_array[0]][(int) $i] = month_name($i);
          }
    }
    else {
          for($i = $from_date_array[1]; $i <= 12; $i++) {
              $time_marks_for_smarty[$from_date_array[0]][(int)$i] = month_name($i);
          }
          for($i = $from_date_array[0]+1; $i < $to_date_array[0]; $i++) {
              for($j = 1; $j <= 12; $j++) {
                  $time_marks_for_smarty[$i][(int)$j] = month($j);
              }
          }
          for($i = 1; $i <= $to_date_array[1]; $i++) {
              $time_marks_for_smarty[$to_date_array[0]][(int)$i] = month_name($i);
          }
    }
    //Здесь подсчитываем суммарную нагрузку препода за месяц по все типам занятий
    $sum_count_hours = array();
    foreach($count_hours as $year => $month_count_hours) {
            foreach($month_count_hours as $month => $hours) {
                    $sum = 0;
                    foreach($hours as $type_id => $count) {
                            $sum += $count;
                    }
                    $sum_count_hours[$year][$month] = $sum;
            }
    }
    $smarty_tpl->assign("from", $from_for_smarty);
    $smarty_tpl->assign("to", $to_for_smarty);
    $smarty_tpl->assign("sum_count_hours", $sum_count_hours);
    $smarty_tpl->assign("time_marks", $time_marks_for_smarty);
    $smarty_tpl->display("individual_teachers_load_print.tpl");
}
else {
    $smarty_tpl->display("teachers_load_print.tpl");
}
?>