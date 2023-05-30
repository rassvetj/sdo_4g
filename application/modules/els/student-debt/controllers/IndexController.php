<?php
class StudentDebt_IndexController extends HM_Controller_Action
{
    protected $_debtService = null;
	
	private $user_programm_id = null;
	private $user = null;
	private $user_languages = null;
	
    public function init()
    {
		//$this->_ticketID = (int) $this->_getParam('TicketID', 0);
        $this->_debtService = $this->getService('StudentDebt');		
		
        parent::init();
    }
    
    
    public function indexAction()
    {
        $config = Zend_Registry::get('config');
		$this->view->setHeader(_('Мои задолженности'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		//$this->getHelper('viewRenderer')->setNoRender();
		
		$select = $this->_debtService->getIndexSelect();
		
		$user = $this->getService('User')->getCurrentUser();
		
		$user_programm 			= (int)$this->getService('StudyGroupProgramm')->getProgrammIdByUser($user->MID);
		$this->user_programm_id = $user_programm;
		$this->user 			= $user;
		$this->user_languages	= $this->getService('LanguagesAssignBase')->getByUserCode($user->mid_external);
		
		$where = $this->_debtService->quoteInto(			
			array('mid_external=?'),
			array(				
				str_replace(' ', '', $user->mid_external), //--На случай, если встретится код вида "XXX XXX".
			)
		);
		$select->where($where);
		$gridId = 'grid';
		
		if(in_array($user->mid_external, array('653947', '655878', '626167', '644731', '651751', '649476', '648791', '643780', '643940', '655950', '635243'))){
			#$select = $this->createFakeQuery($user->mid_external);
		}
		
		$grid = $this->getGrid(
            $select,
            array(
                'student_debt_id' => array('hidden' => true),              
                'mid_external' 	=> array('hidden' => true),				                                               
                'discipline' 	=> array('title' => _('Дисциплина')),				                               
                'type' 	=> array('title' => _('Тип')),				                               
                #'date_revision' 	=> array('title' => _('Дата проверки')),					
                'date_revision' 	=> array('hidden' => true),					
                'semester' 			=> array('title' => _('Семестр')),					
                
				#'isMarksheet' 		=> array('hidden' => true),
                'date_end' 			=> array('hidden' => true),
                'state' 			=> array('hidden' => true),
				
				'isMarksheet'		=> array(
							'title' => _('Ведомость сдана в деканат'),				
							'callback' => array(
								'function' => array($this, 'updateMarksheetType'),
								'params' => array('{{isMarksheet}}')
							)
				),
				'date_end' 	=> array('title' => _('Нормативный срок продления')),					
				'state' 	=> array('title' => _('Состояние')),
				'alternative_name' 	=> array('hidden' => true),
            ),
            array(
				'discipline' 	=> null,				
				'type' 			=> null,				
				'date_revision' => array('render' => 'DateSmart'),
				'semester' 		=> null,				
				#'isMarksheet' 	=> null,
				#'date_end' 		=> null,
				'isMarksheet' 	=> array('values' => HM_StudentDebt_StudentDebtModel::getMarksheetTypes()),
				'date_end' 		=> array('render' => 'DateSmart'),
				#'state' 		=> array('values' => HM_StudentDebt_StudentDebtModel::getStates()),	
				#'state' 		=> null,
            ),
            $gridId
        );		
		
		$grid->updateColumn('date_end', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())  
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_end}}')
            )
        ));
		
		
		
		$grid->updateColumn('state', array(            
            'callback' => array(
                'function' => array($this, 'updateState'),
                'params' => array('{{state}}', '{{date_end}}', '{{discipline}}', '{{semester}}', '{{type}}', '{{alternative_name}}')
            )
        ));
		
		
		
		$grid->updateColumn('date_revision', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
			'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_revision}}')
            )
        ));
		
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
		$this->view->grid = $grid->deploy();
		$this->view->render('index/index.tpl');
		
    }
	
	
	public function updateDate($date)
    {     	
		if (!strtotime($date)) return '';
		
		$timestamp = strtotime($date);		
		return date('d.m.Y', $timestamp);
    }
	
	
	public function updateMarksheetType($type_id){
		$types = HM_StudentDebt_StudentDebtModel::getMarksheetTypes();
		$type_id = (int)$type_id;
		return $types[$type_id];		
	}
	
	# Если ведомость проведена и нормативный срок не истек, то в ТЕКУЩИХ (Продлена). 
	# Во всех остальных случаях Прошедшие (Не продлена))
	# UPD 03.06.2020
	# Значения: продлена/нет_в_сдо/пусто 
	# Продлена  - если сессия продлена и находится в текущих,  
	# нет в сдо - если в сдо по программе студента не существует такой сессии, т.е. студент будет понимать, что такую сессию ему надо сдавать напрямую преподавателю без сдо. 
	# Пусто     - если сессия не продлена и её нет в текущих
	public function updateState($isMarksheet, $date_end, $discipline_name, $semester, $exam_type, $alternative_name = false)
	{
		
			$discipline_name = trim($discipline_name);
		
			$serviceUser = $this->getService('User');
			
			$user_language_id = $this->getUserLanguageId($semester); 
			
			$select = $serviceUser->getSelect();
			
			
			
			####
			
			$subStudents = $serviceUser->getSelect();
			$subStudents->from(array('Students'),	array('MID','CID', 'SID', 'time_ended_debtor', 'time_ended_debtor_2', 'is_graduated' => new Zend_Db_Expr("NULL") ));
			$subStudents->where('MID = ?', intval($this->user->MID));
				
			$subGraduated = $serviceUser->getSelect();
			$subGraduated->from(array('graduated'),	array('MID','CID', 'SID', 'time_ended_debtor' => new Zend_Db_Expr("NULL"), 'time_ended_debtor_2' => new Zend_Db_Expr("NULL"), 'is_graduated' => new Zend_Db_Expr("1") ));
			$subGraduated->where('MID = ?', intval($this->user->MID));
				
			$subSelect = $serviceUser->getSelect();
			$subSelect->union(array($subStudents, $subGraduated));
			
			####
			
			
			$select->from(array('subj' => 'subjects'), array(
				'subject_id'					=> 'subj.subid',
				'subject_end'					=> 'subj.end',
				'subject_is_practice'			=> 'subj.is_practice',
				'subject_semester'				=> 'subj.semester',
				'subject_language_code'			=> 'subj.language_code',
				'assign_id'						=> 'st.SID',
				'assign_time_ended_debtor'		=> 'st.time_ended_debtor',
				'assign_time_ended_debtor_2'	=> 'st.time_ended_debtor_2',				
				'assign_time_ended_debtor_2'	=> 'st.time_ended_debtor_2',				
				'assign_is_graduated'	        => 'st.is_graduated',				
			));
			
			$select->join(array('pe'		=> 'programm_events'),	'pe.item_id = subj.subid', array());
			
			
			$select->joinLeft(array('st' => $subSelect), 'st.CID = subj.subid AND st.MID = '.intval($this->user->MID), array());
			#$select->joinLeft(array('st' => 'Students'), 'st.CID = subj.subid AND st.MID = '.intval($this->user->MID), array());
			
			
			
			$select->where($serviceUser->quoteInto('pe.type 		= ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
			$select->where($serviceUser->quoteInto('pe.programm_id 	= ?', intval($this->user_programm_id)));
			$select->where($serviceUser->quoteInto('subj.exam_type = ?', $this->getExamTypeId($exam_type)));
			
			if(empty($alternative_name)){
				$select->where($serviceUser->quoteInto('subj.name LIKE LOWER(?)', '%' . $discipline_name  . '%'));
			} else {
				$select->where($serviceUser->quoteInto(
					array(' ( subj.name LIKE LOWER(?) ', ' OR subj.name LIKE LOWER(?) ) '), 
					array('%' . $discipline_name  . '%', '%' . $alternative_name  . '%')
				));
			}
			
			$res = $select->query()->fetchAll();
			
			if(empty($res)){
				return 'Нет в СДО';
			}
			
			$subject_found = false;
			$subject_id    = false;
			
			foreach($res as $subject){
				
				if($subject['subject_is_practice']!=1 && $subject['subject_semester']!=$semester){ continue; }
				
				# Если это языковая сессия, то должен совпадать язык студента семестра сессии.
				if(
					!empty($subject['subject_language_code'])
					&&
					$subject['subject_language_code'] != $user_language_id
				){
					continue;					
				}
				
				$subject_found = true;	

				if(empty($subject['assign_id'])){ continue; }
				
				if(
					( date('Y-m-d', strtotime($subject['subject_end'])) > date('Y-m-d') )
					&& 
					empty($subject['assign_is_graduated'])
				){
					return 'В текущих';
				}
				
				if(date('Y-m-d', strtotime($subject['assign_time_ended_debtor'])) > date('Y-m-d')){
					return 'Продлена';
				}
				
				if(date('Y-m-d', strtotime($subject['assign_time_ended_debtor_2'])) > date('Y-m-d')){
					return 'Продлена';
				}
				$subject_id = $subject['subject_id'];
			}
			
			if(empty($subject_found)){
				return 'Нет в СДО';
			}
			
			if($subject_id){
				$ball = $this->getBall($subject_id, $this->user->MID);
				return $this->filterBall($ball);
			}
			return '';
		
	}
	
	public function getUserLanguageId($semester = false)
	{
		return (int)$this->user_languages->exists('semester', $semester)->language_id;		
	}
	
	public function getExamTypeId($exam_type)
	{
		$types = array(
			'Приём экзаменов' 													=> HM_Subject_SubjectModel::EXAM_TYPE_EXAM,
			'Приём зачётов' 													=> HM_Subject_SubjectModel::EXAM_TYPE_TEST,
			'Приём дифференцированных зачетов' 									=> HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK,
			'Прием дифференцированных зачетов по преддипломной практике' 		=> HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK,
			'Прием дифференцированных зачетов по производственной практике' 	=> HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK,
			'Прием дифференцированных зачетов по учебной практике' 				=> HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK,
			'Контроль самостоятельной работы' 									=> HM_Subject_SubjectModel::EXAM_TYPE_INDEPENDENT_WORK,
			'Курсовой проект' 													=> HM_Subject_SubjectModel::EXAM_TYPE_NONE, 
			
			'Практика'															=> HM_Subject_SubjectModel::EXAM_TYPE_NONE,
			'Курсовая работа'													=> HM_Subject_SubjectModel::EXAM_TYPE_NONE,
			'Экзамен'															=> HM_Subject_SubjectModel::EXAM_TYPE_EXAM,
			'Зачет'																=> HM_Subject_SubjectModel::EXAM_TYPE_TEST,
			'Контрольная работа'												=> HM_Subject_SubjectModel::EXAM_TYPE_NONE,
		);
		return intval($types[$exam_type]);
	}
	
	private function createFakeQuery($mid_external)
	{
		$select    = $this->_debtService->getSelect();
		
		$fake_data = $this->getFakeData($mid_external);
		if(empty($fake_data)){ return false; }
		
		$select->from(
			array('t1' => 'student_debts'),
			array(
				'semester'     		=> 't2.semester',
				'discipline'    	=> 't2.discipline',
				'student_debt_id' 	=> 't2.student_debt_id',
				'mid_external'    	=> 't2.mid_external',
				'type'     			=> 't2.type',
				'date_revision'     => 't2.date_revision',
				'isMarksheet'     	=> 't2.isMarksheet',
				'date_end'     		=> 't2.date_end',
				'state'     		=> 't2.isMarksheet',                
			)
		);
		$select->where('t2.mid_external = ?', $mid_external);			
		$select->joinRight(array('t2' => new Zend_Db_Expr('('.$fake_data.')')), 't2.student_debt_id = t1.student_debt_id', array());
		
		return $select;
	
	}
	
	private function getFakeData($mid_external)
	{
		$data = array(
			'653947' => " SELECT '653947' AS mid_external, '5' AS semester, 'Элективные курсы по физической культуре и спорту'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '4' AS semester, 'Технологии самоорганизации и эффективного взаимодействия'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '4' AS semester, 'Иностранный язык'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '5' AS semester, 'Иностранный язык в профессиональной деятельности'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '5' AS semester, 'Элективные курсы по физической культуре и спорту'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '4' AS semester, 'Технологии самоорганизации и эффективного взаимодействия'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '4' AS semester, 'Иностранный язык'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					UNION SELECT '653947' AS mid_external, '5' AS semester, 'Иностранный язык в профессиональной деятельности'	  AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
				",
			'655878' => " SELECT '655878' AS mid_external, 	1 AS semester, 'Экономика' 													AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Обществознание (включая право)' 							AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Основы безопасности жизнедеятельности' 						AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Математика' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Естествознание' 											AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Иностранный язык' 											AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Физическая культура' 										AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Астрономия' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Русский язык' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Литература' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'История' 													AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'История' 													AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Обществознание (включая право)' 							AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Естествознание' 											AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Основы безопасности жизнедеятельности' 						AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Экономика' 													AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Математика' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Физическая культура' 										AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Астрономия' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Литература' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Русский язык' 												AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655878' AS mid_external, 	1 AS semester, 'Иностранный язык' 											AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					",
			'626167' => " SELECT '626167' AS mid_external, 6 AS semester, 'Второй иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 							
					UNION SELECT '626167' AS mid_external, 6 AS semester, 'Второй иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 							
					UNION SELECT '626167' AS mid_external, 6 AS semester, 'Практикум по культуре речевого общения второго иностранного языка' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 							
					UNION SELECT '626167' AS mid_external, 6 AS semester, 'Практикум по культуре речевого общения второго иностранного языка' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					",
			'644681' => " SELECT '644681' AS mid_external, 	5	 AS semester, 'Третий иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '644681' AS mid_external, 	5	 AS semester, 'Второй иностранный язык (язык региона)' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 									
					UNION SELECT '644681' AS mid_external, 	4	 AS semester, 'Исследование социально-экономических и политических процессов в регионе' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 									
					UNION SELECT '644681' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '644681' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '644681' AS mid_external, 	5	 AS semester, 'Третий иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					",
			'644731' => " SELECT '644731' AS mid_external, 	5	 AS semester, 'Политический анализ и прогнозирование' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 							
					UNION SELECT '644731' AS mid_external, 	5	 AS semester, 'Политический анализ и прогнозирование' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 							
					",
			'651751' => " SELECT '651751' AS mid_external, 	3	 AS semester, 'Практика по получению первичных профессиональных умений и навыков' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '651751' AS mid_external, 	3	 AS semester, 'Практика по получению первичных профессиональных умений и навыков' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					",
			'649476' => " SELECT '649476' AS mid_external, 	3	 AS semester, 'Методика и методология исследований в сфере коммуникаций' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	3	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	3	 AS semester, 'Философия' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	3	 AS semester, 'Введение в коммуникацию и профессиональные творческие мастерские' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Безопасность жизнедеятельности' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Копирайтинг и технологии современной прессы' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	3	 AS semester, 'История и теория журналистики' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Психотехнологии коммуникативного менеджмента' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	2	 AS semester, 'Введение в коммуникацию и профессиональные творческие мастерские' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '649476' AS mid_external, 	3	 AS semester, 'Психотехнологии коммуникативного менеджмента' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					",
			'648791' => " SELECT '648791' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '648791' AS mid_external, 	5	 AS semester, 'Политический анализ и прогнозирование' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '648791' AS mid_external, 	4	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '648791' AS mid_external, 	5	 AS semester, 'Международное гуманитарное сотрудничество' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '648791' AS mid_external, 	5	 AS semester, 'Политический анализ и прогнозирование' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					UNION SELECT '648791' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 													
					",
			'643780' => " SELECT '643780' AS mid_external, 	4	 AS semester, 'Второй иностранный язык (язык региона)' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					UNION SELECT '643780' AS mid_external, 	5	 AS semester, 'Второй иностранный язык (язык региона)' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					UNION SELECT '643780' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					UNION SELECT '643780' AS mid_external, 	5	 AS semester, 'Элективные курсы по физической культуре и спорту' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					",
			'643940' => " SELECT '643940' AS mid_external, 	2	 AS semester, 'Научные исследования в профессиональной деятельности' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					UNION SELECT '643940' AS mid_external, 	2	 AS semester, 'Научные исследования в профессиональной деятельности' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 											
					",
			'655950' => " SELECT '655950' AS mid_external, 	1	 AS semester, 'История и онтология науки' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Первый иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Практика по получению профессиональных умений и опыта профессиональной деятельности' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Технологии профессиональной коммуникации и самоорганизации' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Современные теории и методы социального управления' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Практический курс перевода (второй иностранный язык)' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'Первый иностранный язык' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					UNION SELECT '655950' AS mid_external, 	1	 AS semester, 'История и онтология науки' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state 								
					",
			'635243' => " SELECT '635243' AS mid_external, 	9	 AS semester, 'Преддипломная практика' AS discipline, NULL AS student_debt_id, NULL AS type, NULL AS date_revision, 1 AS isMarksheet, NULL AS date_end, NULL AS state
					",
		);
		return $data[$mid_external];
	}
	
	public function getBall($subject_id, $student_id)
	{
		return $this->getService('SubjectMark')->getBall($subject_id, $student_id);
	}
	
	private function filterBall($ball)
	{
		return ($ball < (HM_Lesson_LessonModel::PASS_LESSON_PERCENT) * 100) ? false : $ball;		
	}
	
    
}