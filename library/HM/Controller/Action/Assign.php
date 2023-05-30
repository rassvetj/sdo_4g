<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 15.09.2010
 * Time: 10:59:51
 * To change this template use File | Settings | File Templates.
 */

class HM_Controller_Action_Assign extends HM_Controller_Action
{
	// Коды возврата
	const RETCODE_DOACTION_OK = 0; // Ошибок не было. В данный момент никак не используется.
	const RETCODE_DOACTION_END_ITERATION = 1; // Ошибка затрагивает только текущую интераци, продолжение интерации не возможно.
	const RETCODE_DOACTION_END_LOOP = 2; // Ошибка делает невозможным продолжение всего цикла.

/*    protected $_assignOptions = array(
        'role'           => 'Teacher',
        'courseStatuses' => array(2),
        'table'          => 'Teachers',
        'courseTable'    => 'Courses',
        'personKey'      => 'MID',
        'courseKey'      => 'CID'
    );*/

    protected $_assignOptions = array(
        'role'                  => 'Teacher',
        'courseStatuses'        => array(2),
        'table'                 => 'Teachers',
        'tablePersonField'      => 'MID',
        'tableCourseField'      => 'CID',
        'courseTable'           => 'Courses',
        'courseTablePrimaryKey' => 'CID',
        'courseTableTitleField' => 'Title',
        'courseIdParamName'     => 'course_id'
    );




    protected $_grid = null;
    protected $_fixedRow = true;
    protected $_fixedRowsPrimary = 't1.MID';

    protected $programmCache = array();

    public function indexAction()
    {

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        /*
        $notAll = $this->_getParam('all', 1);

        // temp hack
        if (!isset($this->_assignOptions['courseIdParamName'])) {
            $this->_assignOptions['courseIdParamName'] = 'course_id';
        }

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);

        $sorting = $this->_request->getParam('ordergrid');

        if ($sorting == ""){
            $this->_request->setParam('ordergrid', 'course_ASC');
        }

        $select = $this->getService('User')->getSelect();

        if ($notAll) {
            if ($courseId > 0) {
                $subSelect = $this->getService('User')->getSelect();

                $subSelect->from(
                        array('t1' => $this->_assignOptions['table']),
                        array($this->_assignOptions['tablePersonField'], $this->_assignOptions['tableCourseField'])
                    )->joinInner(
                        array('t2' => $this->_assignOptions['courseTable']),
                        't1.'.$this->_assignOptions['tableCourseField'].' = t2.'.$this->_assignOptions['courseTablePrimaryKey'],
                        array()
                    )->where(
                        't1.'.$this->_assignOptions['tableCourseField'].' = ?', $courseId
                    );

                $select->from(
                            array('t1' => 'People'),
                            array('MID', 'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"))
                        )->joinInner(
                            array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                            array()
                        )/*->joinInner(
                            array('t3' => $this->_assignOptions['courseTable']),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'],
                            array()
                        )* /->joinLeft(
                            array('t4' => $subSelect),
                            't1.MID = t4.'.$this->_assignOptions['tablePersonField'],
                            array('course' => 't4.'.$this->_assignOptions['tableCourseField'])
                        )->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic, t4.'.$this->_assignOptions['tableCourseField']));
                                //echo $select;
                               /// exit;
            } else {
                $select->from(
                            array('t1' => 'People'),
                            array('MID', 'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"))
                        )->joinInner(
                            array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                            array()
                        )/*->joinInner(
                            array('t3' => $this->_assignOptions['courseTable']),
                            't2.'.$this->_assignOptions['tableCourseField'].' = t3.'.$this->_assignOptions['courseTablePrimaryKey'],
                            array()
                        )* /->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic'));
                       // echo $select;
                       // exit;
            }

        } else {
            if ($courseId > 0) {
                $subSelect = $this->getService('User')->getSelect();

                $subSelect->from(
                        array('t1' => $this->_assignOptions['table']),
                        array($this->_assignOptions['tablePersonField'], $this->_assignOptions['tableCourseField'])
                    )->joinInner(
                        array('t2' => $this->_assignOptions['courseTable']),
                        't1.'.$this->_assignOptions['tableCourseField'].' = t2.'.$this->_assignOptions['courseTablePrimaryKey'],
                        array()
                    )->where(
                        't1.'.$this->_assignOptions['tableCourseField'].' = ?', $courseId
                    );

                $select->from(
                            array('t1' => 'People'),
                            array('MID', 'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"))
                        )->joinLeft(
                            array('t2' => $subSelect),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                             array('course' => 't2.'.$this->_assignOptions['tableCourseField'])
                        )->group(array('t1.'.$this->_assignOptions['tablePersonField'], 't1.LastName', 't1.FirstName', 't1.Patronymic', 't2.'.$this->_assignOptions['tableCourseField']));

            } else {
                $select->from(
                            array('t1' => 'People'),
                            array('MID',
                                  'fio'  => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)")
                            )
                        );
            }
        }

        if ($courseId > 0) {
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}').' {{fio}}'),
                    'course' => array(
                        'title' => _('Назначен на этот курс?'),
                        'callback' => array(
                            'function' => array($this, 'updateGroupColumn'),
                            'params' => array('{{course}}', $courseId)
                        )
                    )
                ),
                array(
                    'MID' => null,
                    'fio' => null,
                    'course' => array(
                        'values' => array(
                            $courseId => _('Да'),
                            'ISNULL' => _('Нет')
                        )
                    )
                )
            );
        } else {
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}').' {{fio}}'),
                )
            );
        }*/

        $grid = $this->_grid;

       /* $right = new Bvb_Grid_Extra_Column();

        if($this->_assignOptions['tableCourse'] == 'Courses'){
            $right->position('right')->name('courses')->title(_('Курсы'))->helper(
                array('name' => 'userCourses', 'params' => array('{{MID}}', $this->_assignOptions['role'], ', <br>'))
            );
        }elseif($this->_assignOptions['courseTable'] == 'subjects'){
             $right->position('right')->name('courses')->title(_('Курсы'))->helper(
                array('name' => 'userCourses', 'params' => array('{{MID}}', $this->_assignOptions['role'], ', <br>', 'Subject'))
            );

        }

        $grid->addExtraColumns($right);*/

/*        $grid->addMassAction(
            array('action' => 'index'),
            _('Выберите действие')
        );*/

        $url = array('action' => 'do');
        if ($courseId > 0) {
            $url['courseId'] = $courseId;
            $url[$this->_assignOptions['courseIdParamName']] = $courseId;
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) {

            // заголовок действия назначения на курс в зависимости от контроллера
            switch ( Zend_Controller_Front::getInstance()->getRequest()->getControllerName() ){
                case 'teacher':
                    if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                        break;
                    }
                    $assignMenuItem = ( $courseId>0 )? _('Назначить преподавателей на курс') : _('Назначить преподавателей на курсы');
                    $unassignMenuItem = ( $courseId>0 )? _('Удалить преподавателей с курса') : _('Удалить преподавателей с курсов');
                break;
                case 'student':
                    $subject = $this->view->getParam('subject');
                    if (!$subject || ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED)) {
                        $assignMenuItem = ( $courseId>0 )? _('Назначить слушателей на курс') : _('Назначить слушателей на курсы');
                        $unassignMenuItem = ( $courseId>0 )? _('Удалить слушателей с курса') : _('Удалить слушателей с курсов');
                    }
                break;
                default:
                    if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                        break;
                    }
                    $assignMenuItem = ( $courseId>0 )? _('Назначить на курс') : _('Назначить на курсы');
                    $unassignMenuItem = ( $courseId>0 )? _('Удалить с курса') : _('Удалить с курсов');
                break;
            }

            if ($assignMenuItem) {
                $grid->addMassAction(
                    $url,
                    $assignMenuItem,
                    _('Вы уверены?')
                );
            }

            $url = array('action' => 'unassign');
            if ($courseId > 0) {
                $url[$this->_assignOptions['courseIdParamName']] = $courseId;
                $url['courseId'] = $courseId;
            }

            if ($unassignMenuItem) {
                $grid->addMassAction(
                    $url,
                    $unassignMenuItem,
                    _('Вы уверены?')
                );
            }
        }

        if($this->_assignOptions['tableCourse'] == 'Course'){
            $collection = $this->getService('Course')->fetchAll(
                $this->getService('Course')->quoteInto('Status IN (?)', $this->_assignOptions['courseStatuses'])
            );
        }else{
            $userId = $this->getService('User')->getCurrentUserId();
            //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
            
			if($courseId <= 0){
				if($this->getRequest()->getControllerName() == 'student'){
					
				} else {
					$collection = $this->getService('Dean')->getActiveSubjectsResponsibilities($userId);
					$full_collection = $this->getService('Dean')->getSubjectsResponsibilities($userId);
				}
			}
        }

        if ($courseId <= 0) {
            $courses = array(_('Выберите курс'));
            $all_courses = array(_('Выберите курс'));
            if (count($collection)) {
                $courses = $collection->getList($this->_assignOptions['courseTablePrimaryKey'], $this->_assignOptions['courseTableTitleField'], _('Выберите курс'));
                /*if ($courseId > 0) {
                    if (isset($courses[$courseId])) {
                        $courses = array(_('Выберите курс'), $courseId => $courses[$courseId]);
                    } else {
                        $courses = array(_('Выберите курс'));
                    }
                }*/
            }

            if (count($full_collection)) {
                $all_courses = $full_collection->getList($this->_assignOptions['courseTablePrimaryKey'], $this->_assignOptions['courseTableTitleField'], _('Выберите курс'));
            }
            $grid->addSubMassActionSelect(
                array(
                    $this->view->url(array('action' => 'do'))/*,
                    $this->view->url(array('action' => 'unassign'))*/
                ),
                'courseId[]',
                $courses
            );
            $grid->addSubMassActionSelect(
                array($this->view->url(array('action' => 'unassign'))
                ),
                'unCourseId[]',
                $all_courses
            );

        }
       
         
        $grid->addAction(array(
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index'
        ),
           array('MID'),
           _('Отправить сообщение')     
        );
        //добавляем еще одно действие 
        //по объединению дубликатов
    //    $grid->addAction(array(
    //        'module' => 'union',
    //        'controller' => 'union',
    //        'action' => 'index'
    //    ),
    //        array('MID','dublicate'),
    //        _('Объединить')    
    //    );
        
        $grid->addMassAction(array('module' => 'message',
        						   'controller' => 'send',
        						   'action' => 'index'),
                             _('Отправить сообщение'));

       
   $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
   $this->view->grid = $grid->deploy();
     
    }

    protected function _preAssign($personId, $courseId)
    {

    }

    protected function _postAssign($personId, $courseId)
    {

    }

    protected function _finishAssign()
    {

    }

    protected function _assign($personId, $courseId)
    {
        $data = array(
			$this->_assignOptions['tablePersonField'] => $personId,
			$this->_assignOptions['tableCourseField'] => $courseId
		);
		
		if($this->_assignOptions['role'] == 'Tutor' || $this->_assignOptions['role'] == 'Teacher'){
			$data['date_assign'] = date('Y-m-d 23:59',time());
		}		
		return $this->getService($this->_assignOptions['role'])->insert(
            $data
			/*
			array(
                $this->_assignOptions['tablePersonField'] => $personId,
                $this->_assignOptions['tableCourseField'] => $courseId
            )
			*/
        );
    }

    public function doAction()
    {
        $subjectId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $courseIds = $this->_getParam('courseId', array(0));
        if (!is_array($courseIds)) $courseIds = array($courseIds);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_' . $gridId, '');

	    if ((((count($courseIds) == 1)) && empty($courseIds[0])) || !strlen($postMassIds)) {
			$this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите курс' ) );
        }

	    if (true || $this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $subjectId)) {

	        foreach ($courseIds as $courseId) {
                if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
                    continue;
                }

                $ids = explode(',', $postMassIds);
	            if (count($ids)) {
	                $errors=false;
	                foreach($ids as $id) {
	                	$id = (int) $id;

	                    if (method_exists($this, '_preAssign')) {
	                        $return = $this->_preAssign($id, $courseId);

	                        if ($return === self::RETCODE_DOACTION_END_ITERATION){ // Константы кодов ошибок с описаниями находятся в начале класс
	                        	$errors = true;
	                        	continue;
	                        }
	                        elseif ($return === self::RETCODE_DOACTION_END_LOOP){
	                        	$errors = true;
	                        	break;
	                        }
	                    }

	                    $fetch = $this->getService($this->_assignOptions['role'])->fetchAll(array('MID = ?' => $id, 'CID = ?' => $courseId));
	                    try{
	                        if(count($fetch) == 0){
	                            $this->_assign($id, $courseId);
	                        }
	                    }catch (Zend_Db_Exception  $e){
	                        $errors=true;
	                    }


	                    if (method_exists($this, '_postAssign')) {
	                        $this->_postAssign($id, $courseId);
	                    }
	                }
	            }
			}
	    } else {
            $errors = true;
			$this->_flashMessenger->addMessage ( _ ( 'Нет прав на назначение на этот курс' ) );
        }

		if ($errors == false) {
			$this->_flashMessenger->addMessage ( _ ( 'Пользователи успешно назначены'));
		} else{
			$this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли ошибки'));
		}


        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $messenger = $this->getService('Messenger');
        $messenger->sendAllFromChannels();

        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId,'all'=>!$this->_getParam('all', isset($default->grid['assign-student-index'][$gridId]['all']) ? $default->grid['assign-student-index'][$gridId]['all'] : null)));
    }

    protected function _preUnassign($personId, $courseId)
    {

    }

    protected function _postUnassign($personId, $courseId)
    {

    }

    protected function _finishUnassign()
    {

    }

    protected function _unassign($personId, $courseId)
    {
        return $this->getService($this->_assignOptions['role'])->deleteBy(
            sprintf("%s = %d AND %s = %d", $this->_assignOptions['tablePersonField'], $personId, $this->_assignOptions['tableCourseField'], $courseId)
        );
    }


    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'],0);
        $courseIds = $this->_getParam('unCourseId', $this->_getParam('courseId',array(0)));
        if (!is_array($courseIds)) $courseIds = array($courseIds);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_' . $gridId, '');

	    if ((((count($courseIds) == 1)) && empty($courseIds[0])) || !strlen($postMassIds)) {
			$this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите курс' ) );
        }

	    if (true || $this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $subjectId)) {

	        foreach ($courseIds as $courseId) {
                if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
                    continue;
                }

	            $ids = explode(',', $postMassIds);
	            if (count($ids)) {
	                foreach($ids as $id) {
	                    if (method_exists($this, '_preUnassign')) {
	                        $this->_preUnassign($id, $courseId);
	                    }
	                    $this->_unassign($id, $courseId);
	                    if (method_exists($this, '_postUnassign')) {
	                        $this->_postUnassign($id, $courseId);
	                    }
	                }
	            }
			}
			$this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
	    } else {
			$this->_flashMessenger->addMessage ( _ ( 'Нет прав на назначение на этот курс' ) );
        }

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId));
    }

    public function updateGroupColumn($field, $id)
    {
        if ($field == $id) {
            return _('Да');
            //return $this->view->icon('useradd');
        }
        return _('Нет');
        //return $this->view->icon('usernotadd');
    }

    public function programmsCache($field, $select){

        if($this->programmCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['programms'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->programmCache = $this->getService('Programm')->fetchAll(array('programm_id IN (?)' => $tmp), 'name');
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        if (count($fields)) {
            $programms = $this->getService('Programm')->fetchAll(array('programm_id IN (?)' => $fields), 'name');
            $fields = array();
            if (count($programms)) {
                foreach ($programms as $programm) {
                    $fields[] = $programm->programm_id;
                }
            }
        }


        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . $this->getService('Programm')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->programmCache->exists('programm_id', $value);
            $result[] = sprintf('<p>%s</p>', $tempModel->name);
        }

        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }
    
    
    public function filterDepartments($data)
    {
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }

        if ( $data['userIdField'] ) {
            $userIdField = $data['userIdField'];
        } else {
            $columns = $data['select']->getPart(Zend_Db_Select::COLUMNS);
            foreach ($columns as $column) {
                if ($column[1] == 'MID') {
                    $userIdField = $column[0].'.'.$column[1];
                    break;
                }
            }
        }

        $select = $data['select'];
        
        $select->joinInner(array('p_filter' => 'structure_of_organ'),
            'p_filter.mid = ' . $userIdField,
            array()
        );
        $select->joinInner(array('d_filter' => 'structure_of_organ'),
            'd_filter.soid = p_filter.owner_soid',
            array()
        );
        $select->where($this->quoteInto(
            "d_filter.name LIKE ?",
            '%'.$data['value'].'%'
        ));
    }
    
    public function filterPositions($data)
    {
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }

        if ( $data['userIdField'] ) {
            $userIdField = $data['userIdField'];
        } else {
            $columns = $data['select']->getPart(Zend_Db_Select::COLUMNS);
            foreach ($columns as $column) {
                if ($column[1] == 'MID') {
                    $userIdField = $column[0].'.'.$column[1];
                    break;
                }
            }
        }

        $select = $data['select'];
        
        $select->joinInner(array('p_filter' => 'structure_of_organ'),
            'p_filter.mid = ' . $userIdField,
            array()
        );
        $select->where($this->quoteInto(
            "p_filter.name LIKE ?",
            '%'.$data['value'].'%'
        ));
    }
    
    public function filterSubjects($data)
    {
        $fieldName = $data['fieldName'];
        $tableName = $data['tableName'];
        
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }

        $select = $data['select'];
        
        if($fieldName != ''){
            $tableName = 's_filter';
            $select->joinInner(
                array($tableName => 'Subjects'),
                $fieldName.' = s_filter.subid',
                array()
            );
        }
                
        $select->where($this->quoteInto(
            $tableName.'.name LIKE ?',
            '%'.$data['value'].'%'
        ));
//        var_dump($select->__toString());die;
    }
    
}