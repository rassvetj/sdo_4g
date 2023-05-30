<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/Chat.class.php');
if (!$s['login'] || empty($s['mid'])) exitmsg(_("Пожалуйста, авторизуйтесь"), $sitepath);

$controller->setView('DocumentFrame');
$controller->captureFromOb(CONTENT_EXPANDED);

$rid = $_REQUEST['rid'];

$chat = CChat::factory(array('rid' => $rid, 'cid' => 0));
$chat->displayRooms();

$controller->captureStop(CONTENT_EXPANDED);
$controller->terminate();
?>