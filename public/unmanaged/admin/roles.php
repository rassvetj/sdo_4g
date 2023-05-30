<?php
require_once("../1.php");
require_once("../lib/classes/xml2array.class.php");
require_once("../lib/classes/Roles.class.php");
require_once("../lib/classes/Person.class.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
//if (!$s['perm']) login_error();

$smarty = new Smarty_els();

if (isset($_POST['action'])) $action = isset($_POST['action']) ? $_POST['action'] : "";
if (isset($_GET['action'])) $action = isset($_GET['action']) ? $_GET['action'] : "";

$values = false;
switch($_POST['post_action']) {
    case 'post_new'://switched OFF
        $values['role_name'] = trim(strip_tags($_POST['role_name']));
        $s['roles']['values'] = $values;
    break;
    case 'post_step_1':
        $values = $s['roles']['values'];
        $values['base_role'] = floatval($_POST['base_role']);
        $values['role_name'] = trim(strip_tags($_POST['role_name']));
        $values['default'] = (int) $_POST['default'];
        if (empty($values['role_name'])) {
            $msg[] = _("Не указано название новой роли");
            $_GET['step'] = 1;
        }
        $s['roles']['values'] = $values;
    break;
    case 'post_step_2':
        $values = $s['roles']['values'];
        $values['pages'] = $_POST['need_actions'];
        if (!is_array($values['pages'])) {
            $msg[] = _("Не выбраны базовые действия для роли");
            $_GET['step'] = 2;
        }
        $s['roles']['values'] = $values;
    //break;
    //case 'post_step_3':

                $actions = new CActions();
                $subactions = $actions->get_actions_by_pages($s['roles']['values']['pages'],array($base_roles[$s['roles']['values']['base_role']]));
                $_POST['actions'] = $subactions;


        $values = $s['roles']['values'];
        $values['actions'] = $_POST['actions'];
        if (!is_array($values['actions'])) {
            $msg[] = _("Не выбраны разрешенные действия для роли");
            $_GET['step'] = 3;
        }
        $actions = new CActions();
        
        // Подготовка и оптимизация пермишнов перед сохранением
        $values['actions'] = $actions->prepare_actions_to_save($values['actions'],$values['pages']);
        
        $role = new CRole();
        $role->save($values);
        unset($s['roles']);
        refresh($sitepath."admin/roles.php");
        exit();
    break;
    case 'post_assign':
        $pmid = (int) $_POST['pmid'];
        $people2role = $_POST['people2role'];
        $people = $_POST['people'];
        CRole::del_mids_from_role($people,$pmid);
        if (is_array($people2role) && count($people2role)) {
            foreach($people2role as $v) {    
                CRole::add_mid_to_role($v,$pmid);
            }
        }
        refresh($sitepath."admin/roles.php");        
    break;
}

$smarty->assign('action',$action);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('msg',$msg);
$smarty->assign('values',$s['roles']['values']);

switch ($action) {
    case 'add':
    case 'edit':
        if (!isset($step)) {
            $step = isset($_GET['step'])? (int) $_GET['step'] : 1;
        }
        $GLOBALS['controller']->setHelpSection("step0$step");
        switch($step) {
            case 1:       
                $GLOBALS['controller']->setSubHeader(_('Шаг 1. Выбор базовой роли'));
                       
                if ($action=='edit') {
                    $role = new CRole();
                    if (!isset($s['roles']['values'])) $s['roles']['values'] = $role->get_info((int) $_GET['id']);                    
                }     
                //создание ролей на базе гостя запрещено
                //$base_roles = array_keys($GLOBALS['profiles_basic_aliases']);
                //$base_roles_lang = $GLOBALS['profiles_basic_aliases'];
                /**
                 * Создание ролей на базе пользователя так же запрещено.
                 *
                 * @author Artem Smirnov <tonakai.personal@gmail.com>
                 * @date 24.01.13
                 */
                foreach(array(PROFILE_EMPLOYEE, PROFILE_STUDENT, PROFILE_SUPERVISOR, PROFILE_USER, PROFILE_GUEST, PROFILE_ENDUSER) as $key){
                    unset($base_roles[$guestKey = array_search($key,$base_roles)]);
                    unset($base_roles_lang[$guestKey]);
                }
                //pr($base_roles_lang);
                //pr($GLOBALS['profiles_basic_aliases'][PROFILE_EMPLOYEE]); exit;

                //unset($base_roles[$guestKey = array_search($GLOBALS['profiles_basic_aliases'][PROFILE_EMPLOYEE],$base_roles)]);
                //pr($base_roles); exit;

                $smarty->assign('base_roles',$base_roles);
                $smarty->assign('base_roles_lang',$base_roles_lang);
            break;
            case 2:
                $GLOBALS['controller']->setSubHeader(_('Шаг 2. Выбор функций'));
                $actions = new CActions();
                for($i=0;$i<=$s['roles']['values']['base_role'];$i++) $all_action_roles[] = $base_roles[$i];
                $all_actions = $actions->get_actions_names_by_roles($all_action_roles);
                $necessary_actions = $actions->get_necessary();                
                if (is_array($s['roles']['values']['pages'])) {
                    foreach($s['roles']['values']['pages'] as $v) {
                        if (isset($all_actions[$v])) $need_actions[$v] = $all_actions[$v];
                    }
                } else {
                    $need_actions = $actions->get_actions_names_by_roles(array($base_roles[$s['roles']['values']['base_role']]));
                }
                if (is_array($need_actions)) $all_actions = array_diff($all_actions,$need_actions);
                $smarty->assign('actions',$all_actions);
                $smarty->assign('need_actions',$need_actions);
                $smarty->assign('necessary_actions',$necessary_actions);
            break;
            case 3:
                $GLOBALS['controller']->setSubHeader(_('Шаг 3. Детализация функций'));
                $actions = new CActions();
                $subactions = $actions->get_actions_by_pages($s['roles']['values']['pages'],array($base_roles[$s['roles']['values']['base_role']]));
                if ($action == 'edit') $smarty->assign('role_actions',$s['roles']['values']['actions']);
                else {
                    /**
                    * ===================== Создания массива экшенов которые доступны по умолчанию базовому профилю
                    */
                    $rolesactions = $actions->get_actions_by_pages($s['roles']['values']['pages'],array($base_roles[$s['roles']['values']['base_role']]),true);                                        
                    while(list($k,$v) = each($rolesactions)) {
                        $action_names = array('tabs','links','options');
                        foreach($action_names as $action_name) {
                            reset($v[$action_name]);
                            while(list($kk,$vv) = each($v[$action_name])) $role_actions[] = $vv['id'];
                        }
                    }
                    $smarty->assign('role_actions',$role_actions);
                    /**
                    * =====================
                    */
                }
                $smarty->assign('actions',$subactions);
            break;
        }
        if ($action=='edit') $smarty->assign('values',$s['roles']['values']);
        $smarty->assign('backButton', button(_('Назад'), '', 'Backward', '', $GLOBALS['sitepath']."admin/roles.php".(($step>1) ? "?action=$action&step=".($step-1) : '')));
        $smarty->assign('cancelButton', button(_('На главную'), '', 'Cancel', '', $GLOBALS['sitepath']."admin/roles.php"));
        $smarty->assign('submitButton', okbutton(_('Вперёд')));
        $smarty->display('roles_step_'.$step.'.tpl');
    break;
    case 'delete':
        CRole::del($_GET['id']);
        refresh($sitepath."admin/roles.php");
    break;
    case 'assign':
        $GLOBALS['controller']->setHelpSection('assignment');
        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
                
        $js = 
            "
            function show_user_select(html) {
                var elm = document.getElementById('people_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"people\" name=\"people[]\" multiple style=\"width:100%\">'+html+'</select>';
            }
                    
            function get_user_select(str) {
                var current = 0;
                
                var select = document.getElementById('search_people');
                if (select) current = select.value;
                
                var elm = document.getElementById('people_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"people\" name=\"people[]\" multiple style=\"width:100%\"><option>"._("Загружаю данные...")."</option></select>';
                        
                get_user_select_used('');
                x_search_people_unused(str, show_user_select);
            }
            
            function show_user_select_used(html) {
                var elm = document.getElementById('people2role_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"people2role\" name=\"people2role[]\" multiple style=\"width: 100%\">'+html+'</select>';            
            }
            
            function get_user_select_used(str) {
                var elm = document.getElementById('people2role_container');
                if (elm) elm.ennerHTML = '<select size=10 id=\"people2role\" name=\"people2role[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
                x_search_people_used(str, show_user_select_used);
            }
                       
            ";
            
        $sajax_javascript = CSajaxWrapper::init(array('search_people_unused','search_people_used')).$js;        

        $smarty->assign('pmid',(int) $_GET['id']);
        $people = CRole::get_all_people();
        $search = '';
        if (count($people)<ITEMS_TO_ALTERNATE_SELECT) $search = '*';
        $people2role = CRole::get_people_by_pmid($_GET['id']);
        if (is_array($people) && is_array($people2role)) $people = array_diff($people,$people2role);
        $role_name = CRole::get_name($_GET['id']);
        
        $unused = '';
        if (is_array($people) && count($people) && ($search=='*')) {
            foreach($people as $mid=>$name) {
               $unused .= "<option value=\"$mid\"> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }

        $used = '';
        if (is_array($people2role) && count($people2role)) {
            foreach($people2role as $mid=>$name) {
                $used .= "<option value=\"$mid\"";
                $used .= "> ".htmlspecialchars($name,ENT_QUOTES)."</option>";                
            }
        }
        
        if (empty($search)) $search = _('введите часть имени или логина');
        $smarty->assign('sajax_javascript',$sajax_javascript);
        $smarty->assign('people',$unused);
        $smarty->assign('search',$search);
        $smarty->assign('people2role',$used);
        $smarty->display('roles_assign.tpl');
    break;
    case 'info':
        $GLOBALS['controller']->setView('DocumentPrint');
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $roleInfo = new CRoleInfo();
        $roleInfo->init($_GET['id']);
        $smarty->assign('info',$roleInfo->get());
        $smarty->assign('skin_url',$GLOBALS['controller']->view_root->skin_url);
        $smarty->assign('created_by', CPerson::get($_SESSION['s']['mid'],'LF'));
        $smarty->display('roles_info.tpl');
        $GLOBALS['controller']->terminate();
    break;
    default:
        $actions = new CActions();
        $role = new CRole();
        $smarty->assign('roles_names',$actions->get_all_actions_names());
        $smarty->assign('roles',$role->get_all());
        $smarty->assign('okbutton',okbutton());
        unset($s['roles']);        
        $smarty->display('roles.tpl');    
}

function search_people_unused($search) {
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $people = array();        
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People 
                WHERE (People.LastName LIKE '%".addslashes($search)."%'
                OR People.FirstName LIKE '%".addslashes($search)."%'
                OR People.Login LIKE '%".addslashes($search)."%')                
                ORDER BY LastName, FirstName, Login";
        $res = sql($sql);
        while($row = sqlget($res)) {            
            $people[$row['MID']] = $row['Login']." ({$row['LastName']} {$row['FirstName']} {$row['Patronymic']})";
        }        
        $people2role = CRole::get_people_by_pmid($_GET['id']);
        if (is_array($people) && is_array($people2role)) $people = array_diff($people,$people2role);
        if (is_array($people) && count($people)) {
            foreach ($people as $mid=>$name) {
                $html .= "<option value=\"$mid\"> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
            }
        }
    }    
    return $html;
}

function search_people_used($search) {
    $html = '';
    $people2role = CRole::get_people_by_pmid($_GET['id']);
    if (is_array($people2role) && count($people2role)) {
        foreach($people2role as $mid=>$name) {
            $html .= "<option value=\"$mid\"> ".htmlspecialchars($name,ENT_QUOTES)."</option>";
        }
    }
    return $html;
}

?>