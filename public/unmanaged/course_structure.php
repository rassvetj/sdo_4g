<?php

require_once("1.php");
require_once($wwf.'/teachers/organization.lib.php');
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');
require_once($wwf.'/lib/json/json.class.php');
//require_once('lib/json/json.lib.php');
//require_once($GLOBALS['wwf'].'/lib/classes/IrkutPersonalLogger.class.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

$_SESSION['s']['structureinframe'] = true;

if (isset($_GET['oid'])) {
    $oid = (integer)$_GET['oid'];
    $res = sql("SELECT cid FROM organizations WHERE oid='{$oid}'");
    if ($row = sqlget($res)){
        $CID = $row['cid'];
    }
} else {
    $CID = (int) $_GET['CID'];
}

//CIrkutPersonalLogger::init($CID);

if (defined('ENABLE_EAUTHOR_COURSE_NAVIGATION') && ENABLE_EAUTHOR_COURSE_NAVIGATION) {
    $use_external_navigation = 'true';
}
else {
    $use_external_navigation = 'false';
}
$block_content_copy      = 'false';
$condition = ($GLOBALS['s']['perm']==1) && DISABLE_COPY_MATERIAL;
if ($condition){
	echo '<SCRIPT src="' . $sitepath . 'js/disableclick.js" language="JScript" type="text/javascript"></script>';
	$block_content_copy = 'true';
}

$_SESSION['s']['favorites']['cid'] = $CID;

$boolInFrame = MODE_IN_FRAME;
$closeSheduleWindow = true;
session_register("boolInFrame");
session_register("closeSheduleWindow");

$mainFrameUrl = $GLOBALS['sitepath']."course_structure_metadata.php?CID=$CID";

$tpo_type = 0;
/*$res = sql("SELECT tpo_type FROM Courses WHERE CID='{$CID}'");
if ($row = sqlget($res)){
    $tpo_type = $row['tpo_type'];
}
*/

/*if (!$tpo_type) {
    if (isset($oid) && CCourseContentCurrentItem::isExistsCurrentItem($CID, $oid)){
        $currentItem = $oid;
    }elseif (!($currentItem = CCourseContentCurrentItem::getCurrentItem($_SESSION['s']['mid'],$CID)) || !(CCourseContentCurrentItem::isExistsCurrentItem($CID, $currentItem))) {
        list($organization,$maxLevel)   = getFullOrganization($CID);
        for($i=0;$i<count($organization);$i++) {
            if (CCourseContentCurrentItem::isExistsCurrentItem($CID, $organization[$i]['oid'])) {
                CCourseContentCurrentItem::setCurrentItem($_SESSION['s']['mid'],$CID,$organization[$i]['oid']);
                $currentItem = $organization[$i]['oid'];
                break;
            }
        }
    }
    $bid = CCourseItemIrkut::getModuleId($currentItem);
    $tid = 0;
    if (!$bid) {
        $tid = CCourseItemIrkut::getTaskId($currentItem);
    }
    $run = 0;
    if (!$bid && !$tid) {
        $run = CCourseItemIrkut::getRunId($currentItem);
    }
    if ($currentItem[0] == '_') $bid = 1;
    $mainFrameUrl = $GLOBALS['sitepath']."lib_get.php?bid=".$bid."&cid={$CID}&oid={$currentItem}";
    if ($tid) {
        $mainFrameUrl .= "&tid={$tid}";
    }
    if ($run) {
        $mainFrameUrl .= "&run={$run}";
    }
} else {
    $mainFrameUrl = $GLOBALS['sitepath']."teachers/test_start.php";
}
*/

if ($CID && ($_SESSION['s']['perm'] <= 1) && !isset($_GET['oid'])) {
    $currentItem = CCourseContentCurrentItem::getCurrentItem($_SESSION['s']['mid'], $CID);
    if ($currentItem && CCourseContentCurrentItem::isExistsCurrentItem($CID, $currentItem)) {
        $sql = "SELECT module, vol1, vol2 FROM organizations WHERE cid = '".(int) $CID."' AND oid = '".(int) $currentItem."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $current = sqlget($res);
            if ($current['module']) {
                $mainFrameUrl = $GLOBALS['sitepath']."lib_get.php?bid={$current['module']}&cid={$CID}&oid={$currentItem}";
            } elseif ($current['vol1']) {
                $mainFrameUrl = $GLOBALS['sitepath']."lib_get.php?bid=0&tid={$current['vol1']}&cid={$CID}&oid={$currentItem}";
            } elseif ($current['vol2']) {
                $mainFrameUrl = $GLOBALS['sitepath']."lib_get.php?bid=0&run={$current['vol2']}cid={$CID}&oid={$currentItem}";
            }
        }
    }
}
/*
//попытка открыть пустой курс
$sql = "SELECT module FROM organizations WHERE cid='$CID'";
$res = sql($sql);
$courseIsEmpty = true;
while ($row = sqlget($res)) {
    if ($row['module']>0) {
        $courseIsEmpty = false;
        break;
    }
}
if ($courseIsEmpty) {
    $GLOBALS['controller']->setMessage(_('В курсе нет учебных материалов'),JS_GO_BACK);
    $GLOBALS['controller']->terminate();
    exit();
}
*/
?>

<html>
<head>
<title><?=APPLICATION_TITLE?></title>
<script type="text/javascript">
<!--
var eLearning_server_metadata = {
    version_string: "3000",
    revision: 23423,
    course_options: {
        use_internal_navigation: <?=$use_external_navigation?>,
        block_content_copy: <?=$block_content_copy?>,
        metadata_page: '<?=$sitepath.'teachers/'.$strParamsLink.$strParamsMain?>',
        glossary_url: '<?=$GLOBALS['sitepath'].'glossary_get.php?cid='.$CID?>'
    },
    permission: "<?=$_SESSION['s']['perm'] ?>",
    coursexml: "<?=$GLOBALS['sitepath'].'COURSES/course'.$CID.'/course.xml' ?>"
}
//-->
</script>
<script type="text/javascript" src="<?=$sitepath?>js/jquery.js"></script>
<script type="text/javascript">
  applyGlossary = function()
  {
    return;
  }
</script>
<script type="text/javascript">
    var oids_str;
    var oids = new Array();
    function start_test(argument){
        if (oids.length == 0){
   	        oids_str = argument;
    	    oids = argument.split(',');
        } else {
            alert('<?=_('Предыдущий сеанс тестирования не закончен. Вы будете перенаправлены на страницу тестирования')?>');
        }
        open_test_page();
    }

	var nextSCO = function() {open_test_page();}

    function open_test_page(){
        if (oids.length && top.mainFrame){
            top.mainFrame.location.href = '<?=$GLOBALS['controller']->view_root->root_url?>/lib_get.php?referer=test&oid=' + oids.shift();
        } else {
            top.mainFrame.location.href = '<?=$GLOBALS['controller']->view_root->root_url?>/teachers/test_results.php?oids=' + oids_str;
        }
    }

    var DMCs = new Array();
    function setDMCs(argument){
        DMCs = argument.split(',');
    }
</script>
</head>
<?
//if (isset($oid)) {
/*
?>
<frameset rows="82,*" frameborder="0" border="0" framespacing="0" name="superMainFrameset" id="superMainFrameset">
  <frame src="<?=$GLOBALS['controller']->view_root->root_url.'/topFrame.php?cid='.$CID?>" id="topFrame" name="topFrame" noresize="noresize" scrolling="no">
  <frame onload="applyGlossary();" src="<?=$mainFrameUrl?>" name="mainFrame" id="mainFrame" <?if ($condition) {?>onload="applyGlossary(); disableClickInFrames(this.id);"<?}?> scrolling="auto">
</frameset>
<?
*/
//} else {
?>
<frameset cols="300,*" border="1" framespacing="1" id="mainFrameset" name="mainFrameset" frameborder="yes">
    <frame style="border-right: 1px solid #9EC3F3" src="<?=$sitepath.'course_structure_toc.php?CID='.$CID.($oid ? "&oid=$oid" : '')?>" id="leftFrame" name="leftFrame" scrolling="auto" <?if ($condition) {?>onload="disableClickInFrames(this.id);"<?}?>>
		<frameset rows="95,*, 0" border="0" framespacing="0" id="mainFrameset" name="mainFrameset" frameborder="no">
			<frame src="course_structure_top.php?CID=<?=$CID.($oid ? "&oid=$oid" : '')?>" name="topFrame" id="topFrame" scrolling="no">
			<frame src="<?=$mainFrameUrl?>" name="mainFrame" id="mainFrame" scrolling="auto">
			<frame src="course_structure_bottom.php?CID=<?=$CID?>" name="bottomFrame" id="bottomFrame" scrolling="no">
		</frameset>
</frameset>
<?php
/*
<frameset rows="82,*,0" frameborder="0" border="0" framespacing="0" name="superMainFrameset" id="superMainFrameset">
  <frame src="<?=$GLOBALS['controller']->view_root->root_url.'/cms/course_structure_top.php?cid='.$CID?>" id="topFrame" name="topFrame" noresize="noresize" scrolling="no">
  <frameset cols="250,*" border="1" framespacing="1" id="mainFrameset" name="mainFrameset" frameborder="yes">
      <frame style="border-right: 1px solid #9EC3F3" src="<?=$sitepath.'cms/course_structure_toc.php?CID='.$CID?>" id="leftFrame" name="leftFrame" scrolling="auto" <?if ($condition) {?>onload="disableClickInFrames(this.id);"<?}?>>
      <frame onload="applyGlossary();" src="<?=$mainFrameUrl?>" name="mainFrame" id="mainFrame" <?if ($condition) {?>onload="applyGlossary(); disableClickInFrames(this.id);"<?}?> scrolling="auto">
  </frameset>
  <frame src="<?=$GLOBALS['controller']->view_root->root_url.'/cms/course_structure_bottom.php?cid='.$CID?>" id="bottomFrame" name="bottomFrame" noresize="noresize" scrolling="no">
</frameset>

<?
*/
//}
?>
<noframes><body>
</body></noframes>
</html>
<?php
$GLOBALS['controller']->persistent_vars->terminate();
exit();
?>