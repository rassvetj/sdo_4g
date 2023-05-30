<?php


class Report_AssignStudentController extends HM_Controller_Action_Crud
{
	
    private $_list_users = array();
	
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет о назначении студентов'));
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_AssignStudent();		
    }
	
	public function getAction()
    {		
		
		$request		= $this->getRequest();
		$with_students 	= (int)$request->getParam('with_students', false);
		$date_end		= $request->getParam('date_end', false);
		$date_end 		= str_replace('%2E', '.', $date_end);
		$date_end 		= str_replace('_', '.', $date_end);
		$date_end 		= strtotime($date_end);
		
		
		if($date_end < 0){
			echo 'Укажите дату';
			die;
		}
		$date_end = date('Y-m-d', $date_end);
		
		$serviceUser = $this->getService('User');		
		
		$fields = array(
			'learning_subject_name' 			=> 'ls.name',
			'learning_subject_id_external' 		=> 'subj.learning_subject_id_external',
			'subject_id' 						=> 'subj.subid',
			'subject_external_id' 				=> 'subj.external_id',
			'subject_name' 						=> 'subj.name',
			'subject_exam_type'					=> 'subj.exam_type',
			'tutors'							=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT p.MID)"),
			'group_id_external' 				=> 'sg.id_external',
			'group_name' 						=> 'sg.name',
			'programm_id_external' 				=> 'prog.id_external',
			'programm_name' 					=> 'prog.name',
			'students_unassign'					=> 'subj.subid',
			'students_all'						=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT sgc.user_id)"),
			'students_assign'					=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT st.MID)"),
			
			'begin'								=> 'subj.begin',
			'end'								=> 'subj.end',
			
		);
		
		$select 		= $serviceUser->getSelect();
		$selectLight 	= $serviceUser->getSelect();
		
		$select->from(array('subj' => 'subjects'), $fields);
		$selectLight->from(array('subj' => 'subjects'), array('subject_id' => 'subj.subid'));
		
		$select->joinLeft(array('ls' 	=> 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array());
		$selectLight->joinLeft(array('ls' 	=> 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array());
		
		$select->joinLeft(array('t' 	=> 'Tutors'), 't.CID = subj.subid', array());	
		
		$select->joinLeft(array('p' 	=> 'People'), 'p.MID = t.MID', array());	
		
		$select->joinLeft(array('pe' 	=> 'programm_events'), 'pe.item_id = subj.subid AND pe.type=1', array());	
		$selectLight->joinLeft(array('pe' 	=> 'programm_events'), 'pe.item_id = subj.subid AND pe.type=1', array());	
		
		$select->joinLeft(array('prog' 	=> 'programm'), 'prog.programm_id = pe.programm_id', array());	
		$selectLight->joinLeft(array('prog' => 'programm'), 'prog.programm_id = pe.programm_id', array());	
		
		$select->joinLeft(array('sgp' 	=> 'study_groups_programms'), 'sgp.programm_id = prog.programm_id', array());	
		$selectLight->joinLeft(array('sgp' 	=> 'study_groups_programms'), 'sgp.programm_id = prog.programm_id', array());	
		
		$select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgp.group_id', array());	
		$selectLight->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgp.group_id', array());	
		
		$select->joinLeft(array('sgc' 	=> 'study_groups_custom'), 'sgc.group_id = sg.group_id', array());	
		$selectLight->join(array('sgc' 	=> 'study_groups_custom'), 'sgc.group_id = sg.group_id', array());	
		
		
		$select->joinLeft(array('st' 	=> 'Students'), 'st.MID = sgc.user_id AND st.CID = subj.subid', array());	
		$selectLight->joinLeft(array('st' 	=> 'Students'), 'st.MID = sgc.user_id AND st.CID = subj.subid', array());	
		
		  
		$select->where($serviceUser->quoteInto('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION));	
		$selectLight->where($serviceUser->quoteInto('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION));	
		
		$select->where("(subj.time_ended_debt IS NULL OR subj.time_ended_debt = '')  AND  (subj.time_ended_debt_2 IS NULL OR subj.time_ended_debt_2 = '' )");
		$selectLight->where("(subj.time_ended_debt IS NULL OR subj.time_ended_debt = '')  AND  (subj.time_ended_debt_2 IS NULL OR subj.time_ended_debt_2 = '' )");
		
		$select->where($serviceUser->quoteInto('subj.end >= ?', $date_end));		
		$selectLight->where($serviceUser->quoteInto('subj.end >= ?', $date_end));		
		
		$select->where($serviceUser->quoteInto('subj.begin <= ?', $date_end));		
		$selectLight->where($serviceUser->quoteInto('subj.begin <= ?', $date_end));	

		$selectLight->where('st.MID IS NULL');			
		
		
		
		$select->group(array('ls.name', 'subj.learning_subject_id_external', 'subj.subid', 'subj.external_id', 'subj.name', 
								'subj.learning_subject_id_external', 'subj.semester', 'subj.faculty', 'subj.exam_type', 
								'prog.id_external', 'prog.name ', 'sg.id_external', 'sg.name', 'subj.exam_type', 'subj.begin', 'subj.end'
								));
		$selectLight->group(array('subj.subid'));	
		
		
		
		#pr( $selectLight->assemble() );
		#die;
		
		#$with_students_sbject_ids = array();
		
		if(!empty($with_students)){
			#$res  = $selectLight->query()->fetchAll();
			#if(!empty($res)){
			#	foreach($res as $i){
				#	$with_students_sbject_ids[$i['subject_id']] = $i['subject_id'];
				#}
			#}
			
			#if(empty($with_students_sbject_ids)){
			#	$select->where('1=0');
			#} else {
				$select->having(new Zend_Db_Expr("GROUP_CONCAT(DISTINCT sgc.user_id)") . '!=' . new Zend_Db_Expr("GROUP_CONCAT(DISTINCT st.MID)"));
				//$select->where($serviceUser->quoteInto('subj.subid IN (?)', $with_students_sbject_ids));
			#}
		}
		
		
		#pr( $select->assemble() );
		#die;
		
		$grid = $this->getGrid(
            $select,
            array(
                'subject_id'						=> array('hidden' => true),
				'subject_exam_type'					=> array('title' => _('Контроль')),
				'learning_subject_name'				=> array('title' => _('Предмет')),
				'learning_subject_id_external'		=> array('title' => _('Предмет. Код')),
				'subject_external_id'				=> array('title' => _('Сессия. Код')),
				'subject_name'						=> array('title' => _('Сессия')),
				'tutors'							=> array('title' => _('Тьюторы')),
				'group_id_external'					=> array('title' => _('Гуппа. Код')),
				'group_name'						=> array('title' => _('Гуппа')),
				'programm_id_external'				=> array('title' => _('Программа. Код')),
				'programm_name'						=> array('title' => _('Программа')),
				'students_unassign'					=> array('title' => _('Студенты не назначены')),
				'students_all'						=> array('hidden' => true),
				'students_assign'					=> array('hidden' => true),
               	'begin'								=> array('title' => _('Сессия. Начало')),
				'end'								=> array('title' => _('Сессия. Конец')),
							
            ),
            array(
				'subject_exam_type'				=> array('values' => HM_Subject_SubjectModel::getExamTypes()),            
                'learning_subject_name'			=> null,              
                'learning_subject_id_external'	=> null,              
                'subject_external_id'			=> null,              
                'subject_name'					=> null,              
                'group_id_external'				=> null,              
                'group_name'					=> null,              
                'programm_id_external'			=> null,              
                'programm_name'					=> null,              
                'begin'				=> array('render' => 'SubjectDate'),           
                'end'				=> array('render' => 'SubjectDate'),              
                'subject_semester'				=> null, 
            )
        );
		
		$grid->updateColumn('tutors', array(
				'callback' => array(
					'function' => array($this, 'updateTutors'),
					'params' => array('{{tutors}}')
				)
			)
		);
		
		$grid->updateColumn('students_unassign', array(
				'callback' => array(
					'function' => array($this, 'updateUnassignStudents'),
					'params' => array('{{students_all}}', '{{students_assign}}' )
				)
			)
		);
		
		$grid->updateColumn('subject_exam_type', array(
				'callback' => array(
					'function' => array($this, 'updateExamType'),
					'params' => array('{{subject_exam_type}}' )
				)
			)
		);
		
		$grid->updateColumn('begin', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{begin}}')
            )
        ));
		
		$grid->updateColumn('end', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{end}}')
            )
        ));
		
		
		try {
			$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
			$this->view->grid 			 = $grid->deploy();		
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";
		}
		
	}
	
	public function updateDate($date)
    {
		return $date;
	}
	
	
	public function updateExamType($exam_type)
	{
		$list = HM_Subject_SubjectModel::getExamTypes();
		return $list[intval($exam_type)];
	}
	
	public function updateUnassignStudents($students_all, $students_assign)
	{
		$students_all = explode(',', $students_all);
		$students_all = array_map('intval', $students_all);
		$students_all = array_filter($students_all);
		
		$students_assign = explode(',', $students_assign);
		$students_assign = array_map('intval', $students_assign);
		$students_assign = array_filter($students_assign);
		
		$unassign_students = array_diff($students_all, $students_assign);
		if(empty($unassign_students)){ return _('нет'); }
		$list = array();
		foreach($unassign_students as $student_id){
			$fio = $this->getPeopleFio($student_id, $unassign_students);
			if(empty($fio)){ continue; }
			$list[$student_id] = '<p>' . $fio . '</p>';
		}
		asort($list);
		$count = count($list);
		
		$caption = ($count > 1) ? '<p class="total">' . sprintf(_n('студент plural', '%s студент', $count), $count) . '</p>' : '';
			
		return $caption . implode('',$list);
	}
	
	public function updateTutors($tutors)
	{
		$tutors = explode(',', $tutors);
		$tutors = array_map('intval', $tutors);
		$tutors = array_filter($tutors);
		
		if(empty($tutors)){ return _('нет'); }
		$list = array();
		foreach($tutors as $user_id){
			$fio = $this->getPeopleFio($user_id, $tutors);
			if(empty($fio)){ continue; }
			$list[$user_id] = $fio;
		}
		return implode(', ',$list);
	}
	
	
	
	private function getPeopleFio($user_id, $additional_ids = array())
	{
		$user_id = (int)$user_id;
		if(isset($this->_list_users[$user_id])){ return $this->_list_users[$user_id]; }
		
		$additional_ids[$user_id] 	= $user_id;
		$additional_ids 			= array_map('intval', $additional_ids);
		$additional_ids 			= array_filter($additional_ids);
		if(empty($additional_ids)){ return false; }
		
		$users = $this->getService('User')->getUsersByIds($additional_ids);
		if(!$users){ return false; }
		foreach($users as $user){
			if($user->blocked == HM_User_UserModel::STATUS_BLOCKED){ continue; }
			$this->_list_users[$user->MID] = $user->getName();
		}
		return $this->_list_users[$user_id];
		
	}
	
	
	
}