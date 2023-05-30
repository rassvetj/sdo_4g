<?php
require_once('1.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);

unset($_SESSION['s']['orgstructure']['current']);

$smarty = new Smarty_els();
$smarty->assign('sitepath',$sitepath);
$smarty->assign('page_id', $controller->page_id);
echo $smarty->fetch('orgstructure.tpl');

//незабудем сохраниться
$GLOBALS['controller']->persistent_vars->terminate();

?>