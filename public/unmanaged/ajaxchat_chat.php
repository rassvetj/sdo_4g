<?php

require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/Chat.class.php');
if (!$s['login'] || empty($s['mid'])) exitmsg(_("Пожалуйста, авторизуйтесь"), $sitepath);

if ($GLOBALS['controller']->isAjaxRequest()) {
    header(sprintf('Content-type: text/html; charset=%s;', $GLOBALS['controller']->lang_controller->lang_current->encoding));
    $GLOBALS['controller']->setView('DocumentTab');
}

$GLOBALS['controller']->captureFromOb(CONTENT);
//$rid = (eregi('schedule_[0-9]+',$_REQUEST['rid'])?substr(strrchr($_REQUEST['rid'], "_"), 1):intval ($_REQUEST['rid']);
$tmp_array = array();
/*
if (eregi('schedule_[0-9]+', $_GET['rid'])){
    $tmp_array['idshedule'] = substr(strrchr($_REQUEST['rid'], "_"), 1);
    $_SESSION['s']['ajaxchat']['idshedule'] = $tmp_array['idshedule'];
}
*/
//var_dump($_SESSION['s']); exit; //echo '<hr />';var_dump($GLOBALS); exit;

if (isset($_REQUEST['subject']) && isset($_REQUEST['subject_id'])) {
    $_REQUEST['rid'] = $_REQUEST['subject'].'_'.$_REQUEST['subject_id'];
} else {
    $_REQUEST['rid'] = 0;
}

$tmp_array['rid'] = $_REQUEST['rid'];
$tmp_array['cid'] = (int) $_REQUEST['cid'];

//$chat = CChat::factory(array('rid' => $rid, 'cid' => $cid));
$chat = CChat::factory($tmp_array);
$chat->initRoom();
$chat->displayRoom();

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>