<?
require("../1.php");
require("../courses.lib.php");
require("../metadata.lib.php");

$controller->setView('DocumentPrint');

$smarty_tpl = new Smarty_els;

$course_structure = new CourseStructure;
$course_structure->init($_GET['cid']);

$structure_for_smarty = $course_structure->get_structure();

$smarty_tpl->assign('sitepath',$sitepath);
$smarty_tpl->assign("structure", $structure_for_smarty);
$smarty_tpl->display("course_print.tpl");
$controller->setContent($smarty_tpl->fetch("course_print.tpl"));
$controller->terminate();
?>