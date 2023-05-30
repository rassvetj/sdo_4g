<?php

require_once($GLOBALS['wwf'].'/lib/adldap/adLDAP.php');

class CActiveDirectory {
    var $ldap;
    var $errors = array(), $_insert_buffer = array(), $_update_buffer = array(), $_block_buffer = array(), $_update_mid_buffer = array();
    var $inserted = array(), $updated = array(), $deleted = array();
    var $simulation = false;

    function CActiveDirectory($host='domain.local', $username='user@domain.local', $password='') {
        
        $options = array();
        $options['domain_controllers'] = array($host);
        $options['use_ssl'] = false;
        $options['recursive_groups'] = true;
        $options['ad_username'] = $username;
        $options['ad_password'] = $password;
/*        if (strchr($username,'@') !== false) {
            $options['ad_username'] = substr($username,0,strpos($username,'@'));
            $options['account_suffix'] = substr($username,strpos($username,'@'));
        }
*/        
        $options['account_suffix'] = '@'.$host;
        $ldapbase_pieces = explode('.',$host);
        $options['base_dn'] = 'DC='.join(',DC=',$ldapbase_pieces);

        $this->ldap = new adLDAP($options);
    }
    
    function authenticate($user, $password) {
        if ($this->ldap) {
            return $this->ldap->authenticate($user, $password);
        }
        return false;
    }
    
    function _utf2win($str) {
        return iconv('UTF-8','windows-1251',$str);
    }
    
    function _processUserInfo($info) {
        $info['sn'][0]             = $this->_utf2win($info['sn'][0]);
        $info['company'][0]        = $this->_utf2win($info['company'][0]);
        $info['givenname'][0]      = $this->_utf2win($info['givenname'][0]);
        $info['displayname'][0]    = $this->_utf2win($info['displayname'][0]);
        $info['samaccountname'][0] = $this->_utf2win($info['samaccountname'][0]);
        $info['mail'][0]           = $this->_utf2win($info['mail'][0]);
        $info['dn']                = $this->_utf2win($info['dn']);
        return $info;
    }
    
    function _updateUserInfo($info, $info_ad) {
        if ($info['LastName'] != $info_ad['sn'][0]) return true;
        if ($info['FirstName'] != $info_ad['givenname'][0]) return true;
        if ($info['EMail'] != $info_ad['mail'][0]) return true;
        if ($info['Fax'] != $info_ad['company'][0]) return true;
        return false;
    }
    
    function _processBuffers() {                
        
        foreach($this->_block_buffer as $sql) {
            sql($sql);
        }
        
        if (!$this->simulation) {
            foreach($this->_update_buffer as $sql) {
                sql($sql);
            }
        }
        
        if (is_array($this->_update_mid_buffer) && count($this->_update_mid_buffer)) {
            sql("UPDATE People SET isAD = '1' WHERE MID IN ('".join("','",$this->_update_mid_buffer)."')");
        }
        
        $insert_buffer = array();
        foreach($this->_insert_buffer as $sql) {
            $insert_buffer[] = $sql;
            if (count($insert_buffer) >= 50) {
                $sql = "INSERT INTO people (isAD, LastName, FirstName, Email, Login, Fax) VALUES "
                        .join(',',$insert_buffer); 
                if (!$this->simulation) {
                    sql($sql);
                }
                $insert_buffer = array();
            }
        }
        
        if (count($insert_buffer)) {
            $sql = "INSERT INTO people (isAD, LastName, FirstName, Email, Login, Fax) VALUES "
                    .join(',',$insert_buffer); 
            if (!$this->simulation) {
                sql($sql);            
            }
        }
        
        $sql = "SELECT MID FROM People WHERE isAD = '3'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$this->simulation)
            sql("INSERT INTO Students (MID,CID) VALUES ('{$row['MID']}','0')");
        }
        
        sql("UPDATE People SET isAD = '1' WHERE isAD = '3'");
        
    }
    
    function _blockDeletedUsers() {
        $sql = "SELECT * FROM People WHERE isAD = '2'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $mids[$row['MID']] = $row['MID'];
            $this->deleted[$row['Login']] = $row;
        }
        
        if (is_array($mids) && count($mids) && !$this->simulation) {
            sql("DELETE FROM admins WHERE MID IN ('".join("','",$mids)."')");
            sql("DELETE FROM deans WHERE MID IN ('".join("','",$mids)."')");
            sql("DELETE FROM Teachers WHERE MID IN ('".join("','",$mids)."')");
            sql("DELETE FROM Students WHERE MID IN ('".join("','",$mids)."')");
            sql("DELETE FROM claimants WHERE MID IN ('".join("','",$mids)."')");
        }
        
        if (!$this->simulation) {
            sql("UPDATE People SET isAD = '4' WHERE isAD = '2'");
        } else {
            sql("UPDATE People SET isAD = '1' WHERE isAD = '2'");
        }
    }
        
    function synchronize($dn_filter = '') {
        
        if ($this->simulation) {
        
        $groups = $this->ldap->all_groups(true);
        $filter = '';
        if (!empty($dn_filter)) {
            $filter = "(company=$dn_filter)";
        }
        if (!is_array($groups) || !count($groups)) {
            $users = $this->ldap->all_users(true,"*",true,'',$filter);
        } else {
            $users = array();
            foreach($groups as $gid=>$group) {
                foreach($this->ldap->all_users(true,'*',true, $this->_utf2win($group['dn']), $filter) as $login=>$info) {
                    $users[$login] = $info;
                }
            }
        }
        unset($filter);
                
        if (!is_array($users) || !count($users)) {
            $this->errors[] = _("Не найдено ни одного пользователя в Active Directory");
            return false;
        }
        
        $people = array();
        $where = (!empty($dn_filter) ? 'AND Fax = '.$GLOBALS['adodb']->Quote($dn_filter) : '');
        $sql = "SELECT * FROM People WHERE isAD >= '1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $people[$row['Login']] = $row;
        }
        
        $people_lms = array();
        $sql = "SELECT * FROM People WHERE isAD = '0'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $people_lms[$row['Login']] = $row;
        }
        
        sql("UPDATE People SET isAD = '2' WHERE isAD = '1' $where");
        // =====
/*        $users = array();
        $users['yulia']['samaccountname'][0] = 'yulia';
        $users['yulia']['company'][0] = 'test';
*/
        // =====
        foreach($users as $login => $info) {
            $info = $this->_processUserInfo($info);
            if (!empty($dn_filter) && ($info['company'][0] != $dn_filter)) continue;
            if (empty($info['samaccountname'][0])) continue;
            
            if (isset($people_lms[$info['samaccountname'][0]])) {
                $mid = $people_lms[$info['samaccountname'][0]]['MID'];
                $this->_block_buffer[] = "DELETE FROM Students WHERE MID = '$mid'";
                $this->_block_buffer[] = "DELETE FROM Teachers WHERE MID = '$mid'";
                $this->_block_buffer[] = "DELETE FROM deans    WHERE MID = '$mid'";
                $this->_block_buffer[] = "DELETE FROM admins   WHERE MID = '$mid'";
                $this->_block_buffer[] = "
                    UPDATE People
                    SET Login = ".$GLOBALS['adodb']->Quote('_'.$info['samaccountname'][0])."
                    WHERE MID = '$mid' 
                ";
            }
            
            if (!isset($people[$info['samaccountname'][0]])) {
                $this->_insert_buffer[] = "
                (3,
                ".$GLOBALS['adodb']->Quote($info['sn'][0]).",
                ".$GLOBALS['adodb']->Quote($info['givenname'][0]).",
                ".$GLOBALS['adodb']->Quote($info['mail'][0]).",
                ".$GLOBALS['adodb']->Quote($info['samaccountname'][0]).",
                ".$GLOBALS['adodb']->Quote($info['company'][0]).")";
                $this->inserted[$info['samaccountname'][0]] = $info;
            } else {
                if ($this->_updateUserInfo($people[$info['samaccountname'][0]],$info)) {
                    
                    $this->_update_mid_buffer[] = (int) $people[$info['samaccountname'][0]]['MID'];                        

                    $this->_update_buffer[] = "
                            UPDATE People 
                            SET 
                                FirstName = ".$GLOBALS['adodb']->Quote($info['givenname'][0]).", 
                                LastName  = ".$GLOBALS['adodb']->Quote($info['sn'][0]).", 
                                EMail     = ".$GLOBALS['adodb']->Quote($info['mail'][0]).",
                                isAD      = '1',
                                Fax       = ".$GLOBALS['adodb']->Quote($info['company'][0])."
                            WHERE 
                                MID = '".(int) $people[$info['samaccountname'][0]]['MID']."'";
                    $this->updated[$people[$info['samaccountname'][0]]['Login']] = $info;
                } else {
                    $this->_update_mid_buffer[] = (int) $people[$info['samaccountname'][0]]['MID'];
                }
            }
        }
        
        } else { // if $this->simulation
            $where = (!empty($dn_filter) ? 'AND Fax = '.$GLOBALS['adodb']->Quote($dn_filter) : '');
            sql("UPDATE People SET isAD = '2' WHERE isAD = '1' $where");           
        }
        
        if (!$this->simulation) {
            $this->_block_buffer      = (array) $_SESSION['s']['active_directory']['synchronization']['block_buffer'];
            $this->_update_buffer     = (array) $_SESSION['s']['active_directory']['synchronization']['update_buffer'];
            $this->_update_mid_buffer = (array) $_SESSION['s']['active_directory']['synchronization']['update_mid_buffer'];
            $this->_insert_buffer     = (array) $_SESSION['s']['active_directory']['synchronization']['insert_buffer'];
                
            $this->inserted           = (array) $_SESSION['s']['active_directory']['synchronization']['inserted'];
            $this->updated            = (array) $_SESSION['s']['active_directory']['synchronization']['updated'];
            $this->deleted            = (array) $_SESSION['s']['active_directory']['synchronization']['deleted'];       
        } else {
            $_SESSION['s']['active_directory']['synchronization']['block_buffer']      = $this->_block_buffer;
            $_SESSION['s']['active_directory']['synchronization']['update_buffer']      = $this->_update_buffer;
            $_SESSION['s']['active_directory']['synchronization']['update_mid_buffer'] = $this->_update_mid_buffer;
            $_SESSION['s']['active_directory']['synchronization']['insert_buffer']     = $this->_insert_buffer;

            $_SESSION['s']['active_directory']['synchronization']['inserted'] = $this->inserted;
            $_SESSION['s']['active_directory']['synchronization']['updated']  = $this->updated;
        }
                
        $this->_processBuffers();
        $this->_blockDeletedUsers();

        if ($this->simulation) {
            $_SESSION['s']['active_directory']['synchronization']['deleted']  = $this->deleted;                 
        }

        if (!$this->simulation) {
            unset($_SESSION['s']['active_directory']);
        }
        
        return true;
    }
    
}

?>