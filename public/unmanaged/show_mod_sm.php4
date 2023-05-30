<?php
require_once('1.php');
if(isset($_GET['display'])) {
$smarty_tpl = new Smarty_els;	
$msg = "<center>"._("Выберите учебный модуль")."</center>";
$smarty_tpl->assign("message", $msg);
$smarty_tpl->assign("sitepath", $sitepath);
//$html = $smarty_tpl->fetch("modules_alert.tpl"); 
//echo $html;
$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->setMessage(_("Выберите учебный модуль"),JS_GO_URL,"{$PHP_SELF}?display=1");
$GLOBALS['controller']->terminate();
}
?>