<?php
require_once("1.php");
require_once("lib/classes/Schedule.class.php");

if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");

$sheid    = (int) $_REQUEST['sheid'];
$mid      = (int) $_REQUEST['mid'];
$comments = trim(strip_tags($_POST['comments']));
$id       = (int) $_POST['id'];

$shedule = new Schedule();
$shedule->init($sheid);

if (!in_array($shedule->get_cid(),$s['tkurs'])) {
    $GLOBALS['controller']->terminate();
    exit();        
}

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Комментарий к ').$shedule->get_name()._(' для ').mid2name($mid).' ('.mid2login($mid).')');
$GLOBALS['controller']->captureFromOb(CONTENT);

switch($_POST['post']) {
    case 'update':
        if (!empty($comments)) {
            $sql = "UPDATE scheduleID SET comments=".$GLOBALS['adodb']->Quote($comments)." WHERE SHEID='{$sheid}' AND MID='{$mid}'";
            sql($sql);
        }
        $GLOBALS['controller']->setMessage(_('Комментарий изменен'),JS_GO_URL,'javascript:window.close()');
        $GLOBALS['controller']->terminate();
        exit();        
    break;
}

$sql = "SELECT comments FROM scheduleID WHERE SHEID='".$sheid."' AND MID='".$mid."'";
$res = sql($sql);
if (sqlrows($res)) {
    $row = sqlget($res); 
    $comments = $row['comments'];   
}

echo "
<form method=\"POST\">
<input type=\"hidden\" name=\"post\" value=\"update\">
<input type=\"hidden\" name=\"sheid\" value=\"{$sheid}\">
<input type=\"hidden\" name=\"mid\" value=\"{$mid}\">
<textarea cols=10 rows=10 name=\"comments\" style=\"width: 100%;\"> ".$comments."</textarea>
<p>".okbutton()."
</form>
";

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>