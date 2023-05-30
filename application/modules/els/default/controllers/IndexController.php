<?php

class IndexController extends HM_Controller_Action {

	protected $userId = 0;
	protected $userRole = HM_Role_RoleModelAbstract::ROLE_GUEST;
	
    public function init()
    {
        parent::init();
        $this->getService('Unmanaged')->getController()->page_id = null;
        
        $userService = $this->getService('User');
        $this->userId = (int) $userService->getCurrentUserId();
        $this->userRole = $userService->getCurrentUserRole();

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
            $this->userRole  = HM_Role_RoleModelAbstract::ROLE_ENDUSER;
        }

    }
    
    public function indexAction() { 
		
		// $lng = $this->getRequest()->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		// $opt = $this->getInvokeArg('bootstrap')->getOption('language');
		// $lng = $opt["current"];
 
        if($this->_hasParam('oauth_token') && $this->userRole != HM_Role_RoleModelAbstract::ROLE_GUEST){
            $this->_redirector->gotoUrl($this->view->url(array(
                'module'	=> 'oauth',
                'controller'=> 'v1',
                'action'	=> 'authorize'
            ))
            .'?&oauth_token='.$this->_getParam('oauth_token')
            .'&oauth_callback='.$this->_getParam('oauth_callback', ''));
        }

        // It's a hack to remove some js and css from error page
        if(count($this->_response->getException()) > 0){
            $this->getHelper('viewRenderer')->setNoRender();
            return;
        }

        if (!$this->_hasParam('oauth_token')) {
            $blocks = $this->getService('Infoblock')->getTree($this->userRole, false, $this->userId);
        }
        else $blocks = array('current' => array());

        if(0 && $this->userRole === HM_Role_RoleModelAbstract::ROLE_GUEST){
            // Мы НЕ добавляем инфоблок авторизации принудительно
            // Его можно добавить через штатный механизм настройки главной страницы 
            $block = array(
                'name' => 'Authorization',
                'title' => _('Авторизация'),
                'x' => -1,
                'y' => 0
            );

            $found = false;
            if (count($blocks['current'])) {
                foreach ($blocks['current'] as $key => $bl) {
                    if ($bl['name'] == $block['name']) {
                        $blocks['current'][$key]['x'] = $block['x'];
                        $blocks['current'][$key]['y'] = $block['y'];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $blocks['current'][] = $block;
            }
        }

        $this->view->roles      = HM_Role_RoleModelAbstract::getBasicRoles();
        $this->view->role       = $this->userRole;   // echo '<pre>'; exit(var_dump($this->getService('Infoblock')->returnBlocks($blocks, 'view')));
        $this->view->blocks     = $this->getService('Infoblock')->returnBlocks($blocks, 'view');   
        $this->view->isAdmin    = $this->getService('User')->isRoleExists($this->userId, HM_Role_RoleModelAbstract::ROLE_ADMIN);
        $this->view->isEditMode = false;
        $this->view->user       = $this->getService('User')->getCurrentUser();
    }
    
    public function simpleAuthorizationAction()
    {
        $key = $this->_getParam('key');

        $this->getService('User')->authorizeSimple($key);
    }

    public function authorizationAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        $user = false;
        $form = new HM_Form_Authorization();

        $return = array(
            'code' => 0,
            'message' => _('Вы неверно ввели имя пользователя или пароль')
        );

        $request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
            
            $systemInfo = $request->getParam('systemInfo');
            
            if ($request->getParam('start_login', false) && $form->isValid($request->getParams())) {
                try {
                    $user = $this->getService('User')->authorize($form->getValue('login'), $form->getValue('password'), false, false, $systemInfo);
                    $return['code']    = 1;
                    $return['message'] = _('Пользователь успешно авторизован.');
                    
                    $this->view->jQuery()->addOnLoad('window.location.reload()');
                } catch(HM_Exception_Auth $e) {
                    $return['code']    = $e->getCode();
                    $return['message'] = $e->getMessage();
                }
            } else {
            	$return = array(
            			'code' => 0,
            			'message' => _('Превышено количество неуспешных попыток авторизации, для продолжения необходимо ввести код подтверждения.')
            	);
                $this->getService('Captcha')->attempt($form->getValue('login'));
            }
        }

        if ($request->getParam('start_login', false) && $return['code'] != 1) {
            $form = new HM_Form_Authorization();
            $form->isValid($request->getParams());
            
            $this->getService('UserLoginLog')->login($form->getValue('login'), $return['message'], HM_User_Loginlog_LoginlogModel::STATUS_FAIL);
        } else {
            if ($form->getValue('remember')) {
                $this->getService('Session')->setAuthorizerKey();
            }
            
            $this->getService('UserLoginLog')->login($form->getValue('login'), $return['message'], HM_User_Loginlog_LoginlogModel::STATUS_OK);
            
        }

        if ( $return['code'] != 1  ){

            echo $form->render();
            if ($request->getParam('start_login', false)) {
                echo $this->view->notifications(array(array(
                    'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => $return['message'] . ' !!'
                )), array('html' => true));
            }
        }
    }

    public function logoutAction()
    {
        $this->getService('User')->logout();
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function restoreAction()
    {
        $this->getService('User')->restore();
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function switchAction()
    {
        //pr($this->_getParam('role', false));

        $this->getService('User')->switchRole($this->_getParam('role', false));
        $this->session = new Zend_Session_Namespace('default');
        $this->session->switch_role = 1;
        $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        //$this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function rememberAction()
    {
        $form = new HM_Form_Remember();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $user = $this->getOne(
                    $this->getService('User')->fetchAll($this->getService('User')->quoteInto('Login = ?', $form->getValue('login')))
                );

                if (!$user) {
                    $this->_flashMessenger->addMessage(_('Пользователь не найден.'));
                } else {
                    $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
                    $lastDate = $this->getService('UserPassword')->getChangePasswordLastDate($user->MID);
                    if((time() - strtotime($lastDate)) < ($passwordOptions['passwordMinPeriod'] * 3600*24)){
                        $this->_flashMessenger->addMessage(_('Восстановление пароля невозможно. Не прошел минимальный срок действия пароля.'));
                    }
                    elseif (!strlen($user->EMail)) {
                        $this->_flashMessenger->addMessage(_('Восстановление пароля невозможно. Отсутствует адрес электронной почты.'));
                    } else {
                    
                        $password = $this->getService('User')->getRandomString();
                        $user->Password = new Zend_Db_Expr($this->getService('User')->quoteInto('PASSWORD(?)', $password));
                        $this->getService('User')->update($user->getValues());
                        // отправка нового пароля на email
                        $messenger = $this->getService('Messenger');
                        $messenger->setOptions(HM_Messenger::TEMPLATE_PASS, array('login' => $user->Login, 'password' => $password));
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                        $this->_flashMessenger->addMessage(_('Пароль успешно отправлен на электронную почту.'));
                    }
                }


                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }

        $this->view->form = $form;
    }
    
    public function forcePasswordAction()
    {
        $form = new HM_Form_Force();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                
                $user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId())));
                $userAuth = $this->getService('User')->getCurrentUser();
                
                if($user){
                    $user->Password = new Zend_Db_Expr($this->getService('User')->quoteInto('PASSWORD(?)', $form->getValue('password')));
                    $userAuth->force_password = 0;
                    $user->force_password = 0;
                    $this->getService('User')->update($user->getValues());
                    
                    $this->_flashMessenger->addMessage(_('Пароль успешно обновлен.'));
                    $this->_redirector->gotoSimple('index', 'index', 'default');
                }else{
                    $this->_flashMessenger->addMessage(_('Пользователь не найден.'));
                }
            }
        }

        $this->view->form = $form;
    }

    

    public function errorAction() {
        $this->view->content = '';
    }

    public function ldapAction()
    {
        die('hack detected');

        $options = Zend_Registry::get('config')->ldap->options->toArray();
        $options['username'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $options['username']);

        $ldap = new Zend_Ldap($options);
        $ldap->bind();
        
        $dn = $ldap->getCanonicalAccountName(
            'user1',
            Zend_Ldap::ACCTNAME_FORM_DN
        );

        $entry = $ldap->getEntry($dn);

          
        pr($entry);
        pr($dn);
        die();
    }

    public function dataGridAction()
    {
        // dataSheet
        $data = array();
        $data[1][1] = 1;
        $data[1][2] = 2;
        $data[1][3] = 3;
        $data[2][1] = 21;
        $data[2][2] = 22;
        $data[2][3] = 23;
        $data[3][1] = 31;
        $data[3][2] = 32;
        $data[3][3] = 33;

        $sheet = HM_DataSheet::factory(
            'table',
            array(
                'horizontalHeader' => array(
                    'name' => 'cols',
                    'title' => _('Название горизонтального заголовка'),
                    'checkboxes' => true,
                    'fields' => array(
                        1 => array('title' => _('Заголовок 1'), 'pattern' => '[2-5]'),
                        2 => array('title' => _('Заголовок 2'), 'render' => 'checkbox'),
                        3 => array('title' => _('Заголовок 3'), 'render' => 'select', 'values' => array(31 => 'триодин', 32 => 'тридва', 33 => 'тритри')),
                        4 => array('title' => _('Заголовок 4')),
                        5 => array('title' => _('Заголовок 5')),
                        6 => array('title' => _('Заголовок 6')),
                        7 => array('title' => _('Заголовок 7')),
                        8 => array('title' => _('Заголовок 8')),
                        9 => array('title' => _('Заголовок 9')),
                        10 => array('title' => _('Заголовок 10')),
                        11 => array('title' => _('Заголовок 11')),
                        12 => array('title' => _('Заголовок 12')),
                        13 => array('title' => _('Заголовок 13')),
                        14 => array('title' => _('Заголовок 14')),
                        15 => array('title' => _('Заголовок 15')),
                        16 => array('title' => _('Заголовок 16')),
                    )
                ),
                'verticalHeader' => array(
                    'name' => 'rows',
                    'title' => _('Название вертикального заголовка'),
                    'checkboxes' => true,
                    'fields' => array(
                        1 => array('title' => _('Вертикальный заголовок 1')),
                        2 => array('title' => _('Вертикальный заголовок 2')),
                        3 => array('title' => _('Вертикальный заголовок 3'))
                    )
                ),
                'data' => $data,
                'saveUrl' => $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default'))
            )
        );

        $actions = new HM_DataSheet_Actions(_('Действия со строками'));
        $actions->addAction(_('Тестовое действие'), $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default')));

        $sheet->setVerticalActions($actions);

        $actions = new HM_DataSheet_Actions(_('Действия со столбцами'));
        $actions->addAction(_('Тестовое действия со столбцами'), $this->view->url(array('action' => 'data-grid', 'controller' => 'index', 'module' => 'default')));

        $sheet->setHorizontalActions($actions);

        $this->view->sheet = $sheet->deploy();

    }

    public function languageAction()
    {

        $returnUrl = $_SERVER['HTTP_REFERER'];

        $lang = $this->_getParam('lang', 'rus');

        $langs = Zend_Registry::get('config')->languages->toArray();

        if (isset($langs[$lang])) {
            setcookie(HM_User_UserService::COOKIE_NAME_LANG, $lang, 0, '/');
			

            if ((int) $this->getService('User')->getCurrentUserId() > 0) {
                $this->getService('User')->update(array('lang' => $lang, 'MID' => $this->getService('User')->getCurrentUserId()));
                $user = $this->getService('User')->getCurrentUser();
                if ($user) {
                    $user->lang = $lang;
                }
            }
        }

        $this->_redirector->gotoUrl($returnUrl);
    }
 
}