<?php

class Process_ListController extends HM_Controller_Action_Crud
{
    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function init()
    {
        $this->_setForm(new HM_Form_Process());
        parent::init();

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
            $this->_redirector->gotoSimple('index', 'list', 'poll', array('subject_id' => $subjectId));
        }
        parent::_redirectToIndex();
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Бизнес процесс успешно создан'),
            self::ACTION_UPDATE    => _('Бизнес процесс успешно обновлён'),
            self::ACTION_DELETE    => _('Бизнес процесс успешно удалён'),
            self::ACTION_DELETE_BY => _('Бизнес процессы успешно удалены')
        );
    }


    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

    	$default = new Zend_Session_Namespace('default');
    	if ($subjectId && !isset($default->grid['poll-list-index'][$gridId])) {
    		$default->grid['poll-list-index'][$gridId]['filters']['subject'] = $subjectId; // по умолчанию показываем только слушателей этого курса
    	}

        $order = $this->_request->getParam("order{$gridId}");
        if ($order == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $filters = array(
        	'title' => null,
            'location' => array('values' => HM_Poll_PollModel::getLocaleStatuses()),
            'updated' => array(
                'render' => 'date',
            ),
            'tags' => array('callback' => array('function' => array($this, 'filterTags')))
        );

        $rolesWithFilter = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);

        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
            $filters['public'] = array('values' => HM_Test_Abstract_AbstractModel::getStatuses());
            if($this->_getParam('publicgrid', '') == '' && $this->_getParam('gridmod', '') != 'ajax'){
                $this->_setParam('publicgrid', 1);
            }
        }else{
            $this->_setParam('publicgrid', 1);
        }


        if ($subjectId) {
            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }

            $select = $this->getService('Poll')->getSelect();
            $select->from(
                    array('t' => 'quizzes'),
                    array('t.quiz_id', 't.title','tags' => 't.quiz_id'));


            $subSelect = $this->getService('Poll')->getSelect();
            $subSelect->from(array('s' => 'subjects_quizzes'), array('subject_id', 'quiz_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                       array('s' => $subSelect),
                       't.quiz_id = s.quiz_id',
                       array(
                           //'t.status',
                           'statustemp'  => 't.status',
                       	   't.location',
                           'subject'     => 's.subject_id',
                           'subjecttemp' =>  's.subject_id',
                           't.questions',
                           'locationtemp' =>'t.location',
                           't.updated',
                       )
                   )
                   ->where('(t.location = ' . (int) HM_Poll_PollModel::LOCALE_TYPE_GLOBAL . ' AND t.status = ' . (int) HM_Poll_PollModel::STATUS_PUBLISHED . ') OR t.subject_id = ' . (int) $subjectId);

        }else{

            if($order == ''){
                $this->_setParam('ordergrid', 'public_DESC');
            }

            $select = $this->getService('Poll')->getSelect();
            $select->from(
                array('t' => 'quizzes'),
                array('t.quiz_id', 't.title', 't.questions', 'public' => 't.status', 't.updated','tags' => 't.quiz_id')
            )
            //Пока закомментим
            ->where('location = ?', HM_Poll_PollModel::LOCALE_TYPE_GLOBAL)
            ;
        }

        $grid = $this->getGrid(
            $select,
            array(
                'quiz_id' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'subjecttemp' => array('hidden' => true),
                'locationtemp' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'location' => array('title' => _('Место хранения')),
                'questions' => array('title' => _('Вопросов')),
                'subject'   => array('title' => _('Используется в данном курсе?')),
                'public' => array('title' => _('Статус')),
                'updated' => array('title' => _('Дата последнего изменения')),
                'tags' => array('title' => _('Метки'))
            ),
            $filters,
            $gridId
        );

        if ($subjectId) {
	        $grid->setGridSwitcher(array(
	  			array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId)),
	  			array('name' => 'global', 'title' => _('все, включая опросы из Базы знаний'), 'params' => array('subject' => null), 'order' => 'subject', 'order_dir' => 'DESC'),
	  		));
        }

        $grid->addAction(
            array('module' => 'poll', 'controller' => 'list', 'action' => 'view', 'subject_id'=>$subjectId),
            array('quiz_id'),
            $this->view->icon('view', _('Просмотреть опрос'))
        );

        $grid->addAction(
            array('module' => 'poll', 'controller' => 'list', 'action' => 'edit'),
            array('quiz_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'poll', 'controller' => 'list', 'action' => 'delete'),
            array('quiz_id'),
            $this->view->icon('delete')
        );


        if($subjectId > 0){
            $grid->addMassAction(
                array('module' => 'poll', 'controller' => 'list', 'action' => 'assign'),
                _('Использовать в данном курсе')
            );

            $grid->addMassAction(
                array('module' => 'poll', 'controller' => 'list', 'action' => 'unassign'),
                _('Не использовать в данном курсе')
            );
        } else {
            $grid->addMassAction(
                array('module' => 'poll', 'controller' => 'list', 'action' => 'publish'),
                _('Опубликовать')
            );
        }

//        if(in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_MANAGER,HM_Role_RoleModelAbstract::ROLE_DEVELOPER))){
            $grid->addMassAction(
                array('module' => 'poll', 'controller' => 'list', 'action' => 'delete-by'),
                _('Удалить'),
                _('Вы уверены?')
            );
//        }

        $grid->updateColumn('location',
            array('callback' =>
                array('function' =>
                    array($this,'updateStatus'),
                    'params'   => array('{{location}}')
                )
            )
        );

        $grid->updateColumn('subject',
            array('callback' =>
                array('function' =>
                    array($this,'updateSubject'),
                    'params'   => array('{{subject}}')
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


        $grid->updateColumn('title',
            array('callback' =>
                array('function' =>
                    array($this,'updateName'),
                    'params'   => array('{{title}}', '{{status}}', '{{quiz_id}}')
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
                      'params'   => array('{{questions}}', '{{quiz_id}}', $subjectId)
                )
            )
        );

        $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', $this->getService('TagRef')->getPollType() )
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


    public function create(Zend_Form $form)
    {

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $array = array(
                'title' => $form->getValue('title'),
                'status' => $form->getValue('status'),
                'description' => $form->getValue('description'),
                'subject_id' => $subjectId
            );


        if($subjectId == 0){
            $array['location'] = 1;
        }

        $quiz = $this->getService('Poll')->insert(
            $array
        );

        if ($quiz && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($quiz->quiz_id, HM_Classifier_Link_LinkModel::TYPE_POLL);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($quiz->quiz_id, HM_Classifier_Link_LinkModel::TYPE_POLL, $classifierId);
                    }
                }
            }
        }

        if ($tags = $form->getParam('tags')) {
            $this->getService('Tag')->update( $tags, $quiz->quiz_id, $this->getService('TagRef')->getPollType() );
        }

        if (($subjectId > 0 && $quiz)) {
            $this->getService('SubjectPoll')->insert(array('subject_id' => $subjectId, 'quiz_id' => $quiz->quiz_id));
        }
    }

    public function update(Zend_Form $form)
    {
        $subjectid = (int) $this->_getParam('subject_id', 0);

        $quiz = $this->getOne($this->getService('Poll')->find($this->_request->getParam('quiz_id')));

        if(!$quiz){
            return false;
        }
        $userRole = $this->getService('User')->getCurrentUserRole();

/*        if(!$quiz->isEditAllowed($subjectid, $userRole)){
            return false;
        }*/
        $quiz = $this->getService('Poll')->update(
             array(
                 'quiz_id' => $this->_request->getParam('quiz_id'),
                 'title' => $form->getValue('title'),
                 'status' => $form->getValue('status'),
                 'description' => $form->getValue('description'),
             )
         );


        $this->getService('Tag')->update( $form->getParam('tags',array()), $this->_request->getParam('quiz_id'), $this->getService('TagRef')->getPollType() );


        if ($quiz && !$this->_getParam('subject_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($quiz->quiz_id, HM_Classifier_Link_LinkModel::TYPE_POLL);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($quiz->quiz_id, HM_Classifier_Link_LinkModel::TYPE_POLL, $classifierId);
                    }
                }
            }
        }

    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $quiz = $this->getOne($this->getService('Poll')->find($id));


        if(!$this->getService('Poll')->isEditable($quiz->subject_id, $subjectId, $quiz->location)){
            return false;
        }
        $this->getService('Poll')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('quiz_id', 0);
        if ($id) {
            $res = $this->delete($id);

            if($res == true){
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            }else{
                 $this->_flashMessenger->addMessage(_('Для удаления опроса не хватает прав'));
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
                    $this->_flashMessenger->addMessage(_('Глобальные опросы невозможно удалить из учебного курса.'));
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function setDefaults(Zend_Form $form)
    {
        $quizId = (int) $this->_getParam('quiz_id', 0);

        $quiz = $this->getOne($this->getService('Poll')->find($quizId));
        $values = $quiz->getValues();
        $values['tags'] = $this->getService('Tag')->getTags($quizId, $this->getService('TagRef')->getPollType());
        if ($quiz) {
            $form->setDefaults(
                $values
            );
        }
    }

    public function assignAction()
    {
    	$gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {

                    $res = $this->getService('SubjectPoll')->find($this->id, $id);

                    if(count($res) == 0){
                        $this->getService('SubjectPoll')->insert(array('subject_id' => $this->id, 'quiz_id' => $id));
                    }
                }
                $this->_flashMessenger->addMessage(_('Опросы успешно назначены на курс'));
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

                    $this->getService('SubjectPoll')->delete(array($this->id, $id));
                }
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }

    public function updateStatus($status)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $statuses = HM_Resource_ResourceModel::getLocaleStatuses();

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

        if($this->getService('Poll')->isEditable($subjectId, $subject_id, $status)){
            return $actions;
        }else{
            return '';
        }
    }


    public function updateName($name, $status, $quizId)
    {
        $subjectId = $this->_getParam('subject_id', 0);

        $userRole = $this->getService('User')->getCurrentUserRole();

        //if($this->getService('TestAbstract')->isEditable($subjectId, $subject_id, $status)){
            return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'quiz', 'quiz_id' => $quizId, 'subject_id' => $subjectId), null, true, false).'">' . $name . '</a>';
        //}else{
            //return $name;
        //}
    }


    public function updatePublic($status)
    {
        $statuses = HM_Test_Abstract_AbstractModel::getStatuses();
        return $statuses[$status];

    }

    public function viewAction()
    {
        $quizId = (int) $this->_getParam('quiz_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        if ($quizId) {

            $abstract = $this->getOne($this->getService('Poll')->find($quizId));
            if ($abstract) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                    $this->getService('Test')->quoteInto(
                        array('lesson_id = ?', ' AND test_id = ?'),
                        array(0, $quizId)
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
                            'test_id' => $quizId,
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

                    $_SESSION['default']['lesson']['execute']['returnUrl'] = $this->view->serverUrl($this->view->url(array('module' => 'poll', 'controller' => 'list', 'action' => 'index', 'subject_id' => $subjectId), null, true));
                    $this->_redirector->gotoUrl($this->view->serverUrl(sprintf('/'.HM_Lesson_Test_TestModel::TEST_EXECUTE_URL, $test->tid, 0)));
                }

            }
        }

        $this->_flashMessenger->addMessage(sprintf(_('Задание #%d не найдено'), $quizId));
        $this->_redirector->gotoSimple('index', 'list', 'poll', array('subject_id' => $subjectId));
    }

    public function publishAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Poll')->publish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Упражнения успешно опубликованы.'));
        }
        $this->_redirectToIndex();
    }

    public function updateQuestions($questions, $quiz_id, $subject_id)
    {
        if (!empty($questions)) { //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN) {
            return '<a href="' . $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'quiz', 'quiz_id' => $quiz_id, 'subject_id' => $subject_id)) . '" title="' . _('Список вопросов') . '">' . $questions . '</a>';
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
        $defaults = $this->getService('Poll')->getDefaults();
        $defaults['title'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['title']) && $subjectId) {
            if ($poll = $this->getService('Poll')->insert($defaults)) {

                if ($this->getService('SubjectPoll')->insert(array('subject_id' => $subjectId, 'quiz_id' => $poll->quiz_id))) {
    				$this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                    $result = $poll->quiz_id;
                }
            }
        }
        exit(Zend_Json::encode($result));
    }

}