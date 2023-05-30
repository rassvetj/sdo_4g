<?php
class User_StudentController extends HM_Controller_Action_User 
{

	// Сообщения
	
	const MSG_COURSE_NOT_FOUND = 'Следующие курсы не были найдены в базе: %s';
	const MSG_COURSE_SUCCESS_ASSIGNED = 'Следующие курсы были успешно назначены: %s';
	const MSG_SOMEBODY_ALREADY_ASSIGNED = 'Следующие курсы уже были назначены этому слушателю: %s';
	const MSG_SOME_COURSE_EXPIRED = 'Срок действия следующих курсов истёк: %s';
	
    /**
     * Экшн для списка курсов
     */
    public function assignAction() 
    {

        $userId = $this->_getParam('user_id', 0);
        
        $order = $this->_getParam('ordergrid');
        if($order == ''){
            // @todo: есть подозрение, что в Orcale оно работает наоборот
            $this->_setParam('ordergrid', 'status_DESC');
        }
        
        $select = $this->getService('Subject')->getSelect();
        $subSelect = $this->getService('Subject')->getSelect();
        
        $subSelect->from(array('Students'), array('MID', 'CID'))->where('MID = ?', $userId);
        
        $select->from(array('s' => 'subjects'), array())
                ->joinLeft(array('d' => $subSelect),
                    's.subid = d.CID',
                    array(
                        'subid' => 's.subid',
                        'name' => 's.name',
                        'status' => 'd.MID'
                    )
                )
                ->group(array('s.subid', 's.name', 'd.MID'));

        // Область ответственности     
        $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());

        if($options['unlimited_subjects'] != 1){
            $select->joinInner(array('d2' => 'deans'), 'd2.subject_id = s.subid', array())
                   ->where('d2.MID = ?', $this->getService('User')->getCurrentUserId());            
        }

        //$roles = HM_Role_RoleModelAbstract::getBasicRoles(false);
        $grid = $this->getGrid($select,
            array('subid' => array('hidden' => true),
                'name' => array('title' => _('Название'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}') . '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => ''), null, true) . '{{subid}}'.'">'. ' {{name}}</a>'),
                'login' => array('title' => _('Логин')),
                'role' => array('title' => _('Роли')),
                'status' => array('title' => _('Назначен?'))
            ),
            array(
            	'name' => null,
                'status' => array('values' => array( $userId => _('Да'), 'ISNULL' => _('Нет'),)),
            )
        );

        //$grid->addMassAction(array('action' => 'index'), _('Выберите действие'));
        
        if($this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_STUDENT)){
        	$grid->addMassAction(array('action' => 'do'), _('Назначить на курсы'));
        }else{
        	$grid->addMassAction(array('action' => 'do'), _('Назначить на курсы'), _('Вы уверены, что хотите сделать пользователя слушателем?'));
        }
        $grid->addMassAction(array('action' => 'delete'), _('Отменить назначение курсов'), _('Вы уверены?'));
        

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('role',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRole'),
                    'params' => array('{{role}}')
                )
            )
        );
        
		if ($userId) $grid->setClassRowCondition("'{{status}}' != ''", "selected");  

		
		$grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'subid');
        $grid->updateColumn('fixType', array('hidden' => true));
		
/*        $grid->addSubMassActionSelect(array(
            $this->view->url(
                array('action' => 'assign')
            )
        ),
            'role',
            $roles);*/
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();


    }


    /**
     * Экшн для присваивания ответственностей
     */
    public function doAction() 
    {
		$userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $studentService	= $this->getService('Student');
        $subjectService	= $this->getService('Subject');
        $userService	= $this->getService('User');
        
        $messages = array(
			'not_found'	=> array(),
        	// 'already'	=> array(),
        	'expired'	=> array(),
        	'success'	=> array(),
        );
        
        foreach ($ids as $value){
        	$subject = $this->getOne($subjectService->find($value));
        	
        	if(!$subject){ // Курс не найден
        		$messages['not_found'][] = $subject->getName();
        		continue;
        	}
			
			if($subject->isExpired()) {  // Истёк срок действия курса        	
				$messages['expired'][] = $subject->getName();
				continue;
			}

			if($studentService->isUserExists($value, $userId)){ // Пользователь уже назначен на этот курс
				// $messages['already'][] = $subject->getName();
 				continue;
			}

			if($this->getService('Dean')->isSubjectResponsibility($userService->getCurrentUserId(), $value)){
				$subjectService->assignUser($value, $userId);
				$messages['success'][] = $subject->getName();
			}
        }
        
		if(!empty($messages['not_found'])) $this->_flashMessenger->addMessage(array(
			'type' => HM_Notification_NotificationModel::TYPE_ERROR,
			'message' => sprintf(_(self::MSG_COURSE_NOT_FOUND), implode(', ', $messages['not_found']))
		));
        
		/*if(!empty($messages['already'])) $this->_flashMessenger->addMessage(array(
			'type' => HM_Notification_NotificationModel::TYPE_ERROR,
 			'message' => sprintf(_(self::MSG_SOMEBODY_ALREADY_ASSIGNED), implode(', ', $messages['already']))
		));*/
        
        if(!empty($messages['expired'])) $this->_flashMessenger->addMessage(array(
        	'type' => HM_Notification_NotificationModel::TYPE_ERROR,
        	'message' => sprintf(_(self::MSG_SOME_COURSE_EXPIRED), implode(', ', $messages['expired']))
        ));
        
		if(!empty($messages['success'])) $this->_flashMessenger->addMessage(array(
        	'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
        	'message' => sprintf(_(self::MSG_COURSE_SUCCESS_ASSIGNED), implode(', ', $messages['success']))
		));
        
        $this->_redirector->gotoSimple('assign', 'student', 'user', array('user_id' => $userId));
    }
    
    /**
     * Экшн для удаления ответственностей 
     */
    public function deleteAction() {
		$userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
       $service = $this->getService('Student');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            if($this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $value)){
                $res = $service->deleteBy(
                    array(
                    	'MID = ?' => $userId, 
                        'CID = ?' => $value
                    )
                );
            }
        }

        if ($error === true) {
            $this->_flashMessenger->addMessage(_('На некоторых курсах пользователь не был слушателем'));
        } else {
            $this->_flashMessenger->addMessage(_('Курсы успешно удалены'));
        }
        $this->_redirector->gotoSimple('assign', 'student', 'user', array('user_id' => $userId));

    }



    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
    	$userId = $this->_getParam('user_id', 0);
    	//pr($field);
        if ($field == $userId) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateName($name, $subjectId) {

        return '<a href="' .
                $this->view->url(
                    array('module' => 'subject',
                        'controller' => 'index',
                        'action' => 'index',
                        'subject_id' => $subjectId
                    )
                ) .
                '">' . $name . '</a>';


    }	
    
}

