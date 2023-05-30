<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/Chat.class.php');
if (!$s['login'] || empty($s['mid'])) exitmsg(_("Пожалуйста, авторизуйтесь"), $sitepath);

$GLOBALS['controller']->setView('DocumentTab');
$GLOBALS['controller']->captureFromOb(CONTENT);

$rid = (int) $_REQUEST['rid'];
$cid = (int) $_REQUEST['cid'];

$chat = CChat::factory(array('rid' => $rid, 'cid' => $cid));
$chat->displayUsers();

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>