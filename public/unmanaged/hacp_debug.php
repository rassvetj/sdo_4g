<?php
require_once('1.php');

if (!$_SESSION['s'][login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");


$GLOBALS['controller']->captureFromOb(CONTENT);

switch($_REQUEST['action']) {
    case 'clean':
        sql("DELETE FROM hacp_debug");
        refresh($sitepath.'hacp_debug.php');
        $GLOBALS['controller']->captureStop(CONTENT);
        $GLOBALS['controller']->terminate();
        exit();
        break;
}

$smarty = new Smarty_els();

$sql = "SELECT * FROM hacp_debug ORDER BY `date` DESC, id DESC";
$res = sql($sql);
$messages = array();
while($row = sqlget($res)) {
    if (!empty($row['message'])) {
        $row['message'] = unserialize($row['message']);
    }
    $messages[] = $row;
}

$smarty->assign('messages',$messages);
echo $smarty->fetch('hacp_debug.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>