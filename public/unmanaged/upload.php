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

$arrPathAbsolute = (OS == 'win') ? pathinfo(!empty($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) :  pathinfo($_SERVER['SCRIPT_FILENAME']);
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

if (!$tmp = get_cfg_var('upload_tmp_dir')) {
    $tmp = dirname(tempnam('',''));
}

@session_start();
@session_register("s");

require_once('lib/classes/ProgressBar.class.php');

$size = 0;
if ($_SESSION['s']['login'] && !empty($tmp)) {
    if (is_readable($tmp)) {
        if ($handle = opendir($tmp)) {
            while(false !== ($file = readdir($handle))) {
                if (in_array($file,array('.','..'))) continue;
                if (is_file($tmp.'/'.$file)) {
                    $fileName = $tmp.'/'.$file;
                    break;
                }
            }        
            closedir($handle);
        }
    }
    
    if (!empty($fileName) && file_exists($fileName)) {
        $size = filesize($fileName);
    }
}


if (file_exists($wwf.'/temp/'.$_GET['id'].'.progress')) {
    echo $size;
    
    if ($size > 0) {
        $size = round($size / 1024);
        $progress = new CProgressBar($_GET['id'],false);
        $progress->setAction(getKb($size).' Kb');
        $progress->saveProgress();
    }
}


function explode_by_slashes($str) {
    if (strpos($str, "/") !== false) {
        return explode("/", $str);
    } else {
        return explode("\\", $str); 
    }
}

function getKb($str) {
    $str = (string) $str;
    
    if (strlen($str) > 3) {
        $str = substr($str,0,-3).' '.substr($str,-3);
    }
    
    return $str;
    
}

?>