<?php
//require_once("../1.php");
require_once("manage_course.lib.php4");
require_once("organization.lib.php");

$GLOBALS['controller']->setView('DocumentContent');

//if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
//if (($s['perm']<2) || !isset($_GET['CID'])) login_error();

$CID = (int) $_GET['CID'];

if ($CID && is_course_locked($CID)) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Электронный курс заблокирован. Данная операция невозможна"),JS_GO_URL,$GLOBALS['sitepath'].'course/index/index/course_id/'.$CID);
    $GLOBALS['controller']->terminate();
    exit();
}

//if (($s['perm']!=3) && !in_array($CID,$s['tkurs'])) exitmsg('У вас нет прав редактировать данный курс',"/?$sess");

$GLOBALS['controller']->setHelpSection('import');

echo show_tb();
echo ph(_("Импортирование курса")." ".cid2title($CID));

// Форма испортирования курсов eAuthor
$smarty = new Smarty_els();

$smarty->assign('sitepath',$GLOBALS['sitepath']);
$smarty->assign('progressId',md5($_SESSION['s']['mid'].session_id()));
$smarty->assign('progressTitle',_('Импорт курса'));
$smarty->assign('progressAction',_('Загрузка и обработка файлов курса'));
$smarty->assign('progressComments',_('После загрузки файлов требуется время на распаковку и обработку файлов курса'));
$smarty->assign('CID',$CID);
$smarty->assign('okbutton',okbutton("OK", "", "ok", "if (jQuery(\"#import_type\").get(0).value == 0) {alert(\""._('Выберите тип загружаемого курса')."\"); return false;}"));
$smarty->assign('template_options',$template_options);
$smarty->assign('refresh_checked',$refresh_checked);
$smarty->assign('write_checked',$write_checked);

$GLOBALS['controller']->setHeader(_("Импорт курса").' '.cid2title($CID));
$GLOBALS['controller']->captureFromOb(CONTENT);
echo $smarty->fetch('course_import.tpl');
$GLOBALS['controller']->captureStop(CONTENT);
if (isset($_GET['msg'])) $GLOBALS['controller']->setMessage(strip_tags(base64_decode($_GET['msg'])));

$GLOBALS['controller']->terminate();
?>