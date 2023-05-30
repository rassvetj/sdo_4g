<?php

require_once("1.php");

$GLOBALS['controller']->setView('DocumentBlank');

unset($controller->page_id);
//if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

$mid = $_REQUEST['mid'];

$html = getUserCard($mid);

$GLOBALS['controller']->setHeader(_("Карточка пользователя"));
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();

?>