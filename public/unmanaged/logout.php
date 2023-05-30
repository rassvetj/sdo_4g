<?php
require_once("1.php");
require_once($wwf."/index.lib.php4");

$exit = (isset($_GET['exit'])) ? $_GET['exit'] : "" ;

if ($exit) {
    user_logout($s['mid'],$s['sessid']);
    unset($s);
    $dean=0;
    $admin=0;
    $teach=0;
    $stud=0;
}

?>