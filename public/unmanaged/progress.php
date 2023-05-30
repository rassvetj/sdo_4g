<?php
if (!function_exists('_')){
    function _($str){
        return $str;
    }
}

if (!function_exists('gettext')) {
    function gettext($str) {
        return $str;
    }
}

define("OS", ((strpos($_SERVER['SERVER_SOFTWARE'], "Win") !== false) || (strpos($_SERVER['SERVER_SOFTWARE'], "Microsoft") !== false)) ? 'win' : '*nix');

$arrPathAbsolute = (OS == 'win') ? pathinfo(!empty($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] : (!empty($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME'])) :  pathinfo($_SERVER['SCRIPT_FILENAME']);
$arrPathRelative = pathinfo($_SERVER['PHP_SELF']);
$arrElsDirs = array('', 'admin', 'help', 'reports', 'teachers', 'cron');

$arrNestedDirsAbsolute = explode_by_slashes($arrPathAbsolute['dirname']);
$arrNestedDirsRelative = explode_by_slashes($arrPathRelative['dirname']);

if (is_array($arrNestedDirsAbsolute) && count($arrNestedDirsAbsolute) > 1) {
    if (in_array($arrNestedDirsAbsolute[count($arrNestedDirsAbsolute) - 1], $arrElsDirs)) {
        array_pop($arrNestedDirsAbsolute);
        array_pop($arrNestedDirsRelative);
    }
    if (empty($arrNestedDirsRelative[count($arrNestedDirsRelative) - 1])) {
        array_pop($arrNestedDirsRelative);
    }
    
    $strPathAbsolute = implode("/", $arrNestedDirsAbsolute);
    $strPathRelative = implode("/", $arrNestedDirsRelative);

    $_SERVER['DOCUMENT_ROOT'] = $strPathAbsolute;
    $DOCUMENT_ROOT = $strPathAbsolute;
    $_SERVER["HTTP_HOST"] .= $strPathRelative;
    if (isset($HTTP_HOST)) $HTTP_HOST .= $strPathRelative;

}

$protocol = ($_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$wwwhost  = $_SERVER["HTTP_HOST"];
$sitepath = "{$protocol}://{$wwwhost}/";
$wwf      = $_SERVER['DOCUMENT_ROOT'];

require_once("smarty/Smarty.class.php");
require_once("smarty/Smarty_els.class.php");
require_once('lib/classes/ProgressBar.class.php');

$progress = new CProgressBar($_GET['id']);
$progress->setTitle(urldecode($_GET['title']));
$progress->setHeadAction(urldecode($_GET['action']));
$progress->setComments(urldecode($_GET['comments']));

echo $progress->fetch();

function explode_by_slashes($str) {
    if (strpos($str, "/") !== false) {
        return explode("/", $str);
    } else {
        return explode("\\", $str); 
    }
}

?>