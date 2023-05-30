<?php
require_once('../1.php');

if (!$_SESSION['s'][login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");


$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();
$smarty->assign('sitepath',$sitepath);
echo $smarty->fetch('check_sw.tpl');

$GLOBALS['controller']->setHelpSection('check');
$GLOBALS['controller']->setHeader(_('Проверка установленного ПО'));
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->page_id = PAGE_INDEX;

$GLOBALS['controller']->terminate();

?>