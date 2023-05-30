<?
header("Content-Type: text/css; charset=".$GLOBALS['controller']->lang_controller->lang_current->encoding);
if (@file_exists('skin.css')){
    exit(file_get_contents('skin.css'));
} else {
    ob_start();
    require_once("1.php");
    $GLOBALS['controller']->setView('DocumentCss');
    $GLOBALS['controller']->terminate();
    @$file = fopen('skin.css', 'w');
    fwrite($file, ob_get_contents());
    fclose($file);
}
?>