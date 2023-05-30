<?php

// Different type of accounts in AD
define ('ADLDAP_NORMAL_ACCOUNT', 805306368);
define ('ADLDAP_WORKSTATION_TRUST', 805306369);
define ('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define ('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define ('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define ('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define ('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);

define ('ADLDAP_SECURITY_KEY','AD_LDAP_2oo6_o1_2o');
define ('ADLDAP_INSERTS_IN_QUERY',100);
define ('ADLDAP_UPDATES_IN_QUERY',100);

/**
* XOR Encrypt function
* one-time pad method
*/
function xor_encrypt($string, $key) {
   $result = '';
   for($i=0; $i<strlen($string); $i++) {
     $char = substr($string, $i, 1);
     $keychar = substr($key, ($i % strlen($key))-1, 1);
     $char = chr(ord($char)+ord($keychar));
     $result.=$char;
   }

   return base64_encode($result);
  }

/**
* XOR Decrypt function
* one-time pad method
*/
function xor_decrypt($string, $key) {
   $result = '';
   $string = base64_decode($string);

   for($i=0; $i<strlen($string); $i++) {
     $char = substr($string, $i, 1);
     $keychar = substr($key, ($i % strlen($key))-1, 1);
     $char = chr(ord($char)-ord($keychar));
     $result.=$char;
   }

   return $result;
}

/**
* Class CLdap
*/
class CLdap {
    
    var $host, $username, $password, $ldapbase;
    var $ldap;    
    var $db=false;
    var $people_added = array();
    var $people_deleted = array();
    var $people_modified = array();
    var $people_exists = array();
    var $simulation = false;
    
    /**
    * Constructor
    */
    function CLdap($host='localhost', $username='', $password='') {
        
        if (empty($host)) return false;
                
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        
        /**
        * Определение базы данных
        */
        $ldapbase_pieces = explode('.',$host);
        $this->ldapbase = 'DC='.join(',DC=',$ldapbase_pieces);
        //$this->host = 'test2k';
        
        /**
        * Установка параметров соединения
        */        
        $LDAP_CONNECT_OPTIONS = Array(
        Array ("OPTION_NAME"=>LDAP_OPT_DEREF, "OPTION_VALUE"=>2),
        Array ("OPTION_NAME"=>LDAP_OPT_SIZELIMIT,"OPTION_VALUE"=>0),
        Array ("OPTION_NAME"=>LDAP_OPT_TIMELIMIT,"OPTION_VALUE"=>0),
        Array ("OPTION_NAME"=>LDAP_OPT_PROTOCOL_VERSION,"OPTION_VALUE"=>3),
        Array ("OPTION_NAME"=>LDAP_OPT_ERROR_NUMBER,"OPTION_VALUE"=>13),
        Array ("OPTION_NAME"=>LDAP_OPT_REFERRALS,"OPTION_VALUE"=>FALSE),
        Array ("OPTION_NAME"=>LDAP_OPT_RESTART,"OPTION_VALUE"=>FALSE)
        );
        
        if (!$this->ldap = NewADOConnection('ldap')) return false;
        
        if (!$this->ldap->Connect($this->host,$this->username, $this->password, $this->ldapbase)) return false;
        
        $this->set_fetch_assoc();

        return true;
        
    }
    
    function disable_simulation_mode($mode) {
        $this->simulation = !((boolean) $mode);
    }
    
    /**
    * Установка получения данных в виде ASSOC_ARRAY
    */
    function set_fetch_assoc() {

        $this->ldap->SetFetchMode(ADODB_FETCH_ASSOC);

        return true;

    }
    
    /**
    * Поиск данных в AD
    * @filter - строка фильтра поиска
    */
    function getArray($filter='') {

        $rs = $this->ldap->getArray($filter);
        return $rs;

    }
    
    /**
    * Получение массива OU (групп пользователей)
    */
    function getGroupsArray($filter='') {
        
        /**
        * Фильтр по умолчанию - поиск всех OrganizationUnits
        */
        if (empty($filter))
        $filter = "(|(cn=Users)(&(objectClass=organizationalUnit)(ou=*)))";
        
        return $this->getArray($filter);
        
    }
    
    /**
    * Получение массива пользователей
    * @filter - строка фильтра поиска
    */
    function getUsersArray($filter='') {
        
        /**
        * Фильтр по умолчанию - поиск всех пользователей
        */
        if (empty($filter)) 
        $filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=*))";
        $rs = $this->getArray($filter);
        return $rs;
        
    }
    
    /**
    * Устанавливает класс для работы с DB
    */
    function setDataBase($db) {
     
        $this->db = $db;
        
        return true;
        
    }
    
    /**
    * Возвращает существующие логины пользователей из БД
    */
    function getExistsLogins() {
        
        if (!$this->db) return false;
        
        $ret = false;
    
        $tmpl = "SELECT * FROM people";

        $rs = $this->db->Execute($tmpl);
        
        if ($rs) while ($arr = $rs->FetchRow()) {
            $ret[] = $arr['Login'];
            $this->people_exists[$arr['Login']] = $arr;
        }

        return $ret;

    }    
    
    /**
    * Вставка новых пользователей в БД из буффера
    * $buffer - array
    */
    function insertBuffer(&$buffer) {

        $ret = false;
        if ($this->simulation) $ret=true;
        else {
            $tmpl = "INSERT INTO people (isAD, LastName, FirstName, Email, Login, Password) VALUES "
                    .join(',',$buffer);                      
            if ($res = sql($tmpl)) $ret = true;        
            sqlfree($res);
        }
        unset($buffer);
        return $ret;
        
    }

    /**
    * Обновление существующих пользователей из буффера
    * $buffer - array
    */
    function updateBuffer(&$buffer) {

        $ret = false;
        $tmpl = "UPDATE people SET isAD=1 WHERE isAD=2 AND Login IN ("
                .join(',',$buffer).")";
        if ($res = sql($tmpl)) $ret = true;        
        sqlfree($res);
        unset($buffer);
        return $ret;
        
    }
    
    function _check_user_info($old,$new) {
        if (($old['FirstName'] != $new['givenName']) || ($old['LastName'] != $new['sn']) || ($old['EMail'] != $new['mail']))
            return false;    
        else
            return true;
    }
    
    function _update_user_info($mid,$info) {
        if (!$this->simulation) {
            $sql = "UPDATE People 
                    SET 
                        FirstName=".$GLOBALS['adodb']->Quote($info['givenName']).", 
                        LastName=".$GLOBALS['adodb']->Quote($info['sn']).", 
                        EMail=".$GLOBALS['adodb']->Quote($info['mail']).",
                        isAD='1'
                    WHERE MID='".(int) $mid."'";
            sql($sql);
        } else {
            $sql = "UPDATE People SET isAD='1' WHERE MID='".(int) $mid."'";
            sql($sql);
        }
        $this->people_modified[] = $info;
    }
    
    /**
    * Импорт пользователей из AD в DB
    */
    function import2db($filter='') {        

        /**
        * Получение списка OrganizationUnits и получение пользователей в них
        */
        if (!$groups = $this->getGroupsArray()) $newUsers = $this->getUsersArray($filter);
        else {

            $newUsers = array();
            $this->ldap->SLS = true;
            while (list(,$v)=each($groups)) {
            
                $this->ldap->database = $v['distinguishedName'];
                if ($users = $this->getUsersArray($filter))
                $newUsers = array_merge($newUsers, $users); 
                                                                
            }
            unset($groups);
                        
        }

        if (!$newUsers || count($newUsers)<=0) return false; 
          
        if (!$existsUsers = $this->getExistsLogins()) return false;
        
        $i=0; // Кол-во импортированных записей
        
        $tmpl = "UPDATE people SET isAD=2 WHERE isAD=1";
        $res = sql($tmpl);
        sqlfree($res);
        
        $buffer = array();
        $bufferUpdate = array();
        
        //if (ob_get_level() == 0) ob_start();
                        
        while(list($k,$v) = each($newUsers)) { 
                                   
            if (!empty($v['sAMAccountName'])) {
            
            if (!in_array($v['sAMAccountName'],$existsUsers)) {
                            
                $buffer[] = "
                (1,
                ".$GLOBALS['adodb']->Quote($v['sn']).",
                ".$GLOBALS['adodb']->Quote($v['givenName']).",
                ".$GLOBALS['adodb']->Quote($v['mail']).",
                ".$GLOBALS['adodb']->Quote($v['sAMAccountName']).",
                PASSWORD(".$GLOBALS['adodb']->Quote($v['sAMAccountName'])."))";
                
                $this->people_added[] = $v;
                
                if (count($buffer)>=ADLDAP_INSERTS_PER_QUERY) {
                    
                    $this->insertBuffer($buffer);
                    $buffer = array();

                }
                                           
                                                            
                $existsUsers[] = $v['sAMAccountName'];
                $i++;
                    
                //echo "[Импорт]: ".$v['sAMAccountName'].'<br>'; 
                //flush();
                //ob_flush();
            
            } // !in_array
            else {
                if (!$this->_check_user_info($this->people_exists[$v['sAMAccountName']],$v)) {
                    $this->_update_user_info($this->people_exists[$v['sAMAccountName']]['MID'],$v);    
                } else                
                $bufferUpdate[] = "".$GLOBALS['adodb']->Quote($v['sAMAccountName'])."";
                
                if (count($bufferUpdate) > ADLDAP_UPDATES_IN_QUERY) {
                
                    $this->updateBuffer($bufferUpdate);                                                            
                    $bufferUpdate = array();
                    
                }                                
                
            }
            
            } // empty
            unset($newUsers[$k]);
        }
        
        // Обрабатываем оставшиеся буффера
        if (count($buffer) > 0) $this->insertBuffer($buffer);

        if (count($bufferUpdate) > 0) $this->updateBuffer($bufferUpdate);
                
        $sql = "SELECT * FROM People WHERE isAD=2";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $this->people_deleted[] = $row;
        }
        
        if ($this->simulation) {
            $sql = "UPDATE People SET isAD='1' WHERE isAD='2'";
            sql($sql);
        } else {
            $tmpl = "DELETE FROM People WHERE isAD=2";
            $res = sql($tmpl);
            sqlfree($res);
        }
        
        return $i; 
        
    }
}

// Starts, Ends and Displays Page Creation Time
class Timer {

   function getmicrotime() {
       list($usec, $sec) = explode(" ", microtime());
       return ((float)$usec + (float)$sec);
   }   
  
   function starttime() {
   $this->st = $this->getmicrotime();
   }
      
   function displaytime() {
       $this->et = $this->getmicrotime();
       return round(($this->et - $this->st), 3);
   }
}

?>