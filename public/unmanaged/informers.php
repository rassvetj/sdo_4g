<?php
require_once('1.php');

$controller->captureFromOb(CONTENT);

$smarty = new Smarty_els();
$smarty->assign('sitepath',$sitepath);
echo $smarty->fetch('informers.tpl');

$controller->captureStop(CONTENT);
$controller->terminate();

?>