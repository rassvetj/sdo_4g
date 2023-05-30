<?php
require_once('1.php');
if (empty($s['mid'])) {
	header("Location: index.php");
}
$CID = (int) $_GET['CID'];
if (defined('ENABLE_EAUTHOR_COURSE_NAVIGATION') && ENABLE_EAUTHOR_COURSE_NAVIGATION) {
    $use_external_navigation = 'true';
}
else {
    $use_external_navigation = 'false';
}
?>
<html>
<head>
<title><?=APPLICATION_TITLE?></title>
<script type="text/javascript">
<!--
var eLearning_server_metadata = {
    course_options: {
        use_internal_navigation: <?=$use_external_navigation?>
    },
    version_string: "3000",
    revision: 23423,
    permission: "<?=$_SESSION['s']['perm'] ?>"
}
//-->
</script>
</head>
<frameset cols="300, *" frameborder="no" border="1" framespacing="1" id="mainFrameset" name="mainFrameset">
    <frame src="<?=$sitepath.'course_constructor_menu.php'?>" id="menuFrame" name="menuFrame" scrolling="no">
    <frameset  rows="110, *" frameborder="no" border="1" framespacing="1">
        <frame name="topFrame" id="topFrame" src="<?=$sitepath.'course_constructor_top.php'?>" scrolling="auto">
        <frameset cols="40%,40,60%" frameborder="no" border="1" framespacing="1">
            <frame src="<?=$sitepath.'course_constructor_toc.php?CID=' . (($_SESSION['s']['perm'] == 2) ? $CID : 0) . '&destinationCID='.$CID?>" id="leftFrame" name="leftFrame" scrolling="auto">
            <frame src="<?=$sitepath.'course_constructor_arrow.php'?>" id="arrowFrame" name="arrowFrame" scrolling="no">
            <frame src="<?=$sitepath.'course_constructor_workarea.php?CID='.$CID?>" name="centerFrame" id="centerFrame">
        </frameset>
    </frameset>
</frameset>
<noframes><body></body></noframes>
</html>