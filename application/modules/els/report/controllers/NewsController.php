<?php

# должны попадать курс по названиям, начинающиеся со слов: «! ГИА (2018-2019)», «очка 2016», «Классическая заочка, ДОТ 1,2,3,4,5,6,7,8,9,10,11,12 зет», Практика
	
	# В структуру плана не должны попадать выгруженные сессии, учебные планы которых начинаются со слов: ДО
	# В структуру плана не должны попадать выгруженные сессии, учебные планы которых начинаются со слов: ДО, АН_
	# В курсах, начинающихся со слов: «Классическая заочка….»
		# надо задать условие:
		# Преподаватели будут размещать новости с названиями:
		# Рубежный контроль 1
		# Рубежный контроль 2
		# Рубежный контроль 3 и так далее
		# = ИПЗ, Итоговое практическое задание, индивидуальное практическое задание.
		#
		# При этом рубежный контроль 1 = 1 зет
	# В случае того, если заголовки стоят без цифр, то считываться будет название новостей «Практическое задание 1», «Рубежный контроль 1» = 1 зет

	# ! ГИА (2018-2019) ОЧКА универс. Курс
		# По поиску новостей, необходимо задать параметр того, чтобы поиск находил любую ссылку.
class Report_NewsController extends HM_Controller_Action_Crud
{
	protected $groups 	 = array();
	protected $news 	 = array(); # все новости сессий
	protected $newsLinks = array(); # все новости сессий, в которых есть ссылка	
	protected $_newsPlan = array(); # Должно быть новостей
	protected $_newsFact    = array(); # Новости (факт) 
	protected $_subjects   = array(); # Все сессии + учебные курсы этих сессий
	protected $_lessons    = array(); # Все занятия сессии
	protected $_news       = array(); # Все новости сессий
	
	
	
    public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет по новостям'));
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_News();
		$this->view->isTutor = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR) ? true : false;
    }
	
	public function getAction()
    {		
		#$this->groups 			= $this->getService('StudyGroup')->getSubjectGroupList(); //--получаем список групп.		
		
		$isTutor = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR) ? true : false;
		
		
		$request             =      $this->getRequest();
		$this->_subject_end  =      $request->getParam('subject_end',  0);
		$this->_tutorId      = (int)$request->getParam('tutor_id',     0);
		$this->_faculty      =      $request->getParam('faculty_name', 0);
		$this->_chair        =      $request->getParam('chair_name',   0);
		$this->_role_lector  = (int)$request->getParam('role_lector',  0);		
		$this->_type_subject = (int)$request->getParam('type_subject', 0);		
		$this->_type_do      =      $request->getParam('type_do',      0);
		
		
		if($isTutor){
			$this->_subject_end = date('d.m.Y');
			$this->_tutorId     = $this->getService('User')->getCurrentUserId();
			$this->_type_do     = '-1';
		}
		
		
		if($this->_subject_end){
			$this->_subject_end = str_replace(array('%2E', '_'), '.', $this->_subject_end);
		}
		
		$dt           = DateTime::createFromFormat('d.m.Y', $this->_subject_end);
		$dt_format	  = ($dt) ? $dt->format('Y-m-d 00:00:00') : false;
		
		if(!$dt_format){
			echo 'Укажите дату';
			die;			
		}
		

		
		
		if(!$this->_serviceUser) { $this->_serviceUser = $this->getService('User');  }		
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		$select_light	= $serviceReport->getSelect(); # для предвыборки сессий без доп таблиц для последующего выбра нужных данных
		
		
		# получаем все сессии с планом, начинающимся на  ДО, АН_. Их исключаем
		$select->join(array('pe' => 'programm_events'), 'pe.item_id = subj.subid AND pe.type = ' . HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, array());
		$select->join(array('pr' => 'programm'), 'pr.programm_id = pe.programm_id', array());
		
		$select_light->join(array('pe' => 'programm_events'), 'pe.item_id = subj.subid AND pe.type = ' . HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, array());
		$select_light->join(array('pr' => 'programm'), 'pr.programm_id = pe.programm_id', array());
		
		$select->join(
			array('t' => 'Tutors'),
			$serviceReport->quoteInto('t.CID = subj.subid AND (t.roles IS NULL OR t.roles IN (?))', HM_Lesson_Assign_Tutor_TutorModel::getAvailablePracticeRoleIDs()),
			array()
		);
		
		# получаем сессии по заданному типу
		if(!empty($this->_type_subject)){
			# очка
			if($this->_type_subject == HM_Report_ReportModel::TYPE_FULL_TIME){
				$ids = $this->getService('Subject')->getFullTimeIds($dt_format);
				if(empty($ids)){
					$select->where('1=0');
					$select_light->where('1=0');					
				} else {
					$select->where($serviceReport->quoteInto('subj.subid IN (?)', $ids));
					$select_light->where($serviceReport->quoteInto('subj.subid IN (?)', $ids));
				}				
			}
			
			# дистант
			if($this->_type_subject == HM_Report_ReportModel::TYPE_PRACTICE){
				$ids = $this->getService('Subject')->getPracticeIds($dt_format);
				if(empty($ids)){
					$select->where('1=0');
					$select_light->where('1=0');
				} else {
					$select->where($serviceReport->quoteInto('subj.subid IN (?)', $ids));
					$select_light->where($serviceReport->quoteInto('subj.subid IN (?)', $ids));
				}
			}
		}
		
		$select_light->join(
			array('t' => 'Tutors'), 
			$serviceReport->quoteInto('t.CID = subj.subid AND (t.roles IS NULL OR t.roles IN (?))', HM_Lesson_Assign_Tutor_TutorModel::getAvailablePracticeRoleIDs()),			
			array()
		);
		
		# получаем назначения тьютора
		if(!empty($this->_tutorId)){
			if(!$this->_serviceTutor){ $this->_serviceTutor = $this->getService('Tutor');  }														
			$tutor_subjects = $this->_serviceTutor->fetchAll(array('MID = ?' => $this->_tutorId))->getList('CID');
			
			if(empty($tutor_subjects)){
				echo 'Выбранный тьютор не назначен на сессии';
				die;
			}					
			#$select->where($this->_serviceUser->quoteInto('subj.subid IN (?)', $tutor_subjects));
			$select->where($this->_serviceUser->quoteInto('p.MID = ?', $this->_tutorId));
			
			
			
			$select_light->where($this->_serviceUser->quoteInto('t.MID = ?', $this->_tutorId));			
			
		}
		
		# кафедра
		if($this->_chair == HM_Report_ReportModel::CHAIR_EMPTY){
			$select->where("(subj.chair IS NULL OR subj.chair = '')"); # Нет кафедры					
			$select_light->where("(subj.chair IS NULL OR subj.chair = '')"); # Нет кафедры					
		} elseif(!empty($this->_chair)){
			$select->where($this->_serviceUser->quoteInto('subj.chair = ?', $this->_chair));
			$select_light->where($this->_serviceUser->quoteInto('subj.chair = ?', $this->_chair));
		}
		
		# факультет 
		if($this->_faculty == HM_Report_ReportModel::FACULTY_EMPTY){
			$select->where("(subj.faculty IS NULL OR subj.faculty = '')"); # Нет факультета					
			$select_light->where("(subj.faculty IS NULL OR subj.faculty = '')"); # Нет факультета					
		} elseif(!empty($this->_faculty)){
			$select->where($this->_serviceUser->quoteInto('subj.faculty = ?', $this->_faculty));
			$select_light->where($this->_serviceUser->quoteInto('subj.faculty = ?', $this->_faculty));
		}
		
		
		# роль лектор
		if(!empty($this->_role_lector)){
			
			$subSelect = $serviceReport->getSelect();
			$subSelect->from(array('t' => 'Tutors'), array(
					'CID' 		=> 't.CID',						
					'max_tid' 	=> new Zend_Db_Expr('MAX(t.TID)'),
			));
			$subSelect->join(array('tl' => 'Tutors_lessons'), 'tl.MID = t.MID AND tl.CID = t.CID AND tl.role_id = '.HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR, array());
			$subSelect->where('t.CID > 0 AND t.MID > 0');
			$subSelect->group(array('t.CID'));
		
			$select->join(array('sub_tutors' => $subSelect), '(sub_tutors.max_tid = t.TID AND sub_tutors.CID = subj.subid)', array());
			
			$select->where($serviceReport->quoteInto('t.roles IN (?)', array(HM_Lesson_Assign_Tutor_TutorModel::getAvailableLectorRoleIDs())));
		}
		
		
		if($this->_type_do != '-1'){
			if(empty($this->_type_do)){
				$select->where(" (subj.isDO IS NULL OR subj.isDO='' OR subj.isDO=0) ");
				$select_light->where(" (subj.isDO IS NULL OR subj.isDO='' OR subj.isDO=0) ");
			} else {
				$select->where($serviceReport->quoteInto('subj.isDO = ?', $this->_type_do));
				$select_light->where($serviceReport->quoteInto('subj.isDO = ?', $this->_type_do));
			}
		}

		if($isTutor){
			$fields = array(
				'subject_name' 			=> 'subj.name',
				'programms'				=> 'subj.subid',
				'groups'				=> 'subj.subid',
				'students'				=> 'subj.subid',
				'zet' 					=> 'subj.zet',
				'newsMax'				=> 'subj.subid',
				'newsCount'				=> 'subj.subid',				
				'issetNews'				=> 'subj.subid',
				'subject_link'			=> 'subj.subid',
				'videoCount'			=> 'subj.subid',
				
				'subject_id'			=> 'subj.subid',
				'subject_external_id'	=> 'subj.external_id',
				'learning_subject_name'	=> 'ls.name',
				'subject_base_name'		=> 'subj_base.name',
				'tutors'				=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT p.MID)"),
				'faculty'				=> 'subj.faculty',
				'tutor_chairs'			=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT p.MID)"),
				'chair'					=> 'subj.chair',
			);			
		} else {
			$fields = array(
				'subject_id'			=> 'subj.subid',
				'subject_external_id'	=> 'subj.external_id',
				'learning_subject_name'	=> 'ls.name',
				'zet' 					=> 'subj.zet',
				'subject_base_name'		=> 'subj_base.name',
				'programms'				=> 'subj.subid',
				'groups'				=> 'subj.subid',
				'tutors'				=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT p.MID)"),
				'newsMax'				=> 'subj.subid',
				'newsCount'				=> 'subj.subid',
				'faculty'				=> 'subj.faculty',
				'tutor_chairs'			=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT p.MID)"),
				'subject_name' 			=> 'subj.name',				
				'issetNews'				=> 'subj.subid',
				'chair'					=> 'subj.chair',
				'students'				=> 'subj.subid',
				'subject_link'			=> 'subj.subid',
				'videoCount'			=> 'subj.subid',
			);
		}
		
		$select->from(array('subj' => 'subjects'), $fields);
		
		$select_light->from(array('subj' => 'subjects'),
			array(
				'subject_id' => 'subj.subid',				
				'cource_id'  => 'subj.base_id',				
				'tutor_id'   => 't.MID',				
			)
		);
		
		if(!$isTutor){
			$select->where('subj.end >= ?', $dt_format);
			$select->where('subj.begin < ?', $dt_format); 
		
			$select_light->where('subj.end >= ?', $dt_format);
			$select_light->where('subj.begin < ?', $dt_format); 
		}
		
		#$select_light->joinLeft(array('ls' => 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array());
		#$select_light->where($serviceReport->quoteInto('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION));
		
		
		$subject_ids = array();
		$allow_tutors = array();
		
		$res  = $select_light->query()->fetchAll();
		foreach($res as $i){
			$subject_ids[intval($i['subject_id'])] = intval($i['subject_id']);
			$subject_ids[intval($i['cource_id'])]  = intval($i['cource_id']);
			
			$allow_tutors[$i['subject_id']][$i['tutor_id']] = $i['tutor_id'];
			$allow_tutors[$i['cource_id']][$i['tutor_id']] = $i['tutor_id'];
		}
		
		$this->loadSubjects($subject_ids);
		$this->loadLessons($subject_ids);
		$this->loadNews($subject_ids);
		
		$this->filteredLessons($allow_tutors); # Оставляем только те занятия, которые доступны тьюторам
		
		$this->list_programms	= $this->getProgrammList($subject_ids);
		
		$programm_ids = array();
		if(!empty($this->list_programms)){
			foreach($this->list_programms as $i){
				foreach($i as $programm_id => $programm_name){
					$programm_ids[$programm_id] = $programm_id;
				}
			}
		}		
		$this->list_groups		= $this->getGroupList($programm_ids);
		$this->list_peoples		= $this->getPeopleList($subject_ids);
		#$this->news				= $this->getService('News')->getSubjectNews($subject_ids, false); //--получаем список новостей		
		$this->student_count	= $this->getStudentCount($subject_ids);
		
		
		$select->join(array('subj_base' => 'subjects'), 'subj_base.subid = subj.base_id', array());		
		$select->join(array('p' => 'People'), 'p.MID = t.MID', array());
		#$select->joinLeft(array('l' => 'schedule'), "l.CID = subj.subid AND l.typeID = '".HM_Event_EventModel::TYPE_TEST."'", array());
		
		$select->joinLeft(array('ls' => 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array());
		
		
				
		$select->where('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE);
		
		$select->where($serviceReport->quoteInto('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION));		 
		
		
		$select->group(array(
			'subj.subid',
			'subj.external_id',
			'ls.name',	
			'subj.zet',
			'subj.name',
			'subj.subid',
			'subj.chair',			
			'subj.faculty',	
			'subj_base.name',			
		));
		
		#if($this->getService('User')->getCurrentUserId() == 5829){
			//echo $select->assemble();
		//	die;	
		#}
		
		
		$grid = $this->getGrid(
            $select,
            array(
                'subject_id'			=> array('hidden' => true),
				'subject_external_id'	=> array('title' => _('Код сессии 1С')),
                'learning_subject_name'	=> array('title' => _('Предмет')),
                'zet'      				=> array('hidden' => true),
				'subject_base_name'		=> array('title' => _('Учебный курс')),
				'programms'				=> array('title' => _('План')),
				'groups'				=> array('title' => _('Группа')),
				'tutors'		    	=> array('title' => _('ФИО тьютора')),
				'newsMax'		   		=> array('title' => _('Новости (план)')),
				'newsCount'		   		=> array('title' => _('Новости (факт)')),
				'faculty'				=> array('title' => _('Факультет сессии')),
                'tutor_chairs'	    	=> array('title' => _('Кафедры тьютора')),
				'subject_name'      	=> array('title' => _('Название сессии')),
				'issetNews'      		=> array('title' => _('Выполнено (новости)')),
				'chair'			    	=> array('title' => _('Кафедра в сессии')),
				'students'				=> array('title' => _('Студенты')),
				'subject_link'			=> array('title' => _('Ссылка')),	
				'videoCount'		  	=> array('title' => _('Количество видео (число)')),				
            ),
            array(
                'subject_external_id'	=> null,                
                'learning_subject_name'	=> null, 
				'zet'      				=> null, 				
                'subject_base_name'		=> null, 
				'programms' 			=> array(						
											'callback' => array('function' => array($this, 'filterProgramms'))
										),	
				'groups' 				=> array(						
											'callback' => array('function' => array($this, 'filterGroups'))
										),
				'tutors'   				=> null,
				'newsMax'		      	=> null,
				'newsCount'		      	=> null,				
				'faculty'  				=> null,				
				'subject_name'      	=> null,				
				'issetNews' 			=> array(
											'callback'	=> array('function' => array($this, 'filterIssetNews')),
											'values'	=> array(0 => _('Нет'), 1 => _('Да'))
										),
				'chair'    				=> null,
				'videoCount'    		=> null,
				
            )
        );
		
		
		
		
		
		$grid->updateColumn('issetNews', array(
				'callback' => array(
					'function' => array($this, 'updateIssetNews'),
					'params' => array('{{issetNews}}')
				)
			)
		);
		
		
		$grid->updateColumn('newsMax', array(
				'callback' => array(
					'function' => array($this, 'updateNewsPlan'),
					'params' => array('{{newsMax}}')
				)
			)
		);
		
		$grid->updateColumn('newsCount', array(
				'callback' => array(
					'function' => array($this, 'updateNewsFact'),
					'params' => array('{{newsCount}}')
				)
			)
		);
		
		
		$grid->updateColumn('videoCount', array(
				'callback' => array(
					'function' => array($this, 'updateVideoCount'),
					'params' => array('{{videoCount}}')
				)
			)
		);
		
		$grid->updateColumn('groups', array(
				'callback' => array(
					'function' => array($this, 'updateGroups'),
					'params' => array('{{groups}}')
				)
			)
		);
		
		$grid->updateColumn('programms', array(
				'callback' => array(
					'function' => array($this, 'updateProgramms'),
					'params' => array('{{programms}}')
				)
			)
		);
		
		$grid->updateColumn('tutors', array(
				'callback' => array(
					'function' => array($this, 'updateTutors'),
					'params' => array('{{tutors}}')
				)
			)
		);
		
		$grid->updateColumn('tutor_chairs', array(
				'callback' => array(
					'function' => array($this, 'updateTutorChairs'),
					'params' => array('{{tutor_chairs}}')
				)
			)
		);
		
		
		
		$grid->updateColumn('subject_name', array(
				'callback' => array(
					'function' => array($this, 'updateSubjectName'),
					'params' => array('{{subject_name}}', '{{subject_id}}')
				)
			)
		);
		
		$grid->updateColumn('subject_link', array(
				'callback' => array(
					'function' => array($this, 'updateSubjectUrl'),
					'params' => array('{{subject_link}}')
				)
			)
		);
		
		$grid->updateColumn('students', array(
				'callback' => array(
					'function' => array($this, 'updateStudents'),
					'params' => array('{{students}}')
				)
			)
		);
		
		if($isTutor){
			$grid->updateColumn('subject_external_id', array(
				'hidden' => true
			));
			
			$grid->updateColumn('learning_subject_name', array(
				'hidden' => true
			));
			
			$grid->updateColumn('subject_base_name', array(
				'hidden' => true
			));
			
			$grid->updateColumn('tutors', array(
				'hidden' => true
			));
			
			$grid->updateColumn('faculty', array(
				'hidden' => true
			));
			
			$grid->updateColumn('tutor_chairs', array(
				'hidden' => true
			));
			
			$grid->updateColumn('chair', array(
				'hidden' => true
			));            
		}
		
	
		
		try {
			$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
			$this->view->grid 			 = $grid->deploy();		
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";
		}
	}
	
	public function updateFaculty($id){
		$id = intval($id);
		$facultys = HM_Subject_SubjectModel::getFacultys();
		if(isset($facultys[$id])){ return $facultys[$id]; }
		return HM_Subject_SubjectModel::FACULTY_OTHER;
	}
	
	# Есть валидная новость	
	public function updateIssetNews($subjectId)
	{		
		return $this->issetNews($subjectId) ? _('Да') : _('Нет');
	}
	
	public function issetNews($subjectId)
	{
		if(    !array_key_exists($subjectId, $this->_newsFact)
			|| !array_key_exists($subjectId, $this->_newsPlan)
		){
			$this->calculateNews($subjectId);
		}
		
		if( intval($this->_newsFact[$subjectId]) < intval($this->_newsPlan[$subjectId]) ){
			return false;
		}
		return true;
	}
	
	
	public function updateVideoCount($subject_id)
	{
		$subject_id 			= intval($subject_id);
		$news 					= $this->getNewsBySubject($subject_id);
		if(empty($news)){ return 0; }
		
		$video_count = 0;
		foreach($news as $item){
			if( ! $item->isVideoNews($announce) ){ continue; }			
			if( ! $item->isHasAnyLink()         ){ continue; }			
			$video_count++;
		}
		return $video_count;
	}
	
	public function filterIssetNews($data) {		
        $value  = intval($data['value']);		
        $select = $data['select'];
		$subjIDs = array();
		
		#$ids = $this->_subjects->getList('subid');
		$ids = array_keys($this->_subjects);
		
		#foreach($this->_subjects as $subjectId => $subject){
		foreach($ids as $subjectId){
			if($this->issetNews($subjectId)){
				$subjIDs[$subjectId] = $subjectId;
			}			
		}
		
		if(empty($subjIDs)){ $subjIDs = array(0); }
		
        if($value == 1){
			$select->where('subj.subid IN (?)', $subjIDs);
		} else {
			$select->where('subj.subid NOT IN (?)', $subjIDs);
		}
    }
	
	public function updateGroups($subject_id){
		if(empty($subject_id)){
			return _('Нет');
		}
		
		$programms = $this->list_programms[$subject_id];
		
		if(empty($programms)){ 
			return _('Нет');
		}
		
		$groups = array();
		foreach($programms as $programm_id => $programm){
			$programm_groups = $this->list_groups[$programm_id];
			if(empty($programm_groups)){ continue; }
			foreach($programm_groups as $group_id => $group){
				$groups[$group_id] = $group;
			}
		}
		if(empty($groups)){ 
			return _('Нет');
		}	

		$caption = '';
		if(count($groups) > 1){ $caption = '<p class="total">Всего: ' . count($groups) . '</p>'; }			
		return  $caption . '<p>'.implode('</p><p>', $groups).'</p>';
	}
	
	public function updateProgramms($subject_id)
	{
		if(empty($subject_id)){
			return _('Нет');
		}
		
		$programms = $this->list_programms[$subject_id];
		
		if(empty($programms)){ 
			return _('Нет');
		}
		
		$caption = '';
		if(count($programms) > 1){ $caption = '<p class="total">Всего: ' . count($programms) . '</p>'; }			
		return  $caption . '<p>'.implode('</p><p>', $programms).'</p>';
	}
	
	public function updateTutors($tutor_ids)
	{
		$tutor_ids = explode(',', $tutor_ids);
		$tutor_ids = array_map('intval', $tutor_ids);
		$tutor_ids = array_filter($tutor_ids);
		
		if(empty($tutor_ids)){
			return _('Нет');
		}
		
		$tutors = array();
		foreach($tutor_ids as $tutor_id){
			$fio = $this->list_peoples[$tutor_id];
			if(empty($fio)){ continue; }
			$tutors[$tutor_id] = $fio;
		}
		
		
		if(empty($tutors)){ 
			return _('Нет');
		}
		
		$caption = '';
		if(count($tutors) > 1){ $caption = '<p class="total">Всего: ' . count($tutors) . '</p>'; }			
		return  $caption . '<p>'.implode('</p><p>', $tutors).'</p>';
	}
	
	
	public function updateTutorChairs($tutor_ids)
	{
		$tutor_ids = explode(',', $tutor_ids);
		$tutor_ids = array_map('intval', $tutor_ids);
		$tutor_ids = array_filter($tutor_ids);
		
		if(empty($tutor_ids)){
			return _('Нет');
		}
		
		if(!$this->serviceOrg) { $this->serviceOrg = $this->getService('Orgstructure'); }
		$chairs = array();
		foreach($tutor_ids as $user_id){
			$user_chairs = $this->serviceOrg->getUserChair($user_id);
			if(empty($user_chairs)){ continue; }
			$user_chairs = (array)$user_chairs;
			foreach($user_chairs as $chair_name){
				$chairs[$chair_name] = $chair_name;
			}
		}
		$caption = '';
		if(count($chairs) > 1){ $caption = '<p class="total">Всего: ' . count($chairs) . '</p>'; }			
		return  $caption . '<p>'.implode('</p><p>', $chairs).'</p>';
		
	}
	
	
	public function filterGroups($data)
	{	
		try {
			$value	= trim($data['value']);
			$select	= $data['select'];
			
			$select_sub = $this->getService('Report')->getSelect();
			$select_sub->from(array('pe' => 'programm_events'), array(
				'subject_id' 	=> 'pe.item_id',
			));
			$select_sub->join(array('p' 	=> 'programm'), 'p.programm_id = pe.programm_id', array());
			$select_sub->join(array('sgp'	=> 'study_groups_programms'), 'sgp.programm_id = p.programm_id', array());
			$select_sub->join(array('sg'	=> 'study_groups'), 'sg.group_id = sgp.group_id', array());
			
			$select_sub->where($this->quoteInto('sg.name LIKE LOWER(?)', '%'.$value.'%'));
			$res = $select_sub->query()->fetchAll();
			if(empty($res)){
				$select->where('1=0');
				return;				
			}
			
			$subject_ids = array();
			foreach($res as $i){ $subject_ids[$i['subject_id']] = $i['subject_id']; }
			
			if(empty($subject_ids)){
				$select->where('1=0');
				return;				
			}			
			$select->where($this->quoteInto('subj.subid IN (?)', $subject_ids));	
			
		} catch (Exception $e) {}			
	}
	
	public function filterProgramms($data)
	{
		try {
			$value	= trim($data['value']);
			$select	= $data['select'];
			
			$select_sub = $this->getService('Report')->getSelect();
			$select_sub->from(array('pe' => 'programm_events'), array(
				'subject_id' 	=> 'pe.item_id',
			));
			$select_sub->join(array('p' => 'programm'), 'p.programm_id = pe.programm_id', array());
			$select_sub->where($this->quoteInto("p.name LIKE LOWER(?)", '%'.$value.'%'));
			$res = $select_sub->query()->fetchAll();
			if(empty($res)){
				$select->where('1=0');
				return;				
			}
			
			$subject_ids = array();
			foreach($res as $i){ $subject_ids[$i['subject_id']] = $i['subject_id']; }
			
			if(empty($subject_ids)){
				$select->where('1=0');
				return;				
			}			
			$select->where($this->quoteInto('subj.subid IN (?)', $subject_ids));	
			
		} catch (Exception $e) {}	
	}	
	
	
	# сессия - программа
	private function getProgrammList($subject_ids)
	{
		if(empty($subject_ids)){ return false; }
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		$select->from(array('pe' => 'programm_events'), array(
			'subject_id' 	=> 'pe.item_id',						
			'programm_id'	=> 'p.programm_id',
			'programm_name'	=> 'p.name',
		));
		$select->join(array('p' => 'programm'), 'p.programm_id = pe.programm_id', array());
		$select->where('type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT);
		$select->where($this->quoteInto('pe.item_id IN (?)', $subject_ids));
		
		$res  = $select->query()->fetchAll();
		$data = array();
		foreach($res as $i){
			$data[$i['subject_id']][$i['programm_id']] = $i['programm_name'];
		}
		return $data;
	}
	
	# программа - группа
	private function getGroupList($programm_ids)
	{
		if(empty($programm_ids)){ return false; }
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		$select->from(array('sg' => 'study_groups'), array(
			'programm_id' 	=> 'sgp.programm_id',						
			'group_id'		=> 'sg.group_id',
			'group_name'	=> 'sg.name',
		));
		$select->join(array('sgp' => 'study_groups_programms'), 'sgp.group_id = sg.group_id', array());
		$select->where($this->quoteInto('sgp.programm_id IN (?)', $programm_ids));
		
		$res  = $select->query()->fetchAll();
		$data = array();
		foreach($res as $i){
			$data[$i['programm_id']][$i['group_id']] = $i['group_name'];
		}
		return $data;
	}
	
	private function getPeopleList($subject_ids)
	{
		if(empty($subject_ids)){ return false; }
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		$select->from(array('p' => 'People'), array(
			'MID' 			=> 'p.MID',
			'LastName' 		=> 'p.LastName',
			'FirstName' 	=> 'p.FirstName',
			'Patronymic' 	=> 'p.Patronymic',
		));
		$select->join(array('t' => 'Tutors'), 't.MID = p.MID', array());
		$select->where($this->quoteInto('t.CID IN (?)', $subject_ids));
		
		$res  = $select->query()->fetchAll();
		$data = array();
		foreach($res as $i){
			$data[$i['MID']] = $i['LastName'] . ' ' . $i['FirstName'] . ' ' . $i['Patronymic'];
		}
		return $data;
	}
	
	public function updateSubjectName($subject_name, $subject_id)
	{
		$classes	= empty($this->student_count[$subject_id]) ? 'no_students' : '';
		$url		= $this->view->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index', 'subject_id' => $subject_id, 'subject' => 'subject'), 'default', true);
		return '<a target="_blank" href="'.$url.'" class="'.$classes.'">' . $subject_name . '</a>';
	}
	
	public function updateSubjectUrl($subject_id)
	{
		$url	= $this->view->serverUrl() . $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subject_id), 'default', true);
		return '<a target="_blank" href="'.$url.'" >' . $url . '</a>';
	}
	
	public function updateStudents($subject_id)
	{
		return (int)$this->student_count[$subject_id];		
	}
	
	# кол-во студентов на каждой сессии.
	public function getStudentCount($subject_ids)
	{
		if(empty($subject_ids)){ return false; }
		
		$serviceReport	= $this->getService('Report');
		$select 		= $serviceReport->getSelect();
		$select->from(array('s' => 'Students'), array(
			'subject_id'	=> 's.CID',			
			'students'		=> new Zend_Db_Expr("COUNT(s.MID)"),
		));
		$select->join(array('p' => 'People'), 'p.MID = s.MID', array());
		$select->where($this->quoteInto('s.CID IN (?)', $subject_ids));
		$select->where('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE);
		$select->group('s.CID');
		
		$res  = $select->query()->fetchAll();
		$data = array();
		foreach($res as $i){
			$data[$i['subject_id']] = (int)$i['students'];
		}
		return $data;
	}
	
	# Нахождение кол-во обеспеченных ЗЕТ - верно заполненных новостей с внутренней ссылкой 
	public function updateNewsFact($subjectId)
	{
		if(!array_key_exists($subjectId, $this->_newsFact)){
			$this->calculateNews($subjectId);
		}
		return (int)$this->_newsFact[$subjectId];
	}
	
	public function updateNewsPlan($subjectId)
	{
		if(!array_key_exists($subjectId, $this->_newsPlan)){
			$this->calculateNews($subjectId);
		}
		return (int)$this->_newsPlan[$subjectId];
	}
	
	public function calculateNews($subjectId)
	{
		$subjectId              = (int) $subjectId;
		$list_landmark_control	= array();	# все найденные Рубежный контроль
		$list_practical_task	= array();	# все найденные Практическое задание
		$incorrect_landmarks    = array(); # неверно заполненные РубежныйКонтроль
		$incorrect_tasks        = array(); # неверно заполненные Задание
		$incorrect_links        = array(); # неверно заполненные ссылки
		$news_count             = 0;
		$correct_landmarks      = array();
		$correct_tasks          = array();
		
		
		if(!$subjectId){
			$this->_newsFact[$subjectId] = $news_count;
			$this->_newsPlan[$subjectId] = 0;
			return;
		}
		
		$subject = $this->getSubjectById($subjectId);
		
		if(!$subject){
			$this->_newsFact[$subjectId] = $news_count;
			$this->_newsPlan[$subjectId] = 0;
			return;
		}
		
		$lessons = $this->getLessonsBySubject($subjectId);
		
		if(!empty($lessons)){
			foreach($lessons as $lesson){
				$landmarkNumber = $lesson->getLandmarkNumber();
				$taskNumber     = $lesson->getNumberTask();
				$list_landmark_control[$landmarkNumber] = $landmarkNumber;
				$list_practical_task[$taskNumber]       = $taskNumber;
			}
		}
		
		$list_landmark_control = array_filter($list_landmark_control);
		$list_practical_task   = array_filter($list_practical_task);
		ksort($list_landmark_control);
		ksort($list_practical_task);
		
		$incorrect_landmarks = $list_landmark_control;
		$incorrect_tasks     = $list_practical_task;
		
		$base_subject        = $this->getSubjectById($subject->base_id);
		
		if(
			mb_stripos($base_subject->name, 'очка '              ) === false
			&&
			mb_stripos($base_subject->name, 'классическая заочка') === false
		){
			$this->_newsFact[$subjectId] = (int)$subject->zet;
			$this->_newsPlan[$subjectId] = (int)$subject->zet;
			return;
		}
		
		$news = $this->getNewsBySubject($subjectId);
		
		if(!count($news)){
			$this->_newsFact[$subjectId] = 0;
			$this->_newsPlan[$subjectId] = count($list_landmark_control) + count($list_practical_task);
			return;
		}
		
		# Ищем любую внутреннюю ссылку
		if(
			mb_stripos($base_subject->name, '! ГИА (2018-2019) ОЧКА универс. Курс') !== false
			||
			mb_stripos($base_subject->name, '! Практика') !== false
		){ 
			foreach($news as $item){
				if($item->isHasInnerLink()){ $news_count++; }
			}
			$this->_newsFact[$subjectId] = $news_count;
			$this->_newsPlan[$subjectId] = (int)$subject->zet;
			return;
		}
		
		# не нужно учитывать Практическое задание
		if(mb_stripos($base_subject->name, 'Классическая заочка') !== false){
			foreach($news as $item){
				foreach($list_landmark_control as $number){
					if(isset($correct_landmarks[$number])){ continue; }
					if($item->isHasInnerLink() && $item->isHasModuleNumberLandmark($number)){ 
						$news_count++;
						$correct_landmarks[$number] = $number;
						continue;
					}
				}
			}		
			$this->_newsFact[$subjectId] = $news_count;
			$this->_newsPlan[$subjectId] = count($list_landmark_control);	
			return;
		}
		
		foreach($news as $item){
			foreach($list_landmark_control as $number){
				if(isset($correct_landmarks[$number])){ continue; }
				if($item->isHasInnerLink() && $item->isHasModuleNumberLandmark($number)){
					$news_count++;
					$correct_landmarks[$number] = $number;
					continue;
				}
			}
			
			foreach($list_practical_task as $number){
				if(isset($correct_tasks[$number])){ continue; }
				if($item->isHasInnerLink() && $item->isHasModuleNumberTask($number)){
					$news_count++;
					$correct_tasks[$number] = $number;
					continue;
				}
			}
		}
		$this->_newsFact[$subjectId] = $news_count;
		$this->_newsPlan[$subjectId] = count($list_landmark_control) + count($list_practical_task);
		return;		
	}
	
	public function loadSubjects($ids)
	{
		$ids = array_map('intval', $ids);
		$ids = array_filter($ids);
		if(empty($ids)){
			return false;
		}		
		$collection = $this->getService('Subject')->fetchAll($this->getService('Subject')->quoteInto(
			'subid IN (?)', 
			$ids
		));
		
		#$this->_subjects = new HM_Collection(array(), 'HM_Subject_SubjectModel');
		
		
		foreach($collection as $subject){
			$this->_subjects[$subject->subid] = $subject;
			#$this->_subjects[count($this->_subjects)] = $subject;
		}
		
		
		return true;
	}
	
	public function getSubjectById($id)
	{
		#return $this->_subjects->exists('subid', $id);
		return $this->_subjects[$id];		
	}
	
	public function loadLessons($subjectIds)
	{
		$subjectIds = array_map('intval', $subjectIds);
		$subjectIds = array_filter($subjectIds);
		if(empty($subjectIds)){
			return false;
		}	

		$collection = $this->getService('Lesson')->fetchAll($this->getService('Lesson')->quoteInto(
				array('CID IN (?)', ' AND vedomost = ?', ' AND typeID NOT IN (?)', ' AND isfree = ?'),
				array($subjectIds, 1, array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN)
		));
		foreach($collection as $lesson){
			$this->_lessons[$lesson->CID][$lesson->SHEID] = $lesson;
		}
		return true;
	}
	
	public function getLessonsBySubject($subjectId)
	{
		return $this->_lessons[$subjectId];		
	}
	
	public function loadNews($subjectIds)
	{
		$subjectIds = array_map('intval', $subjectIds);
		$subjectIds = array_filter($subjectIds);
		if(empty($subjectIds)){
			return false;
		}
		
		$collection = $this->getService('News')->fetchAll($this->getService('News')->quoteInto('subject_id IN (?)', $subjectIds));
		foreach($collection as $news){
			$this->_news[$news->subject_id][$news->id] = $news;
		}
		return true;
	}
	
	public function getNewsBySubject($subjectId)
	{
		return $this->_news[$subjectId];		
	}
	
	# Из набора занятий оставляем только те, которые доступны тьюторам
	public function filteredLessons($allow_tutors)
	{
		if(empty($allow_tutors)){
			return false;
		}
		$criteria      = array();
		$allow_lessons = array(); 
		foreach($allow_tutors as $subjectId => $tutors){
			$criteria[] = $this->getService('LessonAssignTutor')->quoteInto(array('( CID = ? ', ' AND MID IN (?) )'), array($subjectId, $tutors));
		}		
		
		$where      = $this->getService('LessonAssignTutor')->quoteInto('((' . implode(') OR (', $criteria) . '))', false);		
		$collection = $this->getService('LessonAssignTutor')->fetchAll($where);
		if(!count($collection)){
			return false;
		}
		foreach($collection as $item){
			$allow_lessons[$item->CID][$item->LID] = $item->LID;			
		}
		foreach($this->_lessons as $subject_id => $lessons){
			$allow_lesson_ids = $allow_lessons[$subject_id];
			if(empty($allow_lesson_ids)){ continue; }
			foreach($lessons as $lesson_id => $lesson){
				if(!in_array($lesson_id, $allow_lesson_ids)){
					unset($this->_lessons[$subject_id][$lesson_id]);
				}
			}
		}
		return true;
	}
	
}