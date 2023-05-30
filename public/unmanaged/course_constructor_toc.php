<?php
require_once("1.php");
require_once($wwf.'/teachers/organization.lib.php');
require_once($GLOBALS['wwf'].'/lib/classes/Module.class.php');
require_once($GLOBALS['wwf'].'/lib/classes/Task.class.php');
//require_once($GLOBALS['wwf'].'/lib/classes/Glossary.class.php');
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

if (!defined('COURSE_STRUCTURE_TOC_MAX_CHARS')) define('COURSE_STRUCTURE_TOC_MAX_CHARS',50);

$GLOBALS['controller']->setView('DocumentPopup');
if ($_SESSION['s']['perm'] == 3) {
	$subtitle = _('Все модули');
	$GLOBALS['controller']->setHelpSection('editprogram-source-all');
} else {
	$subtitle = _('Модули курса');
	$GLOBALS['controller']->setHelpSection('editprogram-source-sandbox');
}
$GLOBALS['controller']->setHeader(/*_('Редактирование структуры курса<br>') .*/ "<span style='font-size: 0.8em'>{$subtitle}</span>");
$GLOBALS['controller']->disableNavigation();
$GLOBALS['controller']->captureFromOb(CONTENT);

$GLOBALS['table_options'] = 'options_cms';
$filename = COption::get_value('logo');
$GLOBALS['table_options'] = 'OPTIONS';
$GLOBALS['controller']->view_root->logo_url = (!empty($filename) && file_exists('cms/options/' . $filename)) ? $sitepath . 'cms/options/' . $filename : $GLOBALS['controller']->view_root->skin_url . "/images/logo.gif";


echo "
<script type=\"text/javascript\">
<!--
function setCurrentItem(id) {
    var elm;
    if (elm = document.getElementById(id)) {
        elm.stylet = 'font-weight = bold';
    }
}
//-->
</script>
";

if (!isset($_REQUEST['level'])) {
    $currentLevel = 1;
} else {
    $currentLevel = $_REQUEST['level'];
}

$GLOBALS['controller']->addFilter(_('Развернуть до уровня'), 'level', array(0,1,2,3), 0, false, '0', true, '', "<input type=\"hidden\" name=\"destinationCID\" value=\"{$_GET['destinationCID']}\">");

//$cond_cid = ($_GET['cid']) ? "WHERE cid = '".(int) $_GET['CID']."'" : "";
//$sql = "SELECT MAX(level)+2 as level FROM organizations {$cond_cid}"; // + 1 уровень - курса; + 1 для сравнения в человеческом формате
//$res = sql($sql);
//if ($row = sqlget($res)) {
//    $maxLevel = $row['level'];
//}
$maxLevel = 3;

if ($currentLevel > $maxLevel) $currentLevel = $maxLevel;

$tree = new CCourseContentTreeDisplay();
$tree->setFreeMode(true);
$tree->setConstructorMode(true);
$tree->setLevel($currentLevel);
$tree->initialize($_GET['CID']);

echo "
<script type=\"text/javascript\" language=\"JavaScript\">
<!--
function courseItemClick(elm) {
        jQuery(elm)
            .parents('.tree-view.course-structure')
            .find('a.current-item')
            .removeClass('current-item')
            .end()
            .end()
            .addClass('current-item');
}
//-->
</script>
";
echo "<OBJECT ID='Runner' width='0' height='0' CLASSID='CLSID:E3E38406-88AB-11D3-B551-080030810427'></OBJECT>";

echo "<table width=100% border=0 cellpadding=0 cellspacing=0 class=\"treeview\">";
//echo "<tr><td id=\"level_select\">";
//echo "</td></tr>";
echo "<tr><td><div id=\"toc\" class=\"tree-view course-structure\" url=\"{$sitepath}course_structure_toc_branch.php?cid={$_GET['CID']}\">";
//echo "<ul id=\"toc\" class=\"main\">";

$tree->display();

//echo "</ul>";
echo "</div>";
echo "</td></tr></table>";

echo "
<script type=\"text/javascript\">
<!--
window.organizationItemId = 0;
//-->
</script>";

$GLOBALS['controller']->captureStop(CONTENT);

$GLOBALS['controller']->view_root->return_path = $sitepath;

$GLOBALS['controller']->terminate();
exit();

?>