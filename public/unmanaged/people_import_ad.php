<?php
die('deprecated');
require_once('1.php');
require_once('lib/classes/ldap.class.php');
require_once('lib/classes/ActiveDirectory.class.php');

$time = new Timer;
$time->starttime();

class CMain {
       
    // =============================================================================================
    /**
    * Синхронизация в диалоговом режиме
    */
    function main() {                            

        if (!$GLOBALS[s][login]) {
            exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$GLOBALS[sess]");
        }

        if ($GLOBALS[s][perm]<3) {
            exitmsg(_("Доступ к странице имеют представители учебной администрации, или администраторы"),"/?$GLOBALS[sess]");
        }

        $GLOBALS[smarty] = new Smarty_els;
        $GLOBALS[smarty]->php_handling = SMARTY_PHP_ALLOW;
        $GLOBALS[smarty]->assign('sitepath',$GLOBALS[sitepath]);

        /**
        * Импорт пользователей
        */
        if ($_POST['import_from_ad'] && $_POST['import_from_ad'] == 'import_from_ad') {
            
            if ($_POST['use_exists_settings']) {
                $sql = "SELECT name,value FROM options WHERE name='ldap_host' OR name='ldap_user'";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if ($row['name']=='ldap_host') $host = $row['value'];
                    if ($row['name']=='ldap_user') $username = $row['value'];
                }
            } else {
                $host = isset($_POST['domain_name']) ? trim(strip_tags($_POST['domain_name'])) : '';
                $username = isset($_POST['username']) ? trim($_POST['username']) : '';
                
                if (!empty($host) && !empty($username)) {
                    $sql = "SELECT name FROM OPTIONS WHERE name='ldap_host' OR name='ldap_user'";
                    $res = sql($sql);
                    if (sqlrows($res)==2) {
                        $sql = "UPDATE OPTIONS SET value=".$GLOBALS['adodb']->Quote($host)." WHERE name='ldap_host'";
                        sql($sql);
                        $sql = "UPDATE OPTIONS SET value=".$GLOBALS['adodb']->Quote($username)." WHERE name='ldap_user'";
                        sql($sql);
                    } else {
                        $sql = "INSERT INTO OPTIONS (name,value) VALUES ('ldap_host',".$GLOBALS['adodb']->Quote($host).") ";
                        sql($sql);
                        $sql = "INSERT INTO OPTIONS (name,value) VALUES ('ldap_user',".$GLOBALS['adodb']->Quote($username).") ";
                        sql($sql);
                    }
                }
            }
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $do = isset($_POST['do']) ? trim($_POST['do']) : '';

            if (empty($host) || empty($username) || empty($password)) {
                exitmsg(_("Пожалуйста, заполните все поля"),"{$GLOBALS[sitepath]}people_import_ad.php?{$GLOBALS[sess]}");            
            }
   
            if ($ldap = new CActiveDirectory($host,$username,$password)) {
                
                $ldap->simulation = !$_POST['disable_simulation'];
        
                //$ldap->setDataBase($GLOBALS[adodb]);

                if (($i = $ldap->synchronize()) !== false) {                    
                    $GLOBALS['smarty']->assign('disable_simulation',$_POST['disable_simulation']);
                    $GLOBALS['smarty']->assign('domain_name',$host);
                    $GLOBALS['smarty']->assign('username',$username);
                    $GLOBALS['smarty']->assign('password',$password);
                    $GLOBALS['smarty']->assign('sitepath',$sitepath);
                    $GLOBALS['smarty']->assign_by_ref('added',$ldap->inserted);
                    $GLOBALS['smarty']->assign_by_ref('deleted',$ldap->deleted);
                    $GLOBALS['smarty']->assign_by_ref('modified',$ldap->updated);
                    $GLOBALS['smarty']->assign('count_added',count($ldap->inserted));
                    $GLOBALS['smarty']->assign('count_deleted',count($ldap->deleted));
                    $GLOBALS['smarty']->assign('count_modified',count($ldap->updated));
                    $html = $GLOBALS['smarty']->fetch('people_import_ad_info.tpl');
                    $GLOBALS['controller']->captureFromOb(CONTENT);
                    echo $html;
                    $GLOBALS['controller']->captureStop(CONTENT);
                    $GLOBALS['controller']->terminate();
                    exit();
                    //exitmsg("Импортировано $i записей (".$GLOBALS[time]->displaytime()." сек)","{$GLOBALS[sitepath]}admin/people.php?{$GLOBALS[sess]}");
                }
        
                exitmsg(_("Ошибки:")." \n".join(",<br>",$ldap->errors),"{$GLOBALS[sitepath]}people_import_ad.php?{$GLOBALS[sess]}");
        
            } else exitmsg(_("Ошибка соединения с")." Active Directory","{$GLOBALS[sitepath]}people_import_ad.php?{$GLOBALS[sess]}");

        }

        /**
        * Вывод формы испортирования пользователей
        */
        $sql = "SELECT name, value FROM options WHERE name='ldap_host' OR name='ldap_user'";
        $res = sql($sql);
        //if (sqlrows($res)==2) $checkbox=1;
        $ldap_host = $ldap_user = '';
        while($row = sqlget($res)) {
            if ($row['name'] == 'ldap_host') $ldap_host = $row['value'];
            if ($row['name'] == 'ldap_user') $ldap_user = $row['value'];
        }
        $GLOBALS['smarty']->assign('ldap_host',$ldap_host);
        $GLOBALS['smarty']->assign('ldap_user',$ldap_user);
        $GLOBALS['smarty']->assign('checkbox',$checkbox);            

        $GLOBALS[smarty]->display('people_import_ad.tpl');        
        
    }
    // =============================================================================================
        
    // =============================================================================================
    /**
    * Синхронизация в Cron-mode
    * Строка параметров options: hostname~|~username~|~password
    */
    function cron() {

        $importOptions = isset($_GET['options']) ? trim(strip_tags($_GET['options'])) : '';

        if (!empty($importOptions)) {
       
            /**
            * Обработка параметров переданных в адресной строке скрипту    
            */   
            $decryptedImportOptions = xor_decrypt($importOptions,ADLDAP_SECURITY_KEY);
    
            $importOptions = explode('~|~',$decryptedImportOptions);
       
            if (count($importOptions)==3) {
        
                $host = $importOptions[0];
                $username = $importOptions[1];
                $password = $importOptions[2];
        
            } else exit('[Ошибка]: '._("Неверно указаны параметры подключения к").' Active Directory');
    
            if (empty($host) || empty($username) || empty($password))
            exit('[Ошибка]: '._("Указаны не все параметры поключения к").' Active Directory');
    
            if ($ldap = new CLdap($host,$username,$password)) {
                
                $ldap->disable_simulation_mode(true);
        
                $ldap->setDataBase($GLOBALS[adodb]);

                if (($i=$ldap->import2db()) !== false) {
                    echo _("Добавлено:")." ".count($ldap->people_added).'\n';
                    echo _("Удалено:")." ".count($ldap->people_deleted).'\n';
                    echo _("Изменено:")." ".count($ldap->people_modified).'\n';
                    exit();
                    //exit("[Завершено]: Импортировано $i записей (".$GLOBALS[time]->displaytime()." сек)");
                }
        
                exit("[Ошибка]:".$ldap->ldap->error);
        
            } else exit("[Ошибка]: "._("Ошибка соединения с")." Active Directory");
    
        }  
        
        exit("[Ошибка]: "._("Параметры подключения к Active Directory не указаны"));      
        
    }
    // =============================================================================================

}

if (isset($_GET['options'])) CMain::cron();

CMain::main();

?>