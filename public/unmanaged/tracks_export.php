<?php
require_once("1.php");
require_once('lib/classes/CourseContent.class.php');
require_once("lib/classes/Track.class.php");
require_once("test.inc.php");
require_once("teachers/organization.lib.php");
require_once("lib/classes/WeekSchedule.class.php");
require_once("lib/classes/Schedule.class.php");
require_once('lib/classes/ProgressBar.class.php');
require_once("schedule.lib.php");

if (!$dean) login_error();

$GLOBALS['controller']->captureFromOb(CONTENT);

$tec = new CTrackExportController();
if ($_POST['post'] == 'generate') {
    $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
    $GLOBALS['progress']->setAction(_('Инициализация'));
    
    switch($_POST['mode']) {
           case '1':
              $tec->initialize_response();
              $tec->execute();
              if (is_array($tec->messages) && count($tec->messages)) {
                   $controller->setMessage('<table><tr><td>' . _("Дистрибутивы курсов успешно созданы в каталоге <b>temp</b> сервера:") . '</td></tr><tr><td nowrap><li>' . implode('<li>', $tec->messages) . "</td></tr></table>", JS_GO_URL, 'tracks_export.php');
              } else {
                   $controller->setMessage(_('Не создано ни одного дистрибутива специальности'), JS_GO_URL, 'tracks_export.php');
              }
           break;
           case 2:
               $cec = new CCourseExportController();
               $cec->initialize_response();
               $cec->execute();
               if (is_array($cec->messages) && count($cec->messages)) {
                   $controller->setMessage('<table><tr><td>' . _("Дистрибутивы курсов успешно созданы в каталоге <b>temp</b> сервера:") . '</td></tr><tr><td nowrap><li>' . implode('<li>', $cec->messages) . "</td></tr></table>", JS_GO_URL, 'tracks_export.php');
               } else {
                   $controller->setMessage(_('Не создано ни одного дистрибутива курсов'), JS_GO_URL, 'tracks_export.php');
               }               
           break;     
    }
    
    $GLOBALS['progress']->saveProgress(-1);    
    $GLOBALS['progress']->unlink();
} else {
    $tec->initialize_request();
    $tec->display();
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>