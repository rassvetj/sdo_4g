<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

header('Content-Type: text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding);

if (!defined('COURSE_STRUCTURE_TOC_MAX_CHARS')) define('COURSE_STRUCTURE_TOC_MAX_CHARS',50);
$cid = (integer)$_GET['cid'];
$id  = $_GET['id'];

$tree = new CCourseWorkAreaTreeDisplay();
$tree->setFreeMode(true);
$tree->setFreeMode(true);

if (ereg("^([a-z]+)([0-9]+)$", $id, $arr)) {
    $type = $arr[1];
    $id = $arr[2];
    if ($type == ID_ORG) {
        if (empty($cid)){
            $res = sql("SELECT cid FROM organizations WHERE oid='{$id}'");
            if ($row = sqlget($res)){
            	$cid = $row['cid'];
            } else {
                exit();
            }
        }
        $tree->initialize($cid);	
    } elseif ($arr[1] == ID_COURSE){
        $tree->initialize($id);	
    }
    $tree->filterByBranch($type, $id);
    $tree->display();
}
?>