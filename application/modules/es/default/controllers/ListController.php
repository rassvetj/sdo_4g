<?php

class ListController extends HM_Controller_Action {
    
    protected $_gridName = 'esMessages';
	
	public function indexAction() {
        
    }
	
	
	
	public function getDefaultSelect() {
		$userService = $this->getService('User');
		$aclService  = $this->getService('Acl');
		$filter 	 = $this->getService('ESFactory')->newFilter();
		$user 		 = $userService->getCurrentUser();
		$userRole 	 = $userService->getCurrentUserRole();
		
		if ($aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TEACHER) || $aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TUTOR)){            
            $filter->setExcludeEventTypes(array(
                'courseAddMaterial',        // Добавление материала в курс
                'courseAttachLesson',       // Назначение занятия студенту
                'courseScoreTriggered',     // Выставление оценки за курс
                'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                'courseTaskScoreTriggered', // Выставление оценки за занятие
				'motivationMessage'         // мотивированное заключение. Запись юолжна быть в БД				
            ));
        } elseif ($aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_STUDENT)) {            
            $filter->setExcludeEventTypes(array(
                'courseTaskAction',          // Выполнение задания студентом				
            ));
        } else {            
            $filter->setExcludeEventTypes(array(
                'courseAddMaterial',        // Добавление материала в курс
                'courseAttachLesson',       // Назначение занятия студенту
                'courseScoreTriggered',     // Выставление оценки за курс
                'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                'courseTaskScoreTriggered', // Выставление оценки за занятие
                'courseTaskAction',         // Выполнение задания студентом
                'motivationMessage'         // мотивированное заключение
            ));
        }
		
		# нужно ли ограничение по типам?
		$filter->setTypes(array(
			'forumAddMessage','blogAddMessage','forumInternalAddMessage','wikiAddPage','wikiModifyPage','blogInternalAddMessage','wikiInternalAddPage','wikiInternalModifyPage','courseAddMaterial','courseAttachLesson',
			'courseScoreTriggered','courseTaskComplete','courseTaskAction','commentAdd','commentInternalAdd','courseTaskScoreTriggered', 'personalMessageSend','motivationMessage','courseAddMessage'
		));
		
		$filterTypes	= $filter->getTypes();
		$excludeTypes	= $filter->getExcludeEventTypes();
		
		$select = $this->getService('User')->getSelect();
		$select->from(
            array('ev' => 'es_events'),
            array(				
				'event_id' 				=> 'ev.event_id',
				#'event_type_id' 		=> 'ev.event_type_id',
				#'event_trigger_id' 	=> 'ev.event_trigger_id',
				#'event_group_id' 		=> 'ev.event_group_id',
				'description' 			=>  new Zend_Db_Expr('CAST(ev.description AS nvarchar(MAX))'),
				'create_time' 			=>  new Zend_Db_Expr('CAST(ev.create_time AS int)'),
				'url'		 			=>  new Zend_Db_Expr('CAST(ev.description AS nvarchar(MAX))'),
				'type_str' 				=> 'evt.name',				
				#'egroup_type'			=> 'tg.name',
				#'egroup_id' 			=> 'tg.event_group_type_id',
				#'trigger_instance_id'	=> 'evg.trigger_instance_id',
				#'group_data' 			=> 'evg.data',
				#'group_type' 			=> 'evg.type',
				'views'		 			=> 'evu.views',
        ));	
		
		$select->join(array('evu' => 'es_event_users'), 'ev.event_id = evu.event_id', array()); 		
		$select->join(array('evt' => 'es_event_types'), 'ev.event_type_id = evt.event_type_id', array());		
		$select->join(array('tg' => 'es_event_group_types'), 'evt.event_group_type_id = tg.event_group_type_id', array());		
		$select->joinLeft(array('evg' => 'es_event_groups'), 'evg.event_group_id = ev.event_group_id', array());
		
		$select->where('evu.user_id = ?', intval($user->MID));
		
		if (!empty($filterTypes)) {		
			$select->where($this->quoteInto('evt.name IN (?)', $filterTypes));
		}
		
		if (!empty($excludeTypes)) {			
			$select->where($this->quoteInto('evt.name NOT IN (?)', $excludeTypes));
		}
		$select->order('create_time DESC');
		
		return $select;
	}
	
	
	public function getDefaultGrid($select) {
		$gridId = 'esMessages';
		
		$grid = $this->getGrid(
			$select,
			array(                               
                'event_id' 				=> array('hidden' => true),
                'event_type_id' 		=> array('hidden' => true),
                'event_trigger_id' 		=> array('hidden' => true),
                'event_group_id' 		=> array('hidden' => true),
				'egroup_type'	 		=> array('hidden' => true),
				'egroup_id' 			=> array('hidden' => true),
				'trigger_instance_id'	=> array('hidden' => true),				
				'group_data' 			=> array('hidden' => true),
				'group_type' 			=> array('hidden' => true),				
                'description' 			=> array('title' => _('Сообщение')),
				'create_time' 			=> array('title' => _('Дата создания')),				
                'type_str' 				=> array('title' => _('Тип сообщения')),
				'views' 				=> array('title' => _('Просмотрено')),				
				'url' 					=> array('title' => _('Ссылка')),				
            ),
            array(
				'create_time'	=>					
					array(												
						'callback' => array(
							'function'	=> array($this, 'dateFilter'),
							'params'	=> array(),								
						),
				),
				'type_str'		=> array('values' => Es_Entity_EventType::getTypeDescriptionsShort()),		                                                 
				'views'			=> array('values' => array(	0 => _('Нет'), 1 => _('Да')	)),				
            ), 
			$this->_gridName
		);
		
		$grid->updateColumn('create_time', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{create_time}}')
            )
        ));
		
		$grid->updateColumn('views', array(           
            'callback' => array(
                'function' => array($this, 'updateViews'),
                'params' => array('{{views}}')
            )
        ));
		
		$grid->updateColumn('description', array(           
            'callback' => array(
                'function' => array($this, 'updateDescription'),
                'params' => array('{{description}}', '{{type_str}}')
            )
        ));
		
		$grid->updateColumn('url', array(           
            'callback' => array(
                'function' => array($this, 'updateUrl'),
                'params' => array('{{url}}', '{{type_str}}')
            )
        ));
		
		# должен быть после description и url
		$grid->updateColumn('type_str', array(           
            'callback' => array(
                'function' => array($this, 'updateTypeMessage'),
                'params' => array('{{type_str}}')
            )
        ));
		
		return $grid;
	}
	
	
	/**
	 * все активные
	*/
	public function currentAction() {

		$select = $this->getDefaultSelect();			
		$select->where('(evu.hidden = ? OR evu.hidden IS NULL)', 0);	
		$grid = $this->getDefaultGrid($select);
		
		$grid->addMassAction(
			array('module' => 'default', 'controller' => 'list', 'action' => 'to-trash'),
            _('Удалить'),
			_('Вы уверены, что хотиле удалить отмеченные уведомления?')
		);
		
		$grid->addMassAction(
			array('module' => 'default', 'controller' => 'list', 'action' => 'set-views'),
            _('Отметить, как прочитанное'),
			_('Вы уверены, что хотиле отметить сообщения как "прочитанное"?')
		);		
		
		try {
			$this->view->isGridAjaxRequest = $this->isAjaxRequest();
			$this->view->grid 			   = $grid->deploy();
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			
		}
    }
	
	/**	
	 * все удаленные
	*/
	public function deletedAction() {
        $select = $this->getDefaultSelect();			
		$select->where('evu.hidden = ?', 1);	
		$grid = $this->getDefaultGrid($select);
		
		$grid->addMassAction(
			array('module' => 'default', 'controller' => 'list', 'action' => 'restore'),
            _('Восстановить'),
			_('Вы уверены, что хотиле восстановить отмеченные уведомления?')
		);
		
		try {
			$this->view->isGridAjaxRequest = $this->isAjaxRequest();
			$this->view->grid 			   = $grid->deploy();
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			
		}
    }
	
	/**
	 * Помечает уведомление, как удаленное
	*/
	public function toTrashAction() {
		$this->getHelper('viewRenderer')->setNoRender();	
		$this->changeEventUser(array('hidden' => 1));
		$this->_redirector->gotoSimple('current', 'list', 'default');		
	}
	
	/**
	 * перевод записи из корзины в раздел с обычным отображением
	*/
	public function restoreAction() {
		$this->getHelper('viewRenderer')->setNoRender();
		$this->changeEventUser(array('hidden' => 0));
		$this->_redirector->gotoSimple('deleted', 'list', 'default');		
	}
	
	
	/**
	 * устанавливает статус "просмотрено"
	*/
	public function setViewsAction() {
		$this->getHelper('viewRenderer')->setNoRender();				
		$this->changeEventUser(array('views' => 1));
		$this->_redirector->gotoSimple('current', 'list', 'default');				
	}
	
	
	protected function changeEventUser($data){				
		$eventIDs = $this->getPostEventIDs();
		if(empty($data)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось изменить данные: не заданы параметры'))
			);
		} elseif(empty($eventIDs)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не выбрано ни одной записи'))
			);	
		} else {			
			$user_id 	= $this->getService('User')->getCurrentUserId();
			$adapter 	= Zend_Db_Table::getDefaultAdapter();				
			$where 		= $adapter->quoteInto(' user_id = ? ', $user_id);
			$where 		= $where.' AND '.$adapter->quoteInto(' event_id IN (?) ', $eventIDs);						
			$isUpdate 	= $adapter->update('es_event_users', $data, $where);			
			if($isUpdate){
				$this->_flashMessenger->addMessage(_('Данные успешно изменены'));				
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не удалось изменить данные'))
				);
			}
		}		
	}
	
	
	protected function getPostEventIDs(){		
		$postMassIds = $this->_getParam('postMassIds_'.$this->_gridName);		
		$postMassIds = explode(',', trim($postMassIds));		
		return array_filter($postMassIds);		
	}
	
	public function updateDescription($json, $type){
		$info = $this->getDescriptionInfo($json, $type);
		return 	$info['description'];
	}	
	
	public function updateDate($date){
        if (!strtotime($date)) return '';
        return $date;
    }
	
	public function updateViews($date){
        if($date == 1)	{	return _('Да');  }
		else			{	return _('Нет'); }		
    }
	
	
	public function dateFilter($data){		
		try {
			$value  = strtotime(trim($data['value']));
			$select = $data['select'];
			
			if(empty($value)){
				$select->where('1=0');
				return;
			}
			
			$value = date('d.m.Y', $value);
			
			$dt = DateTime::createFromFormat('d.m.Y', $value);
			
			$dt->setTime(0, 0, 0);
			$dtBegin = $dt->getTimestamp();
			
			$dt->setTime(23, 59, 59);			
			$dtEnd = $dt->getTimestamp();
			
			if($dt)	{ $select->where($this->quoteInto(array('ev.create_time >= ? AND ', 'ev.create_time <= ?'), array($dtBegin, $dtEnd))); }
			else	{ $select->where('1=0'); }
		} catch (Exception $e) {
			$select->where('1=0');		
		}			
	}

	public function updateTypeMessage($type){
		$descriptions = Es_Entity_EventType::getTypeDescriptionsShort();
		if(isset($descriptions[$type])) { return $descriptions[$type]; }
		return '';		
	}
	
	
	public function updateUrl($json, $type){
		$info = $this->getDescriptionInfo($json, $type);		
		return 	$info['url'];
	}
	
	
	/**
	 * разбирает данные уведомления и формирует элемены для вывода
	 * @return array
	*/
	public function getDescriptionInfo($json, $type){
		$data = json_decode($json, true);
		
		$result = array();
		if(empty($data) || empty($type)){ return $result; }
		
		$author			= ($data['author']) ? $data['author'] : $data['user_name'];
		$lesson_name 	= ($data['lesson_name']) ? ($data['lesson_name']) : ($data['lesson_title']);
		$lesson_name 	= (empty($lesson_name)) ? ($data['title']) : ($lesson_name);		
		
		$author = '<span class="hm-es-event-list-item-author">'.$author.'</span>';
		$course = '<span class="hm-es-event-list-item-author">&laquo;'.$data['course_name'].'&raquo;</span>';
		$lesson = '<span class="hm-es-event-list-item-author">&laquo;'.$lesson_name.'&raquo;</span>';
		
		$title  = ($data['title']) ? ('<span class="hm-es-event-list-item-author">&laquo;'.$data['title'].'&raquo;</span>') : ('');
	
		switch ($type) {
			case 'forumAddMessage':	
				$urlParams	= array('module' => 'forum', 'controller' => $data['forum_id'], 'action' => $data['section_id'], 'baseUrl' => '');
				$msg 		= '<p>'.$author.' добавил(а) сообщение '.$title.':</p><p>'.strip_tags($data['text']).'</p>';
				break;
			case 'blogAddMessage':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');				
				$msg 		= $author.' добавил(а) запись';
				break;
			case 'wikiAddPage':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');				
				$msg 		= $author.' добавил(а) страницу';
				break;
			case 'wikiModifyPage':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');				
				$msg 		= $author.' изменил(а) страницу';
				break;
			case 'forumInternalAddMessage':
				$urlParams	= array('module' => 'forum', 'controller' => 'subject', 'action' => 'subject', $data['course_id'] => $data['section_id'], 'baseUrl' => '');
				$msg 		= '<p>'.$author.' добавил(а) сообщение '.$title.':</p><p>'.strip_tags($data['text']).'</p>';
				break;
			case 'blogInternalAddMessage':
				$urlParams	= array('module' => 'blog', 'controller' => 'index', 'action' => 'index', 'subject_id' => $data['course_id'], 'subject' => 'subject', 'baseUrl' => '');				
				$msg 		= '<p>'.$author.' добавил(а) запись: '.$title.'</p><p>'.strip_tags($data['body']).'</p>';
				break;
			case 'wikiInternalAddPage':
				$urlParams	= array('module' => 'wiki', 'controller' => 'index', 'action' => 'view', 'id' => $data['subjectId'], 'subject_id' => $data['course_id'], 'subject' => 'subject', 'baseUrl' => '');
				$msg 		= $author.' создал(а) страницу: '.$title;
				break;
			case 'wikiInternalModifyPage':
				$urlParams	= array('module' => 'wiki', 'controller' => 'index', 'action' => 'view', 'id' => $data['subjectId'], 'subject_id' => $data['course_id'], 'subject' => 'subject', 'baseUrl' => '');
				$msg 		= $author.' изменил(а) страницу '.$title;
				break;
			case 'courseAddMaterial':
				$urlParams	= array('module' => 'subject', 'controller' => 'materials', 'action' => 'index', 'subject_id' => $data['course_id'], 'baseUrl' => '');				
				$msg 		= $author.' добавил(а) новый материал '.$title;
				break;
			case 'courseAttachLesson':				
				$urlParams	= array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $data['course_id'], 'baseUrl' => '');
				$msg		= 'Вам назначено новое занятие '.$lesson;
				break;
			case 'courseScoreTriggered':
				$urlParams	= array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $data['course_id'], 'baseUrl' => '');
				$msg		= 'Вам выставлена итоговая оценка в курсе '.$course;					
				break;
			case 'courseTaskAction':
				$urlParams	= array('module' => 'interview', 'controller' => 'index', 'action' => 'index', 'subject_id' => $data['course_id'], 'lesson_id' => $data['lesson_id'], 'user_id' => $data['user_id'], 'baseUrl' => '');
				$msg		= '<p>'.$author.' выполнил(-а) новое действие в задании '.$lesson.'</p><p>'.strip_tags($data['message']).'</p>';					
				break;
			case 'commentAdd':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');
				$msg		= $author.' добавил(а) новый комментарий';
				break;
			case 'commentInternalAdd':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');
				$msg		= $author.' добавил(а) новый комментарий';
				break;
			case 'courseTaskScoreTriggered':
				$urlParams	= array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $data['course_id'], 'baseUrl' => '');
				$msg		= 'В занятии '.$lesson.' Вам была поставлена оценка';
				break;
			case 'personalMessageSend':
				$urlParams	= array('module' => 'message', 'controller' => 'view', 'action' => 'index', 'from' => $data['author_id'], 'baseUrl' => '');
				$msg		= '<p>'.$author.' Вам написал(а): </p><p>'.strip_tags($data['message']).'</p>';				
				break;
			case 'motivationMessage':
				#$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => '', 'user_id' => '', 'baseUrl' => '');
				$msg		= 'У Вас новое мотивированное заключение по курсу '.$course;
				break;
			case 'courseAddMessage':
				$urlParams	= array('module' => 'subject', 'controller' => 'interview', 'action' => 'index', 'subject_id' => $data['subjectId'], 'user_id' => $data['user_id'], 'baseUrl' => '');
				$msg 		= '<p>'.$author.' написал(а) Вам сообщение в курсе'.$course.': </p><p>'.$data['message'].'</p>';
				break;
		}
		$result['url']			= (empty($urlParams)) ? ('') : ('<a href="'.$this->view->url($urlParams, 'default', true).'" target="_blank">Перейти</a>');
		$result['description']	= $msg;
		
		return $result;
	}
}

?>
