<?php
require_once("../1.php");
require_once($wwf."/index.lib.php4");

if (!$stud) login_error();

$s['offline_course_path'] = user_offline_courses_paths($s['mid']);
if (is_array($s['offline_course_path'])) {
    foreach($s['offline_course_path'] as $v) {
        if (!empty($v)) {
            $s['offline_courses_enabled'] = true;
            break;
        }
    }
}

echo show_tb();
echo ph(_("Локальные копии курсов"));
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_("Локальные копии курсов"));
// ===============================================================

   /**
   * Если студент то вывод формы редактирования пути к офф-лайн курсу
   */

$offline_course_form_msg = '';
if (isset($_POST['saveOfflineCoursePath']) && ($_POST['saveOfflineCoursePath']=='saveOfflineCoursePath')) {

	if ($_POST['offline_courses_enabled'] !== '1') {
		sql("UPDATE Students SET offline_course_path=NULL WHERE MID='".(int) $s['mid']."'");
		unset($s['offline_course_path']);
		unset($_SESSION['s']['offline_courses_enabled']);
	} else {

		$offlineCoursesPaths = isset($_POST['offlineCoursePath']) ? $_POST['offlineCoursePath']: '';

		if (is_array($offlineCoursesPaths) && count($offlineCoursesPaths)) {

			foreach ($offlineCoursesPaths as $k => $v) {

				$v = strip_tags(trim($v));

				// как минимум там должна быть папка media
				if (($pos = strpos($v, "media")) === false) {
					continue;
				} else {
					$v = substr($v, 0, $pos-1);
				}

				$sql = "UPDATE Students SET offline_course_path=".$GLOBALS['adodb']->Quote($v)." WHERE MID='".(int) $s['mid']."' AND CID='".(int) $k."'";
				sql($sql);

				$s['offline_course_path'] = user_offline_courses_paths($s['mid']);
				$offline_course_form_msg = "<b><font color=green>"._("Пути к локальным версиям курсов сохранены")."</font></b>";
				$_SESSION['s']['offline_courses_enabled'] = true;
				$success = true;

			}
		}
	}
if (!$success) $offline_course_form_msg = "<b><font color=red>"._("Пути к локальным версиям курсов не сохранились. Проверьте правильность указания папки курса и наличия в пути папки media.")."</font></b>";
}

$offlineCourse = '';
if ($stud && !$admin && !$dean && !$teach) {

	$stCourses = getCourses($s['mid']);
	if ($s['offline_courses_enabled']) {
		$checked = "checked";
	} else {
		$disabled = "disabled";
	}
	$coursesInputs .= "<tr><td colspan=2><input type='checkbox' {$checked} name='offline_courses_enabled' onClick=\"javascript:switch_inputs(this.checked)\" value='1'>&nbsp;"._("использовать локальные копии курсов")."<br><span style='font-size:8px;'>"._("(укажите на любой файл из локально установленной копии курса)")."</span></td></tr>";

	foreach ($stCourses as $k => $v) {

		$coursesInputs .= "<tr><td nowrap valign='top' align='right'>$v:</td><td width='100%'><input type=\"file\" name=\"offlineCoursePath[$k]\" id=\"offlineCoursePath_{$k}\" size=60 {$disabled}>";
		if (strlen($s['offline_course_path'][$k])) $coursesInputs .= "<br><span style='font-size:8px;padding:5px;'>"._("Выбран каталог:")." ".$s['offline_course_path'][$k]."</span></br>";
		$coursesInputs .= "</td></tr>";
	}

	$offlineCourse = loadtmpl('st-offline-course-form.html');
	$offlineCourse = str_replace('[COURSES-INPUTS]', $coursesInputs, $offlineCourse);
	$offlineCourse = str_replace('[MESSAGE]', $offline_course_form_msg, $offlineCourse);
	$offlineCourse = str_replace('[OKBUTTON]', okbutton(), $offlineCourse);
}

echo $offlineCourse;
//$html=str_replace("[OFFLINE-COURSE-FORM]",$offlineCourse,$html);
// ===============================================================

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->setMessage(strip_tags($offline_course_form_msg));
echo show_tb();

function getCourses( $mid ){
	// формирует массив названий курсов
	$q="SELECT Courses.CID as CID, Courses.Title as Title
           FROM Courses, Students
           WHERE Courses.CID=Students.CID
             AND Students.MID=$mid
                  ORDER BY Courses.Title
 ";
	//Courses.CID=Students.CID
	// AND
	//Students.cgid, Students.Registered
	$r=sql( $q, "ERRGETMIDCOURSES");
	//  echo "<H1>$q</H1>";
	while ( $rr=sqlget( $r ) ){
		$cid=$rr[CID];
		$courses[ $cid ]=$rr[Title];

		//    $c=$rr[Title];
		//    echo $courses[ $cid ]."!".$cid."! $mid <BR>";
	}
	sqlfree($r);
	return( $courses );
}

?>