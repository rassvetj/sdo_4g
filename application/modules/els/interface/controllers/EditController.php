<?php

class Interface_EditController extends HM_Controller_Action
{

    private function __columnsCss($columns, $spacing, $className)
    {
        $cssCommon = <<<COMPCSS
$className .hgll-colmask { margin: 0 !important; border: 0 !important; padding: 0 !important; position: relative; float: none; clear: both; width: 100%; overflow: hidden; }
$className .hgll-colwrap-middle, $className .hgll-colwrap-inner, $className .hgll-col1wrap, $className .hgll-col1, $className .hgll-col2, $className .hgll-col3 { left: auto; right: auto; float: none; position: static; margin-left: 0; margin-right: 0; padding-left: 0; padding-right: 0; width: auto; overflow: visible; }
$className .hgll-colwrap-middle, $className .hgll-colwrap-inner { float: left; width: 100%; position: relative; }
$className .hgll-col1, $className .hgll-col2, $className .hgll-col3 { float: left; position: relative; padding: 0; overflow: hidden; }
$className .hgll-colwrap-outer { background: transparent; }
COMPCSS;

        $css;
        $cssBookmark = <<<COMPCSS
$className .dashboard-columns-reminder { display: none; }
$className .dashboard-columns-reminder .d-first, $className .dashboard-columns-reminder .d-second, $className .dashboard-columns-reminder .d-third { width: 0; }
COMPCSS;
        $first = $columns[0];
        $second = $columns[1];

        $firstContent = $first - $spacing / 2;

        if (count($columns) == 3) {
            $third = $columns[2];
            $secondContent = $second - $spacing;
            $thirdContent = $third - $spacing / 2;
            $firstOffset = 100 - $first;
            $secondOffset = 100 - $first + $spacing;
            $thirdOffset = 100 - $first + 2 * $spacing;

            $css = <<<COMPCSS
$className .hgll-colwrap-middle { right: $third%; background: transparent; }
$className .hgll-colwrap-inner { right: $second%; background: transparent; }
$className .hgll-col1 { width: $firstContent%; left: $firstOffset%; }
$className .hgll-col2 { width: $secondContent%; left: $secondOffset%; }
$className .hgll-col3 { width: $thirdContent%; left: $thirdOffset%; }
$className .dashboard-columns-reminder .d-first { width: {$first}px;}
$className .dashboard-columns-reminder .d-second { width: {$second}px; }
$className .dashboard-columns-reminder .d-third { width: {$third}px; }
COMPCSS;
        } else if (count($columns) == 2) {
            $secondContent = $second - $spacing / 2;

            $firstOffset = $second;
            $secondOffset = $second + $spacing;

            $css = <<<COMPCSS
$className .hgll-colwrap-inner { right: $second%; background: transparent; }
$className .hgll-col1 { width: $firstContent%; left: $firstOffset%; }
$className .hgll-col2 { width: $secondContent%; left: $secondOffset%; }
$className .dashboard-columns-reminder .d-first { width: {$first}px;}
$className .dashboard-columns-reminder .d-second { width: {$second}px; }
COMPCSS;
        } else if (count($columns) == 1) {
            $css = "$className .hgll-col1 { width: 100%; left: 0; } $className .dashboard-columns-reminder .d-first { width: {$first}px; }";
        }
        
        $return = $cssBookmark."\n".$cssCommon."\n".$css;
        
        // если неправильная локаль, то дробные проценты сохраняются с запятой и браузер не понимает
        $pattern = '/(\w+): ([0-9]+)\,([0-9]+)%;/';
        $replacement = '$1: $2.$3%;';
        if (preg_match($pattern, $return, $matches)) {
            $return = preg_replace($pattern, $replacement, $return);
        }
        
        return $return;
    }

    public function indexAction()
    {
        $this->getService('Unmanaged')->setHeader(_('Главная страница Портала'));
        $this->prepare();

        $role   = $this->_getParam('role', 'admin');
        $userId = $this->getService('User')->getCurrentUserId() ? $this->getService('User')->getCurrentUserId() : 0;


        $blocks = $this->getService('Infoblock')->getTree($role, false, 0);

        $this->view->roles      = HM_Role_RoleModelAbstract::getBasicRoles(true, true);
        $this->view->role       = $role;
        $this->view->blocks     = $this->getService('Infoblock')->returnBlocks($blocks, 'edit');
        $this->view->isAdmin    = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN) || $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_ADMIN);
        $this->view->isEditMode = true;
        $this->view->user       = $this->getService('User')->getCurrentUser();
        //$this->view->infoblocks = $blocks['all'];
    }

    public function clearMeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $role = $this->_request->getParam('role', $this->getService('User')->getCurrentUserRole());
        $userId = $this->getService('User')->getCurrentUserId();
        $currentUserRole = $this->getService('User')->getCurrentUserRole();

        if(in_array($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_EMPLOYEE, HM_Role_RoleModelAbstract::ROLE_STUDENT, HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, HM_Role_RoleModelAbstract::ROLE_USER))){
            $currentUserRole = HM_Role_RoleModelAbstract::ROLE_ENDUSER;
        }
        if ($role && $userId && $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_ADMIN)) {
            $this->getService('Infoblock')->clearUserData($role, $userId);
            echo ucfirst($role)."!\n";
            echo "The coast is clear";
        } else if ($userId && $currentUserRole) {
            $this->getService('Infoblock')->clearUserData($currentUserRole, $userId);
            echo "How dare U. Just kidding";
        } else {
            echo "How dare U. NOT kidding";
        }
    }

    public function updateAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $role = $this->_request->getParam('role');
        $userId = $this->getService('User')->getCurrentUserId();

        // TODO: check that this is admin and that role exists
        if ( $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_ADMIN) ) {
            $portlets = Zend_Json::decode($this->_request->getParam('portlets'));
            $columns  = Zend_Json::decode($this->_request->getParam('columns'));

            if (is_array($portlets) && is_array($columns)) {
                $this->getService('Infoblock')->insertBlocks($portlets, $role);

                file_put_contents(PUBLIC_PATH."/upload/user-css/index-$role.css", $this->__columnsCss($columns, 1, ".user-$role-dashboard") );
                file_put_contents(PUBLIC_PATH."/upload/user-css/index-$role-settings.json", Zend_Json::encode( array( 'columns' => array( 'count' => count($columns) ) ) ) );
            } else {
            }
        } else {
            throw new Exception("U'r not allowed he-he", 403);
        }
    }


    public function updateMyAction(){

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $role = $this->_request->getParam('role');
        $role = $role ? $role : $this->getService('User')->getCurrentUserRole();
        $portlets = $this->_request->getParam('portlets');

        $portlets = Zend_Json::decode($portlets);
        $userId = $this->getService('User')->getCurrentUserId() ? $this->getService('User')->getCurrentUserId() : 0;

        if ($userId != 0){
            $this->getService('Infoblock')->insertBlocks($portlets, $role, $userId);
        }
    }


    /*
    public function myAction()
    {
        $this->prepare();

        $role = $this->getService('User')->getCurrentUserRole();

        $this->view->roles = HM_Role_RoleModelAbstract::getBasicRoles();
        //array_unshift($this->view->roles, _('Выберите роль'));

        $userId = $this->getService('User')->getCurrentUserId() ? $this->getService('User')->getCurrentUserId() : 0;
        $this->view->setExtended(
            array(
                    'subjectName' => 'user',
                    'subjectId' => 0,
                    'subjectIdParamName' => 'user_id',
                    'subjectIdFieldName' => 'MID',
            )
        );

        $blocks = $this->getService('Infoblock')->getTree($role, false, $userId);
        $this->view->columns = $this->getService('Infoblock')->returnBlocks($blocks, 'edit');
        $this->view->infoblocks = $blocks['all'];
        $this->view->role = $role;
    }
    */


    public function prepare(){
        $this->getService('Unmanaged')->getController()->page_id = 'm00';

        $userId = $this->_getParam('user_id', 0);
        if ($userId > 0) {
            if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_VIEW)) {
                $userId = $this->getService('User')->getCurrentUserId();
            }
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if ($this->getRequest()->getActionName() == 'card') {
            $this->view->setHeader(_('Личный кабинет'));
            if ($userId != $this->getService('User')->getCurrentUserId()) {
                $user = $this->getOne($this->getService('User')->find($userId));
                if ($user) {
                    $this->view->setHeader(sprintf(_('Пользователь %s'), $user->getName()));
                }
            }
        }

        $this->_userId = $userId;

        // Если нет истории обучения, то удаляем этот пункт из меню
        $container = $this->view->getContextNavigation();
        if (null != $container) {
            if ($userId != $this->getService('User')->getCurrentUserId()) {
                if (!$this->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_EDIT)) {
                    $page = $container->findBy('resource', 'cm:user:page2');
                    $page->visible = false;
                    //$container->removePage($page);
                }

                $page = $container->findBy('resource', 'cm:user:page3');
                $page->visible = false;
                //$container->removePage($page);
            }

            $page = $container->findByAction('study-history');
            if ($page) {
                $collection = $this->getService('Graduated')->fetchAll($this->getService('Graduated')->quoteInto('MID = ?', $userId));
                if (!count($collection)) {
                    $page->visible = false;
                    //$container->removePage($page);
                }
            }
        }



    }






}