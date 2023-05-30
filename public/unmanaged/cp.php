<?php
require_once('1.php');
require_once('lib/classes/Curl.class.php');
require_once('lib/classes/Connect.class.php');

if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) { 

	$id = $_REQUEST['id'];
	try {
	$cp = new ConnectProXMLApiAdapter(CONNECT_PRO_HOST, getField('People', 'Login', 'MID', $_SESSION['s']['mid']), CONNECT_PRO_DEFAULT_PASSWORD);
	$cp->enter($id);
    } catch(Exception $e) {
        $GLOBALS['controller']->setMessage($e->getMessage(), JS_GO_URL, $GLOBALS['sitepath']);
        $GLOBALS['controller']->terminate();
        exit();
    }

}

refresh($GLOBALS['sitepath']);
exit();
