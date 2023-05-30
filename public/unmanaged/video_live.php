<?php
require_once('1.php');
require_once('lib/classes/Option.class.php');

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

if ($_GET['view']=='blank') $GLOBALS['controller']->setView('DocumentBlank');

//define('VIDEO_LIVE_CONFIG','http://developer/els_corporate/session.wme');
//define('VIDEO_LIVE_URL','mms://server/testpp');
$options = COption::get_all_as_array('video');
define('VIDEO_LIVE_CONFIG',$sitepath.'options/'.$options['video_config_file']);
define('VIDEO_LIVE_URL',$options['video_url']);

if (isset($_POST['make']) && ($_POST['make']=='showVideoLive')) 
    $GLOBALS['controller']->setView('DocumentPopup');

$smarty = new Smarty_els();

$smarty->assign('fullscreen', isset($_GET['fullscreen']));
$smarty->assign('controller_enable',(($s['perm']==2) ? 1 : 0));
$html = $smarty->fetch('video_live.tpl');
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();
?>