<?php
require_once("1.php");
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');
require_once($GLOBALS['wwf'].'/teachers/organization.lib.php');
require_once($GLOBALS['wwf'].'/metadata.lib.php');

if (!defined('COURSE_STRUCTURE_TOC_MAX_CHARS')) define('COURSE_STRUCTURE_TOC_MAX_CHARS',50);
if (!defined('COURSE_ITEM_MOVE_STEP')) define('COURSE_ITEM_MOVE_STEP',5);

$CID = (int) $_GET['CID'];

if (isset($_REQUEST['groupstep'])) {
    if ($_REQUEST['groupstep'] <= 0) $_REQUEST['groupstep'] = COURSE_ITEM_MOVE_STEP;
}

class DocumentFrame_Workarea extends  DocumentFrame {

}

//$GLOBALS['controller']->setView('DocumentFrame_Workarea');
$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setLink('m060108',array($CID),_('Вы действительно желаете очистить программу курса?'));
$GLOBALS['controller']->disableNavigation();
$subtitle = _('Программа курса');
$GLOBALS['controller']->setHeader("<span style='font-size: 0.8em'>{$subtitle}</span>");
$GLOBALS['controller']->setHelpSection('editprogram-destination');

$GLOBALS['controller']->captureFromOb(CONTENT);

/*
echo "
     <form action=\"\" method=\"POST\" name=\"settings\">
     <table border=0 cellpadding=0 cellspacing=0>
     <tr>
         <td style=\"background: url({$GLOBALS['sitepath']}images/elementbox.gif) no-repeat top left; width: 175px; height: 32px;\" align=center>
         Очистить программу&nbsp;&nbsp;
         <a href='{$GLOBALS['sitepath']}teachers/edit_navigation.php?make=delete_all&CID={$CID}' onClick=\"javascript: return(confirm('Вы действительно желаете очистить программу курса?'))\"><img title=\""._('Очистить программу курса')."\" border=0 align=absmiddle src=\"{$GLOBALS['sitepath']}images/icons/delete.gif\" hspace=2 align='top' style='padding-bottom:5px;'>
         </td>
     </tr>
     </table>
     </form>
     ";*/

//чиним дерево на случай если оно повреждено
fixOganizationTree($CID);


$tree = new CCourseWorkAreaTreeDisplay();
$tree->setFreeMode(true);
$tree->setConstructorMode(true);
$tree->setLevel(2);
$tree->initialize($CID);

//echo "<ul id=\"toc\" class=\"main\">";
echo "<table width=100% border=0 cellpadding=0 cellspacing=0 class=\"treeview\"><tr><td>";
echo "<div id=\"toc\" class=\"tree-view course-structure\" url=\"{$sitepath}course_constructor_workarea_branch.php?cid={$_GET['CID']}&preview=false\">";
$tree->display();
echo "
</div></td></tr>
<tr><td><br><br>
	<div class=\"button\" style=\"float: right;\"><a href=\"javascript:void(0);\" onclick=\"top.location.href = 'courses.php4'; return false;\">"._("Готово")."</a></div>
</td></tr>
</table>
";

/*echo "
<script type=\"text/javascript\">
<!--
parent.modulesFrame.location.href = '{$sitepath}course_constructor_modules.php?CID=$CID';
//-->
</script>
";*/

$GLOBALS['controller']->captureStop(CONTENT);

//$GLOBALS['controller']->captureFromOb(CONTENT_COLLAPSED);
//$GLOBALS['controller']->captureStop(CONTENT_COLLAPSED);

$GLOBALS['controller']->terminate();

?>