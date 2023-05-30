<?php

class CChatMessage extends CDBObject {
    var $table = 'chat_messages';
}

class CChat {
    
    protected $rid, $cid, $uid, $idshedule;
    protected $smarty;
    
    function __construct($params = array()) {
        $this->rid = $params['rid'];
        $this->cid = $params['cid'];
        $this->uid = $_SESSION['s']['mid'];
        //$this->idshedule = $params['idshedule'];
        $this->idshedule = $_SESSION['s']['ajaxchat']['idshedule'];
    }
    
    function factory($params) {
        if (1 == $params['view']) {
            return new CChatLite($params);
        }
        return new CChat($params);
    }
    
    function display() {
        echo '
        <html>
        <head>        
        <meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding.'">        
        <title>'.APPLICATION_TITLE.'</title>
        </head>
        <frameset cols="300,*, 150" frameborder="1" border="1" framespacing="0" id="mainFrameset" name="mainFrameset">
            <frame src="ajaxchat_rooms.php?rid='.$this->rid.'" id="leftFrame" name="leftFrame" noresize  scrolling="no">
            <frame src="ajaxchat_chat.php?rid='.$this->rid.'" name="mainFrame" scrolling="no">
            <frame src="ajaxchat_users.php?rid='.$this->rid.'" name="rightFrame" scrolling="no" noresize>
        </frameset>
        <noframes><body></body></noframes>
        </html>';
    }
    
    protected function &getSmarty() {
        if (!$this->smarty) {
            $this->smarty = new Smarty_els();
            $this->smarty->assign('sitepath', $sitepath);            
        }
        
        return $this->smarty;
    }
    
    protected function _getRooms() {
        $rooms = array();
        switch($_SESSION['s']['perm']) {
            case 1:
                if (is_array($_SESSION['s']['skurs']) && count($_SESION['s']['skurs'])) {
                    $sql = "SELECT CID, Title FROM Courses WHERE CID IN ('".join("','", $_SESSION['s']['skurs'])."') ORDER BY Title";
                    $res = sql($sql);
                    
                    while($row = sqlget($res)) {
                        $rooms[$row['CID']] = array('name' => $row['Title'], 'id' => $row['CID'], 'room' => false);
                    }
                }
                break;                
            case 2:
                if (is_array($_SESSION['s']['tkurs']) && count($_SESION['s']['tkurs'])) {
                    $sql = "SELECT CID, Title FROM Courses WHERE CID IN ('".join("','", $_SESSION['s']['tkurs'])."') ORDER BY Title";
                    $res = sql($sql);
                    
                    while($row = sqlget($res)) {
                        $rooms[$row['CID']] = array('name' => $row['Title'], 'id' => $row['CID'], 'room' => false);
                    }
                }                
                break;
            default:
                $sql = "SELECT CID, Title FROM Courses WHERE Status > 1 ORDER BY Title";
                $res = sql($sql);
                
                while($row = sqlget($res)) {
                    $rooms[$row['CID']] = array('name' => $row['Title'], 'id' => $row['CID'], 'room' => false);
                }
                
                break;                
        }
        
        if (count($rooms)) {
            $sql = "SELECT CONCAT(t1.LastName, ' ', t1.FirstName) as name, t1.MID as id, t2.CID
                    FROM People t1
                    INNER JOIN Teachers t2 ON (t2.MID = t1.MID)
                    WHERE t2.CID IN ('".join("','", array_keys($rooms))."')
                    ORDER BY name";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $rooms[$row['CID']]['children'][$row['id']] = array('name' => $row['name'], 'id' => $row['id'], 'room' => true);
            }
        }
        return $rooms;
    }
    
    function displayRooms() {
        $rooms = $this->_getRooms();
        
        $smarty = &$this->getSmarty();  
        $smarty->clear_all_assign();
        $smarty->assign('rooms', $rooms);              
        echo $smarty->fetch('ajaxchat_rooms.tpl');
    }
    
    function initRoom() {
        sql("DELETE FROM chat_users WHERE uid = '".$this->uid."'");
        sql("INSERT INTO chat_users (joined, user, rid, cid, uid) VALUES ('".time()."', ".$GLOBALS['adodb']->Quote(getField('People', 'Login', 'MID', $this->uid)).", ".$GLOBALS['adodb']->Quote($this->rid).", '{$this->cid}', '{$this->uid}')");
        unset($_SESSION['s']['ajaxchat']);
        $_SESSION['s']['ajaxchat']['rid'] = $this->rid;
        $_SESSION['s']['ajaxchat']['cid'] = $this->cid;
        $_SESSION['s']['ajaxchat']['idshedule'] = $this->idshedule;
    }
    
    protected function _getMessages() {
        $messages = array();
        $tmp = '';
        if (!empty($this->idshedule)){
            $tmp = " and sheid='".$this->idshedule."'";
        }
        
        $sql = "SELECT id, rid, cid, uid, message, posted, user, sendto FROM chat_messages WHERE rid = ".$GLOBALS['adodb']->Quote($this->rid)." AND cid = '{$this->cid}' ".$tmp." ORDER BY posted DESC LIMIT 100";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $messages[$row['id']] = new CChatMessage($row);
        }
        return $messages;
    }
    
    function displayRoom() {        
        $smarty = &$this->getSmarty();  
        $smarty->clear_all_assign();
        $smarty->assign('messages', $this->_getMessages());
        $smarty->assign('okbutton', okbutton(_('Сказать'), '', 'ok', 'sendMessage();'));        
        echo $smarty->fetch('ajaxchat_chat.tpl');        
    }
    
    protected function _getUsers() {
        $users = array();
        $sql = "SELECT 
                    t1.MID, t1.LastName, t1.FirstName, t2.user 
                FROM
                    People t1  
                INNER JOIN chat_users t2 ON (t2.uid = t1.MID) 
                WHERE 
                    t2.rid = ".$GLOBALS['adodb']->Quote($this->rid)." 
                    AND t2.cid = '{$this->cid}' 
                    AND t2.joined >= '".(time() - 20)."' 
                ORDER BY t2.user";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $users[$row['MID']] = $row;
        }
        
        return $users;
    }
        
    function fetchUsers() {
        $smarty = &$this->getSmarty();  
        $smarty->clear_all_assign();        
        $smarty->assign('users', $this->_getUsers());
        return $smarty->fetch('ajaxchat_users_list.tpl');                
    }
        
    function displayUsers() {
        $smarty = &$this->getSmarty();  
        $smarty->clear_all_assign();        
        $smarty->assign('users', $this->_getUsers());
        echo $smarty->fetch('ajaxchat_users.tpl');        
    }
}

class CChatLite extends CChat {
    function display() {
        echo '
        <html>
        <head>        
        <meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding.'">        
        <title>'.APPLICATION_TITLE.'</title>
        </head>
        <frameset cols="*,150" frameborder="1" border="1" framespacing="0" id="mainFrameset" name="mainFrameset">
            <frame src="ajaxchat_chat.php?rid='.$this->rid.'" name="mainFrame" scrolling="no">
            <frame src="ajaxchat_users.php?rid='.$this->rid.'" name="rightFrame" scrolling="no" noresize>
        </frameset>
        <noframes><body></body></noframes>
        </html>';        
    }
}

?>