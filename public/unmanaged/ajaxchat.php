<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/Chat.class.php');
if (!$s['login'] || empty($s['mid'])) exitmsg(_("Пожалуйста, авторизуйтесь"), $sitepath);

header(sprintf('Content-type: text/html; charset=%s;', $GLOBALS['controller']->lang_controller->lang_current->encoding));

$rid = $view = 0;

if (isset($_GET['sheid'])) {
    $rid = 'schedule_'.(int) $_GET['sheid'];
}

if (isset($_GET['view']) && ($_GET['view'] == 'blank')) {
    $view = 1;
}

$chat = CChat::factory(
    array(
        'rid'  => $rid,
        'cid'  => 0,
        'view' => $view
    )
);

$chat->display();


?>