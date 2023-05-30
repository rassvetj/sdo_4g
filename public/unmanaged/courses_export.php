<?php
require_once('1.php');
require_once("lib/classes/Track.class.php");
require_once("test.inc.php");
require_once("teachers/organization.lib.php");
require_once('lib/classes/WeekSchedule.class.php');
require_once('lib/classes/Schedule.class.php');
require_once('schedule.lib.php');

if (!$dean) login_error();

$GLOBALS['controller']->captureFromOb(CONTENT);

$cec = new CCourseExportController();

if ($_POST['action']=='generate') {
    $cec->initialize_response();
    $cec->execute();
	if (is_array($cec->messages) && count($cec->messages)) {
		$controller->setMessage('<table><tr><td>Дистрибутивы курсов успешно созданы в каталоге <b>temp</b> сервера:<br><li>' . implode('<li>', $cec->messages) . "</td></tr></table>", JS_GO_URL, 'courses_export.php');
	} else {
		$controller->setMessage('Не создано ни одного дистрибутива курсов', JS_GO_URL, 'courses_export.php');
	}
    
} else {
	$cec->initialize_request();
    $cec->display();
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>