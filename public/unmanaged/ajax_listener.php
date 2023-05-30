<?php
require_once '1.php';
require_once 'lib/sajax/SajaxWrapper.php';

if (!$_SESSION['s']['login'] || !$_SESSION['s']['mid']) {
    die();
}

$_POST['rs'] = 'ajax'.$_POST['rs'];

CSajaxWrapper::init(array($_POST['rs']));

function ajaxToggleHelpAlwaysShow($value) {
    if ($value == 'true') $value = 1;
    else $value = 0;
    $_SESSION['s']['user']['helpAlwaysShow'] = (int) $value;
    $sql = "UPDATE People SET Course = '".(int) $value."' WHERE MID = '".(int )$_SESSION['s']['mid']."'";
    $res = sql($sql);
    return 1;
}

function ajaxChatGetMessages() {
    $html = '';
    if (isset($_SESSION['s']['ajaxchat']) && isset($_SESSION['s']['ajaxchat']['rid']) && isset($_SESSION['s']['ajaxchat']['rid'])) {
        $tmp = '';
        if (isset($_SESSION['s']['ajaxchat']['idshedule']) && $_SESSION['s']['ajaxchat']['idshedule']>0){
            $tmp = " and sheid='".$_SESSION['s']['ajaxchat']['idshedule']."'";
        }
        $sql = "SELECT id, rid, cid, uid, message, posted, user, sendto FROM chat_messages WHERE rid = '{$_SESSION['s']['ajaxchat']['rid']}' AND cid = '{$_SESSION['s']['ajaxchat']['cid']}' ".$tmp." ORDER BY posted DESC LIMIT 100";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $html .= sprintf("<span class=\"date\">%s</span>, <a onClick=\"addNick('%s')\" href=\"javascript:void(0);\">%s</a>: %s<br>", date('d.m.Y H:i:s', $row['posted']), htmlspecialchars($row['user']), $row['user'], htmlspecialchars($row['message']));
        }
    }    
    return $html;
}

function ajaxChatGetUsers() {
    $html = '';
    if (isset($_SESSION['s']['ajaxchat']) && isset($_SESSION['s']['ajaxchat']['rid']) && isset($_SESSION['s']['ajaxchat']['cid'])) {
        require_once $GLOBALS['wwf'].'/lib/classes/Chat.class.php';
        
        $chat = CChat::factory(array('rid' => $_SESSION['s']['ajaxchat']['rid'], 'cid' => $_SESSION['s']['ajaxchat']['cid']));
        $chat->initRoom();
        $html = $chat->fetchUsers();
    }
    return $html;
}

function ajaxChatSendMessage($message) {
    $message = iconv('UTF-8', $GLOBALS['controller']->lang_controller->lang_current->encoding, $message);
    if (isset($_SESSION['s']['ajaxchat']) && isset($_SESSION['s']['ajaxchat']['rid']) && isset($_SESSION['s']['ajaxchat']['cid'])) {
        $sql = "INSERT INTO chat_messages 
                (
                    rid, 
                    uid, 
                    cid, 
                    user, 
                    message, 
                    posted,
                    sheid)
                VALUES 
                (
                    '{$_SESSION['s']['ajaxchat']['rid']}', 
                    '{$_SESSION['s']['mid']}', 
                    '{$_SESSION['s']['ajaxchat']['cid']}', 
                    ".$GLOBALS['adodb']->Quote($_SESSION['s']['login']).", 
                    ".$GLOBALS['adodb']->Quote($message).", 
                    '".time()."',
                    '{$_SESSION['s']['ajaxchat']['idshedule']}')
        ";
        return sql($sql);
    }
}

?>