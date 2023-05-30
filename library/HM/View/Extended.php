<?php
class HM_View_Extended extends Zend_View
{
    /**
     * @var string
     */
    protected $_params = array();

    private $_extendedFile = null;

    private $_disabledExtendedFile = null;

    private $_infoblocks = array();

    private $_serviceNamePane = null;
    /**
     * @var HM_Navigation
     */
    private $_contextNavigation = null;

    private $_contextNavigationModifiers = array();
    /**
     * @var HM_Navigation
     */
    private $_subjectNavigation = null;

    private $_tabLinks = array();

    public $_header = false;
    public $_subHeader = false;

    public $_withoutActivities = false;

    protected $_contextMenus = array();

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function setExtended($params)
    {
        $request = $this->getRequest();
        $this->_params = $params;
        //Если ajax-запрос то расширенная страница не нужна
        if($request->isXmlHttpRequest()){
            return false;
        }
        $this->setExtendedFile('default.tpl');

        //Добавляем виджеты на страницу в аккордеон

        $this->_serviceNamePane = strtolower($params['subjectName']);
        $this->initDefaultAccordeon($params);
        $this->initCustomAccordeon($params);

        if (isset($params['subjectIdParamName'])) {
            $this->setContextNavigation(
                strtolower($params['subjectName']),
                array(
                    $params['subjectIdParamName'] => $params['subjectId']
                )
            );
        } else {
            $this->setContextNavigation(strtolower($params['subjectName']));
        }
		# очень много памяти есть
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_CONTEXT_MENU);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, &$this);
        //pr($params);
        $this->initSubjectNavigation($params);
        if ($params['subject']) {
            $this->initTabs($params);
        }

        if ($this->_header == false) {
            if ($this->getContextNavigation()) {

                $header = new HM_View_PageHeader();
                $page = $this->navigation()->findActive($this->getContextNavigation());

                if(!empty($page)) { // это НЕ главная страница аккордеона

                    $pageParams = $page['page']->toArray();

                    if($pageParams['in_bc'] == "false"){
	                    if ($this->getSubjectNavigation()) {
	                        $page = $this->navigation()->breadcrumbs()->findActive($this->getSubjectNavigation());
	                        $pageParams = $page['page']->toArray();
	                    }
                    }

                    $header->pageTitle = $pageParams['label'];
                    $header->panelTitle = $this->getPanelShortname($params);

                } else { // это главная страница аккордеона  

                   if ($this->getSubjectNavigation()) {
                        $page = $this->navigation()->breadcrumbs()->findActive($this->getSubjectNavigation());
                        $pageParams = $page['page']->toArray();  
                    }

                    $header->pageTitle = $this->getPanelName($params); // показываем полностью
                }

                $header->panelTitleFull = $this->getPanelName($params);
                $header->panelUrl = $this->_getPanelUrlArr($params);

                if (isset($params['subject']) && is_a($params['subject'], 'HM_Subject_SubjectModel')) {
                    $header->isPinable = $this->_isPinable($params);
                    $header->isPinned = $this->_isPinned($params);
                    $header->pinUri = urlencode($request->getRequestUri());

                    $subjects = Zend_Registry::get('serviceContainer')->getService('User')->getSubjects()->getList('subid');
                    $header->isInactive = !in_array($params['subjectId'], $subjects);
            }
                $this->setHeader($header);
        }
        }

        //$this->addInfoBlock('Dummy', array('title' => _('Заглушка')));
    }

    protected function initDefaultAccordeon($params)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $subject = strtolower($params['subjectName']);
        // Add Main context Menu
        $this->addContextMenu($this->getPaneName($params['subjectName']), $subject, array($params['subjectIdParamName'] => $params['subjectId']));
        }

    /**
     * В этом методе определяем кастомные аккордеоны.
     * @param array $params Параметры расширенной страницы.
     * @todo Пожалуйста, рефакторьте меня, кто нибудь!
     */
    public function initCustomAccordeon($params)
    {
        /** @var HM_Acl $aclService */
        $aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
        $currentUserRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        
        switch($params['subjectName']){
            case 'Subject':
                // красивые виджеты для слушателя вместо меню
                if($aclService->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_ENDUSER, HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))){
                    $this->deleteContextMenu('subject');
                    $this->addInfoBlock('freeAccessToSubjectBlock', array('title' =>  _('Материалы курса'), 'subject' => $params['subject']));
                    $this->addInfoBlock('scheduledAccessToSubjectBlock', array('title' =>  _('План занятий'), 'subject' => $params['subject'], 'user' =>  $params['userId']));
                    $this->addInfoBlock('userActivityByCourseBlock', array('title' =>  _('Моя активность'), 'subject' => $params['subject']));
                }
                if($aclService->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TEACHER,HM_Role_RoleModelAbstract::ROLE_TUTOR))){
                    $this->deleteContextMenu('subject');
                    $this->addContextMenu(_('Материалы курса'), 'free_access', array($params['subjectIdParamName'] => $params['subjectId']));
                    $this->addContextMenu(_('План занятий'), 'scheduled_access', array($params['subjectIdParamName'] => $params['subjectId']));
                    $this->addContextMenu(_('Участники'), 'students', array($params['subjectIdParamName'] => $params['subjectId']));
// @todo: счетчик посещаемости починить
//                     $this->addInfoBlock('usersSubjectCounterBlock', array('subject_id' => $this->id, 'title' => _('Счетчик посещаемости курса')));

                    // @todo: рефакторить, чтоб работало для всех блоков
                    $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_CONTEXT_BLOCK);
                    Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, false);
                    if (!$event->getReturnValue()) {                   
                        $this->addContextMenu(_('iWebinar'), 'webinars', array($params['subjectIdParamName'] => $params['subjectId']));
                    }
                }
                if($aclService->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_DEAN)){
                    $this->addContextMenu(_('Обратная связь'), 'feedback', array($params['subjectIdParamName'] => $params['subjectId']));
                }
                /*
                if ($aclService->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_STUDENT, HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
                    //список занятий на оценку, при взаимном оценивании
                    $this->addInfoBlock('scheduleEvaluationBlock', array('title' =>  _('Задания на проверку'), 'subject' => $params['subject']));
                }
                 */
                // сервисы взаимодействия
                $this->addContextMenu(_('Сервисы'), 'services', array($params['subjectIdParamName'] => $params['subjectId']));
//                 $this->addInfoBlock('ActivitiesBlock', array_merge(array('title' => _('Сервисы')), $params));
                break;
            case 'Resource':
                $this->addInfoBlock('resourceRevisionsBlock', array('title' =>  _('История изменения'), 'subject' => $params['subject']));
                $this->addInfoBlock('relatedResourcesBlock', array('title' =>  _('Связанные ресурсы'), 'subject' => $params['subject']));
                break;
        }
    }

    public function initTabs($params)
    {
        $request = $this->getRequest();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());

        if (!in_array($page, array(
            strtolower($params['subjectName']).'-index-index',
            //'lesson-list-index',
            //'lesson-list-my'
        	))
        	|| $page == 'resource-index-index'
        ) {
            return true;
        }

        if (in_array($page, array('lesson-list-my', 'resource-index-index'))) {
            $this->useContentVerticalScroll = true;
        }

        if ($params['subject']) {
            $names = HM_Activity_ActivityModel::getTabActivities();
            $urls = HM_Activity_ActivityModel::getTabUrls();

            foreach($names as $activityId => $activityName) {
                if ($params['subject']->services & $activityId) {
                    $url = $urls[$activityId];
                    if (is_array($url)) {
                        $url['subject'] = strtolower($params['subjectName']);
                        $url['subject_id'] = $params['subjectId'];
                    } elseif(is_string($url)) {
                        $url .= "?subject=".strtolower($params['subjectName'])."&subject_id={$params['subjectId']}";
                    }
                    $this->addTabLink($names[$activityId], $url);

                }
            }
        }
    }

    public function addTabLink($title, $url)
    {
        if (is_array($url)) {
            if (!Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed(sprintf('mca:%s:%s:%s', $url['module'], $url['controller'], $url['action']))) {
                return false;
            }
            $url = $this->url($url);
        }
        $this->_tabLinks[$title] = $url;
    }

    public function getTabLinks()
    {
        return $this->_tabLinks;
    }

    public function clearTabLinks()
    {
        $this->_tabLinks = array();
    }

    public function setContextNavigation($name, $substitutions = null)
    {
        if(count($this->_contextMenus) > 0){
            $config = new HM_Config_Xml(APPLICATION_PATH . '/settings/context.xml', $this->_contextMenus);
        }else{
            $config = new HM_Config_Xml(APPLICATION_PATH . '/settings/context.xml', $name);
        }
        $container = new HM_Navigation($config, Zend_Registry::get('serviceContainer')->getService('Acl'), $substitutions);

        // very dangerous thing
        $this->getSubNavigation($container, $name, $substitutions);

        $this->_contextNavigation = $container;
    }

    /**
     * @param $container
     * @param $name
     * @param null $substitutions
     * @return void
     */
    public function getSubNavigation($container, $name, $substitutions = null){
        if (null !== $container) {
            $serviceContainer = Zend_Registry::get('serviceContainer');
            switch(strtolower($name)) {
                case 'subject':
                
                    //здесь было много неиспользуемого кода
                    
                    break;
                case 'user':
                    $userId = $this->_params['subjectId'];

                    if ($userId != $serviceContainer->getService('User')->getCurrentUserId()) {
                        if (!$serviceContainer->getService('Acl')->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_EDIT)) {
                            $page = $container->findBy('resource', 'cm:user:page1');
                            $page->visible = false;
                            //$container->removePage($page);
                        }

                        $page = $container->findBy('resource', 'cm:user:page3');
                        $page->visible = false;
                        //$container->removePage($page);
                    }

                    $page = $container->findByAction('study-history');
                    if ($page) {
                        $collection = $serviceContainer->getService('Graduated')->fetchAll($serviceContainer->getService('Graduated')->quoteInto('MID = ?', $userId));
                        if (!count($collection)) {
                            $page->setVisible(false);

                            //$container->removePage($page);
                        }

                    }
                    break;
                case 'resource':

                    if ($resource = $this->_params['subject']) {
                        if (
                            ($resource->created_by != $serviceContainer->getService('User')->getCurrentUserId()) &&
                            !$serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_MANAGER) // манагеру всё можно
                        ) {
                            $page = $container->findBy('resource', 'cm:resource:page1_2');
                            $page->visible = false;
                            $page = $container->findBy('resource', 'cm:resource:page1_3');
                            $page->visible = false;
                        }
                    }

                    break;
            }

            if (is_array($this->_contextNavigationModifiers) && count($this->_contextNavigationModifiers)) {
                foreach($this->_contextNavigationModifiers as $modifier) {
                    $modifier->process($container);
                }
            }
        }
    }

    /**
     * @return HM_Navigation|null
     */
    public function getContextNavigation()
    {
        return $this->_contextNavigation;
    }

    public function initSubjectNavigation($params)
    {    
        $pages = array();
        if ($params['subject']) {  
            $subject = $params['subject'];

            $pages['class'] = 'bselect';
            switch(strtolower($params['subjectName'])) {
                case 'orgstructure':
                    $pages['label'] = substr($subject->name, 0, 30);
                    if (strlen($subject->name) > 30) $pages['label'] .= '...';
                    $pages['title'] = $subject->name;
                    $pages['module'] = 'orgstructure';
                    $pages['controller'] = 'index';
                    $pages['action'] = 'index';
                    $pages['params'] = array('org_id' => $subject->soid);
                    break;
                case 'course':
                    $pages['label'] = substr($subject->Title, 0, 30);
                    if (strlen($subject->Title) > 30) $pages['label'] .= '...';
                    $pages['title'] = $subject->Title;
                    $pages['module'] = 'course';
                    $pages['controller'] = 'index';
                    $pages['action'] = 'index';
                    $pages['params'] = array('course_id' => $subject->CID);
                    break;
                case 'subject':
                    $pages['label'] = $this->getPanelShortname($params);  // echo '<pre>'; exit(var_dump($subject->name_translation));
                    $pages['title'] = $subject->name_translation;
                    $pages['module'] = 'subject';
                    $pages['controller'] = 'index';
                    $pages['action'] = 'card';

// теперь все видят на главной странице карточку
//                    if (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT) {
//                        $pages['action'] = 'my';
//                    } else {
//                        $pages['action'] = 'index';
//                    }
                    $pages['params'] = array('subject_id' => $subject->subid);
                    $pages['subjects'] = Zend_Registry::get('serviceContainer')->getService('User')->getSubjects();

                    if (!in_array($subject->subid, $pages['subjects']->getList('subid'))) {
                        $pages['class'] .= '-inactive';
                        $pages['title'] = _('Вы не являетесь слушателем данного курса');
                    }
                    break;
                case 'resource':
                    $pages['label'] = substr($subject->title, 0, 30);
                    if (strlen($subject->title) > 30) $pages['label'] .= '...';
                    $pages['title'] = $subject->title;
                    $pages['module'] = 'resource';
                    $pages['controller'] = 'index';
                    $pages['action'] = 'index';
                    $pages['params'] = array('resource_id' => $subject->resource_id);
                    break;
                case 'testabstract':
                    $pages['label'] = substr($subject->title, 0, 30);
                    if (strlen($subject->title) > 30) $pages['label'] .= '...';
                    $pages['title'] = $subject->title;
                    $pages['module'] = 'question';
                    $pages['controller'] = 'list';
                    $pages['action'] = 'test';
                    $pages['params'] = array('test_id' => $subject->test_id);
                    break;
            }

            $request = Zend_Controller_Front::getInstance()->getRequest();

            if (((strtolower($request->getActionName()) != $pages['action'])
                || (strtolower($request->getControllerName() != $pages['controller']))
                || (strtolower($request->getModuleName() != $pages['module'])))
                /*|| (in_array(strtolower($params['subjectName']), array('subject')))*/) {

                $pages['pages'] = array(array('uri' => '', 'active' => true));
                unset($pages['active']);
            }

            $pages = array('uri' => '', 'pages' => array($pages));

        }

        switch(strtolower($params['subjectName'])) {
            case 'user':

                $pages['label'] = _('Пользователь');
                $pages['module'] = 'user';
                $pages['controller'] = 'edit';
                $pages['action'] = 'card';
                $pages['active'] = 1;
                if (((strtolower($this->getRequest()->getActionName()) != $pages['action'])
                    || (strtolower($this->getRequest()->getControllerName() != $pages['controller']))
                    || (strtolower($this->getRequest()->getModuleName() != $pages['module'])))) {
                    $pages['pages'] = array(array('uri' => '', 'active' => true));
                    unset($pages['active']);
                } else {
                    $pages = array();
                }
            break;
        }

        if (count($pages)) {  
            $this->setSubjectNavigation($pages);
        }
    }

    public function getPanelName($params)
    {  
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
	
		if(strtolower($params['subjectName']) == 'subject' && $lng == 'eng' && $params['subject']->name_translation != '')
			$params['subject']->name = $params['subject']->name_translation;
	
        switch(strtolower($params['subjectName'])) {
            case 'subject': 
            case 'orgstructure':
                return $params['subject']->name;
            case 'course':
                return $params['subject']->Title;
            case 'resource':
                return $params['subject']->title;
            case 'user':
                return $params['subject']->LastName;
        }
    }

    public function getPanelShortname($params)
    {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);		
		
        if (isset($params['subject']->shortname) && !empty($params['subject']->shortname)) {
			
			if($lng == 'eng' && isset($params['subject']->shortname_translation) && $params['subject']->shortname_translation != '')
				return $params['subject']->shortname_translation;
			else
				return $params['subject']->shortname;
        } else {
            $name = $this->getPanelName($params);
            $label = substr($name, 0, 24);
            if (strlen($name) > 24) $label .= '...';
            return $label;
        }
    }

    private function _getPanelUrlArr($params)
    {
        $url = array();
        if ($params['subject']) {

            $subject = $params['subject'];
            switch(strtolower($params['subjectName'])) {

                case 'orgstructure':
                    $url = array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $subject->soid);
                    break;
                case 'course':
                    $url = array('module' => 'course', 'controller' => 'index', 'action' => 'index', 'course_id' => $subject->CID);
                    break;
                case 'subject':
                    $url = array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subject->subid);
                    break;
                case 'resource':
                    $url = array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'resource_id' => $subject->resource_id);
                    break;
            }
        }

        switch(strtolower($params['subjectName'])) {
            case 'user':
                $url = array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'resource_id' => $subject->resource_id);
            break;
        }
        return $url;
    }


    public function setSubjectNavigation(array $page)
    {
        $pages = array(
            $page
        );

        $this->_subjectNavigation = new HM_Navigation($pages);
    }

    /**
     * @return HM_Navigation|null
     */
    public function getSubjectNavigation()
    {
        return $this->_subjectNavigation;
    }

    /*public function getUnmanagedNavigation()
    {
         return Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getNavigationContainer();
    }
    */

    public function setExtendedFile($name)
    {
        //$this->addScriptPath(APPLICATION_PATH.'/views/extended/');
        $this->_extendedFile = $this->_script($name);
    }

    public function getExtendedFile()
    {
        return $this->_extendedFile;
    }

    public function disableExtendedFile()
    {
        $this->_disabledExtendedFile = $this->getExtendedFile();
        $this->_extendedFile = null;
    }

    public function enableExtendedFile()
    {
        $this->_extendedFile = $this->_disabledExtendedFile;
    }

    public function addInfoBlock($name, $options = null)
    {
        $this->_infoblocks[] = array('name' => $name,'options' => $options);
    }

    public function getInfoBlocks()
    {
        $temp = $this->_infoblocks;

        foreach($temp as $key => $block){
            if($block['name']  == 'ActivitiesBlock'){
                unset($this->_infoblocks[$key]);
                $this->_infoblocks[] = $block;
            }
        }

        return $this->_infoblocks;
    }

    /**
     * @param  string $name
     * @return string
     */
    public function render($name)
    {
        $extendedFile = null;
        if (null !== $this->_extendedFile) {
            $extendedFile = $this->_extendedFile;
            $this->_extendedFile = null;
        }
        $workspace = parent::render($name);
        if (null == $extendedFile) return $workspace;
        $this->workspace = $workspace;
//        ob_start();
        $this->_run($extendedFile);
//        return str_replace('#WORKSPACE#', $workspace, ob_get_clean());
    }

    public function setHeader($header)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $serviceContainer->getService('Unmanaged')->setHeader($header);
        $this->_header = $header;
    }

    public function setSubHeader($subHeader)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $serviceContainer->getService('Unmanaged')->setSubHeader($subHeader);
        $this->_subHeader = $subHeader;
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function getPaneName(){
        $names = array('subject' => _('Учебный курс'), 'user' => _('Пользователь'), 'course' => _('Учебный модуль'), 'resource' => _('Информационный ресурс'));

        if(in_array($this->_serviceNamePane, array_keys($names)) === false){
            return _('Операции');
        }else{
            return $names[$this->_serviceNamePane];
        }

    }

    public function addContextMenu($title, $partition, $substitutions)
    {
        $this->addInfoBlock('ContextMenuBlock', array('title' => $title, 'partition' => $partition, 'substitutions' => $substitutions));
        $this->_contextMenus[] = $partition;
    }

    public function deleteContextMenu($partition){

        $this->deleteInfoBlock(null, $partition);
        $key = array_search($partition, $this->_contextMenus);
        if($key !== false){
            unset($this->_contextMenus[$key]);
        }
    }

    public function deleteInfoBlock($name, $partition = null){

        if($partition != NULL){
            foreach($this->_infoblocks as $key => $value){
                if($value['options']['partition'] == $partition){
                    unset($this->_infoblocks[$key]);
                }
            }
        }else{
            foreach($this->_infoblocks as $key => $value){
                if($key == $name){
                    unset($this->_infoblocks[$key]);
                }
            }
        }
    }

    public function getContextMenus()
    {
        return $this->_contextMenus;
    }

    public function addContextNavigationModifier(HM_Navigation_Modifier $modifier)
    {
        $this->_contextNavigationModifiers[] = $modifier;
    }

    // @todo: подумать как это узнать через ACL
    // @todo: реализовать для других вирт.кабинетов
    protected function _isPinable()
    {
        $request = $this->getRequest();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        return in_array($page, array(
            'subject-index-card',
            'subject-index-index',
            'subject-materials-index',
            'resource-index-index',
            'lesson-list-index', // ??
            'lesson-list-my',    // ??
            'news-index-index',
            'news-index-view',
            'forum-index-index',
            'blog-index-index',
            'blog-index-view',
            'wiki-index-index',
            'wiki-index-view',
            'chat-index-index',
            'chat-index-view',
            'message-contact-index',
            'message-view-index',
            'storage-index-index',
        ));
    }

    public function _isPinned($params)
    {
        if (!empty($params['subject']->default_uri)) {
            $request = $this->getRequest();
            return $params['subject']->default_uri == $request->getRequestUri();
        }
    }

    /*
     * pageTitle
     * panelTitle
     * panelTitleFull
     * panelUrl = array(module, controller, action)
    */
    public function setHeaderOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->_header->$key = $value;
        }
    }
    public function getParam($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : false;
    }

}