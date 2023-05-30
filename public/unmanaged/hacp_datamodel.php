<?php

//require_once("1.php");
require_once($GLOBALS['wwf']."/lib/hacp/hacp.class.php");

//$_POST = array('command' => 'GetParam', 'session_id' => 280, 'version' => '2.0');
//$_GET = array('subject_id' => 17, 'lesson_id' => 362);

ob_start();

print_r($_POST);

print_r($_GET);

$content = ob_get_contents();
$fp = fopen('hacp_track.log','w+');
fwrite($fp,$content);
fclose($fp);
ob_clean();

if (defined('HACP_DEBUG') && HACP_DEBUG) {
    $sql = "INSERT INTO hacp_debug
            (message, date, direction)
            VALUES (".$GLOBALS['adodb']->Quote(serialize($_POST)).",".$GLOBALS['adodb']->DBTimestamp(date("Y-m-d H:i:s")).", '0')";
    sql($sql);
}

header('Content-type: text/plain');

$tracking = new CHACP_Tracking();
$tracking->init();

?>