<?php

class Test_AbstractController extends HM_Controller_Action_Crud
{

    protected $service      = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName  = 'subid';
    protected $id           = 0;
	protected $_currentLang = 'rus';  
 
    public function init()
    {
        parent::init();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $testId    = (int) $this->_getParam('test_id', 0);

        if ($subjectId) { 
            $this->id = (int) $this->_getParam($this->idParamName, 0);
            $subject = $this->getService($this->service)->getOne($this->getService($this->service)->find($this->id));

// больше нет тестов со свободным доступом            
//             if($subject && $testId && ($this->_request->getActionName() == 'edit')) {
//                 // если тест в курсе совбодного доступа - идем в свой экшен
//                 $this->_redirector
//                      ->gotoSimple('edit-Fc',
//                                   'list',
//                                   'test',
//                                    array(
//                                             'subject_id' => $subjectId,
//                                             'test_id' => $this->_getParam('test_id',0)
//                                    ));
//             }
        }
        
        $this->_setForm(new HM_Form_Abstract());
        if (!$this->isAjaxRequest()) {

            if ($subjectId || ($testId && !$subjectId && !$this->_hasParam('gridmod'))) {

                if ($subjectId) {
                    $this->_subject = $subject;
                }

                if (!$subjectId) {
                    $this->service = 'TestAbstract';
                    $this->idParamName = 'test_id';
                    $this->idFieldName = 'test_id';

                    $this->id = $testId;
                    $subject = $this->getOne($this->getService($this->service)->find($this->id));
                }

                if ($subject) {
                    $this->view->setExtended(
                        array(
                            'subjectName' => $this->service,
                            'subjectId' => $this->id,
                            'subjectIdParamName' => $this->idParamName,
                            'subjectIdFieldName' => $this->idFieldName,
                            'subject' => $subject
                        )
                    );
                }

            }

        }
    }

    protected function _redirectToIndex()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($subjectId > 0) {
            $this->_redirector->gotoSimple('index', 'abstract', 'test', array('subject_id' => $subjectId));
        }

        if (!$this->_hasParam('gridmod') && $this->_request->getActionName() == 'edit') { // Если редактирование из расширенной странице теста
            $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $this->_getParam('test_id', 0)));
        }

        parent::_redirectToIndex();
    }

    public function assignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            /** @var $globalTests HM_Test_Abstract_AbstractService */
            $globalTests = $this->getService("TestAbstract");
            $ids = $globalTests->filterByLocation($ids);
            if(!empty($ids)){
                    foreach($ids as $id) {
                        $res = $this->getService('SubjectTest')->find($this->id, $id);
                        if(count($res) == 0){
                            $this->getService('SubjectTest')->insert(array('subject_id' => $this->id, 'test_id' => $id));
                        }
                        /** was different variable instead of $this->id. Was it necessary? */
                        $this->getService('TestAbstract')->createLesson($this->id, $id);
                    }

                $this->_flashMessenger->addMessage(_('Тесты успешно назначены на курс'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $subjectId = $this->_getParam('subject_id', 0);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            /** @var $globalTests HM_Test_Abstract_AbstractService */
            $globalTests = $this->getService("TestAbstract");
            $ids = $globalTests->filterByLocation($ids);
            if (!empty($ids)) {
                foreach($ids as $id) {
                    $this->getService('TestAbstract')->clearLesson($this->_subject, $id);

                    $this->getService('SubjectTest')->delete(array($this->id, $id));
                }
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $default = new Zend_Session_Namespace('default');
        if ($subjectId && !isset($default->grid['test-abstract-index'][$gridId])) {
            $default->grid['test-abstract-index'][$gridId]['filters']['subject'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }

        $order = $this->_request->getParam("order{$gridId}");
        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $filters = array(
            'title' => null,
            'question_name' => array('callback' => array('function' => array($this, 'filterQuestionName'))),
            'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
            'updated' => array(
                'render' => 'date',
            ),
            'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            'classifiers' => null,
            'subject_control' => null,
        );

        $rolesWithFilter = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
		$isManager = false;
        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
            $isManager = true;
			$filters['public'] = array('values' => HM_Test_TestModel::getStatuses());
            if (($this->_getParam('publicgrid', -1) == -1) && !$this->isGridAjaxRequest()) {
                //$this->_setParam('publicgrid', 1); //сбивает всех c толку; не надо;
            }
        }else{
            if (($this->_getParam('publicgrid', -1) == -1) && !$this->isGridAjaxRequest()) {
                //$this->_setParam('publicgrid', 1);
            }
        }

        $group = array(
            't.test_id', 't.title', 't.test_translate', 't.location',
            't.location', 't.questions', 't.updated',
            't.status'
        );
        
        
        if ($subjectId) {

            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }

            $select = $this->getService('TestAbstract')->getSelect();
            $select->from(
                array('t' => 'test_abstract'),
                array('t.test_id', 't.title', 'question_name' => 't.test_id',  't.test_translate', 't.location', 'subject' => 's.subject_id',
                    'locationtemp' =>'t.location', 't.questions', 't.updated', 'tags' => 't.test_id',
                    'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(cf.name)"),
                )
            );


            $subSelect = $this->getService('TestAbstract')->getSelect();
            $subSelect->from(array('s' => 'subjects_tests'), array('subject_id', 'test_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                       array('s' => $subSelect),
                       't.test_id = s.test_id',
                       array(
                           'statustemp'  => 't.status',
                           'subjecttemp' =>  's.subject_id',
                       )
                   )
                   ->where('(t.location = ' . (int) HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL . ' AND t.status = ' . (int) HM_Test_TestModel::STATUS_STUDYONLY . ') OR t.subject_id = ' . (int) $subjectId);
            $group = array_merge($group, array('s.subject_id'));
        }else{
            if($order == ''){
                $this->_setParam('ordergrid', 'public_DESC');
            }

            $select = $this->getService('TestAbstract')->getSelect();
            $select->from(
                array('t' => 'test_abstract'),
                array('t.test_id','t.title', 'question_name' => 't.test_id', 't.test_translate', 't.questions', 'public' => 't.status','statustemp' => 't.status',
                    'subject' => new Zend_Db_Expr("'0'"), 'subjecttemp' => new Zend_Db_Expr("'0'"),
                    't.updated', 'tags' => 't.test_id',
                    'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(cf.name)"),
					'subject_control' => 't.subject_id',
                )
            )
            //Пока закомментим
            ->where('t.location = ?', HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL)
            ;
			$group = array_merge($group, array('t.subject_id'));

        }

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            'cl.item_id = t.test_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_TEST,
            array()
        );
        $select->joinLeft(
            array('cf' => 'classifiers'),
            'cl.classifier_id = cf.classifier_id',
            array()
        ); 

        $select->group($group);
        
        
        $grid = $this->getGrid(
            $select,
            array(
                'test_id' => array('hidden' => true),
                'tid' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'subjecttemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'question_name' => array('title' => _('Вопросы')),
                'location' => array('title' => _('Место хранения')),
                'status' => array('title' => _('Тип')),
                'questions' => array('title' => _('Количество вопросов')),
                'subject'   => (
                    $subjectId ? 
                        array(
                            'title' => _('Доступ для слушателей'),
                            'callback' => array(
                                'function' => array($this, 'updateSubjectColumnTests'),
                                'params' => array(HM_Event_EventModel::TYPE_TEST, '{{test_id}}', '{{subject}}', $subjectId)
                            )) : 
                        array('hidden' => true)
                ),
                'public' => array('title' => _('Статус')),
                'updated' => array('title' => _('Дата последнего изменения')),
                'tags' => array('title' => _('Метки')),
                'subject_control' => (!$isManager) ? (array('hidden' => true)) : (array('title' => _('Id курса'))),
                'classifiers' => array(
                    'title' => _('Классификаторы'),
                    'callback' => array(
                        'function' => array($this, 'updateClassifiers'),
                        'params' => array('{{classifiers}}')
                    )
                )
            ),
            $filters,
            $gridId
        );

        if ($subjectId && $this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')) {
            
            $options = array(
                    'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId)),
                    'global' => array('name' => 'global', 'title' => _('все, включая тесты из Базы знаний'), 'params' => array('subject' => null), 'order' => 'subject', 'order_dir' => 'DESC'),
            );
            
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();
            
            $grid->setGridSwitcher($options);
        }

        $grid->addAction(
            array('module' => 'test', 'controller' => 'abstract', 'action' => 'view'),
            array('test_id'),
            $this->view->icon('view', _('Просмотреть тест'))
        );

        $grid->addAction(
            array('module' => 'test', 'controller' => 'abstract', 'action' => 'edit'),
            array('test_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'test', 'controller' => 'abstract', 'action' => 'delete'),
            array('test_id'),
            $this->view->icon('delete')
        );


        if($subjectId > 0){
            $grid->addMassAction(
                array('module' => 'test', 'controller' => 'abstract', 'action' => 'assign'),
                _('Использовать в данном курсе')
            );

            $grid->addMassAction(
                array('module' => 'test', 'controller' => 'abstract', 'action' => 'unassign'),
                _('Не использовать в данном курсе')
            );
        }

        $grid->addMassAction(
            array('module' => 'test', 'controller' => 'abstract', 'action' => 'publish'),
            _('Назначить статус: только для использования в учебных курсах')
        );
        $usr = $this->getService('User')->getCurrentUserRole();
        if($this->getService('Acl')->inheritsRole($usr, HM_Role_RoleModelAbstract::ROLE_MANAGER)
            || $this->getService('Acl')->inheritsRole($usr, HM_Role_RoleModelAbstract::ROLE_ADMIN)
            || $this->getService('Acl')->inheritsRole($usr, HM_Role_RoleModelAbstract::ROLE_DEVELOPER)
            //$this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $grid->addMassAction(
                array('module' => 'test', 'controller' => 'abstract', 'action' => 'unpublish'),
                _('Назначить статус: не опубликован')
            );
        }
        $grid->addMassAction(
            array('module' => 'test', 'controller' => 'abstract', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );

        $grid->updateColumn('location',
            array('callback' =>
                array('function' =>
                    array($this,'updateStatus'),
                    'params'   => array('{{location}}')
                )
            )
        );

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this,'updatePublic'),
                    'params'   => array('{{public}}')
                )
            )
        );
		
		#########
		$grid->updateColumn('question_name',
            array('callback' =>
                array('function' =>
                    array($this,'updateQuestionName'),
                    'params'   => array('{{question_name}}')
                )
            )
        );

        $grid->updateColumn('test_translate', array(
                'hidden' => true
            )
        );	
        $grid->updateColumn('title',
            array('callback' =>
                array('function' =>
                    array($this,'updateName'),
                    'params'   => array('{{title}}', '{{test_translate}}', '{{status}}', '{{subject}}', '{{test_id}}')
                )
            )
        );

        $grid->updateColumn('updated',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{updated}}')
                )
            )
        );

        $grid->updateColumn('questions',
            array('callback' =>
                array('function' => array($this, 'updateQuestions'),
                      'params'   => array('{{questions}}', '{{test_id}}', $subjectId)
                )
            )
        );

        $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', $this->getService('TagRef')->getTestType(), $subjectId, '{{locationtemp}}')
                )
            ));

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{locationtemp}}', '{{subjecttemp}}')
            )
        );

        if ($subjectId) $grid->setClassRowCondition("'{{subject}}' != ''", "selected");

        $this->view->subjectId = $subjectId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Тест успешно создан'),
            self::ACTION_UPDATE => _('Тест успешно обновлён'),
            self::ACTION_DELETE => _('Тест успешно удалён'),
            self::ACTION_DELETE_BY => _('Тесты успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $array = array(
                    'title' => $form->getValue('title'),
					'test_translate' => $form->getValue('test_translate'),
                    'status' => $form->getValue('status'),
                    'description' => $form->getValue('description'),
                    'subject_id' => $subjectId
                );


        if($subjectId == 0){
            $array['location'] = 1;
        }



        $test = $this->getService('TestAbstract')->insert(
            $array
        );

        if ( $test ) {
            if ($tags = $form->getParam('tags')) {
                $this->getService('Tag')->update( $tags, $test->test_id, $this->getService('TagRef')->getTestType() );
            }
        }

        if ($test && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($test->test_id, HM_Classifier_Link_LinkModel::TYPE_TEST);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($test->test_id, HM_Classifier_Link_LinkModel::TYPE_TEST, $classifierId);
                    }
                }
            }
        }


        if (($subjectId > 0 && $test)) {
            $this->getService('SubjectTest')->insert(array('subject_id' => $subjectId, 'test_id' => $test->test_id));
            /**
             * Не нужно чтобы добавлялся при создании теста,
             * Насколько понял нужно чтобы тест находился в курсе но не были создан урок.
             * @author Artem Smirnov <tonakai.personal@gmail.com>
             * @date 14.01.2013
             */
            //$this->getService('TestAbstract')->createLesson($this->_subject->subid, $test->test_id);
        }
    }

    public function update(Zend_Form $form)
    {

        $subjectid = (int) $this->_getParam('subject_id', 0);

        $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($form->getValue('test_id')));

        if(!$test){
            return false;
        }

        $userRole = $this->getService('User')->getCurrentUserRole();

        if(!$this->getService('TestAbstract')->isEditable($test->subject_id, $subjectid, $test->location)){
            return false;
        }
        $test = $this->getService('TestAbstract')->update(
             array(
                 'test_id' => $form->getValue('test_id'),
                 'title' => $form->getValue('title'),
				 'test_translate' => $form->getValue('test_translate'),
                 'status' => $form->getValue('status'),
                 'description' => $form->getValue('description'),
				 'testshort_translate' => $form->getValue('testshort_translate')
             )
         );


        $this->getService('Tag')->update( $form->getParam('tags',array()), $form->getValue('test_id'), $this->getService('TagRef')->getTestType() );


        if ($test && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($test->test_id, HM_Classifier_Link_LinkModel::TYPE_TEST);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($test->test_id, HM_Classifier_Link_LinkModel::TYPE_TEST, $classifierId);
                    }
                }
            }
        }

    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($id));

        if(!$this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location)){
            return false;
        }
        $this->getService('TestAbstract')->delete($id);

        return true;
    }


    public function deleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $error = false;
            if (count($ids)) {
                foreach($ids as $id) {
                    $temp = $this->delete($id);
                    $this->getService('TestAbstract')->clearLesson(null, $id);
                    if($temp === false){
                        $error = true;
                    }
                }
                if($error === false){
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                }else{
                    $this->_flashMessenger->addMessage(_('Глобальные тесты невозможно удалить из учебного курса.'));
                }
            }
        }
        $this->_redirectToIndex();
    }




    public function deleteAction()
    {
        $id = (int) $this->_getParam('test_id', 0);

        if ($id) {
            $res = $this->delete($id);

            $this->getService('TestAbstract')->clearLesson(null, $id);

            if($res == true){
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            }else{
                 $this->_flashMessenger->addMessage(_('Для удаления теста не хватает прав'));
            }

        }
        $this->_redirectToIndex();
    }

    public function setDefaults(Zend_Form $form)
    {
        $testId = (int) $this->_getParam('test_id', 0);

        $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($testId));
        $values = $test->getValues();
        $values['tags'] = $this->getService('Tag')->getTags($test->test_id, $this->getService('TagRef')->getTestType());
        if ($test) {

            $this->getService('Unmanaged')->setSubHeader($test->title);

            $form->setDefaults(
               $values
            );

            if (!$this->_hasParam('gridmod')) { // Если редактирования из расширенной странице
                $form->setDefault('cancelUrl', $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $test->test_id), null, true));
            }
        }
    }

    public function updateStatus($status)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $statuses = HM_Test_Abstract_AbstractModel::getLocaleStatuses();

        return $statuses[$status];
      /*  if($subjectId == $locale && $status == HM_Resource_ResourceModel::STATUS_UNPUBLISHED){
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL];
        }else{
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL];
        }*/
    }

    public function updateSubject($subject)
    {

        if($subject !=''){
            return _('Да');
        }else{
            return _('Нет');
        }

    }

    public function updateActions($location, $subjectId, $actions)
    {
        $subject_id = $this->_getParam('subject_id', 0);
        if($this->getService('TestAbstract')->isEditable($subjectId, $subject_id, $location)){
            return $actions;
        }else{
            $actions = explode('</a>', $actions);  // fucking hardcode
            unset($actions[1]);
            unset($actions[2]);
            $actions = join('</a>', $actions);
            return $actions;
        }
    }


    public function updateName($name, $nametranslate='', $location, $subjectId, $testId)
    {
        $subject_id = $this->_getParam('subject_id', 0);

        $userRole = $this->getService('User')->getCurrentUserRole();
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)
            //$userRole == HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){

            if($this->_lessons == NULL){
                $this->_lessons = $this->getService('Lesson')->fetchAll(array('CID = ?' => $subjectId, 'typeID = ?' => HM_Event_EventModel::TYPE_TEST));
            }

            $lessonId = 0;

            foreach($this->getService('Test')->fetchAll(array('test_id = ?' => $testId)) as $test){
                foreach($this->_lessons as $lesson){
                    if($lesson->getModuleId() == $test->tid){
                        $lessonId = $lesson->SHEID;
                    }
                }
            }

			if($nametranslate != '' && $this->_currentLang == 'eng') 
			return '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'subject_id' => $subject_id, 'lesson_id' => $lessonId), null, true, false).'">' . $nametranslate . '</a>';
		else
            return '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'subject_id' => $subject_id, 'lesson_id' => $lessonId), null, true, false).'">' . $name . '</a>';
        }
			if($nametranslate != '' && $this->_currentLang == 'eng') 
			return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $testId, 'subject_id' => $subject_id), null, true, false).'">' . $nametranslate . '</a>';
		else
			return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $testId, 'subject_id' => $subject_id), null, true, false).'">' . $name . '</a>';
    }

    public function updatePublic($status)
    {
        $statuses = HM_Test_TestModel::getStatuses();

        return $statuses[$status];

    }

    public function viewAction()
    {
        $testId = (int) $this->_getParam('test_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();

        /*$lessonId = $this->_getParam('lesson_id', 0);

        if($this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE && $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
            $mark = $this->getOne($this->getService('LessonAssign')->fetchAll(array('SHEID = ?' => $lessonId, 'MID = ?' => $userId)));
            if($mark){
                $mark->V_STATUS = 100;
                $this->getService('LessonAssign')->update($mark->getData());
            }
        }*/


        if ($testId) {
            $abstract = $this->getOne($this->getService('TestAbstract')->find($testId));
            if ($abstract) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                    $this->getService('Test')->quoteInto(
                        array('lesson_id = ?', ' AND test_id = ?'),
                        array(0, $testId)
                    )
                ));
                if (!$test) {
                    $test = $this->getService('Test')->insert(
                        array(
                            'cid' => 0,
                            'datatype' => 1,
                            'sort' => 0,
                            'free' => 0,
                            'rating' => 0,
                            'status' => 1,
                            'last' => 0,
                            'cidowner' => 0,
                            'title' => $abstract->title,
                            'data' => $abstract->data,
                            'lesson_id' => 0,
                            'test_id' => $testId,
                            'mode' => 0,
                            'lim' => 0,
                            'qty' => 1,
                            'startlimit' => 0,
                            'limitclean' => 0,
                            'timelimit' => 0,
                            'random' => 0,
                            'questres' => 1,
                            'showurl' => 0,
                            'endres' => 1,
                            'skip' => 1,
                            'allow_view_log' => 0,
                            'comments' => '',
                            'type' => $abstract->getTestType()
                        )
                    );
                }

                if ($test) {

                    $test->data = $abstract->data;
                    $test->title = $abstract->title;

                    $test = $this->getService('Test')->update($test->getValues(array('tid', 'data', 'title')));

                    $_SESSION['default']['lesson']['execute']['returnUrl'] = $_SERVER['HTTP_REFERER']; //$this->view->serverUrl($this->view->url(array('module' => 'test', 'controller' => 'abstract', 'action' => 'index', 'subject_id' => $subjectId), null, true));
                    $this->_redirector->gotoUrl($this->view->serverUrl(sprintf('/'.HM_Lesson_Test_TestModel::TEST_EXECUTE_URL, $test->tid, 0)));
                }

            }
        }

        $this->_flashMessenger->addMessage(sprintf(_('Тест #%d не найден'), $testId));
        $this->_redirector->gotoSimple('index', 'abstract', 'test', array('subject_id' => $subjectId));
    }

    public function publishAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('TestAbstract')->publish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Назначен статус: только для использования в учебных курсах.'));
        }
        $this->_redirectToIndex();
    }
    
    public function unpublishAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('TestAbstract')->unpublish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Назначен статус: не опубликован.'));
        }
        $this->_redirectToIndex();
    }

    public function updateQuestions($questions, $test_id, $subject_id)
    {
        if (!empty($questions)
            && !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //&& $this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_STUDENT
        ) { //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN) {
            return '<a href="' . $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $test_id, 'subject_id' => $subject_id)) . '" title="' . _('Список вопросов') . '">' . $questions . '</a>';
        }
        return $questions;
    }

    public function updateDate($date){

        if($date == ""){
            return _('Нет');
        }else{
            $date = new Zend_Date($date);

            if($date instanceof Zend_Date){
                return $date->toString(Zend_Locale_Format::getDateTimeFormat());
            }else{
                return _('Нет');
            }

        }
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('TestAbstract')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($test = $this->getService('TestAbstract')->insert($defaults)) {

                if ($this->getService('SubjectTest')->insert(array('subject_id' => $subjectId, 'test_id' => $test->test_id))) {
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                    $result = $test->test_id;
                }
            }
        }
        exit(Zend_Json::encode($result));
    }
    public function questionAction()
    {
        $this->getService('TestQuestion')->deleteBy('test_id > 0');
        $tests = $this->getService('TestAbstract')->fetchAll();
        if (count($tests)) {
            foreach($tests as $test) {
                $questions = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);
                if (count($questions)) {
                    foreach($questions as $id) {
                        if ($id) {
                            $this->getService('TestQuestion')->insert(
                                array(
                                    'subject_id' => $test->subject_id,
                                    'test_id' => $test->test_id,
                                    'kod' => $id
                                )
                            );
                        }
                    }
                }
            }
        }

        die('tests_questions updated...');
    }
	
	public function updateQuestionName($test_id)
	{
		return '-';
	}
	public function filterQuestionName($data)
	{
		$value	= trim($data['value']);
		$select = $data['select'];
		if(empty($value)){ return; }
		
		$value = str_replace('%3F', '?', $value);
		
		$serviceTest 	= $this->getService('TestAbstract');
		$selectSub 		= $serviceTest->getSelect();
		$selectSub->from(array('l' => 'list'), array('t.test_id') );
		$selectSub->join(array('t' => 'tests_questions'), 't.kod = l.kod', array());
		$selectSub->where($serviceTest->quoteInto('l.qdata LIKE ?', '%' . $value . '%'));
		$res = $selectSub->query()->fetchAll();
		
		if(empty($res)){
			$select->where('1=0');	
			return;
		}
		$test_ids = array();
		foreach($res as $i){ $test_ids[$i['test_id']] = $i['test_id']; }
		
		$select->where($serviceTest->quoteInto('t.test_id IN (?)', $test_ids));
	}
}