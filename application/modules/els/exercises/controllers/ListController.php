<?php

class Exercises_ListController extends HM_Controller_Action_Crud
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function init()
    {        
        $this->_setForm(new HM_Form_List());
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
            $this->_redirector->gotoSimple('index', 'list', 'exercises', array('subject_id' => $subjectId));
        }
        parent::_redirectToIndex();
    }

    public function assignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        //pr($postMassIds); exit;
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    
                    $res = $this->getService('SubjectExercise')->find($this->id, $id);
                    
                    if(count($res) == 0){
                        $this->getService('SubjectExercise')->insert(array('subject_id' => $this->id, 'exercise_id' => $id));
                    }
                }
                $this->_flashMessenger->addMessage(_('Тесты успешно назначены на курс'));
            }
        }
        $this->_redirectToIndex();
    }
    
    public function unassignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    
                    $this->getService('SubjectExercise')->delete(array($this->id, $id));
                }
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }
    
    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $order = $this->_getParam('ordergrid', '');
        
        $filters = array('title' => null,
                         'tags' => array('callback' => array('function' => array($this, 'filterTags'))));
        
        $rolesWithFilter = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
            
        if(in_array($this->getService('User')->getCurrentUserRole(), $rolesWithFilter)){
            $filters['public'] = array('values' => HM_Test_Abstract_AbstractModel::getStatuses());
/*            if(!$this->_getParam('publicgrid', '')&& !$this->isAjaxRequest()){
                $this->_setParam('publicgrid', 1);
            }*/
        } else {
//            $this->_setParam('publicgrid', 1);
        }

        if(!$this->_getParam('publicgrid', '')&& !$this->isAjaxRequest()){
            $this->_setParam('publicgrid', 1);
        }

        
        
        if ($subjectId) {
            
            if($order == ''){
                $this->_setParam('ordergrid', 'subject_ASC');
            }
            
            $select = $this->getService('Exercises')->getSelect();
            $select->from(
                    array('t' => 'exercises'),
                    array('t.exercise_id', 't.title','tags'=>'t.exercise_id'));
            
            
            $subSelect = $this->getService('Exercises')->getSelect();
            $subSelect->from(array('s' => 'subjects_exercises'), array('subject_id', 'exercise_id'))->where('subject_id = ?', $subjectId);

            $select->joinLeft(
                       array('s' => $subSelect), 
                       't.exercise_id = s.exercise_id',
                       array(
                           't.status', 
                           'statustemp'  => 't.status', 
                           'subject'     => 's.subject_id', 
                           'subjecttemp' =>  's.subject_id', 
                           't.questions'
                       )
                   )
                   ->where('t.status = ' . (int) HM_Test_Abstract_AbstractModel::STATUS_PUBLISHED . ' OR t.subject_id = ' . (int) $subjectId);
        }else{
            
            if($order == ''){
                $this->_setParam('ordergrid', 'public_DESC');
            }
            
            $select = $this->getService('Exercises')->getSelect();
            $select->from(
                array('t' => 'exercises'),
                array('t.exercise_id', 't.title', 't.questions', 'public' => 't.status', 'subject' => new Zend_Db_Expr('0'),'tags'=>'t.exercise_id')
            )
            //Пока закомментим
            //->where('status = ?', HM_Test_Abstract_AbstractModel::STATUS_PUBLISHED)
            ;     
        }
        //pr($select); exit;
        $grid = $this->getGrid(
            $select,
            array(
                'exercise_id' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
            	'subjecttemp' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'status' => array('title' => _('Тип')),
                'questions' => array('title' => _('Вопросов')),
                'subject'   => array('title' => _('Используется в данном курсе?')),
                'public' => array('title' => _('Статус ресурса БЗ')),
            	'tags' => array('title' => _('Метки'))
            ),
            $filters
        );
        
        $grid->addAction(
            array('module' => 'exercises', 'controller' => 'list', 'action' => 'edit'),
            array('exercise_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'exercises', 'controller' => 'list', 'action' => 'delete'),
            array('exercise_id'),
            $this->view->icon('delete')
        );


        if($subjectId > 0){
            $grid->addMassAction(
                array('module' => 'exercises', 'controller' => 'list', 'action' => 'assign'),
                _('Использовать в данном курсе')
            );
            
            $grid->addMassAction(
                array('module' => 'exercises', 'controller' => 'list', 'action' => 'unassign'),
                _('Не использовать в данном курсе')
            );
        } else {
            $grid->addMassAction(
                array('module' => 'exercises', 'controller' => 'list', 'action' => 'publish'),
                _('Опубликовать')
            );
        }
        
        $grid->addMassAction(
            array('module' => 'exercises', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );
        
        
        $grid->updateColumn('status',
            array('callback' => 
                array('function' => 
                    array($this,'updateStatus'),
                    'params'   => array('{{subjecttemp}}','{{statustemp}}')
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
                    'params'   => array('{{title}}', '{{status}}', '{{subject}}', '{{exercise_id}}')
                )
            )
        );
         $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', $this->getService('TagRef')->getExercisesType())
                )
            ));
  
        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{statustemp}}', '{{subjecttemp}}')
            )
        );

        $this->view->subjectId = $subjectId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Упражнение успешно создано'),
            self::ACTION_UPDATE => _('Упражнение успешно обновлёно'),
            self::ACTION_DELETE => _('Упражнение успешно удалёно'),
            self::ACTION_DELETE_BY => _('Упражнения успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {
        
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $exercise = $this->getService('Exercises')->insert(
            array(
                'title'       => $form->getValue('title'),
                'status'      => $form->getValue('status'),
                'description' => $form->getValue('description'),
                'subject_id'  => $subjectId
            )
        );

        if ($exercise && !$this->_getParam('exercise_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($exercise->exercise_id, HM_Classifier_Link_LinkModel::TYPE_EXERCISE);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($exercise->exercise_id, HM_Classifier_Link_LinkModel::TYPE_EXERCISE, $classifierId);
                    }
                }
            }
        }
        
        if ($tags = $form->getParam('tags')) {
            $this->getService('Tag')->update( $tags, $exercise->exercise_id, $this->getService('TagRef')->getExercisesType() );
        }

        if (($subjectId > 0 && $exercise)) {
            $this->getService('SubjectExercise')->insert(array('subject_id' => $subjectId, 'exercise_id' => $exercise->exercise_id));
        }
    }

    public function update(Zend_Form $form)
    {
        
        $subjectid = (int) $this->_getParam('subject_id', 0);
        //pr ($form->getValue('exercise_id')); exit;
        $exercise = $this->getService('Exercises')->getOne($this->getService('Exercises')->find($form->getValue('exercise_id')));

        if(!$exercise){
            return false;
        }
        $userRole = $this->getService('User')->getCurrentUserRole();
        
        if(!$this->getService('Exercises')->isEditable($exercise->subject_id, $subjectid, $exercise_id->status)){
            return false;
        }
        $exercise = $this->getService('Exercises')->update(
             array(
                 'exercise_id' => $form->getValue('exercise_id'),
                 'title' => $form->getValue('title'),
                 'status' => $form->getValue('status'),
                 'description' => $form->getValue('description'),
             )
         );

        
        $this->getService('Tag')->update( $form->getParam('tags',array()), $form->getValue('exercise_id'), $this->getService('TagRef')->getExercisesType() );
        
         
        if ($exercise && !$this->_getParam('exercise_id', 0)) {
            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($exercise->exercise_id, HM_Classifier_Link_LinkModel::TYPE_EXERCISE);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($exercise->exercise_id, HM_Classifier_Link_LinkModel::TYPE_EXERCISE, $classifierId);
                    }
                }
            }
        }

    }

    public function delete($id)
    {
        $subjectid = (int) $this->_getParam('subject_id', 0);
        
        $exercise = $this->getService('Exercises')->getOne($this->getService('Exercises')->find($id));

        //$userRole = $this->getService('User')->getCurrentUserRole();
        
        if(!$this->getService('Exercises')->isEditable($exercise->subject_id, $subject_id, $exercise->status)){
            return false;
        }    
        $this->getService('Exercises')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('exercise_id', 0);
        if ($id) {
            $res = $this->delete($id);
            
            if($res == true){
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            }else{
                 $this->_flashMessenger->addMessage(_('Для удаления упражнения не хватает прав'));
            }
            
        }
        $this->_redirectToIndex();
    }

    public function setDefaults(Zend_Form $form)
    {
        $exerciseId = (int) $this->_getParam('exercise_id', 0);

        $exercise = $this->getService('Exercises')->getOne($this->getService('Exercises')->find($exerciseId));
        $values = $exercise->getValues();
        $values['tags'] = $this->getService('Tag')->getTags($exerciseId, $this->getService('TagRef')->getExercisesType());
        if ($exercise) {
            $form->setDefaults( $values );
        }
    }
    
    public function updateStatus($locale, $status)
    {
        
       // $statuses = HM_Test_Abstract_AbstractModel::getLocaleStatuses();
        //return $statuses[$locale];
        
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $statuses = HM_Test_Abstract_AbstractModel::getLocaleStatuses();

        if($subjectId == $locale && $status == HM_Test_Abstract_AbstractModel::STATUS_UNPUBLISHED){
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL];
        }else{
            return $statuses[HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL];
        }        
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

        if($this->getService('Exercises')->isEditable($subjectId, $subject_id, $status)){
            return $actions;
        }else{
            return '';
        }
    }
    
    
    public function updateName($name, $status, $subjectId, $exerciseId)
    {
        $subject_id = $this->_getParam('subject_id', 0);
        
        $userRole = $this->getService('User')->getCurrentUserRole();
        
        //if($this->getService('TestAbstract')->isEditable($subjectId, $subject_id, $status)){
            return '<a href="'.$this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'new', 'exercise_id' => $exerciseId, 'subject_id' => $subjectId), null, true, false).'">' . $name . '</a>';
        //}else{
            //return $name;
        //}
    }

    
    public function updatePublic($status)
    {
        $statuses = HM_Test_Abstract_AbstractModel::getStatuses();
        
        return $statuses[$status];
        
    }
    
    public function publishAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Exercises')->publish($id);
                }
            }
            $this->_flashMessenger->addMessage(_('Упражнения успешно опубликованы.'));
        }
        $this->_redirectToIndex();
    }

}