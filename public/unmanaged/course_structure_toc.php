<?php
require_once("1.php");
require_once($wwf.'/teachers/organization.lib.php');
require_once($GLOBALS['wwf'].'/lib/classes/Module.class.php');
require_once($GLOBALS['wwf'].'/lib/classes/Task.class.php');
require_once($GLOBALS['wwf'].'/lib/classes/Glossary.class.php');
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

define("_PAGE_CST", 1);
if (!defined('COURSE_STRUCTURE_TOC_MAX_CHARS')) define('COURSE_STRUCTURE_TOC_MAX_CHARS',50);


//$GLOBALS['controller']->setView('DocumentFrame_Structure');
$GLOBALS['controller']->setView('DocumentFrame');
$GLOBALS['controller']->disableNavigation();
if ($_SESSION['s']['perm']>1) {
    $GLOBALS['controller']->view_root->return_path = $GLOBALS['sitepath'].'courses.php4';
} else {
    $GLOBALS['controller']->view_root->return_path = $GLOBALS['sitepath'].'index.php';
}

$GLOBALS['controller']->addFilter(_('Развернуть до уровня'), 'level', array(0,1,2,3), 0);
$GLOBALS['controller']->setLink('m130117', array($_GET['CID']));

if (!defined("LOGO_URL")) {
    define("LOGO_URL", true);
}

$GLOBALS['controller']->captureFromOb(CONTENT_EXPANDED);

//$smarty = new Smarty_els();
//$smarty->assign('CID', $_GET['CID']);
//$smarty->assign('sitepath',$sitepath);
//echo $smarty->fetch('course_structure_toc_irkut.tpl');

if (!isset($_REQUEST['level'])) {
    $currentLevel = 2;
} else {
    $currentLevel = $_REQUEST['level'];
}

//$cond_cid = ($_GET['CID']) ? "WHERE cid = '".(int) $_GET['CID']."'" : "";
//$sql = "SELECT MAX(level)+2 as level FROM organizations {$cond_cid}"; // + 1 уровень - курса; + 1 для сравнения в человеческом формате
//$res = sql($sql);
//if ($row = sqlget($res)) {
//    $maxLevel = $row['level'];
//}

//чиним дерево на случай если оно повреждено
fixOganizationTree($_GET['CID']);

$maxLevel = 99;

$currentLevel = ($currentLevel > $maxLevel || !$currentLevel)?$maxLevel:$currentLevel;

$tree = new CCourseContentTreeDisplay();
if ($_SESSION['s']['perm'] > 1) {
	$tree->setFreeMode(true);
	//$tree->setConstructorMode(true);
	$tree->setPreviewMode();
} elseif (empty($_GET['CID'])) {
	$tree->setFreeMode(true);
//	$maxLevel = 3;
}

if (isset($_GET['oid']) && $_GET['oid']) {
    $tree->setLevel($currentLevel + 1 + (int) getField('organizations', 'level', 'oid', (int) $_GET['oid']));
} else {
    $tree->setLevel($currentLevel);
}

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
echo "<table width=100% border=0 cellpadding=0 cellspacing=0>";
echo "<tr><td><div id=\"toc\" class=\"tree-view course-structure\" url=\"{$sitepath}course_structure_toc_branch.php?cid={$_GET['CID']}&preview=true\">";
//echo "<ul id=\"toc\" class=main>\n";

if (isset($_GET['oid']) && $_GET['oid']) {
    $tree->filterByBranch(ID_ORG, $_GET['oid'], false);
    $tree->tree['children'] = array('cid'.$_GET['CID'] => array('level' => 1, 'attributes' => array('oid' => 'cid'.$_GET['CID']), 'children' => array($_GET['oid'] => $tree->tree)));
}

$tree->display();

//echo "</ul>";
echo "</div>";
echo "</td></tr></table>";

/*if (CGlossaryWord::isWordsExist($CID)) {
    echo "<p><a title=\""._('Глоссарий')."\" href=\"javascript:void(0);\" onClick=\"window.open('{$GLOBALS['sitepath']}glossary.php?mini&cid={$CID}','glossary','toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=300');\"><img src=\"{$GLOBALS['sitepath']}images/icons/book.gif\" border=0 alt=\""._("Глоссарий")."\" align=absmiddle hspace=5>"._("Глоссарий")."</a>";
}
*/
$GLOBALS['controller']->captureStop(CONTENT_EXPANDED);

$GLOBALS['controller']->captureFromOb(CONTENT_COLLAPSED);
echo "<a href=\"javascript:void(0);\" onclick=\"parent.leftFrame.expanded(true);\" title=\""._("Развернуть программу курса")."\"><img border=0 src=\"{$sitepath}images/course_program.gif\"></a>";
$GLOBALS['controller']->captureStop(CONTENT_COLLAPSED);


//$GLOBALS['controller']->view_root->return_path = $sitepath;

$GLOBALS['controller']->view_root->disable_search = true;
$GLOBALS['controller']->view_root->disable_favorites = true;

//$smarty->clear_all_assign();
//echo $smarty->fetch('course_structure_js.tpl');

$GLOBALS['controller']->terminate();
exit();

?>