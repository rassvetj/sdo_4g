<?php


class Report_DebtStudentController extends HM_Controller_Action_Crud
{
	
    private $_list_users 	= array();
	private $_subjects		= array(); # кэш сессий
	private $_assigns		= array(); # кэш назначений студентов на сессии
	private $_balls			= array(); # кэш оценок студентов
	private $_language_assigns = null; # распределения языков студентов
	
	private $_count_db    = 0;
	private $_count_cache = 0;
	
	const CACHE_NAME = 'Report_DebtStudentController';
	
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет об академических долгах студента'));
    }
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 '_subjects'	=> $this->_subjects,                 			 
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_subjects	= $actions['_subjects'];
            return true;
        }

        return false;
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_DebtStudent();	

		$this->clearCache();		
    }
	
	public function getAction()
    {		
		$serviceUser = $this->getService('User');
		$select 		= $serviceUser->getSelect();
		$select_sub 	= $serviceUser->getSelect();
		
		$this->loadSubjects();
		$this->loadAssigns();
		$this->loadBalls();
		$this->loadLanguagesAssigns();
		
		$fields = array(
			'student_id' 				=> 'p.MID',
			'student_mid_external' 		=> 'p.mid_external',
			'student_fio'				=> new Zend_Db_Expr("p.LastName + ' ' + p.FirstName + ' ' + p.Patronymic"),
			
			'debt_discipline' 			=> 'debt.discipline',
			'debt_form_control' 		=> 'debt.type',
			'debt_semester'				=> 'debt.semester',
			'debt_alternative_name'		=> 'debt.alternative_name',
	  
			'group_id_external'			=> 'sg.id_external',
			'group_name'		 		=> 'sg.name',
	  
			'programm_id' 				=> 'pr.programm_id',
			'programm_id_external' 		=> 'pr.id_external',
			'programm_name'				=> 'pr.name',
			'debt_status'				=> 'p.MID',
			'subject_id'				=> 'p.MID',
		);
	  
		$fields_sub = array(
			'mid_external',
			'discipline',
			'alternative_name',
			'type',
			'semester',			
		);
		$select_sub->from(array('debt' 	=> 'student_debts'), $fields_sub);
		
		$select->from(array('p' 	=> 'People'), $fields);
		$select->join(array('sgc' 	=> 'study_groups_custom'), 	  	'sgc.user_id  	= p.MID', array());
		$select->join(array('sg' 	=> 'study_groups'), 		  	'sg.group_id  	= sgc.group_id', array());
		$select->join(array('sgp' 	=> 'study_groups_programms'), 	'sgp.group_id 	= sgc.group_id', array());
		$select->join(array('pr' 	=> 'programm'), 				'pr.programm_id = sgp.programm_id', array());	
		$select->join(array('debt' 	=> $select_sub), 				'debt.mid_external = p.mid_external', array());
		$select->where($serviceUser->quoteInto('p.blocked != ?',  HM_User_UserModel::STATUS_BLOCKED));

		
		
		$grid = $this->getGrid(
            $select,
            array(
				'student_id' 				=> array('title' => _('Студент. ID')),
				'student_mid_external' 		=> array('title' => _('Студент. Код')),
				'student_fio'				=> array('title' => _('Студент')),
				
				'debt_discipline' 			=> array('title' => _('Долг. Дисциплина')),
				'debt_form_control' 		=> array('title' => _('Долг. Фома контроля')),
				'debt_semester'				=> array('title' => _('Долг. Семестр')),
				
				'group_id_external'			=> array('title' => _('Группа. Код')),
				'group_name'		 		=> array('title' => _('Группа')),
		  
				'programm_id' 				=> array('title' => _('Программа. ID')),
				'programm_id_external' 		=> array('title' => _('Программа. Код')),
				'programm_name'				=> array('title' => _('Программа')),
				'debt_status'				=> array('title' => _('Состояние')),
				'subject_id'				=> array('title' => _('Сессия. ID')),
				'debt_alternative_name' 	=> array('hidden' => true),
            ),
            array(
				'student_id'			=> null,
				'student_mid_external'	=> null,
				'student_fio'			=> null,
				'debt_discipline'		=> null,
				'debt_form_control'		=> null,
				'debt_semester'			=> null,
				'group_id_external'		=> null,
				'group_name'			=> null,
				'programm_id'			=> null,
				'programm_id_external'	=> null,
				'programm_name'			=> null,
            )
        );
		
		
		$grid->updateColumn('debt_status', array(
				'callback' => array(
					'function' => array($this, 'updateDebtStatus'),
					'params' => array('{{student_id}}', '{{debt_discipline}}', '{{debt_form_control}}', '{{debt_semester}}', '{{programm_id}}', '{{debt_alternative_name}}' )
				)
			)
		);
		
		$grid->updateColumn('subject_id', array(
				'callback' => array(
					'function' => array($this, 'updateSubject'),
					'params' => array('{{student_id}}', '{{debt_discipline}}', '{{debt_form_control}}', '{{debt_semester}}', '{{programm_id}}', '{{debt_alternative_name}}' )
				)
			)
		);
		
		
		
		
		try {
			$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
			$this->view->grid 			 = $grid->deploy();		
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";
		}
		
		
		
		
		
	}
	
	public function updateSubject($student_id, $debt_discipline, $debt_form_control, $debt_semester, $programm_id, $debt_alternative_name = false)
	{
		$debt_discipline = trim($debt_discipline);
			
		$debt_form_control_id 	= $this->getExamTypeId($debt_form_control);
		$subjects 				= $this->getSubjects($debt_discipline, $debt_form_control_id, $debt_semester, $programm_id, $debt_alternative_name);
		$assigns 				= $this->getAssigns($student_id);
		
		$user_language_id = $this->getUserLanguageId($student_id, $debt_semester); 
		
		if(empty($subjects)){ return ''; }
		
		$subject_found = false;
		$subject_id = false;
		foreach($subjects as $subject){
				
			# Если это языковая сессия, то должен совпадать язык студента семестра сессии.
			if(
				!empty($subject['language_code'])
				&&
				$subject['language_code'] != $user_language_id
			){
				continue;					
			}
			
			$subject_found = true;
			
			$assign_current = $assigns[ $subject['id'] ];
				
			if(empty($assign_current)){ continue; }
				
			if(date('Y-m-d', strtotime($subject['end'])) > date('Y-m-d')){ 
				return $subject['id'];
			}
				
			if(date('Y-m-d', strtotime($assign_current['assign_time_ended_debtor'])) > date('Y-m-d')){
				return $subject['id'];
			}
				
			if(date('Y-m-d', strtotime($assign_current['assign_time_ended_debtor_2'])) > date('Y-m-d')){
				return $subject['id'];
			}
			$subject_id = $subject['id'];
		}
		
		if(empty($subject_found)){
			return '';
		}
		
		return $subject_id;
	}
	
	
	public function updateDebtStatus($student_id, $debt_discipline, $debt_form_control, $debt_semester, $programm_id, $debt_alternative_name = false)
	{
		$debt_discipline = trim($debt_discipline);
			
		$debt_form_control_id 	= $this->getExamTypeId($debt_form_control);
		$subjects 				= $this->getSubjects($debt_discipline, $debt_form_control_id, $debt_semester, $programm_id, $debt_alternative_name);
		$assigns 				= $this->getAssigns($student_id);
		
		$user_language_id = $this->getUserLanguageId($student_id, $debt_semester); 
		
		if(empty($subjects)){ return 'Нет в СДО'; }
		
		$subject_found = false;
		$subject_id = false;
		foreach($subjects as $subject){
				
			# Если это языковая сессия, то должен совпадать язык студента семестра сессии.
			if(
				!empty($subject['language_code'])
				&&
				$subject['language_code'] != $user_language_id
			){
				continue;					
			}
			
			$subject_found = true;
			
			$assign_current = $assigns[ $subject['id'] ];
				
			if(empty($assign_current)){ continue; }
				
			if(
				date('Y-m-d', strtotime($subject['end'])) > date('Y-m-d')
				&&
				empty($assign_current['assign_is_graduated'])
			){ 
				return 'В текущих ';
			}
				
			if(date('Y-m-d', strtotime($assign_current['assign_time_ended_debtor'])) > date('Y-m-d')){
				return 'Продлена ';
			}
				
			if(date('Y-m-d', strtotime($assign_current['assign_time_ended_debtor_2'])) > date('Y-m-d')){
				return 'Продлена ';
			}
			$subject_id = $subject['id'];
		}
		
		if(empty($subject_found)){
			return 'Нет в СДО';
		}
		
		if($subject_id){
			$ball = $this->getBall($subject_id, $student_id);			
			return $this->filterBall($ball);
		}
		return '';
	}
	
	
	# для сокращения кол-ва запросов к БД отбор идет только на названию сессии. А уже из кучи данных помещаем в кэш, разделяя по всем остальным полям.
	public function getSubjects($name, $form_control, $semester, $programm_id, $alternative_name = '')
	{
		$key = $this->getKey(array($form_control, $programm_id));
		
		if(!array_key_exists($key, $this->_subjects)){
			return false;
		}
		
		$subjects_raw = $this->_subjects[$key];
		
		$subjects = array();
		foreach($subjects_raw as $i){
			if($i['is_practice']!=1 && $i['semester']!=$semester){ continue; }
			
			if(
				$i['form_control'] == $form_control 
				&& 
				$i['programm_id'] == $programm_id
				&&
				(
					(!empty($name) && stripos($i['name'], $name) !== false)
					|| 
					(!empty($alternative_name) && stripos($i['name'], $alternative_name) !== false)
				)
			){
				$subjects[$i['id']] = $i;
			}
		}
		return $subjects;
	}
	
	
	# Группируем по ключу, чтобы не перебирать весь массив, а только определенные записи.
	private function loadSubjects()
	{
		$this->restoreFromCache();
		if(!empty($this->_subjects)){
			return true;
		}
		
		$serviceUser = $this->getService('User');
			
		$select = $serviceUser->getSelect();
		
		$select->from(array('subj' => 'subjects'), array(
			'id'					=> 'subj.subid',
			'end'					=> 'subj.end',
			'name'					=> 'subj.name',
			'semester'				=> 'subj.semester',
			'form_control'			=> 'subj.exam_type',
			'is_practice'			=> 'subj.is_practice',
			'language_code'			=> 'subj.language_code',
			'programm_id'			=> 'pe.programm_id',
		));
		
		$select->join(array('pe'		=> 'programm_events'),	'pe.item_id = subj.subid', array());
		
		$select->where($serviceUser->quoteInto('pe.type 		= ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
		
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		
		# т.к. сессии находим поч частичному совпадению, группируем их безучета названия
		foreach($res as $i){
			$key 								= $this->getKey(array($i['form_control'], $i['programm_id']));			
			$this->_subjects[$key][$i['id']] 	= $i;
		}
		unset($res);
		
		$this->saveToCache();
		return true;
	}
	
	private function loadAssigns()
	{
		$serviceUser	= $this->getService('User');
		
		$select		= $serviceUser->getSelect();
		$select_sub = $serviceUser->getSelect();
		
		
		####
			
			$subStudents = $serviceUser->getSelect();
			$subStudents->from(array('Students'),	array('MID','CID', 'SID', 'time_ended_debtor', 'time_ended_debtor_2', 'is_graduated' => new Zend_Db_Expr("NULL")));
				
			$subGraduated = $serviceUser->getSelect();
			$subGraduated->from(array('graduated'),	array('MID','CID', 'SID', 'time_ended_debtor' => new Zend_Db_Expr("NULL"), 'time_ended_debtor_2' => new Zend_Db_Expr("NULL"), 'is_graduated' => new Zend_Db_Expr("1") ));
				
			$subSelect = $serviceUser->getSelect();
			$subSelect->union(array($subStudents, $subGraduated));
			
		####
		
		# для получения всех назначений обрабатываемых студентов
		$fields = array(
			'student_id'					=> 'p.MID',
			'subject_id'					=> 'st.CID',
			'assign_time_ended_debtor'		=> 'st.time_ended_debtor',
			'assign_time_ended_debtor_2'	=> 'st.time_ended_debtor_2',
			'assign_is_graduated'	        => 'st.is_graduated',
		);
		
		$select_sub->from(array('debt' 	=> 'student_debts'), array('mid_external'));
		$select_sub->group(array('mid_external'));
		
		$select->from(array('p' 	=> 'People'), $fields);		
		$select->join(array('debt' 	=> $select_sub), 	'debt.mid_external = p.mid_external', array());
		
		
		#$select->join(array('st' 	=> 'Students'),  'st.MID = p.MID', array());
		$select->joinLeft(array('st' => $subSelect), 'st.MID = p.MID', array());
		
		$select->where($serviceUser->quoteInto('p.blocked != ?',  HM_User_UserModel::STATUS_BLOCKED));
		#$select->where($serviceUser->quoteInto('p.role_1c = ?',  HM_User_UserModel::ROLE_1C_STUDENT));
		$select->group(array('p.MID', 'st.CID', 'st.time_ended_debtor', 'st.time_ended_debtor_2', 'st.is_graduated'));
		
		$res = $select->query()->fetchAll();
				
		if(empty($res)){
			return false;
		}
			
		foreach($res as $i){
			$this->_assigns[$i['student_id']][$i['subject_id']] = $i;
		}
		
		unset($res);
		return true;
	}
	
	
	private function loadBalls()
	{
		$serviceUser	= $this->getService('User');
		
		$select		= $serviceUser->getSelect();
		$select_sub = $serviceUser->getSelect();
		
		$fields = array(
			'student_id'	=> 'p.MID',
			'subject_id'	=> 'cm.cid',
			'mark'			=> 'cm.mark',
			'mark_current'	=> 'cm.mark_current',
			'mark_landmark'	=> 'cm.mark_landmark',
		);
		
		$select_sub->from(array('debt' 	=> 'student_debts'), array('mid_external'));
		$select_sub->group(array('mid_external'));
		
		$select->from(array('p' 	=> 'People'), $fields);		
		$select->join(array('debt' 	=> $select_sub), 	'debt.mid_external = p.mid_external', array());
		$select->join(array('cm' 	=> 'courses_marks'), 	'cm.mid = p.MID', array());
		
		$select->where($serviceUser->quoteInto('p.blocked != ?',  HM_User_UserModel::STATUS_BLOCKED));
		$select->group(array('p.MID', 'cm.cid', 'cm.mark', 'cm.mark_current', 'cm.mark_landmark'));
		
		$res = $select->query()->fetchAll();
				
		if(empty($res)){
			return false;
		}
			
		foreach($res as $i){			
			$model = new HM_Subject_Mark_MarkModel($i);
			$this->_balls[$i['student_id']][$i['subject_id']] = $model->getBall();
		}		
		unset($res);
		return true;		
	}
	
	
	private function loadLanguagesAssigns()
	{
		$serviceUser	= $this->getService('User');
		
		$select		= $serviceUser->getSelect();
		
		$fields = array(
			'student_id'	=> 'p.MID',
			'language_id'	=> 'sl.language_id',
			'semester'		=> 'sl.semester',
		);
		
		$select->from(array('p' 	=> 'People'), $fields);
		$select->join(array('sl' 	=> 'Students_language'), 	'sl.mid_external = p.mid_external', array());
		
		$select->where($serviceUser->quoteInto('p.blocked != ?',  HM_User_UserModel::STATUS_BLOCKED));
		$select->group(array('p.MID', 'sl.language_id', 'sl.semester'));
		
		$res = $select->query()->fetchAll();
				
		if(empty($res)){
			return false;
		}
			
		foreach($res as $i){			
			$this->_language_assigns[$i['student_id']][$i['semester']] = $i;
		}		
		unset($res);		
		
		return true;
	}
	
	
	public function getKey($params)
	{
		return md5(implode('~', $params));
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
	
	public function getAssigns($student_id)
	{
		return $this->_assigns[intval($student_id)];
	}
	
	public function getBall($subject_id, $student_id)
	{
		return $this->_balls[$student_id][$subject_id];		
	}
	
	private function filterBall($ball)
	{
		return ($ball < (HM_Lesson_LessonModel::PASS_LESSON_PERCENT) * 100) ? false : $ball;		
	}
	
	public function print_mem($text = false)
	{
	   echo $text . ':' . round(memory_get_usage() / (1024*1024)) . ' Мб<br>';	   
	}
	
	public function getUserLanguageId($student_id, $semester)
	{
		return intval($this->_language_assigns[$student_id][$semester]['language_id']);
	}
		
	
	
}