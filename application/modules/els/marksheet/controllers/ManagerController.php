<?php
class Marksheet_ManagerController extends HM_Controller_Action
{
	
	protected $groupList					= array(); # Список групп. id группы => название группы
	protected $_groupUsersList				= array(); # Список групп студентов id студента => array(id группы => название группы)
	protected $_groupAvailableStudentList	= array(); # Список групп студентов доступных тьютору, которые переведены в прошедшее обучение. key => subject_id~tutor_id, values array(id группы => название группы)
    protected $programms 					= array();
    protected $_graduatedStudentList		= array(); # Все назначенные на сессию студенты, которые переведены в прошедшее обучение
	protected $_availableStudentList		= array(); # Все назначенные на сессию студенты, доступные конкретному тьютору. id сессии => id тьютора => array of id студентов
    protected $_userList					= array(); # КЭШ Список пользователей вида id => ФИО
	
	
	public function indexAction()
    {
		if(!$this->serviceSubject) 	{ $this->serviceSubject 	= $this->getService('Subject');		}
		if(!$this->programmService) { $this->programmService	= $this->getService('Programm');	}
		if(!$this->serviceGroup) 	{ $this->serviceGroup 		= $this->getService('StudyGroup'); 	}
		
		$this->view->setHeader(_('Возврат студентов из "прошедших обучение" по ведомости'));
		
		$isGridAjaxRequest = $this->isGridAjaxRequest();
		
		if(!$isGridAjaxRequest){
			#$this->userService->clearCache();
			#$this->groupService->clearCache();
			$this->programmService->clearCache();
		}
		
		$this->programms 	= $this->programmService->getSubjectProgrammList(); //--получаем список программ.
		
		$select = $this->serviceSubject->getSelect();
		$select->from(array('fm' => 'files_marksheet'),
			array(
				'subid' 				=> 's.subid',
				'subject_name' 			=> 's.name',
				'subject_external_id' 	=> 's.external_id',
				'subject_begin' 		=> 's.begin',
				'subject_end' 			=> 's.end',				
				'subject_debt' 			=> new Zend_Db_Expr("CASE WHEN s.time_ended_debt_2 IS NOT NULL AND s.time_ended_debt_2 != '' THEN s.time_ended_debt_2 ELSE s.time_ended_debt END"),
				
				'subject_exam_type'		=> 's.exam_type',
				'isDO'  				=> new Zend_Db_Expr('CASE WHEN s.isDO IS NULL THEN 0 ELSE s.isDO END'),
				'program' 				=> 's.subid',
				'groups' 				=> 's.subid',
				'students' 				=> 's.subid',
				'marksheet_external_id'	=> 'fm.marksheet_external_id',
				'marksheet_date_issue'	=> 's.subid',
				'marksheet_type'		=> 
						new Zend_Db_Expr("
							CASE 
								WHEN fm.student_id IS NOT NULL AND fm.student_id != ''  THEN ".HM_Marksheet_MarksheetModel::TYPE_IN."  
								WHEN fm.group_id IS NOT NULL AND fm.group_id != '' 		THEN ".HM_Marksheet_MarksheetModel::TYPE_GROUP."
								ELSE ".HM_Marksheet_MarksheetModel::TYPE_SUBJECT."
							END
						"),
				
				
				
				'file_group_id' 		=> 'fm.group_id',
				'file_student_id' 		=> 'fm.student_id',
				
				'file_name' 			=> 'fm.name',
				'file_id' 				=> 'fm.file_id',
				'file_author_id'		=> 'fm.author_id',
			)
		);
		
		$select->join(array('s' => 'subjects'), 'fm.subject_id = s.subid', array());
		
		$select->where('fm.author_id > 0');
		
		$grid = $this->getGrid($select, array(            
			'subid'    				=> array('hidden' => true),            
            'subject_name' 			=> array('title' => _('Название сессии')),
			'subject_external_id' 	=> array('title' => _('ID (из 1С)')),
			'subject_begin' 		=> array('title' => _('Дата начала')),
			'subject_end' 			=> array('title' => _('Дата окончания')),
			'subject_debt' 			=> array('title' => _('Дата продления')),
			
			'subject_exam_type' => array(
                'title' 	=> _('Контроль'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateExamType'),
                    'params' 	=> array('{{subject_exam_type}}')
                )
            ),	
			
			'isDO' => array(
                'title' 	=> _('ДО/неДО'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateIsDO'),
                    'params' 	=> array('{{isDO}}')
                )
            ),
			
			'program' => array(
                'title' 	=> _('Программа'),
                'callback' 	=> array(
                    'function'	=> array($this, 'updateProgramm'),
                    'params' 	=> array('{{program}}')
                )
            ),
			
			
			'groups' => array(
                'title' 	=> _('Группа'),
                'callback' 	=> array(
                    'function'	=> array($this, 'updateGroups'),
                    'params' 	=> array('{{groups}}', '{{file_author_id}}', '{{file_group_id}}', '{{file_student_id}}')
                )
            ),
			
			'students' => array(
                'title' 	=> _('Кол слуш'),
                'callback' 	=> array(
                    'function'	=> array($this, 'updateStudents'),
                    'params' 	=> array('{{students}}', '{{file_author_id}}', '{{file_group_id}}', '{{file_student_id}}')
                )
            ),
			
			'marksheet_external_id'	=> array('title' => _('Номер ведомости')),
			
			
			'marksheet_date_issue' => array(                
				'title' 	=> _('Дата ведомости'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateMarksheetDateIssue'),
                    'params' 	=> array('{{marksheet_date_issue}}', '{{file_author_id}}', '{{file_group_id}}', '{{file_student_id}}', '{{marksheet_external_id}}')
                )
            ),
			
			'marksheet_type' => array(
                'title' 	=> _('Тип'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateMarksheetType'),
                    'params' 	=> array('{{marksheet_type}}')
                )
            ),
			
			
			'file_group_id'		=> array('hidden' => true), 
			'file_student_id'   => array('hidden' => true), 
			'file_id'			=> array('hidden' => true),     
			
			'file_name' => array(
                'title' 	=> _('Ведомость'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateMarksheetFile'),
                    'params' 	=> array('{{file_name}}', '{{file_id}}', '{{file_author_id}}')
                )
            ),
			

			'file_author_id' => array(
                'title' 	=> _('Кто сформировал'),
                'callback' 	=> array(
                    'function' 	=> array($this, 'updateAuthor'),
                    'params' 	=> array('{{file_author_id}}')
                )
            ),
			
        ),
            array(
                'subid' 				=> null,
                'subject_external_id' 	=> null,
                'subject_name' 			=> null,
				'subject_begin' 		=> array('render' => 'DateSmart'),
				'subject_end' 			=> array('render' => 'DateSmart'),
				'subject_debt' 			=> array('render' => 'DateSmart'),
                'file_id' 				=> null,
                'file_name' 			=> null,
                
				'file_author_id' =>
					array(						
						'callback' => array(
							'function'	=> array($this, 'fileAuthorFilter'),
							'params'	=> array()
					)
				),
				
				'subject_exam_type'		=> array('values' => HM_Subject_SubjectModel::getExamTypes()),
				'isDO' 					=> array('values' => HM_Subject_SubjectModel::getFacultys()),
				
				'program' =>
					array(						
						'callback' => array(
							'function'	=> array($this, 'programmFilter'),
							'params'	=> array()
					)
				),
				
				'groups' =>
					array(						
						'callback' => array(
							'function'	=> array($this, 'groupFilter'),
							'params'	=> array()
					)
				),
				
				'marksheet_external_id'	=> null,
				
				'marksheet_date_issue' =>
					array(							
						'callback'	=> array(
							'function'	=> array($this, 'marksheetDateFilter'),
							'params'	=> array()
						),
						'render'	=> 'MarksheetDate',
				),
				
				'marksheet_type'		=> array('values' => HM_Marksheet_MarksheetModel::getTypes()),
				
			)
        );
		
		
		$grid->updateColumn('subject_begin', array(
            'format'	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
			'callback'	=> array(
				'function'	=> array($this, 'updateDate'),
                'params'	=> array('{{subject_begin}}')
            )
        ));
		
		$grid->updateColumn('subject_end', array(
            'format'	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
			'callback'	=> array(
				'function'	=> array($this, 'updateDate'),
                'params'	=> array('{{subject_end}}')
            )
        ));
		
		$grid->updateColumn('subject_debt', array(
            'format'	=> array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
			'callback'	=> array(
				'function'	=> array($this, 'updateDate'),
                'params'	=> array('{{subject_debt}}')
            )
        ));
		
		
		
		
		$grid->addAction(array(
                'module'     => 'marksheet',
                'controller' => 'manager',
                'action'     => 'to-active',
            ),
            array('file_id'),
            _('Восстановить'),
            _('Вы действительно желаете вернуть студентов из "прошедших обучение" в активное?')
        );
		
		
		
		$grid->addMassAction(array('module' 	=> 'marksheet',
                                   'controller' => 'manager',
                                   'action' 	=> 'to-active-mass',
                                  ),
                             _('Восстановить все выбранные'),
                             _('Вы действительно желаете вернуть студентов из "прошедших обучение" в активное для всех выбранных строк?'));
		
		
		
		try {
			$this->view->grid = $grid->deploy();
			
		} catch (Exception $e) {
			echo 'Ошибка: ',  $e->getMessage(), "\n";
		}
		
		
		$this->view->gridAjaxRequest = $isGridAjaxRequest;        
	}
	
	
	/**
	 * Возвращает студентов из прошедших обучение в текущее по сформированной ведомостИ (одной).
	 * Сам файл ведомости не удаляется, он открепляется от тьютора.
	*/
	public function toActiveAction()
    {
		if(!$this->getService('User')->getCurrentUserId()){
			$this->_flashMessenger->addMessage(_('Необходимо авторизоваться'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$file_id	 = (int) $this->_getParam('file_id', 0);		
		
		$isActivated = $this->toActive($file_id);
		
		if(!$isActivated){
			$this->_redirector->gotoSimple('index', 'index', 'default');
			die;			
		}
		
		$this->_redirector->gotoSimple('index', 'manager', 'marksheet');
		die;
	}
	
	/**
	 * Возвращает студентов из прошедших обучение в текущее по сформированным ведомостям. Может быть выбрано несколько ведомостей сразу.
	 * Сам файл ведомости не удаляется, он открепляется от тьютора.
	*/
	public function toActiveMassAction()
    {
		$postMassIds = $this->_getParam('postMassIds_grid', '');
		if(empty($postMassIds)){
			$this->_flashMessenger->addMessage(_('Не выбран ни один элемент'));	
			$this->_redirector->gotoSimple('index', 'manager', 'marksheet');
			die;
		}
		
        $ids = explode(',', $postMassIds);
        if (empty($ids)){
			$this->_flashMessenger->addMessage(_('Не выбран ни один элемент'));	
			$this->_redirector->gotoSimple('index', 'manager', 'marksheet');
			die;
		}
		
		foreach($ids as $file_id){
			$isActivated = $this->toActive($file_id);	
			if(!$isActivated){
				$this->_redirector->gotoSimple('index', 'index', 'default');
				die;			
			}			
		}
		
		$this->_redirector->gotoSimple('index', 'manager', 'marksheet');
		die;
	}
	
	
	/**
	 * Возвращаем студентов обратно на сессию по сформированной ведомости.
	*/
	public function toActive($file_id){
		$file 		= $this->getService('FilesMarksheet')->getById($file_id);
		
		if(empty($file)){
			$this->_flashMessenger->addMessage(_('Файл №'.$file_id.' не найден'));			
			return false;
		}
		
		if(empty($file->subject_id)){
			$this->_flashMessenger->addMessage(_('Не определена сессия для файла №'.$file_id));			
			return false;
		}
		
		$subject = $this->getService('Subject')->getById($file->subject_id);
		if(empty($subject)){
			$this->_flashMessenger->addMessage(_('Не найдена сессия №'.$file->subject_id));			
			return true;			
		}
		$subject_name = $subject->name;
		
		
		# Это ИН.
		# Возвращаем только для указанного студента
		if(!empty($file->student_id)){			
			$this->getService('Subject')->assignStudent($file->subject_id, $file->student_id, true);
			# TODO Обновляем файл ведомости			
			$isUnAttachAuthor 	= $this->getService('FilesMarksheet')->unAttachAuthor($file_id);
			$fio 				= $this->getUserName($file->student_id);
			$this->_flashMessenger->addMessage(_('Студент "'.$fio.'" назначен на сессию "'.$subject_name.'"'));			
			return true;
		}
		
		# Все студенты группы, доступные указанному тьютору.
		if(!empty($file->group_id)){			
			$students_available		= $this->getAvailableStudents($file->author_id, $file->subject_id);
			if(empty($students_available)){
				$fio 				= $this->getUserName($file->author_id);
				$group_name 		= $this->getGroupById($file->group_id);
				$this->_flashMessenger->addMessage(_('У тьютора "'.$fio.'" нет доступных студентов в группе "'.$group_name.'", сессии "'.$subject_name.'"'));				
				return true;
			}
			
			$students_group			= $this->getService('StudyGroup')->getUsers($file->group_id);			
			$students_need_assign	= array_intersect($students_available, $students_group);
			
			if(empty($students_need_assign)){
				$this->_flashMessenger->addMessage(_('Нет доступных студентов для повторного назначения на сессию "'.$subject_name.'"'));				
				return true;
			}
			
			foreach($students_need_assign as $student_id){
				$this->getService('Subject')->assignStudent($file->subject_id, $student_id, true);
			}
			# TODO Обновляем файл ведомости
			$isUnAttachAuthor = $this->getService('FilesMarksheet')->unAttachAuthor($file_id);
			
			$group_name 		= $this->getGroupById($file->group_id);
			$this->_flashMessenger->addMessage(_('Студенты группы "'.$group_name.'" ('.count($students_need_assign).' человек(а)) назначены на сессию "'.$subject_name.'"'));			
			return true;
		}
		
		# Возвращаем всех студентов на сессии из прошедших.		
		$students_need_assign		= $this->getAvailableStudents($file->author_id, $file->subject_id);
		if(empty($students_need_assign)){
			$fio 				= $this->getUserName($file->author_id);
			$this->_flashMessenger->addMessage(_('У тьютора "'.$fio.'" нет доступных студентов в сессии "'.$subject_name.'"'));			
			return true;
		}
		
		foreach($students_need_assign as $student_id){
			$this->getService('Subject')->assignStudent($file->subject_id, $student_id, true);
		}
		# TODO Обновляем файл ведомости
		$isUnAttachAuthor = $this->getService('FilesMarksheet')->unAttachAuthor($file_id);
		
		$this->_flashMessenger->addMessage(_('Все студенты ('.count($students_need_assign).' человек(а)) назначены на сессию "'.$subject_name.'"'));		
		return true;
	}
	
	
	
	public function updateDate($date){        
		return $date;    
    }
	
	public function updateExamType($examType){
        $examTypes = HM_Subject_SubjectModel::getExamTypes();
        return $examTypes[$examType];
    }
	
	public function updateIsDO($type){
		$facultList = HM_Subject_SubjectModel::getFacultys();
		return $facultList[$type];		
	}
	
	public function updateMarksheetType($type){
		$typeList = HM_Marksheet_MarksheetModel::getTypes();
		return $typeList[$type];		
	}
	
	
	
	public function updateProgramm($subject_id){
		if(!$subject_id)						{	return _('Нет');						}
		if(isset($this->programms[$subject_id])){	return $this->programms[$subject_id];	}
		return _('Нет');
	}
	
	# фильтр по полю "Кто сформировал"
	public function fileAuthorFilter($data){
		try {
			$value	= trim($data['value']);
			$select	= $data['select'];
			if(!$this->userService) { $this->userService = $this->getService('User'); }
			
			$user_IDs = $this->userService->getUserIDsByName($value); # массив id пользователей
			
			if(count($user_IDs)){																	
				$select->where($this->quoteInto('fm.author_id IN (?)', $user_IDs));						
			} else {
				$select->where('1=0');		
			}
			
		} catch (Exception $e) { }
	}
	
	# фильтр Дата ведомости
	public function marksheetDateFilter(){
		try {
			$value	= trim($data['value']);
			$select	= $data['select'];
			echo 1;
			#die;
			
			
		} catch (Exception $e) { }
	}
	
	# фильтр по полю "Группа"
	# Могут быть промахи, т.к цепочка такая:
	# группа -> назначенные студенты на группу -> сессии, на которые назначены студенты -> отбор по сессиям, которые есть ведомость в files_marksheet без учета автора
	public function groupFilter($data){
		try {
			$value	= trim($data['value']);
			$select	= $data['select'];		
			# все сессии студентов, назначенных на указанные группы.
			if(!$this->serviceGroup){ $this->serviceGroup = $this->getService('StudyGroup'); }
			$group_IDs = $this->serviceGroup->getGroupIDsByName($value);
			if(empty($group_IDs)){
				$select->where('1=0');	
				return;
			}
			
			if(!$this->serviceGroupUsers){ $this->serviceGroupUsers = $this->getService('StudyGroupUsers'); }
			$user_IDs = $this->serviceGroupUsers->fetchAll($this->serviceGroupUsers->quoteInto('group_id IN (?)',$group_IDs))->getList('user_id');
			
			if(empty($user_IDs)){
				$select->where('1=0');	
				return;
			}
			
			$subUsers = $this->serviceGroup->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
							
			$subGrad = $this->serviceGroup->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
							
			$subUSelect = $this->serviceGroup->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
							
			$select_subject = $this->serviceGroup->getSelect();			
			$select_subject->from(array('s' => $subUSelect), array('subject_id' => 's.CID'));
			$select_subject->join(array('f' => 'files_marksheet'), 'f.subject_id = s.CID', array());
			$select_subject->where($this->serviceGroup->quoteInto('s.MID IN (?)', $user_IDs));
			$select_subject->group(array('s.CID'));		
			$res = $select_subject->query()->fetchAll();
			
			if(empty($res)){
				$select->where('1=0');	
				return;
			}
			
			$subject_IDs = array();
			foreach($res as $i){
				$subject_IDs[$i['subject_id']] = $i['subject_id'];
			}
			
			$select->where($this->quoteInto('s.subid IN (?)', $subject_IDs));
			
		} catch (Exception $e) { }		
	}
	
	
	public function programmFilter($data){				
		try {
			$value=trim($data['value']);
			$select=$data['select'];
			
			if(!$this->userService) { $this->userService = $this->getService('User'); }
			$programmSelect = $this->userService->getSelect();
			$programmSelect->from(
				array('p' => 'programm'),						
				array(				
					'CID' => 'pe.item_id',				
				)
			);	
			$programmSelect->join(array('pe' => 'programm_events'), 'pe.programm_id = p.programm_id', array());
			$programmSelect->where('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT);
			$programmSelect->where($this->quoteInto("p.name LIKE LOWER(?)", '%'.$value.'%'));
			$programmSelect->group(array('pe.item_id'));			
			$res = $programmSelect->query()->fetchAll();			
			$subjectIDs = array();		
			if(count($res)){
				foreach($res as $u){
					$subjectIDs[$u['CID']] = $u['CID'];
				}
			}			
			if(count($subjectIDs)){																	
				$select->where($this->quoteInto('s.subid IN (?)', $subjectIDs));						
			} else {
				$select->where('1=0');		
			}
		} catch (Exception $e) {}				
	}
	
	
	
	/**
	 * Получаем кол-во студентов.
	 * Опционально, если надо будет и список всех студентов в виде схлопывающегося списка.
	*/
	public function updateStudents($subject_id, $tutor_id, $group_id, $student_id){
		if(empty($subject_id)){ return 0; }
		
		if(!empty($group_id)){
			if(!$this->serviceGroup){ $this->serviceGroup = $this->getService('StudyGroup'); }
			
			$available_students = $this->getAvailableStudents($tutor_id, $subject_id);	
			if(empty($available_students)){ return 0; }			
			$group_students		= $this->serviceGroup->getUsers($group_id);
			
			$students 	= array_intersect($available_students, $group_students);
			$ret  		= '<p class="total">'.count($students).'</p>';
			
			foreach($students as $user_id){
				$ret .= '<p>'.$this->getUserName($user_id).'</p>';
			}			
			return $ret;			
		}
		
		if(!empty($student_id)){
			$ret  = '<p class="total">1</p>';
			$ret .= '<p>'.$this->getUserName($student_id).'</p>';
			return $ret;
		}
		
		$available_students = $this->getAvailableStudents($tutor_id, $subject_id);
		if(empty($available_students)){ return 0; }
		
		
		
		$ret  		= '<p class="total">'.count($available_students).'</p>';			
		foreach($available_students as $user_id){
			$ret .= '<p>'.$this->getUserName($user_id).'</p>';
		}			
		return $ret;
	}
	
	
	public function updateAuthor($user_id){
		return $this->getUserName($user_id);
	}
	
	
	
	public function getUserName($user_id){
		
		if(empty($user_id)){ return _('-'); }
		if(isset($this->_userList[$user_id])){ return $this->_userList[$user_id]; }	
		
		if(!$this->serviceUser) { $this->serviceUser = $this->getService('User'); }
		$user 						= $this->serviceUser->getById($user_id);
		$this->_userList[$user_id]	= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		
		return $this->_userList[$user_id];
	}
	
	
	/**
	 * Определяем группу. Если не указана группа, то берем группы всех доступных студентов тьютора
	 * Если указана группа, то выводим ее
	 * Если указан студент (ИН), то берем все его группы.
	*/
	public function updateGroups($subject_id, $tutor_id, $group_id, $student_id){
		if(empty($subject_id)){ return _('Нет'); }
		
		if(!empty($group_id)){
			$name = $this->getGroupById($group_id);
			return !empty($name) ? $name : _('Нет');
		}
		
		if(!empty($student_id)){
			$groups = $this->getGroupByUserId($student_id);
			if(empty($groups)){ return _('Нет'); }
			return implode(', ', $groups);			
		}
		
		$available_students = $this->getAvailableStudents($tutor_id, $subject_id);
		if(empty($available_students)){ return _('Нет'); }
		
		$groups = $this->getGroupByUserId_s($subject_id, $tutor_id, $available_students);
		if(empty($groups)){ return _('Нет'); }
		return implode(', ', $groups);
	}
	
	/**
	 * Получаем строку - группу по ее id
	*/
	public function getGroupById($group_id){
		if(empty($group_id))					{ return false; }
		if(isset($this->groupList[$group_id]))	{ return $this->groupList[$group_id]; }		
		
		if(!$this->serviceGroup){ $this->serviceGroup = $this->getService('StudyGroup'); }
		$this->groupList[$group_id] = $this->serviceGroup->getById($group_id)->name;
		
		return $this->groupList[$group_id];
	}
	
	
	/**
	 * Получаем массив групп студента
	*/
	public function getGroupByUserId($user_id){
		if(empty($user_id))							{ return false; }
		if(isset($this->_groupUsersList[$user_id]))	{ return $this->_groupUsersList[$user_id]; }
		
		if(!$this->serviceGroup){ $this->serviceGroup = $this->getService('StudyGroup'); }
		$this->_groupUsersList[$user_id] = $this->serviceGroup->getGroupListOnUserIDs(array($user_id));
		
		return $this->_groupUsersList[$user_id];		
	}
	
	/**
	 * Получаем массив групп студентОВ
	 * Если в кэше нет данных хотя бы по одному из студентов, находим 
	*/
	public function getGroupByUserId_s($subject_id, $tutor_id, $user_id_s){		
		if(empty($subject_id) || empty($tutor_id) || empty($user_id_s)){ return false; }
		$key = $subject_id.'~'.$tutor_id;
		
		if(isset($this->_groupAvailableStudentList[$key]))	{ return $this->_groupAvailableStudentList[$key]; }
		
		if(!$this->serviceGroup){ $this->serviceGroup = $this->getService('StudyGroup'); }
		$this->_groupAvailableStudentList[$key] = $this->serviceGroup->getGroupListOnUserIDs($user_id_s);
		
		return $this->_groupAvailableStudentList[$key];		
	}
	
	
	
	
	/**
	 * Получаем id всех студентов, завершивших обучение на сесси
	 * || кэшируем ФИО пользователей и самих назанченных студентов
	*/
	public function getGraduatedStudents($subject_id){
		if(empty($subject_id))								{ return false; }
		if(isset($this->_graduatedStudentList[$subject_id])){ return $this->_graduatedStudentList[$subject_id]; }
		
		if(!$this->serviceSubject){ $this->serviceSubject = $this->getService('Subject'); }
		$users = $this->serviceSubject->getAssignedGraduatedActive($subject_id);
		
		if(!empty($users)){
			foreach($users as $row){
				$this->_graduatedStudentList[$subject_id][$row->MID] = $row->MID;
				if(!isset($this->_userList[$row->MID])){ $this->_userList[$row->MID] = $row->LastName.' '.$row->FirstName.' '.$row->Patronymic; }				
			}
		}
		return $this->_graduatedStudentList[$subject_id];
	}
	
	
	
	public function getAvailableStudents($tutor_id, $subject_id){
		if(empty($tutor_id) || empty($subject_id)){ return false; }
		
		if(isset($this->_availableStudentList[$subject_id][$tutor_id])){ return $this->_availableStudentList[$subject_id][$tutor_id]; }
		
		$students_graduated = $this->getGraduatedStudents($subject_id);	
		
		if(empty($students_graduated)){ return false; }
		
		if(!$this->serviceSubject){ $this->serviceSubject = $this->getService('Subject'); }
		
		$students_available	= $this->serviceSubject->getAvailableStudents($tutor_id, $subject_id);
		
		# доступны тьютору все студенты
		if($students_available === false){
			$this->_availableStudentList[$subject_id][$tutor_id] = $students_graduated;
		} else {
			$this->_availableStudentList[$subject_id][$tutor_id] = array_intersect($students_graduated, $students_available);
		}
		
		return $this->_availableStudentList[$subject_id][$tutor_id];		
	}
	
	
	public function updateMarksheetFile($file_name, $file_id, $author_id){
		return '<a href="'.$this->view->url(array('module' => 'marksheet', 'controller' => 'get', 'action' => 'manager', 'file_id' => $file_id, 'author_id' => $author_id), 'default', true).'" target="_blank">'.$file_name.'</a>';
	}
	
	
	
	/**
	 * Получаем дату ведомости
	*/
	public function updateMarksheetDateIssue($subject_id, $author_id, $group_id, $student_id, $marksheet_external_id){
		
		if(!$this->serviceMarksheet)	{ $this->serviceMarksheet 	= $this->getService('Marksheet');	}
		if(!$this->serviceStudyGroup)	{ $this->serviceStudyGroup 	= $this->getService('StudyGroup');	}
		
		$group	= $this->serviceStudyGroup->getById($group_id);		
		$info	= $this->serviceMarksheet->getInfo($subject_id, $student_id, $group->id_external);
		
		$timestamp = strtotime($info->date_issue);
		if(empty($timestamp)){ return ''; }
		
		return date('d.m.Y', $timestamp);
	}
	
	
	
	
	
	
	
	
}

