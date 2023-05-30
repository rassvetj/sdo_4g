<?
require_once("1.php");
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

if (!isset($_GET['cid']) && !isset($_GET['sheid'])) {
    $filter_kurses = selCourses($s['perm']==1 ? $s['skurs'] : $s['tkurs'],$_GET['cid'],true);
    if (!is_array($filter_kurses) || !count($filter_kurses)) {
        $controller->setMessage(_('У вас нет ни одного доступного чата'), JS_GO_URL, $GLOBALS['sitepath']);
        $controller->terminate();
        exit();
    }
    $controller->setView('Document');
    $controller->addFilter(_("Курс"),'cid',$filter_kurses,$_GET['cid'],true);
    $controller->terminate();
    exit();
}

$get = ($s['perm']==2) ? "rid={$_SESSION['s']['mid']}&uid={$_SESSION['s']['mid']}" : "";
if ($_GET['sheid']) $get = "rid=shedule_{$_GET['sheid']}&uid={$_SESSION['s']['mid']}&cid=0";
$get = ($_GET['rid'] && $_GET['uid']) ? "rid={$_GET['rid']}&uid={$_SESSION['s']['mid']}" : $get;
$get .= (isset($_GET['cid'])) ? "&cid={$_GET['cid']}" : "";
if ($_GET['view'] && ($_GET['view']=='blank')) {
?>
<title>eLearning Server 3000</title>
<frameset cols="*" frameborder="1" border="1" framespacing="0" id="mainFrameset" name="mainFrameset">
  <frame src="chat_chat.php?<?=$get?>" name="mainFrame" scrolling="no">
</frameset>
<noframes><body>
</body></noframes>
<?php
} else {
?>
<frameset cols="300,*" border="1" framespacing="1" id="mainFrameset" name="mainFrameset" frameborder="yes">
    <frame style="border-right: 1px solid #9EC3F3" src="chat_left.php4?<?=$get?>" id="leftFrame" name="leftFrame" scrolling="auto">
		<frameset rows="95,*, 0" border="0" framespacing="0" id="mainFrameset" name="mainFrameset" frameborder="no">
			<frame src="course_structure_top.php?CID=<?=$CID?>" name="topFrame" id="topFrame" scrolling="no">
			<frame src="chat_chat.php?<?=$get?>" name="mainFrame" id="mainFrame" scrolling="auto">			
		</frameset>
</frameset>
<?
}
?>