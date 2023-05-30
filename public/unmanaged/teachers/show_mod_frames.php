<?
require_once("dir_set.inc.php4");
$strParamsLeft = base64_decode($_GET['paramsLeft']);
$strParamsMain = base64_decode($_GET['paramsMain']);
$strParamsLink = base64_decode($_GET['Link']);
?>
<html>
<head>
<?
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
        metadata_page: '<?=$sitepath.'teachers/'.$strParamsLink.$strParamsMain?>'
    }
}
//-->
</script>
</head>
<frameset cols="300,*" frameborder="no" border="0" framespacing="0" id="mainFrameset" name="mainFrameset" noresize>
  <frame src="edit_mod.php4?<?=$strParamsLeft?>" id="leftFrame" name="leftFrame" noresize="noresize" scrolling="auto" <?if ($condition) {?>onload="disableClickInFrames(this.id);"<?}?>>
  <frame src="<?=$strParamsLink?><?=$strParamsMain?>" name="mainFrame" scrolling="auto" id="mainFrame" <?if ($condition) {?>onload="disableClickInFrames(this.id);"<?}?>>
</frameset>
<noframes><body>
</body></noframes>
</html>
<?
$controller->terminate_flush();
?>