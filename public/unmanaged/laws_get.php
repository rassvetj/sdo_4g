<?php
require_once('1.php');

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

$GLOBALS['controller']->setView('DocumentBlank');

if (isset($_GET['id'])) {
    
    if (check_access_level($_GET['id'],$GLOBALS['s']['user']['meta']['access_level'])) {
    
        $id = (int) $_GET['id'];    
        $sql = "SELECT * FROM laws WHERE (id='".$id."' OR parent='".$id."') AND current_version='1'";
        $res = sql($sql);
        
        if (sqlrows($res)) {
            
            $row = sqlget($res);
            if (empty($row['filename'])) {
                $GLOBALS['controller']->setMessage(_("Документ не найден"),JS_GO_URL,'javascript:window.close();');
                $GLOBALS['controller']->terminate();
            }
            
            header("Location: {$sitepath}laws{$row['filename']}");
            
        } else $GLOBALS['controller']->setMessage(_("Нет текущий версии документа"),JS_GO_URL,'javascript:window.close();');
        
        } else $GLOBALS['controller']->setMessage(_("У вас нет соответствующего уровня доступа"),JS_GO_URL,'javascript:window.close();');
    
} else $GLOBALS['controller']->setMessage(_("Не указан необходимый документ"),JS_GO_URL,'javascript:window.close();');

$GLOBALS['controller']->terminate();

function check_access_level($id,$access_level) {
    if ($access_level>0) 
        $sql_access_level = " access_level>='".(int) $access_level."' OR ";
    $sql = "SELECT * FROM laws WHERE id='".(int) $id."' AND ({$sql_access_level} access_level='0')";
    $res = sql($sql);
    if (sqlrows($res)==1) return true;
    return false;
}

?>