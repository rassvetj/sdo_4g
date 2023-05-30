<?php
require_once("1.php");

$CID = (int) $_GET['CID'];

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(get_course_title($CID));
if (isset($_GET['show_material'])) {
    $GLOBALS['controller']->captureFromOb(CONTENT);
    echo "<OBJECT ID='Runner' width='0' height='0' CLASSID='CLSID:E3E38406-88AB-11D3-B551-080030810427'></OBJECT>";
    $GLOBALS['controller']->captureStop(CONTENT);
} else {
    if (($_SESSION['s']['perm']>1) && !isset($_GET['oid'])) {
        $GLOBALS['controller']->view_root->return_path = $GLOBALS['sitepath'].'courses.php4';
    } else {
        $GLOBALS['controller']->view_root->return_path = $GLOBALS['sitepath'].'index.php';
    }
}
$GLOBALS['controller']->enableNavigation();
$GLOBALS['controller']->terminate();
?>