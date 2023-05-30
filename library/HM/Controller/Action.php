<?php
/*
 *  custom log
 * */
function ll($str){
    $f=fopen("C:\\Temp\\logs\\1.log", "a");
    fwrite($f, $str.PHP_EOL);
    fclose($f);
}

function lv($var){
    ob_start();
    var_dump($var);
    $res = ob_get_clean();

    ll($res);
}


abstract class HM_Controller_Action extends Zend_Controller_Action
{
    protected $_user;
    protected $required_permission_level;
    protected $_gridAjaxRequest = null;
    protected $_ajaxRequest = false;

    protected $courseCache = null;//#17462
    protected $departmentCache = array();
    protected $classifierCache = array();
    protected $lessonsCache = null;
    protected $testsCache = array();
    protected $subjectsCache = array();

    /**
     * MUST BE OVERRIDEN
     * @var HM_Service_Abstract
     */
    protected $_defaultService = null;

    /**
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger = null;

    /**
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    protected $_serviceContainer;

    protected $_acl;

    /**
    * View object
    * @var HM_View_Extended
    */
    public $view;

    public function init()
    {
        //  Этот код НЕОБХОДИМ!!!
        $this->_acl = $this->getService('Acl');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_redirector = $this->_helper->getHelper('ConditionalRedirector');

        $this->_user = Library::getAuth('default')->getIdentity();
        if ($this->required_permission_level && (intval($this->_user->permission_level) < $this->required_permission_level)) {
            $this->_flashMessenger->addMessage(_('Недостаточно прав для использования этой функции'));

        }

        // Обработка форсированной смены пароля
        $user = $this->getService('User')->getCurrentUser();
        if($user->force_password == 1 && !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'force-password')
                        && !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'authorization')
                        && !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'logout') ){
            $this->_redirector->gotoSimple('force-password', 'index', 'default');
        }

        Zend_Registry::get('unmanaged_controller')->setFilterTarget($this->view->url() . '/');

        $messages = $this->_flashMessenger->getMessages();
        if (is_array($messages) && count($messages)) {
            $this->_helper->Notificator($messages);
            //Zend_Registry::get('unmanaged_controller')->setMessage(join('<br>', $messages));
        }

        $params = $this->_request->getParams();
        //Изменение параметров для даты в гриде
        foreach($params as $key => $value){
            if(strpos($key, '[from]') || strpos($key, '[to]')){
                $this->_request->setParam($key, str_replace('-','.',$value));
            }
        }
        $this->getService('Help')->testAndSave($params);

        if($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_GUEST){
            $this->getService('Guest')->setSession($this->getRequest()->getCookie('usersSystemCounter_guest'));
        }


        // Проверка на ajax request и grid ajax request
        if ($this->getRequest()->isXmlHttpRequest()
            || $this->_getParam('ajax', false)/*
            || ($this->_hasParam('gridmod') && ($this->_getParam('gridmod') == 'ajax'))*/) {
            $this->_helper->getHelper('layout')->setLayout('ajax');
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
            $this->_ajaxRequest = true;
            //$this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset, true);
        }

        if (isset(Zend_Registry::get('config')->iecompatmode)) {
            $this->getResponse()->setHeader('X-UA-Compatible', 'IE='.Zend_Registry::get('config')->iecompatmode, false);
        }
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return $this->_helper->ServiceContainer($name);
    }

    /**
     * @return Bvb_Grid
     */
    public function getGrid($select = null, $columnsOptions = null, $filters = null, $id = 'grid')
    {

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $columnsOptions);
        $columnsOptions = $event->getReturnValue();

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/grid.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.collapsorz_1.1.min.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/content-modules/grid.js');

        /*
        if ($this->getRequest()->isXmlHttpRequest() || $this->_getParam('ajax', false)) {
            $params = $this->getRequest()->getParams(); // fucking hardcore =(
            if (is_array($params) && count($params)) {
                foreach($params as $name => $value) {
                    if (is_string($value)) {
                        $this->getRequest()->setParam($name, iconv('UTF-8', Zend_Registry::get('config')->charset, $value));
                    }
                }
            }
            // #6260
            //if($this->_getParam('gridmod', false) == 'ajax'){
                //$this->_helper->getHelper('layout')->disableLayout();
            //}
        }
        */
        /*
        if (!$this->_getParam('order'.$id, false)) { // MSSQL TOP HACK
            foreach($columnsOptions as $columnKey => $column) {
                if (!isset($column['hidden'])) {
                    $this->_setParam('order'.$id, $columnKey.'_ASC');
                    break;
                }
            }
        } */

        $grid = Bvb_Grid::factory('table', array(
            'deploy' => array(
                'excel' => array('download' => 1, 'dir' => Zend_Registry::get('config')->path->upload->temp),
                'word' => array('download' => 1, 'dir' => Zend_Registry::get('config')->path->upload->temp),
            )
        ), $id);
        $grid->setAjax($id);
        $grid->setImagesUrl('/images/bvb/');
        $grid->setExport(array('print', 'excel', 'word'));
        $grid->setEscapeOutput(true);
        $grid->setAlwaysShowOrderArrows(false);

        //Получаем колво строк с помощью старого метода
        $perPage = COption::get_value('grid_rows_per_page');
        if($perPage <= 0){
            $perPage = 25;
        }

        $grid->setNumberRecordsPerPage($perPage);
        $grid->setcharEncoding(Zend_Registry::get('config')->charset);
        if (null !== $select) {
            if (is_array($select)) {
                $grid->setSource(new Bvb_Grid_Source_Array($select, array_keys($columnsOptions)));
            } elseif ($select instanceof Zend_Db_Select) {
                $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
            }
        }
        if (null != $columnsOptions) {
            if (is_array($columnsOptions) && count($columnsOptions)) {
                foreach($columnsOptions as $column => $options) {
                    $grid->updateColumn($column, $options);
                }
            }
        }

        if (null != $filters) {
            if (is_array($filters) && count($filters)) {
                $gridFilters = new Bvb_Grid_Filters();
                foreach($filters as $field => $options) {
                    $gridFilters->addFilter($field, $options);
                }
                $grid->addFilters($gridFilters);
            }
        }

        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $grid->setTranslator($translator);

        return $grid;
    }

    /**
     * @return bool
     */
    public function isGridAjaxRequest()
    {
        if (null === $this->_gridAjaxRequest) {
            $this->_gridAjaxRequest = false;
            if ($this->_hasParam('gridmod') && ($this->_getParam('gridmod') == 'ajax')) {
                $this->_gridAjaxRequest = true;
            }
        }
        return $this->_gridAjaxRequest;
    }

    public function isAjaxRequest()
    {
        return $this->_ajaxRequest;
    }

    public function validateFormAction($form = null)
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
        $form->isValid($this->_getAllParams());
        $json = $form->getMessagesUtf8();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8', true);
        echo Zend_Json::encode($json);
        exit();
    }

    public function getOne($collection)
    {
        if (($collection instanceof HM_Collection_Abstract) && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function quoteInto($where, $args)
    {
        return $this->getService('User')->quoteInto($where, $args);
    }

    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Экшн для  переключения фиксации строк
     * Передаем в параметрах экшн на котором нужно зафиксировать строку
     * Так же idrow - id строки
     * html - ее html код
     * И записываем в сессию
     * неймспейс - gridFixedRows
     * при этом поля объекта - массивы к разным страницам
     *
     */
    public function toggleFixedRowAction()
    {

        $fixedNamespace = new Zend_Session_Namespace('gridFixedRows');

        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getParam('getaction');

        $html = $this->_request->getParam('html');
        $id = $this->_request->getParam('idrow');

        $html=str_ireplace("\"checkbox\" onclick", "\"checkbox\" checked='checked' onclick", $html);


        if (is_numeric($id))
        {

            $string = $module . ":" . $controller . ":" . $action;

            if(isset($fixedNamespace->{$string}[$id])){
                unset($fixedNamespace->{$string}[$id]);
                echo 'unset';
            }else{
                  $fixedNamespace->{$string}[$id] = array(
                'id' => $id,
                'html' => iconv('UTF-8','Windows-1251',$html));
                 echo 'set';
            }

        } else
        {
           echo 'error';

        }
        exit();

    }

     /**
     * Экшн для показы зафиксированных строк(Тестовый)
     * Передаем в параметрах экшн на котором нужно показать строки
     * неймспейс - gridFixedRows
     */
    public function showFixedRowAction()
    {

        $fixedNamespace = new Zend_Session_Namespace('gridFixedRows');

        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getParam('getaction');

        $html = $this->_request->getParam('html');
        $id = $this->_request->getParam('idrow');


        $string = $module . ":" . $controller . ":" . $action;
        print_r($fixedNamespace->{$string});

        exit();

    }

    public function coursesCache($field, $select){
        if ($this->courseCache === null){
            $this->courseCache = array();
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['courses'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $tmp = array_filter($tmp);
            if (count($tmp)) {
            $this->courseCache = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $tmp), 'name');
        }
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result =  array();
        if (is_a($this->courseCache, 'HM_Collection')) {
        foreach($fields as $value){
                if ($tempModel = $this->courseCache->exists('subid', $value)) {
            $result[] = "<p>{$tempModel->name}" . ($tempModel->external_id ? ' (<span style="color:#3888c2">'.$tempModel->external_id.'</span>)' : '') . "</p>";
        }
            }
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('',$result);
        } else {
            return _('Нет');
    }
    }

    public function departmentsCache($field, $select, $isPosition = false)
    {
        $key = $isPosition ? 'positions' : 'departments';
        
        if(!isset($this->departmentCache[$key])/* || ($this->departmentCache[$key] === array())*/){
            // #8770 метод выборки по soid-ам пользователей из select не работает с фиксированными строками
            /*$smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                if ($val[$key]) {
                    $tmp[$key][] = $val[$key];
                }
            }
            if (!empty($tmp[$key])) {
                $tmp[$key] = implode(',', $tmp[$key]);
                $tmp[$key] = explode(',', $tmp[$key]);
                $tmp[$key] = array_unique($tmp[$key]);
                $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';
                $this->departmentCache[$key] = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $tmp[$key], $extraCond));
            } else {
                // если пусто, то не надо каждый раз долбаться
                $this->departmentCache[$key] = array();
            }*/
            $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';
            
            $select = $this->getService('Orgstructure')->getSelect();
            $select->from('structure_of_organ', array(
                'soid',
                'name'
            ));
            $select->where($extraCond);
            $deps = $select->query()->fetchAll();
            $index = array();
            foreach ($deps as $dep) {
                $index[$dep['soid']] = _($dep['name']);
            }
            $this->departmentCache[$key] = $index;
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $pluralForm = $isPosition ? 'pluralFormPositionsCount' : 'pluralFormCount';

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Orgstructure')->$pluralForm(count($fields)) . '</p>') : array();
        
        $cache = &$this->departmentCache[$key];
        
        foreach($fields as $value){
            if (isset($cache[$value])) {
                $result[] = "<p>{$cache[$value]}</p>";
            }
        }
        
        if ($result) {
            return implode('', $result);
        } else {
            return _('Нет');
        }
    }

    public function groupsCache($field, $select)
    {

        if(!isset($this->departmentCache['groups'])) {
            $this->departmentCache['groups'] = $this->getService('StudyGroup')->fetchAll();
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $result = (is_array($fields) && count($fields) > 1) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('StudyGroup')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            if ( count($this->departmentCache['groups']) ) {
                $tempModel = $this->departmentCache['groups']->exists('group_id', $value);
                if ($tempModel) {
                    $result[] = '<p><a href="'.$this->view->url(array( 'module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id'  => '' ), null, true) . $tempModel->group_id .'">'. $tempModel->name .'</a></p>';
                }
            }
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function customDepartmentsFilter($params)
    {
        $params['select']->where('d.owner_soid = ?', $params['value']);

    }

    public function classifiersCache($field, $select){

        if($this->classifierCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['classifiers'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->classifierCache = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp));
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->classifierCache->exists('classifier_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function tagsAction()
    {
        $tagName = $this->_getParam('tag');
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }
        $tags = $this->getService('Tag')->fetchAll(
            $this->getService('Tag')->getTagCondition(null, $tagName)
        );
        $res = array();
        foreach($tags as $tag) {
            $o = new stdClass();
            $o->key = $tag->body;
            $o->value = $tag->id;
            $res [] = $o;
        }

        header('Content-type: application/json; charset=UTF-8');
        exit(Zend_Json::encode($res));
    }

    public function displayTags($itemId, $itemType)
    {
       // var_dump($itemId,$itemType);
        if ( $tags = $this->getService('Tag')->getStrTagsByIds($itemId, $itemType) ) {
            return $tags;
        }
        return '';
    }

    public function filterTags($data)
    {
        $module = strtolower( $this->_request->getModuleName() );
        $data['value'] = trim($data['value']);
        $service = false;

        switch ( $module ) {
            case 'blog':
                $service = $this->getService('TagRefBlog');
                break;
            case 'resource':
                $service = $this->getService('TagRefResource');
                break;
            case 'course':
            case 'subject':
                $service = $this->getService('TagRefCourse');
                break;
            case 'subject':
                $service = $this->getService('TagRefCourse');
                break;
            case 'test':
                $service = $this->getService('TagRefTest');
                break;
            case 'exercises':
                $service = $this->getService('TagRefExercises');
                break;
            case 'poll':
                $service = $this->getService('TagRefPoll');
                break;
            case 'task':
                $service = $this->getService('TagRefTask');
                break;
            case 'study-groups':
            case 'user':
            case 'assign':
                $service = $this->getService('TagRefUser');
                break;
        }

        if ( $service ) {
            $data['select'] = $service->getFilterSelect( $data['value'], $data['select'] );
        }
    }

    public function groupsFilter($data)
    {
        if($data['tableName'] == ''){
            $tableName = 'sgc';
        } else {
            $tableName = $data['tableName'];   
        }
        
        // print_r($data); exit;
        $value = $data['value'];
        $select = $data['select'];

        //Только больше 4 символов чтобы много не лезло в in

        if(strlen($value) > 0){
            $fetch = $this->getService('StudyGroup')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('group_id', 'name');

            if ($data) {
                $select->where($tableName.'.group_id IN (?)', array_keys($data));
            } else {
                $select->where($tableName.'.group_id IN (?)',0);
            }
        }
//        var_dump($select->__toString());die;
    }

    public function postDispatch()
    {
        if ($this->getRequest()->isXmlHttpRequest()
        || $this->_getParam('ajax', false)) {

            $headers = $this->getResponse()->getHeaders();
            $hasHeader = false;
            foreach ($headers as $key => $header) {
                if ('content-type' == strtolower($header['name'])) {
                    $hasHeader = true;
                    break;
                }
            }

            if (!$hasHeader) {
                $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset, true);
            }
        }
    }

    protected function setDefaultService(HM_Service_Abstract $service)
    {
        $this->_defaultService = $service;
    }

    public function getDateForGrid($date)
    {
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
    }

    static public function formatDate($date)
    {
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
    }


    public function updateStateAction()
    {

        $stateId = $this->_getParam('state_id', 0 );

        $names = $this->_getParam('names', array());
        $forState = $this->_getParam('forState', '');

        $state = $this->getService('State')->find($stateId)->current();

        $params = unserialize($state->params);

        foreach($names as $name){
            $params[$forState][$name] = $this->_getParam($name, '');

        }
         $this->getService('State')->update(
             array(
                 'state_of_process_id' => $stateId,
                 'params'           => serialize($params)
             )
         );
        exit();
    }

    public function updateSubjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        if (in_array($typeId, HM_Lesson_LessonModel::getTypesFreeModeEnabled())) {
            $isFreeCondition = new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)));
        } else {
            $isFreeCondition = HM_Lesson_LessonModel::MODE_PLAN;
        }

        if($this->lessonsCache === null){
            $this->lessonsCache = array();
            $lessons = $this->getService('Lesson')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree IN (?)' => $isFreeCondition,
            ));
            foreach ($lessons as $lesson) {
                $params = $lesson->getParams();
                if ($params['module_id']) {
                    $this->lessonsCache[$params['module_id']] = $lesson;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->lessonsCache[$moduleId])) {
            if (in_array($this->lessonsCache[$moduleId]->isfree, array(HM_Lesson_LessonModel::MODE_FREE_BLOCKED, HM_Lesson_LessonModel::MODE_PLAN))) {
                return _('Доступ через план занятий');
            } else {
                return _('Свободный доступ');
            }
        }
        return _('Нет доступа');
    }

    public function updateSubjectColumnTests($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        $testTypeId = HM_Test_TestModel::mapEvent2TestType($typeId);

        if($this->testsCache === array()){
            $tests = $this->getService('Test')->fetchAll(array(
                'cid = ?' => $subjectId,
                'type = ?' => $testTypeId,
            ));
            foreach ($tests as $test) {
                if ($test->test_id && $test->tid) {
                    $this->testsCache[$test->tid] = $test->test_id;
                }
            }
        }
        if($this->lessonsCache === null){
            $this->lessonsCache = array();
            $lessons = $this->getService('Lesson')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree =? ' => HM_Lesson_LessonModel::MODE_PLAN,
            ));
            foreach ($lessons as $lesson) {
                $params = $lesson->getParams();
                if ($params['module_id']) {
                    $this->lessonsCache[$this->testsCache[$params['module_id']]] = $lesson;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->lessonsCache[$moduleId])) {
            if ($this->lessonsCache[$moduleId]->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                return _('Доступ через план занятий');
            }
        }
        return _('Нет доступа');
    }

    public function updateEmail($email, $emailConfirmed, $validateEmailEnabled)
    {
        return ($emailConfirmed || !$validateEmailEnabled) ? $email : '<span class="unconfirmed" title="' . _('Email не подтверждён пользователем') . '">' . $email . '</span>';
    }

    /**
     * Форматирование номера сертификата перед выводом
     * @param int|string $cId
     * @return string
     */
    public function updateCertificateNumber($cId = 0)
    {
        if ( !$cId ){
            return _('Нет');
        }
         $formatingNumber = $this->getService('Certificates')->getFormatNubmer($cId);
         $cert_path = Zend_Registry::get('config')->path->upload->cetrificates .  $cId . ".pdf";

         return ( file_exists($cert_path) )? '<a href="' . $this->view->url(array('action'=>'certificate',
         																		  'controller' => 'get',
         																		  'module' =>'file',
         																		  'certificate_id' => $cId)) . '" >' . $formatingNumber . '</a>':
         $formatingNumber;
    }


	/**
     * Форматирование итоговой оценки
     * @param int|string $mark
     * @param int|string $status
     * @return string
     */
    public function updateMark($mark, $scaleId)
    {
        return HM_Scale_Value_ValueModel::getTextStatus($scaleId, $mark);
    }

     /**
     * Убирает из меню действий грида определеное действие по его url
     * @param string $actionMenu      - строка html кода меню
     * @param array|string $actionUrl - урл, пункт меню которого необходимо удалить
     * @return string
     */
    public function removeActionFromMenu($actionMenu, $actionUrl)
    {
        if (is_array($actionUrl)) {
            $actionUrl = $this->view->url($actionUrl);
        } else {
            $actionUrl = (string) $actionUrl;
        }

        $urlPos    = strpos($actionMenu, $actionUrl);
        $startPos  = strrpos(substr($actionMenu, 0, $urlPos), '<li>');
        $endPos    = strpos($actionMenu, '</li>', $urlPos);
        return ($urlPos !== false && $startPos !== false && $endPos !== false)? substr($actionMenu,0,$startPos) . substr($actionMenu, $endPos+5) : $actionMenu;
    }

    public function loginAsAction()
    {
        $userId = (int) $this->_getParam('MID', 0);
        $isAdmin = false;

        $hasPermission = false;
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){
            $hasPermission = $this->getService('DeanResponsibility')->isResponsibleFor($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)){
            $hasPermission = $this->getService('Supervisor')->isResponsibleFor($userId);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)){
            $hasPermission = true;
            $isAdmin = true;
        }

        $isEnduser = true;
        if (!$isAdmin && count($userRoles = $this->getService('User')->getUserRoles($userId))) {
            foreach ($userRoles as $userRole) {
                if (!$this->getService('Acl')->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                    $message =  _('Данную функцию нельзя использовать применительно к пользователям, имеющим административные роли или роль преподавателя.');
                    $isEnduser = false;
                    break;
                }
            }
        }

        if ($userId && $hasPermission && $isEnduser) {
            $this->getService('User')->authorizeOnBehalf($userId);
        } else {
            if (empty($message)) $message = _('У Вас нет прав на авторизацию от имени данного пользователя');
            $this->_flashMessenger->addMessage(array(
                'message' => $message,
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ));

        }
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function updateResourceName($resourceId, $title, $title_translation='', $type, $filetype, $filename, $activity_type)
    {		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		if($title_translation != '' && $this->_currentLang == 'eng') {
        return $this->view->cardLink(
                $this->view->url(array(
                    'module' => 'resource',
                    'controller' => 'index',
                    'action' => 'card',
                    'resource_id' => '')
                ) . $resourceId,
                _('Карточка информационного ресурса'),
                'icon-custom',
                'pcard',
                'pcard',
                'material-icon-small ' . HM_Resource_ResourceService::getIconClass($type, $filetype, $filename, $activity_type)
            ) .
            '<a href="'.$this->view->url(array(
                'module' => 'resource',
                'controller' => 'index',
                'action' => 'index',
                'resource_id' => $resourceId
		), null, false, false).'">' . $title_translation . '</a>';}
		else
		{        return $this->view->cardLink(
                $this->view->url(array(
                    'module' => 'resource',
                    'controller' => 'index',
                    'action' => 'card',
                    'resource_id' => '')
                ) . $resourceId,
                _('Карточка информационного ресурса'),
                'icon-custom',
                'pcard',
                'pcard',
                'material-icon-small ' . HM_Resource_ResourceService::getIconClass($type, $filetype, $filename, $activity_type)
            ) .
            '<a href="'.$this->view->url(array(
                'module' => 'resource',
                'controller' => 'index',
                'action' => 'index',
                'resource_id' => $resourceId
		), null, false, false).'">' . $title . '</a>';
		}
    }
    
    public function updateClassifiers($classifiers, $classifiers_translation='') {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
        if(strlen($classifiers)){
            $classifiers = explode(',', $classifiers);
        } else {
            $classifiers = array();
        }
        $result = _('Нет');
        if(strlen($classifiers_translation)){
            $classifiers_translation = explode(',', $classifiers_translation);
        } else {
            $classifiers_translation = array();
        }
        $result_translation = _('Нет');
        $count = count($classifiers);
        if ($count) {
            $result = ($count > 1) ? array('<p class="total">' . sprintf(_n('классификатор plural', '%s классификатор', $count), $count) . '</p>') : array();
			$result_translation = ($count > 1) ? array('<p class="total">' . sprintf(_n('classifier plural', '%s classifiers', $count), $count) . '</p>') : array();
            foreach($classifiers as $classifier){
				$result[] = "<p>{$classifier}</p>";
	        }
            foreach($classifiers_translation as $classifier_translation){
				$result_translation[] = "<p>{$classifier_translation}</p>";
			}
            $result = implode('',$result);
			$result_translation = implode('',$result_translation);
        }
		if($this->_currentLang == 'eng') 
        return $result_translation;
		else
		return $result;
    }
    


    /**
     * @param string $field Поле для обработки
     * @param Bvb_Grid $grid Разделитель
     * @return string
     */
    public function updateRole($mid, $grid)
    {
        static $rolesCache = null;
        static $basicRolesCache = null;
        if ($rolesCache === null) {

            $basicRolesCache = HM_Role_RoleModelAbstract::getBasicRoles(false, true);

            // оптимизация получения ролей
            $gridResult = $grid->getResult();
            $mids = array();

            foreach ($gridResult as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $rolesCache = array();

            if (count($mids)) {
                $select = $this->getService('User')->getSelect();
                $select->from('roles', array('mid', 'role'));
                $select->where('mid IN (?)', $mids);
                $allUsersRoles = $select->query()->fetchAll();
                foreach ($allUsersRoles as $userRole) {
                    $rolesCache[$userRole['mid']] = explode(',', $userRole['role']);
                }
            }
        }

        $roles = $basicRolesCache;
        $userRoles = !empty($rolesCache[$mid]) ? $rolesCache[$mid] : array();
        $userRolesIndex = array(
            HM_Role_RoleModelAbstract::ROLE_ENDUSER => $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER]
        );
        foreach ($userRoles as $userRole) {
            if (!isset($roles[$userRole])) {
                continue;
            }
            $userRolesIndex[$userRole] = $roles[$userRole];
        }

        // #5337 - сворачивание высоких ячеек
        //$result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        $result[$roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER]] = "<p>" . $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER] . "</p>";

        foreach($userRolesIndex as $value){
            $result[$value] = "<p>{$value}</p>";
        }
        $result = array_reverse($result);
        $roleCount = count($result);

        $result[] = ($roleCount > 1) ? '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount($roleCount) . '</p>' : '';

        $result = array_reverse($result);

        if($result) {
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }


}
