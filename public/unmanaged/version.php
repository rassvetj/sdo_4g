<?php
require_once('1.php');
$controller->setView('DocumentBlank');
$smarty = new Smarty_els();
if (defined('ELS_BUILD') && ELS_BUILD && (ELS_BUILD != '24155479')) {
	$smarty->assign('biuld', str_replace('-', '', ELS_BUILD));
} else {
	$smarty->assign('build', date('ymd', filemtime('1.php')));
}

$modules = array();
if (defined('ALLOW_SWITCH_2_CMS')) $modules[] = 'eLearning CMS';
if (defined('ALLOW_SWITCH_2_AT')) $modules[] = 'Assessment Tools';
$smarty->assign('modules', $modules);
$smarty->assign('year', date('Y'));
$message = $smarty->fetch('version.tpl');

$controller->setMessage($message, JS_GO_URL, 'index.php');
$controller->terminate();
?>