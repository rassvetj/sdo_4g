<?php
class Assign_StaffController extends HM_Controller_Action_Assign
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    protected $courseCache = array();
    protected $_lastMsgCache = NULL; # данные последнего сообщения всеей таблицы или определенных студентов (завитит от роли и зоны ответственности)
    protected $_userFilesCache = NULL; # данные прикрепленных файлов студентов.
    protected $_userInfoCache = NULL; # данные пользователей (ФИО, email)
	

    //protected $_fixedRow = false;

    protected $_assignOptions = array(
        'role'                  => 'Student',
        'courseStatuses'        => array(2),
        'table'                 => 'subjects_users',
        'tablePersonField'      => 'user_id',
        'tableCourseField'      => 'subject_id',
        'courseTable'           => 'subjects',
        'courseTablePrimaryKey' => 'subid',
        'courseTableTitleField' => 'name',
        'courseIdParamName'     => 'subject_id'
    );

    public function init()
    {
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

    public function indexAction()
    {  		
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)){            			
			$department = $this->getService('Orgstructure')->isAssignedOnOrgstructure();
		}			
		if (!$department){		
			$this->_flashMessenger->addMessage(_('Данная страница доступна только супервайзерам, назначенным в орг.структуру'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		//очищаем кэш
		$this->getService('Orgstructure')->clearCache();
		$this->getService('StudyGroup')->clearCache();
		
		
		$form = new HM_Form_Staff();		
		$this->view->form = $form;			
	}
	
	
	public function getReportAction()
    {   	
		
		ini_set('memory_limit', '2999M');
		
		try {
		
		
		$this->getHelper('viewRenderer')->setNoRender();
	   
		if (!isset($this->_assignOptions['courseIdParamName'])) {
			$this->_assignOptions['courseIdParamName'] = 'course_id';
		}

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId = ($courseId) ? "grid{$courseId}" : 'grid'; // ВАЖНО! это не $courseId, а скорее subjectId - id уч.курса, если мы находимся в панели управления;

    	$default = new Zend_Session_Namespace('default');

    	$notAll = !$this->_getParam('all', isset($default->grid['assign-student-index'][$gridId]['all']) ? $default->grid['assign-student-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }
        if ($sorting == 'fio_ASC') {
            $this->_request->setParam("masterOrder{$gridId}", 'notempty DESC');
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)){
            $isAssignedOrgstructure = $this->getService('Orgstructure')->isAssignedOnOrgstructure();
        }
         
        if (!$isAssignedOrgstructure){
            $this->_flashMessenger->addMessage(_('Данная страница доступна только супервайзерам, назначенным в орг.структуру'));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }        

		//$this->usersPosition = $this->getService('Orgstructure')->getUserDepartamentList($this->getService('User')->getCurrentUserId()); //--студент => департамент, => позиция.
		//$this->usersGroupList = $this->getService('StudyGroup')->getGroupListOnUserIDs(array_keys($this->usersPosition)); //--список групп.
		$this->usersGroupList = $this->getService('StudyGroup')->getGroupsByResponsibility($this->getService('User')->getCurrentUserId()); //--сохранить в кэщ?
		
		$select = $this->getService('User')->getSelect();

		$groupIDs = $this->_request->getParam('group_id', array());	
		if(is_string($groupIDs)){ # GET обращение к скрипту: для кнопок "распечатать"/"excel"/"word" в компоненте GRID 
			$groupIDs = explode(',',$groupIDs);
		}
		$groupIDs = (array)$groupIDs;
		$groupIDs = array_filter($groupIDs); # удаляются элементы "Все" и "Нет доступных групп".
		
		
		$subLastMsgSelect = $this->getService('User')->getSelect(); # выполенние этого запроса ниже.
		if(count($groupIDs)){			
			$this->_request->setParam('group_id', implode(',',$groupIDs)); # GET обращение к скрипту: для кнопок "распечатать"/"excel"/"word"/"искать" в компоненте GRID	
			$groupUsers = $this->getService('StudyGroupUsers')->fetchAll($this->quoteInto('group_id IN (?)', $groupIDs)); //--находим всех студентов по выбранным группам.
			if(count($groupUsers)){
				foreach($groupUsers as $g){
					$userIDs[$g->user_id] = $g->user_id;
				}
			}	
			$select->where($this->quoteInto('sg.group_id IN (?)', $groupIDs));	
		} else {											
			$groupUsers = $this->getService('StudyGroupUsers')->fetchAll($this->quoteInto('group_id IN (?)', array_keys($this->usersGroupList))); //--находим всех студентов по всем группам.
			if(count($groupUsers)){
				foreach($groupUsers as $g){
					$userIDs[$g->user_id] = $g->user_id;
				}
			}	
			$select->where($this->quoteInto('sg.group_id IN (?)', array_keys($this->usersGroupList)));						
		}
		
		$this->usersPosition = $this->getService('Orgstructure')->getUserDepartamentListByUserId($userIDs); //--список позиций по id пользователя
		$subLastMsgSelect->where($this->getService('User')->quoteInto(array('(user_id IN (?) ', ' OR to_whom IN (?))'), array($userIDs, $userIDs)));
		
		$cur_date = date('Y-m-d 23:59:59',time());
		$period = $this->_request->getParam('period', HM_Subject_SubjectModel::SESSION_ALL); //--текущие, прошедшие, все  сессии.
		
		
		if($period == HM_Subject_SubjectModel::SESSION_PRESENT){
			$criteriaStudents 	= $this->getService('User')->quoteInto(array('(st.MID = t1.MID AND st.CID = s.subid) AND  ((s.end >= ? AND s.time_ended_debt IS NULL)', ' OR (st.time_ended_debtor >= ?))'), array($cur_date, $cur_date));
			$criteriaWhere 		= $this->getService('User')->quoteInto(array('((s.end >= ? AND s.time_ended_debt IS NULL)', ' OR (s.time_ended_debt >= ?))', ' AND s.begin <= ?'), array($cur_date, $cur_date, $cur_date));
		} elseif($period == HM_Subject_SubjectModel::SESSION_PAST){
			$criteriaStudents 	= $this->getService('User')->quoteInto(array('(st.MID = t1.MID AND st.CID = s.subid) AND  ((s.end < ? AND s.time_ended_debt IS NULL)', ' OR (st.time_ended_debtor < ?))'), array($cur_date, $cur_date));
			$criteriaWhere		= $this->getService('User')->quoteInto(array('((s.end < ? AND s.time_ended_debt IS NULL)', ' OR (s.time_ended_debt < ?))'), array($cur_date, $cur_date));
		} else {
			$criteriaStudents 	= 'st.MID = t1.MID AND st.CID = s.subid';
			$criteriaWhere		= $this->getService('User')->quoteInto('s.begin <= ?', $cur_date);
		}
		
        // подчиненными супервайзера являются все enduser'ы из его подразделения и всех вложенных
        
		
		
        $select->from(
            array('t1' => 'People'),
            array(
                'MID',
                'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                'departments' => 't1.MID',
                'positions' => 't1.MID',
                'group_id_external' => new Zend_Db_Expr('GROUP_CONCAT(sg.id_external)'),                
				'email' => 't1.Email',
                'email_confirmed' => 't1.email_confirmed',
                'subid' => 's.subid',
                'courses' => 's.name',
                'time_assign' => 't2.begin',
                'time_begin' => 's.begin',
                'time_ended' => 's.end',
				'dete_debtor' => 'st.time_ended_debtor',	
                'status' => 't2.status',
                'mark' => 'cm.mark',
				
				/*
				'ball_date' => new Zend_Db_Expr('CASE WHEN i_last.last_interview_type = '.HM_Interview_InterviewModel::MESSAGE_TYPE_BALL.' THEN i_last.last_interview_date ELSE NULL END'), //--дата выставления последней оценки
				'type_last_message' => 'i_last.last_interview_type',								
				'last_message_id' => 'i_last.last_interview_id',								
				'is_new' => new Zend_Db_Expr('CASE WHEN t1.MID = i_last.last_interview_user_id THEN 1 ELSE 0 END'),				
				'i_file' => 'i_file.interview_file',								
				*/
				
				'ball_date' => 'MID', //--дата выставления последней оценки
				'type_last_message' => 'MID',								
				'last_message_id' => 'MID',	
				'is_new' => 'MID',
				'i_file' => 'MID',
				
				'i_lesson_name' => 'l.title',
				'lesson_id' => 'l.SHEID',
				#'tutors' => new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  CONCAT(CONCAT(CONCAT(CONCAT(pt.LastName, ' ') , pt.FirstName), ' '), pt.Patronymic) )"),
				#'tutor_emails' => new Zend_Db_Expr("GROUP_CONCAT( pt.EMail )"),
				#'teacher' => new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  CONCAT(CONCAT(CONCAT(CONCAT(pth.LastName, ' ') , pth.FirstName), ' '), pth.Patronymic) )"),
				'tutors' => new Zend_Db_Expr("GROUP_CONCAT( t.MID )"),
				'tutor_emails' => new Zend_Db_Expr("GROUP_CONCAT( t.MID )"),
				'teacher' => new Zend_Db_Expr("GROUP_CONCAT( th.MID )"),
				
				't_chair' => new Zend_Db_Expr("GROUP_CONCAT(t.MID)"), //--тьютор
				't_faculty' => new Zend_Db_Expr("GROUP_CONCAT(t.MID)"),											
            )
        );
	
	
		
	
		
		$select->joinLeft(
            array('t2' => $this->_assignOptions['table']),
            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
            array()
        )->joinLeft(
            array('s' => 'subjects'),
            's.subid = t2.subject_id',
            array()
        )->joinLeft(
            array('cm' => 'courses_marks'),
            'cm.cid = t2.subject_id AND cm.mid = t2.user_id',
            array()
        )
        
		
		->joinLeft(
            array('sgc' => 'study_groups_custom'),
            'sgc.user_id = t1.MID',
            array()
        )
		->joinLeft(
            array('sg' => 'study_groups'),
            'sg.group_id = sgc.group_id AND sg.id_external IS NOT NULL',
            array()
        )
		->join(
            array('st' => 'Students'),            
			$criteriaStudents,
			array()
		)
		
		
		

		
		->joinLeft(array('t' => 'Tutors'), 't.CID = s.subid', array()) //--отбор тьюторов
		#->joinLeft(array('pt' => 'People'), 'pt.MID = t.MID', array())
		
		->joinLeft(array('th' => 'Teachers'), 'th.CID = s.subid', array()) //--отбор преподавателя
		#->joinLeft(array('pth' => 'People'), 'pth.MID = th.MID', array())
		
		
		
		
		->where('t1.blocked != 1') //[che 30.06.2014 #17114]  //--Временно закомментировал это условие для проверки. Потом вернуть обратно. 
        ;
		if(count($userIDs)){			
			$select->where($this->getService('User')->quoteInto('t1.MID IN (?)', $userIDs));
		} else {			
			$select->where('1=0');
		}
		
		$select->where($criteriaWhere); 
		
		$select->join(array('l' => 'schedule'), 'l.CID = s.subid', array()); //--Уроки
		
		$select->join(array('sch' => 'scheduleID'), 'sch.MID = t1.MID AND sch.SHEID=l.SHEID', array());	//--выбираем оценки студентов по их id и номеру занятия.						
		
		$select->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes())); //--Что это за типы занятий?
        $select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN); //--Выводим только бесплатные или свободные??
		
		
		//->where('du.lft > ?', $department->lft)
        //->where('du.rgt < ?', $department->rgt)
        //->where('t1.MID != ?', $position->mid)	

		//->where('sg.id_external IS NOT NULL')		
		//;
		//var_dump($position->mid);
		
		
		
		
		
		
		//----------------------------
		//выбираем самое последнее сообщение, чтобы знать, кто ответил последним + тип сообщения
		# 11111111111111111
		/*		
		$subSelect = $this->getService('User')->getSelect();
		$subSelect->from('interview', array(
				'interview_hash' => 'interview.interview_hash',
				'last_interview_id' => 'MAX(interview.interview_id)',			
				'last_interview_user_id' => 'MAX(interview.user_id)',										
				'last_interview_type' => 'interview.type',							
				'last_interview_date' => 'interview.date',							
			))			
			->group(array('interview.interview_hash', 'interview.type', 'interview.date'));
		
		
		
		
		//присоединяем данные первого сообщения				
		$select->join(array('i' => 'interview'), 'i.to_whom = t1.MID AND i.user_id = 0 AND i.lesson_id = l.SHEID ', array());
		
		$select->joinInner(array('i_last' => $subSelect), 'i_last.interview_hash = i.interview_hash', array());			
		
		
		
		
		//--Группируем хэш и последнюю дату, что бы нн було дублей из-за нескольких уроков.
		$subSelect3 = $this->getService('User')->getSelect();
		$subSelect3->from('interview', array(
				'interview_hash' => 'interview.interview_hash',				
				'last_interview_date' => 'MAX(interview.date)',	
				'interview_lesson_id' 	=> 'interview.lesson_id',	 		
			))						
			->group(array('interview.interview_hash', 'interview.lesson_id'));
		
		$select->joinInner(array('i_last3' => $subSelect3), 'i_last3.last_interview_date = i_last.last_interview_date AND i.interview_hash = i_last3.interview_hash', array());			
		*/	
		# 11111111111111111		
		//----------------------------
		
		
		
		
		//----------------------------
		# 2222222222222
		//--Отбираем все прикрепленные документы студента		
		/*
		$subSelectFile = $this->getService('User')->getSelect();
		$subSelectFile->from(
			array('i' => 'interview'), 
			array(								
				'interview_hash' 		=> 'i.interview_hash',				
				'user_id' 				=> 'i.user_id',										
				'interview_file' 		=> new Zend_Db_Expr("GROUP_CONCAT( DISTINCT '|'+f.name )"),
				'interview_lesson_id' 	=> 'i.lesson_id',
			));		
		
		$subSelectFile->join(array('intf' => 'interview_files'), 'intf.interview_id = i.interview_id', array());
		$subSelectFile->join(array('f' => 'files'), 'f.file_id = intf.file_id', array());			
		//$subSelectFile->where('i.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_TEST);	//--тип - решение на проверку.
		
		$subSelectFile->group(array('i.interview_hash', 'i.lesson_id', 'i.user_id'));
		
		$select->joinLeft(array('i_file' => $subSelectFile), 'i_file.interview_hash = i.interview_hash AND i_file.user_id = t1.MID', array());
		*/		
		# 2222222222222
		//----------------------------
		
		
		# сделать отдельный селект на прикрепленные файлы
		
		
		
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)
        ) {
            if($responsibilityType = $this->getService('SupervisorResponsibility')->getResponsibilityType($this->getService('User')->getCurrentUserId())){
                $responsibilities = $this->getService('SupervisorResponsibility')->fetchAll($this->quoteInto(
                    array('user_id = ?'),
                    array($this->getService('User')->getCurrentUserId())
                ))->getList('responsibility_id', 'responsibility_id');

                if($responsibilities){
                    if( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE ){

                        $select->where('s.subid IN (?)', $responsibilities);
                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE) {

                        $subSelect = $this->getService('StudyGroupUsers')->getSelect();
                        $subSelect->from(
                            array('sgu' => 'study_groups_users'),
                            array('sgu.user_id')
                        )
                            ->where('sgu.group_id IN (?)', $responsibilities)
                            ->group('sgu.user_id');

                        $availabeGroupUsers = $subSelect->query()->fetchAll();
                        $users = array();
                        foreach( $availabeGroupUsers as $availabeGroupUser ) {
                            $users[] = $availabeGroupUser['user_id'];
                        }
                        if(count($users) < 1){ 
							$users[] = 0;
						}
						$select->where('t1.MID IN (?)', $users);												
						#$subLastMsgSelect->where($this->getService('User')->quoteInto(array('(user_id IN (?) ', ' OR to_whom IN (?))'), array($users, $users)));

                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE) {

                        $subSelect = $this->getService('ProgrammUser')->getSelect();
                        $subSelect->from(
                            array('pu' => 'programm_users'),
                            array('pu.user_id')
                        )
                            ->where('pu.programm_id IN (?)', $responsibilities)
                            ->group('pu.user_id');

                        $availabeProgrammUsers = $subSelect->query()->fetchAll();
                        $users = array();
                        foreach( $availabeProgrammUsers as $availabeProgrammUser ) {
                            $users[] = $availabeProgrammUser['user_id'];
                        }
						
						if(count($users) < 1){ //--fix. При count($users) < 1 ошибка запроса.
							$users[] = 0;
						}
                        $select->where('t1.MID IN (?)', $users);						
						#$subLastMsgSelect->where($this->getService('User')->quoteInto(array('(user_id IN (?) ', ' OR to_whom IN (?))'), array($users, $users)));

                    } elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE) {

                        if(count($responsibilities) < 1){
							$responsibilities[] = 0;
						}
						$select->where('t1.MID IN (?)', $responsibilities);						
						#$subLastMsgSelect->where($this->getService('User')->quoteInto(array('(user_id IN (?) ', ' OR to_whom IN (?))'), array($users, $users)));
                    }
                }
            } else {
                $select->where('1 != 1');				
				$subLastMsgSelect->where('1 != 1');
            }
        }
		
		
		$fileStudentInterview = array(); # id сообщений от студентов. Нужно для отбора прикрепленных файлов.
		###### данные последнего сообщения BEGIN		
		$subLastMsgSelect->from('interview', array('last_id' => 'MAX(interview_id)'));			
		$subLastMsgSelect->group(array('interview_hash', 'lesson_id'));
		
		$lastMsgSelect = $this->getService('User')->getSelect();
		$lastMsgSelect->from(array('i' => 'interview'), array('i.interview_id', 'i.message', 'i.type', 'i.ball', 'i.date', 'i.user_id', 'i.lesson_id', 'i.to_whom'));		
		$lastMsgSelect->join(array('i_last' => $subLastMsgSelect), 'i_last.last_id = i.interview_id', array());	
		
		$lastMsgData = $lastMsgSelect->query()->fetchAll();
		unset($subLastMsgSelect);
		unset($lastMsgSelect);
		
		if(!empty($lastMsgData)){
			foreach($lastMsgData as $m){
				if(is_array($userIDs) && !empty($userIDs)){
					if(in_array($m['user_id'], $userIDs)){ # данные только по студентам. Если данных нет - значит это или тьютор, или выдано задание.
						$this->_lastMsgCache[$m['lesson_id'].'~'.$m['user_id']] = $m;
					}
				}
				#$this->_lastMsgCache[$m['lesson_id'].'~'.$m['user_id']] = $m; # Если последнее сообщ. от тьютора, то его мы не найдем, 		
				#$this->_lastMsgCache[$m['lesson_id'].'~'.$m['to_whom']] = $m; # поэтому также формируем ключ с привязкой к получателю, т.е. студенту			
				if(empty($users)){				
					if($m['user_id'] > 0){
						$fileStudentInterview[$m['interview_id']] = $m['interview_id'];
					}
				} elseif(in_array($m['user_id'], $users)) {
					$fileStudentInterview[$m['interview_id']] = $m['interview_id'];
				}
			}			
		}
		unset($lastMsgData);
		###### данные последнего сообщения END
		
		
		
		###### данные прикрепленного файла BEGIN
		
		$fileSelect = $this->getService('User')->getSelect();
		$fileSelect->from(array('i' => 'interview_files'),
			array(								
				'interview_id' 			=> 'i.interview_id',										
				'interview_file' 		=> new Zend_Db_Expr("GROUP_CONCAT( DISTINCT '|'+f.name )"),					
			)
		);
		$fileSelect->join(array('f' => 'files'), 'f.file_id = i.file_id', array());
		
		if(empty($fileStudentInterview))	{	$fileSelect->where(' 1 != 1 ');										}
		else	 							{ 	$fileSelect->where('i.interview_id IN (?)', $fileStudentInterview);	}
		$fileSelect->group('i.interview_id');			
			
		$userFilesData = $fileSelect->query()->fetchAll();
		unset($fileStudentInterview);
		unset($fileSelect);
		
		if(!empty($userFilesData)){
			foreach($userFilesData as $m){
				$this->_userFilesCache[$m['interview_id']] = $m;							
			}			
		}
		unset($userFilesData);		
		###### данные прикрепленного файла END
		
		

		###### формирование массива доступных тьюторов и преподов BEGIN
		try {
			$userSelect = $this->getService('User')->getSelect();
			$userSelect->from(array('p' => 'People'),
				array(								
					'MID' 	=> 'p.MID',										
					'EMail' => 'p.EMail',					
					'fio'	=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),					
				)
			);
			$userSelectData = $userSelect->query()->fetchAll();
			unset($userSelect);
			if(!empty($userSelectData)){
				foreach($userSelectData as $i){
					$this->_userInfoCache[$i['MID']] = $i;							
				}			
			}
			unset($userSelectData);
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}	
		###### формирование массива доступных тьюторов и преподов END 
		
		
		

		/* //--в этот раздел имеет доступ только наблюдатель.
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        ){
            $select = $this->getService('DeanResponsibility')->checkUsers($select, 't1.MID', 'd.soid');

        }
		*/
       
        $group_fields = array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.Email', 't1.email_confirmed', 't2.begin', 's.subid', 's.name', 's.begin', 's.end', 't2.end', 't2.status', 'mark', 'l.title', 'l.SHEID',
								'st.time_ended_debtor'
								#'i_last.last_interview_user_id',
								#'i_last.last_interview_type',								
								#'i_last.last_interview_date',								
								#'i_last.last_interview_id',
								#'i_file.interview_file'
							);
        
        $select->group($group_fields);
		
		
		# Перехватываем встроенный экспорт. Т.к. он слишком ресурсоемкий и 70 000 строк не может обработать.
		$exportType = $this->_request->getParam('_exportTogrid', '');
		if($exportType == 'excel'){
			$res = $select->query()->fetchAll();
			unset($select);			
			$this->excelAction($res);			
			die;
		}			
	
		
        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),                
                'subid' => array('hidden' => true),
                'notempty' => array('hidden' => true),
                'email_confirmed' => array('hidden' => true),
                'lesson_id' => array('hidden' => true),
                'courses' => array(
                    'title' => _('Курсы'),
                    'callback' => array(
                        'function' => array($this, 'updateSubjectName'),
                        'params' => array('{{subid}}', '{{courses}}', '{{MID}}')
                    )
                ),
                'fio' => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink(
                                       $this->view->url(array(
                                                             'module' => 'user',
                                                             'controller' => 'list',
                                                             'action' => 'view',
                                                             'user_id' => '')).'{{MID}}',
                                       _('Карточка пользователя')
                                   ).
                                   '<a href="'.$this->view->url(array(
                                                                       'module' => 'user',
                                                                       'controller' => 'edit',
                                                                       'action' => 'card',
                                                                       'user_id' => ''
                                                                  )) . '{{MID}}'.'">'.'{{fio}}</a>'
                ),
                'email' => array(
                    'title' => _('E-mail'),
                    'callback' => array(
                        'function' => array($this, 'updateEmail'),
                        'params' => array('{{email}}', '{{email_confirmed}}', $this->getService('Option')->getOption('regValidateEmail'))
                    )
                ),
                
				'departments' => array(
                    'title' => _('Подразделение'),
                    'callback' => array(                        
						'function' => array($this, 'updateDepartments'),
                        'params' => array('{{departments}}')
                    )
                ),
												
				'positions' => array(
					'title' => _('Должность'),					
					'callback' => array(
						'function' => array($this, 'updatePosition'),						
						'params' => array('{{positions}}')					
					)					
				),
				
				'group_id_external' => array(
					'title' => _('ID группы'),					
					'callback' => array(
						'function' => array($this, 'updateGroup'),						
						'params' => array('{{group_id_external}}')						
					)					
				),
                'time_assign' => array('title' => _('Дата назначения')),
                'time_begin' => array('title' => _('Дата начала обучения')),
                'time_ended' => array('title' => _('Дата окончания обучения')),
                'status' => array('title' => _('Статус')),
                'mark' => array('title' => _('Оценка')),
                'ball_date' => array('title' => _('Оценка выставлена')),
				
				'type_last_message' => array('title' => _('Тип последнего сообщения')),    											
				
				'last_message_id' => array(
					'title' => _('Последнее сообщение'),					
					'callback' => array(
						'function' => array($this, 'updateMessage'),						
						'params' => array('{{last_message_id}}', '{{lesson_id}}')						
					)					
				),
				
				
				'is_new' => array('title' => _('Статус обработки')),
				'i_date' => array('hidden' => true),
				'dete_debtor' => array('title' => _('Дата продления')),
				'i_file' => array('title' => _('Файлы')),				
				'i_lesson_name' => array('title' => _('Урок')),					
				'tutors' => array('title' => _('Тьюторы')),					
				'tutor_emails' => array('title' => _('Email тьюторов')),					
				'teacher' => array('title' => _('Преподаватели')),					
				't_chair' => array('title' => _('Кафедра')),					
				't_faculty' => array('title' => _('Факультет')),								
            ),
            array(
                'fio' => null,							
				'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))), //--вот это узкое место. Эти 2 фильтра конфликтуют, если				
				//'positions'   => array('callback' => array('function' => array($this, 'filterPositions'))), //--их применить одновременно. 
				'positions'   => array('callback' => array('function' => array($this, 'filterPositionsMod'))),
                
                #'tutors' => null,
                #'tutor_emails' => null,
                #'teacher' => null,
                'email' => null,
                'courses' => null,                
                'group_id_external' => null,                
                'time_assign' => array('render' => 'date'),
                'time_begin' => array('render' => 'date'),
                'time_ended' => array('render' => 'date'),
				'dete_debtor' => array('render' => 'date'),                
                /*
				'status' => array(
									'values' => HM_Subject_SubjectModel::getLearningStatuses(),
									'callback' => array('function' => array($this, 'filterStatus'))
				),
				*/
                'mark' => null,				
				#'ball_date' => array('render' => 'date'),
				
                #'type_last_message' => array('values' => HM_Interview_InterviewModel::getTypes() ),	
                /*
				'is_new' => array('values' => array(
												'1'=>'Не обработано',
												'0'=>'Обработано',
										)),	
				*/
				#'i_lesson_name' => null,				
				#'t_chair' => null,
				#'t_faculty' => null,
            )
        );

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        ) {
            $grid->setGridSwitcher(array(
                array('name' => 'all_students', 'title' => _('всех слушателей'), 'params' => array('all' => 0), 'order' => 'fio'),
                array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => 1), 'order' => 'fio'),
            ));
  		}
		
		
		$grid->updateColumn('tutors',
            array('callback' =>
                array('function' => array($this, 'updateTutors'),
                      'params'   => array('{{tutors}}')
                )
            )
        );
		
		$grid->updateColumn('teacher',
            array('callback' =>
                array('function' => array($this, 'updateTeacher'),
                      'params'   => array('{{teacher}}')
                )
            )
        );
		
		$grid->updateColumn('tutor_emails',
            array('callback' =>
                array('function' => array($this, 'updateTutorEmails'),
                      'params'   => array('{{tutor_emails}}')
                )
            )
        );
		
		$grid->updateColumn('t_chair',
            array('callback' =>
                array('function' => array($this, 'updateTChair'),
                      'params'   => array('{{t_chair}}')
                )
            )
        );
		
		$grid->updateColumn('time_begin',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_begin}}')
                )
            )
        );
		
		$grid->updateColumn('time_assign',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_assign}}')
                )
            )
        );
		
		$grid->updateColumn('time_ended',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_ended}}')
                )
            )
        );
		
		$grid->updateColumn('dete_debtor',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{dete_debtor}}')
                )
            )
        );
		
		$grid->updateColumn('t_faculty',
            array('callback' =>
                array('function' => array($this, 'updateTFaculty'),
                      'params'   => array('{{t_faculty}}')
                )
            )
        );
		
		$grid->updateColumn('status',
            array('callback' =>
                array('function' => array($this, 'updateStatus'),
                      'params'   => array('{{status}}')
                )
            )
        );
		
		$grid->updateColumn('ball_date',
            array('callback' =>
                array('function' => array($this, 'updateBallDate'),
                      'params'   => array('{{ball_date}}', '{{lesson_id}}')
                )
            )
        );
		
		$grid->updateColumn('type_last_message', array(						
			'callback' => array(
				'function' => array($this, 'updateLastMessageType'),
				'params' => array('{{type_last_message}}', '{{lesson_id}}')
			)
		));	
		
		
		$grid->updateColumn('is_new', array(						
			'callback' => array(
				'function' => array($this, 'getStatusCheck'),
				'params' => array('{{is_new}}', '{{lesson_id}}')
			)
		));
		
		$grid->updateColumn('i_file', array(
            'callback' => array(
				'function' => array($this, 'updateFiles'),
                'params' => array('{{i_file}}', '{{lesson_id}}')
            )
        ));
		
		
		
		
		/*
        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );
		
		$grid->updateColumn('group_id',
            array('callback' =>
                array('function' => array($this, 'updateUserGroup'),
                      'params'   => array('{{group_id}}')
                )
            )
        );
		*/
		
		
        
        $grid->addAction(array(
            'module' => 'assign',
            'controller' => 'staff',
            'action' => 'login-as'
        ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );  

		/*
			
		*/
		/*
		$grid->updateColumn('is_new', array(						
			'callback' => array(
				'function' => array($this, 'getStatusCheck'),
				'params' => array('{{is_new}}')
			)
		));	

		
		
		*/
		
		/*
		$grid->formButton(
			'excelButton',
			_('Excel'),
			array('onClick' => 'alert(111);')
		);
		*/
		
		
		
		
        
        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");
		
		$grid->setDeployOption('memory_limit','2999M'); 		
		$this->_grid = $grid;
		
		parent::indexAction();
		
		echo $this->view->grid;
		
	   } catch(Exeption $e){		   
		   echo $e->getCode();
	   }	   
	}

    protected function _assign($personId, $subjectId)
    {
        return $this->getService('Subject')->assignUser($subjectId, $personId);
    }

    protected function _unassign($personId, $subjectId)
    {
        return $this->getService('Subject')->unassignStudent($subjectId, $personId);
    }

    public function updateDate($date){
        if (($date == "") || (!strtotime($date))){
            return _('Нет');
        }else{
            $date = new Zend_Date($date);

            if($date instanceof Zend_Date){
                return $date->toString(HM_Locale_Format::getDateFormat());
            }else{
                return _('Нет');
            }

        }
    }

    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }

    public function updateStatus($status)
    {
        $statuses = HM_Subject_SubjectModel::getLearningStatuses();
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }

        return _('Неизвестно');
    }

    public function blockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        // Нельзя заблокировать себя
        if ($key = array_search($this->getService('User')->getCurrentUserId(), $ids)) {
            unset($ids[$key]);
        }

        $array = array('blocked' => 1);
        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно заблокированы!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время блокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        }
    }

    public function unblockAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('blocked' => 0);

        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно разблокированы!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время разблокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        }
    }
    
    public function deleteByAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('User');
        foreach ($ids as $value) {

            if ($value != $this->getService('User')->getCurrentUserId()) {
                $service->delete(intval($value));
            } else {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить себя!'));
                $this->_redirector->gotoSimple('index', 'staff', 'assign');
            }
        }
        $this->_flashMessenger->addMessage(_('Пользователи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'staff', 'assign');
    }

    public function updateSubjectName($subjectId, $name, $userId)
    {
        return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId, 'user_id' => $userId)) . '">' . $name . '</a>';
    }
	
	
	 /**
     * Возвращает наименование типа по его числовому представлению
     * @param int $type
     * @return string
     */
    public function getTaskTypeString( $type)
    {	
        $ivModel = HM_Interview_InterviewModel::factory(array('type' => intval($type)));
        return  $ivModel->getType();		
    }
	
	
	
	

	
	public function updateTutors($tutors, $noHtml = false){
		if(!$tutors){
			return _('Нет');					
		}
				
		$filesArray = explode(',', $tutors);		
		$filesArray = array_filter($filesArray);
		
		if($noHtml)	{ 	$result = array(); }
		else 		{	$result = (is_array($filesArray) && (count($filesArray) > 1)) ? array('<p class="total">' . count($filesArray)  . ' тьютора</p>') : array(); }
		
		
		foreach($filesArray as $f){			
			if($noHtml)	{ $result[] = $this->_userInfoCache[$f]['fio']; 			}
			else 		{ $result[] = '<p>'.$this->_userInfoCache[$f]['fio'].'</p>';}
		}
		
		if($result) {
			$delimiter = ($noHtml) ? (', ') : ('');		
			return implode($delimiter, $result);
		} else {
			return _('Нет');	
		}
	}
	
	public function updateTutorEmails($tutor_emails, $noHtml = false){
		if(!$tutor_emails){
			return _('Нет');					
		}
				
		$filesArray = explode(',', $tutor_emails);		
		$filesArray = array_filter($filesArray);
		
		$result = array();
		
		foreach($filesArray as $f){	
			if($noHtml)	{ $result[] = $this->_userInfoCache[$f]['EMail'];				}
			else 		{ $result[] = '<p>'.$this->_userInfoCache[$f]['EMail'].'</p>';	}
			
		}
		
		if($result) {
			$delimiter = ($noHtml) ? (', ') : ('');	
			return implode($delimiter, $result);
		} else {
			return _('Нет');	
		}
	}
	
	
	
	public function updateTeacher($teacher, $noHtml = false){
		if(!$teacher){
			return _('Нет');					
		}
				
		$filesArray = explode(',', $teacher);		
		$filesArray = array_filter($filesArray);
		
		if($noHtml)	{ 	$result = array(); }
		else 		{	$result = (is_array($filesArray) && (count($filesArray) > 1)) ? array('<p class="total">' . count($filesArray)  . ' преподавателя(ей)</p>') : array(); }
		
		
		foreach($filesArray as $f){
			if($noHtml)	{	$result[] = $this->_userInfoCache[$f]['fio'];				}
			else 		{	$result[] = '<p>'.$this->_userInfoCache[$f]['fio'].'</p>';	}
		}
		
		if($result) {
			$delimiter = ($noHtml) ? (', ') : ('');	
			return implode($delimiter, $result);
		} else {
			return _('Нет');	
		}
	}
	
	public function updateTChair($TChair, $noHtml = false){
		if(!$TChair){
			return _('Нет');
		}
		$userIDs = explode(',', $TChair);
		$userIDs = array_filter($userIDs);
		if(!count($userIDs)){
			return _('Нет');
		}
		$chairs = array();
		$serviceOrgstructure = $this->getService('Orgstructure');
		foreach($userIDs as $id){
			$res = $serviceOrgstructure->getUserChair($id);			
			if($res && is_array($res)){				
				$chairs = array_merge($chairs, $res);
			}			
		}
		if(!count($chairs)){
			return _('Нет');
		}
		
		if($noHtml)	{ 	$result = array(); }
		else		{	$result = (is_array($chairs) && (count($chairs) > 1)) ? array('<p class="total">' . count($chairs)  . ' кафедр(ы)</p>') : array(); }
		
		
		foreach($chairs as $f){
			if($noHtml)	{	$result[] = $f;				}
			else 		{	$result[] = "<p>{$f}</p>";	}			
		}
		
		if($result) {
			$delimiter = ($noHtml) ? (', ') : ('');	
			return implode($delimiter, $result);
		} else {
			return _('Нет');	
		}		
	}
	
	public function updateTFaculty($TFaculty, $noHtml = false){
		if(!$TFaculty){
			return _('Нет');
		}
		$userIDs = explode(',', $TFaculty);
		$userIDs = array_filter($userIDs);
		if(!count($userIDs)){
			return _('Нет');
		}
		$faculty = array();
		$serviceOrgstructure = $this->getService('Orgstructure');
		foreach($userIDs as $id){			
			$res = $serviceOrgstructure->getUserFaculty($id);
			if($res && is_array($res)){
				$faculty = array_merge($faculty, $res);
			}			
		}
		if(!count($faculty)){
			return _('Нет');
		}
		
		if($noHtml)	{ 	$result = array(); }
		else		{	$result = (is_array($faculty) && (count($faculty) > 1)) ? array('<p class="total">' . count($faculty)  . ' факультета(ов)</p>') : array(); }
		
		
		foreach($faculty as $f){
			if($noHtml)	{	$result[] = $f;				}
			else 		{	$result[] = "<p>{$f}</p>";	}	
		}
		
		if($result) {
			$delimiter = ($noHtml) ? (', ') : ('');	
			return implode($delimiter, $result);
		} else {
			return _('Нет');	
		}		
	}
	
	
	//--Измененная ф-ция для фильтра роли в GRID.
	public function filterPositionsMod($data)
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
        
        $select->joinInner(array('p_filter2' => 'structure_of_organ'),
            'p_filter2.mid = ' . $userIdField,
            array()
        );
        $select->where($this->quoteInto(
            "p_filter2.name LIKE ?",
            '%'.$data['value'].'%'
        ));
    }
	
	/**
	 * подготавливает введенные данные в поле фильтрауиии грида. Поле "Status"
	*/
	public function filterStatus($data)
    {
        /*
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
        
        $select->joinInner(array('p_filter2' => 'structure_of_organ'),
            'p_filter2.mid = ' . $userIdField,
            array()
        );
        $select->where($this->quoteInto(
            "p_filter2.name LIKE ?",
            '%'.$data['value'].'%'
        ));
		*/
    }
	
	
	public function updateUserGroup($str){
		if(empty($str)){
			return 'нет';
		}
		
		$tt = explode(',',$str);
		$tt = array_filter($tt);
		$tt = array_unique($tt);
		if(empty($tt)){
			return 'нет';
		}
		return implode(',', $tt);
		
	}
	
	
	public function updateDepartments($user_id){
		if(!$user_id){
			return _('Нет');
		}
		
		$department = $this->usersPosition[$user_id]['department'];
		if(!empty($department)){
			return $department;
		}
		return _('Нет');		
	}
	
	public function updatePosition($user_id){
		if(!$user_id){
			return _('Нет');
		}
		
		$department = $this->usersPosition[$user_id]['position'];
		if(!empty($department)){
			return $department;
		}
		return _('Нет');		
	}
	
	/**
	 * @params string
	*/
	public function updateGroup($groupIDs){
		if(!$groupIDs || empty($groupIDs)){
			return _('Нет');
		}
		$t = explode(',',$groupIDs);
		$t = array_unique($t);
		if(count($t)){
			return implode(', ',$t);
		}
		return _('Нет');
	}
	

	
	
	public function updateBallDate($user_id, $lesson_id){
		if(isset($this->_lastMsgCache[$lesson_id.'~'.$user_id])){
			return $this->updateDate($this->_lastMsgCache[$lesson_id.'~'.$user_id]['date']);
		}
		return _('Нет');		
	}
	
	public function updateLastMessageType($user_id, $lesson_id){
		if(isset($this->_lastMsgCache[$lesson_id.'~'.$user_id])){
			return $this->getTaskTypeString($this->_lastMsgCache[$lesson_id.'~'.$user_id]['type']);
		}
		return _('Нет');		
	}
	
	public function updateMessage($user_id, $lesson_id){
		if(isset($this->_lastMsgCache[$lesson_id.'~'.$user_id])){
			return strip_tags($this->_lastMsgCache[$lesson_id.'~'.$user_id]['message']);
		}
		return _('Нет');
	}
	
	
	public function getStatusCheck($user_id, $lesson_id){
		
		$no = '<span style="color:red;">Не обработано</span>';
		$yes = 'Обработано';
		
		if(isset($this->_lastMsgCache[$lesson_id.'~'.$user_id])){
			#if($this->_lastMsgCache[$lesson_id.'~'.$user_id]['user_id'] == $user_id){
				return $no;
			#} else {
			#	return $yes;
			#}
		}
		return $yes;		
	}
	
	public function updateFiles($user_id, $lesson_id, $noHtml = false){
		if(isset($this->_lastMsgCache[$lesson_id.'~'.$user_id])){
			if(isset($this->_userFilesCache[$this->_lastMsgCache[$lesson_id.'~'.$user_id]['interview_id']])){
				$files = $this->_userFilesCache[$this->_lastMsgCache[$lesson_id.'~'.$user_id]['interview_id']]['interview_file'];				
				unset($this->_userFilesCache[$this->_lastMsgCache[$lesson_id.'~'.$user_id]['interview_id']]);
				
				$filesArray = explode('|', $files);
				unset($files);
				$filesArray = array_filter($filesArray);
				
				if($noHtml)	{	$result = array();	}
				else 		{	$result = (is_array($filesArray) && (count($filesArray) > 1)) ? array('<p class="total">' . count($filesArray)  . ' файла</p>') : array(); 	}
				foreach($filesArray as $f){
					if($noHtml)	{	$result[] = $f; }
					else 		{	$result[] = "<p>{$f}</p>"; }
				}
				if($result) {
					$delimiter = ($noHtml) ? (', ') : ('');	
					return implode($delimiter, $result); 
				}				
			}			
		}
		return _('Нет');				
	}
	
	
	
	
	
	public function excelAction($data) {
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
    	
		$tmpFileName  = time(); 
		
		
        $this->view->fields = array(			
			'ФИО',				'Подразделение',	'Должность',		'ID группы', 			'E-mail',					'Курсы',				'Дата назначения',		'Дата начала обучения',	'Дата окончания обучения',
			'Дата продления',	'Статус', 			'Оценка',			'Оценка выставлена',	'Тип последнего сообщения', 'Последнее сообщение',	'Статус обработки',		'Файлы',
			'Урок',				'Тьюторы',			'Email тьюторов',	'Преподаватели',		'Кафедра',					'Факультет',			
		);
		
		
		$content =  $this->view->render('staff/export_header.tpl');
		$this->view->content = NULL;				
		$xls = fopen(Zend_Registry::get('config')->path->upload->tmp.'/'.$tmpFileName, 'w');
		fwrite($xls, $content);
		unset($content);
		fclose($xls);
		
		
		$prepareData = array();
		$countRows = 0;
		foreach($data as $key => $i){
			$countRows++;			
			$prepareData[] = array(
                'fio' 				=> $i['fio'],
				'departments'		=> $this->updateDepartments($i['departments']),
				'positions'			=> $this->updatePosition($i['positions']),
				'group_id_external'	=> $this->updateGroup($i['group_id_external']),
				'email'				=> $i['email'],
				'courses'			=> $i['courses'],
				'time_assign'		=> $this->updateDate($i['time_assign']),
				'time_begin'		=> $this->updateDate($i['time_begin']),
				'time_ended'		=> $this->updateDate($i['time_ended']),
				'dete_debtor'		=> $this->updateDate($i['dete_debtor']),
				'status'			=> $this->updateStatus($i['status']),
				'mark'				=> (!empty($i['mark'])) ? (str_replace('.', ',', $i['mark'])) : (''),
				'ball_date'			=> $this->updateBallDate($i['ball_date'], $i['lesson_id']),
				'type_last_message'	=> $this->updateLastMessageType($i['type_last_message'], $i['lesson_id']),
				'last_message_id'	=> substr($this->updateMessage($i['last_message_id'], $i['lesson_id']), 0, 100),
				'is_new'			=> $this->getStatusCheck($i['is_new'], $i['lesson_id']),
				'i_file'			=> $this->updateFiles($i['i_file'], $i['lesson_id'], true),
				'i_lesson_name'		=> $i['i_lesson_name'],
				'tutors'			=> $this->updateTutors($i['tutors'], true),
				'tutor_emails'		=> $this->updateTutorEmails($i['tutor_emails'], true),
				'teacher'			=> $this->updateTeacher($i['teacher'], true),
				't_chair'			=> $this->updateTChair($i['t_chair'], true),
				't_faculty'			=> $this->updateTFaculty($i['t_faculty'], true),  
			);			
			unset($this->_lastMsgCache[$i['lesson_id'].'~'.$i['MID']]);
			unset($data[$key]);
			
			if($countRows % 10000 == 0){ # пишем каждую 10 000 строку				
				$this->view->content = $prepareData;
				$prepareData = array();
				$content =  $this->view->render('staff/export.tpl');
				$this->view->content = NULL;				
				$xls = fopen(Zend_Registry::get('config')->path->upload->tmp.'/'.$tmpFileName, 'a');
				fwrite($xls, $content);
				unset($content);
				fclose($xls);        				
			}
		}
		
		$this->view->content = $prepareData;
		$prepareData = array();
		$content =  $this->view->render('staff/export_footer.tpl');
		$this->view->content = NULL;				
		$xls = fopen(Zend_Registry::get('config')->path->upload->tmp.'/'.$tmpFileName, 'a');
		fwrite($xls, $content);
		unset($content);
		fclose($xls);
		
		
		$this->_lastMsgCache = NULL;
		$this->_userFilesCache = NULL;
		$this->_userInfoCache = NULL;
		unset($data);
		
		$this->getFile(realpath(Zend_Registry::get('config')->path->upload->tmp.'/'.$tmpFileName), 'Обучение моих студентов от '.date('d.m.Y H-i'), 'xls');		
		die;		
    }
	
	
	
	public function getFile($tmpFilePath, $name, $ext = 'doc') {
		if (file_exists($tmpFilePath)) {
			// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
			// если этого не сделать файл будет читаться в память полностью!
			if (ob_get_level()) {
				ob_end_clean();
			}
			switch(true){
            	case $ext == 'doc':
            		$contentType = 'application/word';
            		break;
            	case $ext == 'xls':
            		$contentType = 'application/excel';
            		break;
            	case strpos($this->getRequest()->getHeader('user_agent'), 'opera'):
            		$contentType = 'application/x-download';
            		break;
            	default:
            		$contentType = 'application/unknown';
            }
			
			header('Content-Type: '.$contentType);
			header('Content-Disposition: attachment; filename='.$name.'.'.$ext);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($tmpFilePath));
			// читаем файл и отправляем его пользователю
			readfile($tmpFilePath);
			exit;
		} 		
	}

}