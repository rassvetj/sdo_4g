<?php
require_once('1.php');
require_once($wwf.'/metadata.lib.php');

istest();

$CID = (int) $_REQUEST['CID'];

//$GLOBALS['controller']->setView('DocumentPopup');
//$GLOBALS['controller']->enableNavigation();
$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromOb(CONTENT);

//echo "<table width=100% class=main cellspacing=0><tr><td valign=top>";
$str = view_metadata_as_text_extended(read_metadata (stripslashes(getField('Courses','Description','CID',$CID)),COURSES_DESCRIPTION), COURSES_DESCRIPTION);
$str_course = get_course_title($CID);

if (strlen(trim(strip_tags($str)))) {
echo "
<div style=\"padding: 40px;\">
    <div class=\"card-info-block\">
        <p class=\"card-info-title\">"._("Описание курса")."</p>
        <p>{$str}</p>
        <div class=\"clear-both\"></div>
    </div>
</div>
";
} /*else {
    $nodata = _("Описание курса не задано");
    echo <<<E0D
<div style="padding: 40px;">
    <div class="card-info-block">
        <div class="nodata" style="padding: 20px 0;">{$nodata}</div>
        <div class="clear-both"></div>
    </div>
</div>
E0D;
}*/
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>