<?php

class Task_ListController extends HM_Controller_Action_Crud
{
    protected $service      = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName  = 'subid';
    protected $id           = 0;
	protected $_currentLang = 'rus'; 

    public function init()
    {
        $this->_setForm(new HM_Form_Task());
        parent::init();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if (!$this->isAjaxRequest()) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

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

    protected function _redirectToIndex()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($subjectId > 0) {
            $this->_redirector->gotoSimple('index', 'list', 'task', array('subject_id' => $subjectId));
        }
        parent::_redirectToIndex();
    }

    public function assignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {

                    $res = $this->getService('SubjectTask')->find($this->id, $id);

                    if(count($res) == 0){
                        $this->getService('SubjectTask')->insert(array('subject_id' => $this->id, 'task_id' => $id));
                    }
                }
                $this->_flashMessenger->addMessage(_('Задания успешно назначены на курс'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {

                    $this->getService('SubjectTask')->delete(array($this->id, $id));
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
        if ($subjectId && !isset($default->grid['task-list-index'][$gridId])) {
            $default->grid['task-list-index'][$gridId]['filters']['subject'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }

        $order = $this->_request->getParam("order{$gridId}");
        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $filters = array(
            'title' => null,
            'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
            'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            'classifiers' => null,
            'subject_control'  => null,
        );

        $rolesWithFilter = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
		$isManager = false;
        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
			$isManager = true;
            $filters['public'] = array('values' => HM_Task_TaskModel::getStatuses());
            if($this->_getParam('publicgrid', '') == '' && $this->_getParam('gridmod', '') != 'ajax'){
                $this->_setParam('publicgrid', 1);
            }
        }else{
            $this->_setParam('publicgrid', 1);
        }

        $group = array(
            't.task_id',
            't.title',
            't.title_translation',
            't.location',
            't.location',
            't.questions',
            't.status'
        );
        
        
        if ($subjectId) {

            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }

            $select = $this->getService('Task')->getSelect();
            $select->from(
                    array('t' => 'tasks'),
                    array('t.task_id',
						't.title',
						't.title_translation',
						't.location',
						'subject' => 's.subject_id',
                        'locationtemp' => 't.location',
						't.questions', 'tags' => 't.task_id',
                        'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(cf.name)"),
                    ));


            $subSelect = $this->getService('Task')->getSelect();
            $subSelect->from(array('s' => 'subjects_tasks'), array('subject_id', 'task_id'))->where('subject_id = ?', $subjectId);
//echo $subSelect;
//exit;
            $select->joinLeft(
                       array('s' => $subSelect),
                       't.task_id = s.task_id',
                       array(
                           'statustemp'  => 't.status',
                           'subjecttemp' =>  's.subject_id',
                       )
                   )
                   ->where('(t.location = ' . (int) HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL . ' AND t.status = ' . (int) HM_Task_TaskModel::STATUS_STUDYONLY . ') OR t.subject_id = ' . (int) $subjectId);
            
            $group = array_merge($group, array('s.subject_id'));
            //echo $select;
        }else{
            if($order == ''){
                $this->_setParam('ordergrid', 'public_DESC');
            }

            $select = $this->getService('Task')->getSelect();
            $select->from(
                array('t' => 'tasks'),
                array('t.task_id',
					  't.title',
					  't.title_translation',	
					  't.questions', 'public' => 't.status',
                    'statustemp' => 't.status', 'subject' => new Zend_Db_Expr("'0'"),
                    'subjecttemp' => new Zend_Db_Expr("'0'"),'tags' => 't.task_id',
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
            'cl.item_id = t.task_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_TASK.' AND cl.item_id NOT IN (0,12343)',
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
                'task_id' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'subjecttemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'title_translation' => array('title' => _('Перевод (en)')),
                'location' => array('title' => _('Место хранения')),
                'status' => array('title' => _('Тип')),
                'questions' => array('title' => _('Количество вариантов')),
                'subject'   => (
                    $subjectId ?
                        array(
                            'title' => _('Доступ для слушателей'),
                            'callback' => array(
                                'function' => array($this, 'updateSubjectColumnTests'),
                                'params' => array(HM_Event_EventModel::TYPE_TASK, '{{task_id}}', '{{subject}}', $subjectId)
                            )) :
                        array('hidden' => true)
                ),
                'public' => array('title' => _('Статус ресурса БЗ')),
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

        if ($subjectId) {
            
            $options = array(
                    'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId)),
                    'global' => array('name' => 'global', 'title' => _('все, включая задания из Базы знаний'), 'params' => array('subject' => null), 'order' => 'subject', 'order_dir' => 'DESC'),
            );
             
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();
             
            $grid->setGridSwitcher($options);
        }

        $grid->addAction(
            array('module' => 'task', 'controller' => 'list', 'action' => 'variants'),
            array('task_id'),
            $this->view->icon('view', _('Просмотреть задание'))
        );

        $grid->addAction(
            array('module' => 'task', 'controller' => 'list', 'action' => 'edit'),
            array('task_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'task', 'controller' => 'list', 'action' => 'delete'),
            array('task_id'),
            $this->view->icon('delete')
        );


        if($subjectId > 0){
            $grid->addMassAction(
                array('module' => 'task', 'controller' => 'list', 'action' => 'assign'),
                _('Использовать в данном курсе')
            );

            $grid->addMassAction(
                array('module' => 'task', 'controller' => 'list', 'action' => 'unassign'),
                _('Не использовать в данном курсе')
            );
        } else {
            $grid->addMassAction(
                array('module' => 'task', 'controller' => 'list', 'action' => 'publish'),
                _('Назначить статус: только для использования в учебных курсах')
            );
            $grid->addMassAction(
                array('module' => 'task', 'controller' => 'list', 'action' => 'unpublish'),
                _('Назначить статус: не опубликован')
            );
        }

        $grid->addMassAction(
            array('module' => 'task', 'controller' => 'list', 'action' => 'delete-by'),
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

        $grid->updateColumn('title_translation', array(
                'hidden' => true
            )
        );		
		
        $grid->updateColumn('title',
            array('callback' =>
                array('function' =>
                    array($this,'updateName'),
                    'params'   => array('{{title}}', '{{title_translation}}', '{{status}}', '{{subject}}', '{{task_id}}')
                )
            )
        );

        $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', $this->getService('TagRef')->getTaskType(), $subjectId, '{{locationtemp}}')
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
            self::ACTION_INSERT => _('Задание успешно создано'),
            self::ACTION_UPDATE => _('Задание успешно обновлёно'),
            self::ACTION_DELETE => _('Задание успешно удалёно'),
            self::ACTION_DELETE_BY => _('Задания успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $array = array(
                    'title' => $form->getValue('title'),
                    'title_translation' => $form->getValue('title_translation'),
                    'status' => $form->getValue('status'),
                    'description' => $form->getValue('description'),
                    'description_translation' => $form->getValue('description_translation'),
                    'subject_id' => $subjectId
                );


        if($subjectId == 0){
            $array['location'] = 1;
        }



        $test = $this->getService('Task')->insert(
            $array
        );

        if ($test && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($test->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($test->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK, $classifierId);
                    }
                }
            }
        }
        if ($tags = $form->getParam('tags',array())) {
            $this->getService('Tag')->update( $tags, $test->task_id, $this->getService('TagRef')->getTaskType() );
        }

        if (($subjectId > 0 && $test)) {
            $this->getService('SubjectTask')->insert(array('subject_id' => $subjectId, 'task_id' => $test->task_id));
        }
    }

    public function update(Zend_Form $form)
    {

        $subjectid = (int) $this->_getParam('subject_id', 0);

        $test = $this->getService('Task')->getOne($this->getService('Task')->find($form->getValue('task_id')));

        if(!$test){
            return false;
        }

        $userRole = $this->getService('User')->getCurrentUserRole();

        if(!$this->getService('Task')->isEditable($test->subject_id, $subjectid, $test->location)){
            return false;
        }
        $test = $this->getService('Task')->update(
             array(
                 'task_id' => $form->getValue('task_id'),
                 'title' => $form->getValue('title'),
				 'title_translation' => $form->getValue('title_translation'),
                 'status' => $form->getValue('status'),
                 'description' => $form->getValue('description'),
				 'description_translation' => $form->getValue('description_translation')
             )
         );

        $this->getService('Tag')->update( $form->getParam('tags',array()), $form->getValue('task_id'), $this->getService('TagRef')->getTaskType() );


        if ($test && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($test->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($test->task_id, HM_Classifier_Link_LinkModel::TYPE_TASK, $classifierId);
                    }
                }
            }
        }

    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $task = $this->getService('Task')->getOne($this->getService('Task')->find($id));

        if(!$this->getService('Task')->isEditable($task->subject_id, $subjectId, $task->location)){

            return false;
        }
        $this->getService('Task')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('task_id', 0);
        if ($id) {
            $res = $this->delete($id);

            if($res == true){
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            }else{
                 $this->_flashMessenger->addMessage(_('Для удаления заданий не хватает прав'));
            }

        }
        $this->_redirectToIndex();
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
                    if($temp === false){
                        $error = true;
                    }
                }
                if($error === false){
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                }else{
                    $this->_flashMessenger->addMessage(_('Глобальные задания невозможно удалить из учебного курса.'));
                }
            }
        }

        $this->_redirectToIndex();
    }

    public function setDefaults(Zend_Form $form)
    {
        $taskId = (int) $this->_getParam('task_id', 0);

        $task = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
        $values = $task->getValues();
        $values['tags'] = $this->getService('Tag')->getTags($taskId, $this->getService('TagRef')->getTaskType());
        if ($task) {
            $form->setDefaults( $values );
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

    public function updateActions($status, $subjectId, $actions)
    {
        $subject_id = $this->_getParam('subject_id', 0);
        if($this->getService('Task')->isEditable($subjectId, $subject_id, $status)){
            return $actions;
        }else{
            $actions = explode('</a>', $actions);  // fucking hardcode
            unset($actions[1]);
            unset($actions[2]);
            $actions = join('</a>', $actions);
            return $actions;
        }
    }


    public function updateName($name, $nameTranslation='', $status, $subjectId, $testId)
    {
        $subject_id = $this->_getParam('subject_id', 0);

        $userRole = $this->getService('User')->getCurrentUserRole();
		
		
		
		if($nameTranslation != '' && $this->_currentLang == 'eng') 
			return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'task', 'task_id' => $testId, 'subject_id' => $subject_id), null, true, false).'">' . $nameTranslation . '</a>';
		else
        //if($this->getService('TestAbstract')->isEditable($subjectId, $subject_id, $status)){
            return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'task', 'task_id' => $testId, 'subject_id' => $subject_id), null, true, false).'">' . $name . '</a>';
        //}else{
            //return $name;
        //}
    }

    public function updatePublic($status)
    {
        $statuses = HM_Task_TaskModel::getStatuses();

        return $statuses[$status];

    }


    public function viewAction()
    {
        $taskId = (int) $this->_getParam('task_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        if ($taskId) {

            $abstract = $this->getOne($this->getService('Task')->find($taskId));
            if ($abstract) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                    $this->getService('Test')->quoteInto(
                        array('lesson_id = ?', ' AND test_id = ?'),
                        array(0, $taskId)
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
                            'test_id' => $taskId,
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

                    $_SESSION['default']['lesson']['execute']['returnUrl'] = $this->view->serverUrl($this->view->url(array('module' => 'task', 'controller' => 'list', 'action' => 'index', 'subject_id' => $subjectId), null, true));
                    $this->_redirector->gotoUrl($this->view->serverUrl(sprintf('/'.HM_Lesson_Test_TestModel::TEST_EXECUTE_URL, $test->tid, 0)));
                }

            }
        }

        $this->_flashMessenger->addMessage(sprintf(_('Задание #%d не найдено'), $taskId));
        $this->_redirector->gotoSimple('index', 'list', 'task', array('subject_id' => $subjectId));
    }

    public function publishAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Task')->publish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Назначен статус: только для использования в учебных курсах.'));
        }
        $this->_redirectToIndex();
    }

    public function unpublishAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Task')->unpublish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Назначен статус: не опубликован.'));
        }
        $this->_redirectToIndex();
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Task')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($task = $this->getService('Task')->insert($defaults)) {

                if ($this->getService('SubjectTask')->insert(array('subject_id' => $subjectId, 'task_id' => $task->task_id))) {
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                    $result = $task->task_id;

                    // Создаем вариант
                    $qData = array(
                        'subject_id' => $subjectId,
                        'qtype'=>6,
                        'qmoder'=>1,
                        'qdata'=> $task->title,
                        'adata'=>"",
                        'qtema'=>"",
                        'url'=>""
                    );

                    $question = $this->getService('Question')->insert($qData);
                    if ( $question ) {
                        $this->getService('Task')->update(array('task_id' => $task->task_id, 'data' => $question->kod, 'questions' => 1));
                    }
                }
            }
        }



        exit(Zend_Json::encode($result));
    }

    /**
     * Экшен отображения списка вариантов по данному заданию
     * отображается в стиле модуля interview
     */
    public function variantsAction()
    {
        $taskId = $this->_request->getParam('task_id', 0);
        $task   = $this->getOne($this->getService('Task')->findDependence('AuthorAssign',$taskId));
        $files  = array();

        if ( !$task ) {
           $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                    'message' => _('Задание не найдено')));
           $url =  $this->view->url(array('module'     => 'task',
                                          'controller' => 'list',
                                          'action'     => 'index',
                                          'task_id'    => null));
           $this->_redirector->gotoUrl($url);
        }
        $ids    = explode(HM_Task_TaskModel::QUESTION_SEPARATOR, $task->data);

        $questionFiles = array();
        foreach ($ids as $id) {
            $files[$id] = $this->getService('Question')->getFiles($id);
            $questionFiles[$id] = $this->getService('Question')->getFiles($id, false);
        }
        $questions = $this->getService('Question')->fetchAll(array("kod IN (?)" => $ids));

        $this->view->questions = $questions;
        $this->view->files     = $files;
        $this->view->questionFiles = $questionFiles;
        $this->view->task      = $task;
    }
}