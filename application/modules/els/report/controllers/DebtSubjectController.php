<?php


class Report_DebtSubjectController extends HM_Controller_Action_Crud
{
	
   
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет о продленных сессиях'));
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_DebtSubject();		
    }
	
	public function getAction()
    {		
		
		$request	= $this->getRequest();
		$date_end	= $request->getParam('date_end', false);
		$date_end 	= str_replace('%2E', '.', $date_end);
		$date_end 	= str_replace('_', '.', $date_end);
		$date_end 	= strtotime($date_end);
	
		if($date_end < 0){
			echo 'Укажите дату';
			die;
		}
		$date_end = date('Y-m-d', $date_end);
		
		$serviceUser	= $this->getService('User');		
		
		$fields = array(
			'learning_subject_name' 			=> 'ls.name',
			'learning_subject_id_external' 		=> 'subj.learning_subject_id_external',
			'subject_id' 						=> 'subj.subid',
			'subject_external_id' 				=> 'subj.external_id',
			'subject_name' 						=> 'subj.name',
			'time_ended_debt'					=> 'subj.time_ended_debt',
			'time_ended_debt_2'					=> 'subj.time_ended_debt_2',
			'subject_semester'					=> 'subj.semester',
			
			'group_id_external' 				=> 'sg.id_external',
			'group_name' 						=> 'sg.name',
			'programm_id_external' 				=> 'prog.id_external',
			'programm_name' 					=> 'prog.name',
			
			'subject_exam_type'					=> 'subj.exam_type',
			'student_mid_external'				=> 'p.mid_external',
			'student_fio'						=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
			'time_ended_debtor'					=> 'st.time_ended_debtor',
			'time_ended_debtor_2'				=> 'st.time_ended_debtor_2',
		);
		  
	 	
		$select = $serviceUser->getSelect();
		$select->from(array('subj' => 'subjects'), $fields);
		
		$select->joinLeft(array('ls' 	=> 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array());	
		$select->joinLeft(array('pe' 	=> 'programm_events'), 'pe.item_id = subj.subid AND pe.type=1', array());	
		$select->joinLeft(array('prog' 	=> 'programm'), 'prog.programm_id = pe.programm_id', array());	
		$select->joinLeft(array('sgp' 	=> 'study_groups_programms'), 'sgp.programm_id = prog.programm_id', array());	
		$select->joinLeft(array('sg' 	=> 'study_groups'), 'sg.group_id = sgp.group_id', array());	
		$select->joinLeft(array('sgc' 	=> 'study_groups_custom'), 'sgc.group_id = sg.group_id', array());	
		
		$select->joinLeft(array('st' 	=> 'Students'), 'st.CID = subj.subid AND st.MID = sgc.user_id', array());	
		$select->joinLeft(array('p' 	=> 'People'), 'p.MID = st.MID', array());
		  
		$select->where($serviceUser->quoteInto('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION));	
		$select->where($serviceUser->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));	
		
		$select->where($serviceUser->quoteInto(array('(subj.time_ended_debt >= ? ', ' OR subj.time_ended_debt_2 >= ?)'), array($date_end, $date_end)));
		$select->where($serviceUser->quoteInto(array('(st.time_ended_debtor >= ? ', ' OR st.time_ended_debtor_2 >= ?)'), array($date_end, $date_end)));
		
		
		
		
		$select->group(array('ls.name',	'subj.learning_subject_id_external',	'subj.subid',	'subj.external_id', 	'subj.name', 
							'subj.time_ended_debt',      'subj.time_ended_debt_2',      'subj.faculty',
								'subj.exam_type',	 'prog.id_external',	'prog.name ',	'sg.id_external', 	'sg.name', 	
								'subj.time_ended_debt','subj.time_ended_debt_2', 'p.LastName' , 'p.FirstName', 'p.Patronymic',
								'st.time_ended_debtor', 'st.time_ended_debtor_2' , 'p.mid_external', 'subj.semester'
								));
	
		
		$grid = $this->getGrid(
            $select,
            array(
                'subject_id'						=> array('hidden' => true),
				'subject_exam_type'					=> array('title' => _('Контроль')),
				'learning_subject_name'				=> array('title' => _('Предмет')),
				'learning_subject_id_external'		=> array('title' => _('Предмет. Код')),
				'subject_external_id'				=> array('title' => _('Сессия. Код')),
				'subject_name'						=> array('title' => _('Сессия')),
				'time_ended_debt'					=> array('title' => _('Сессия. Продление 1')),
				'time_ended_debt_2'					=> array('title' => _('Сессия. Продление 2')),
				'subject_semester'					=> array('title' => _('Семестр')),
				
				'group_id_external'					=> array('title' => _('Гуппа. Код')),
				'group_name'						=> array('title' => _('Гуппа')),
				'programm_id_external'				=> array('title' => _('Программа. Код')),
				'programm_name'						=> array('title' => _('Программа')),
				
               	
				
				'student_mid_external'				=> array('title' => _('Студент. Код')),
				'student_fio'						=> array('title' => _('Студент')),
				'time_ended_debtor'					=> array('title' => _('Студент. Продление 1')),
				'time_ended_debtor_2'				=> array('title' => _('Студент. Продление 2')),
							
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
                'time_ended_debt'				=> array('render' => 'SubjectDate'),           
                'time_ended_debt_2'				=> array('render' => 'SubjectDate'),              
                'subject_semester'				=> null,              
                'student_mid_external'			=> null,              
                'student_fio'					=> null,              
                'time_ended_debtor'				=> array('render' => 'SubjectDate'),              
                'time_ended_debtor_2'			=> array('render' => 'SubjectDate'),
            )
        );
		
	
		
		$grid->updateColumn('subject_exam_type', array(
				'callback' => array(
					'function' => array($this, 'updateExamType'),
					'params' => array('{{subject_exam_type}}' )
				)
			)
		);
		
		$grid->updateColumn('time_ended_debt', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{time_ended_debt}}')
            )
        ));
		
		$grid->updateColumn('time_ended_debt_2', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{time_ended_debt_2}}')
            )
        ));
		
		$grid->updateColumn('time_ended_debtor', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{time_ended_debtor}}')
            )
        ));
		
		$grid->updateColumn('time_ended_debtor_2', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{time_ended_debtor_2}}')
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
	
	
	
	
}