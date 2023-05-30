<?php
exit(); // todo: навигацию по курсу "вперед-назад" в этом фрейме
require_once("1.php");

if (!$_SESSION['s']['login']) die();

$cid = (int) $_GET['cid'];

$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();
$smarty->assign('cid',$cid);
$smarty->assign('skin_url',$GLOBALS['controller']->view_root->skin_url);
$smarty->assign('root_url',$GLOBALS['controller']->view_root->root_url);
$smarty->assign('disabled_favorites', (($s['perm'] > 1) || (getField('Courses','tpo_type','CID',(int) $cid))));
echo $smarty->fetch('course_structure_bottom.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>