<?php
class Subject_ExtendedController extends HM_Controller_Action_Crud {

    protected $classifierCache = array();
    protected $sessionsCache   = array();
    protected $groups          = array();
    protected $programms       = array();
	
    protected $_subjectId = 0;
    protected $_subject   = null;
    protected $_tutorsOnSubject = array(); # тьюторы, назначенные на сесию 'subject_id' => array('mid'=>'mid')

    private $_subjectMaxBallCache = array();
    private $_classifierCache     = array();
    private $_baseIdCache         = array();
    private $_teacherCache        = array();
    private $_tutorCache          = array();
    private $_tutorOnGroupCache   = array();
    private $_programmCache       = array();
    private $_groupCache          = array();
    private $_sessionsCache       = array(); # сессии для учебных курсов. key - учебный курс, value - array of сессии

    public function init() 
	{
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);

        if ($this->_subjectId > 0) {
            $this->_subject = $this->getOne(
                $this->getService('Subject')->find($this->_subjectId)
            );            
            $this->_setParam('subid', $this->_subjectId);
        } 

        if($this->_getParam('subid', 0) > 0){
            $subjectId = (int) $this->_getParam('subid', 0) ;
            $model = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));           
        }

        $test 		= $this->_getParam('test', 0);
        $baseType	= $this->_getParam('base', false);

        // Накидываем модификаторы формы
        if(($baseType === false) && ($subjectId > 0)){
            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if($subject){
                $baseType = $subject->getBaseType();
            }
        }
        parent::init();       
    }

    protected function _redirectToIndex()
    {
        if ($this->_subjectId > 0) {
            $this->_redirector->gotoSimple('card', 'index', 'subject', array('subject_id' => $this->_subjectId));
        }

        if ($this->_getParam('base') == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $this->_redirector->gotoUrl($this->view->url(array('action' => 'index', 'controller' => 'list', 'module' => 'subject', 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION, 'subid' => null)) . '/?page_id=m0607');
        }
        $this->_redirector->gotoSimple('index');
    }

    public function indexAction()
    {
		$config = Zend_Registry::get('config');

		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
        
		
		try {
		if(!$this->userService)			{ $this->userService = $this->getService('User'); 			}
		if(!$this->groupService)		{ $this->groupService = $this->getService('StudyGroup'); 	}
		if(!$this->programmService)		{ $this->programmService = $this->getService('Programm'); 	}
		
		$isExport 		= $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		
		#if(!$isSetEmptyQuery){
		#	$this->userService->clearCache();
		#	$this->groupService->clearCache();
		#	$this->programmService->clearCache();
		#}
		
		
		#if(!$isSetEmptyQuery){
		#	$this->tutorList 		= $this->userService->getTutorList();
		#	$this->tutorGroupList	= $this->userService->getTutorGroupList();				
		#	$this->teacherList 		= $this->userService->getTeacherList();
		#}
		
		$baseType 				= $this->_getParam('base', 0);
		$url 					= array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => '{{subject_id}}');
		$cardName 				= ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) ? (_('Карточка учебной сессии')) : (_('Карточка учебного курса'));
        
		$this->view->baseType = $baseType;
		
		
		if ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
			#$this->groups 		= $this->groupService->getSubjectGroupList(); //--получаем список групп.
			#$this->programms 	= $this->programmService->getSubjectProgrammList(); //--получаем список программ.
		} else {
			if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
                $this->_request->setParam('ordergrid', 'name_ASC');
            }
		}

		$fields = array(
            'subid'				  => "CONCAT(s.subid, CONCAT('~', sgu.group_id))", # для разбора при назначении на сессию-группу.
            'subject_id' 		  => 's.subid',
            'basetype' 			  => 's.base',
            'name' 				  => 's.name',
            'base_id'  			  => 's.base_id',
            'classifiers' 		  => 's.subid',
            'max_ball_sum' 		  => 's.subid',
            'year_of_publishing'  => 's.year_of_publishing',            
			'external_id' 		  => 's.external_id',
            'begin' 			  => "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.begin IS NULL) THEN s.begin_planned ELSE s.begin END",
            'end' 				  => "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.end IS NULL) THEN s.end_planned ELSE s.end END",
			'end_debt' 			  => 's.time_ended_debt',			
			'tutor_name' 		  => 's.subid', 
			'tutor_name_in_group' => 'sgu.group_id', 
			'teacher_name' 		  => 's.subid',
            'zet'         		  => 's.zet',            
            'chair'       		  => 's.chair',
            'exam_type'   		  => 's.exam_type',
            'hours_total' 		  => 's.hours_total',
            'classroom'   		  => 's.classroom',
            'self_study'  		  => 's.self_study',
            'lection'     		  => 's.lection',
            'lab'         		  => 's.lab',
            'practice'    		  => 's.practice',
            'exam'        		  => 's.exam',
            'sessions'  		  => 's.subid', #'s.base',
			'isDO'  			  => new Zend_Db_Expr('CASE WHEN s.isDO IS NULL THEN 0 ELSE s.isDO END'),			
			'programm' 			  => 's.subid',
			'students' 			  => new Zend_Db_Expr('COUNT(DISTINCT ps.mid)'),	# всего в группе. Но может быть доступно тьютору меньше
			#'group_name'		  => new Zend_Db_Expr("CASE WHEN sg.name IS NULL THEN 'Нет' ELSE sg.name END"),
			'group_name'          => 'sgu.group_id',
			'mark_type'           => 's.mark_type',
        );
        
        $select = $this->getService('Subject')->getSelect();
        $select->from(array('s' => 'subjects'), $fields);
        $select->joinLeft(array('st' 	=> 'Students'), 			'st.CID = s.subid AND st.MID > 0',													array());
		$select->joinLeft(array('ps' 	=> 'People'), 				'st.MID = ps.MID AND ps.blocked != '.(int) HM_User_UserModel::STATUS_BLOCKED,		array());
		#$select->joinLeft(array('base' 	=> 'Subjects'), 			's.base_id = base.subid', 			array());
        $select->joinLeft(array('sgu' => 'study_groups_users'), 	'sgu.user_id = st.MID',       array());
		#$select->joinLeft(array('sg'  => 'study_groups'), 			'sg.group_id = sgu.group_id', array());
		
		if ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
			$select->where('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
		} else {
			$select->where('s.base != ? OR s.base IS NULL', HM_Subject_SubjectModel::BASETYPE_SESSION);
		}
       
	    //Область ответственности
        #$options = $this->getService('Dean')->getResponsibilityOptions($this->userService->getCurrentUserId());
        #if($options['unlimited_subjects'] != 1){
		#	$select->joinInner(array('d2' => 'deans'), 'd2.subject_id = s.subid', array());
        #    $select->where('d2.MID = ?', $this->userService->getCurrentUserId());
        #}

		
        $select->group(array(
			's.subid',				
			's.base',
			's.name',
			's.year_of_publishing',
			's.period_restriction_type',
			's.begin',
			's.begin_planned',
			's.end_planned',
			's.end',
			's.time_ended_debt',
			's.price',
			's.external_id',
			's.longtime',
			's.base_id',
			's.zet',
			's.chair',
			's.exam_type',
			's.hours_total',
			's.classroom',
			's.self_study',
			's.lection',
			's.lab',
			's.practice',
			's.exam',
			's.isDO',
			#'sg.name',	
			#'sg.group_id',
			's.mark_type',
			'sgu.group_id',
		));


		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}

		$grid = $this->getGrid($select, array(          
            'state'    					=> array('hidden' => true),
            'period_restriction_type' 	=> array('hidden' => true),
            'subject_id' 				=> array('hidden' => true),
            'subid' 					=> array('hidden' => true),
            'basetype'    				=> array('hidden' => true),
            'period' 					=> array('hidden' => true),
            'longtime' 					=> array('hidden' => true),			
			'exam' 						=> array('hidden' => true),
            'classroom' 				=> array('hidden' => true),
            'self_study' 				=> array('hidden' => true),
            'lection' 					=> array('hidden' => true),
            'lab' 						=> array('hidden' => true),
            'practice' 					=> array('hidden' => true),
            'mark_type' 				=> array('hidden' => true),
            'base_id'				 	=> array('title' => _('Учебный курс')),
			'type' 						=> array('title' => _('Тип')),
			'max_ball_sum' 				=> array('title' => _('БРС')),
            'year_of_publishing' 		=> array('title' => _('Год издания')),            
			'external_id' 				=> array('title' => _('ID')),
			'end' 						=> array('title' => _('Дата окончания')),
			'end_debt' 					=> array('title' => _('Дата продления')),
			'sessions' 					=> array('title' => _('Сессии')),
			'zet' 						=> array('title' => _('ЗЕТ')),
            'chair' 					=> array('title' => _('Кафедра')),			
            #'group_name' 				=> array('title' => _('Группа')),
                  
            'name' => array('title' => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'subject_id' => '')) . '{{subject_id}}', $cardName) . ' <a href="' . $this->view->url($url, null, true, false) . '">{{name}}</a>'
            ),
            'begin' => array('title' => _('Дата начала'),
                'id' => 'dsad'
            ),
            
            'students' => array('title' => _('Кол слуш'),
                'callback' => array(
                    'function'	=> array($this, 'updateStudents'),
                    'params'	=> array('{{students}}', '{{subject_id}}')
                )
            ),           		
			
            'classifiers' => array('title' => _('Классификаторы'),
                'callback' => array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{classifiers}}', $select)
                )
            ),
            
			'tutor_name' => array('title' => _('Тьюторы (все)'),
                'callback' => array(
                    'function' => array($this, 'updateTutors'),
                    'params' => array('{{tutor_name}}', $select)
                )
            ),
			
			'tutor_name_in_group' => array('title' => _('Тьюторы (группа)'),
                'callback' => array(
                    'function' => array($this, 'updateTutorsOnGroup'),
                    'params' => array('{{subject_id}}', '{{tutor_name_in_group}}', $select)
                )
            ),
			
            'teacher_name' => array('title' => _('Преп.'),
                'callback' => array(
                    'function' => array($this, 'updateTeachers'),
                    'params' => array('{{teacher_name}}', $select)
                )
            ),
			
			'programm' => array('title' => _('Программа'),
                'callback' => array(
                    'function' => array($this, 'updateProgramm'),
                    'params' => array('{{programm}}', $select)
                )
            ),

            'group_name' => array('title' => _('Группа'),
                'callback' => array(
                    'function' => array($this, 'updateGroup'),
                    'params' => array('{{group_name}}', $select)
                )
            ),
            
            'exam_type' => array('title' => _('Контроль'),
                'callback' => array(
                    'function' => array($this, 'updateExamType'),
                    'params' => array('{{exam_type}}')
                )
            ),            			
			
			'isDO' => array('title' => _('ДО'),
                'callback' => array(
                    'function' => array($this, 'updateIsDO'),
                    'params' => array('{{isDO}}')
                )
            ),            
        ),
        array(
			'name' 					=> null,
			'students' 				=> null,						
			'year_of_publishing' 	=> null,			
			'external_id' 			=> null, 
			'exam' 					=> null,
			'hours_total' 			=> null,
			'classroom' 			=> null,
			'self_study' 			=> null,
			'lection' 				=> null,
			'lab' 					=> null,
			'practice' 				=> null,		
			'chair' 				=> null,
			'begin' 				=> array('render' => 'SubjectDate'),
			'end' 					=> array('render' => 'SubjectDate'),
			'end_debt' 				=> array('render' => 'DateSmart'),	
			'zet' 					=> array('values' => HM_Subject_SubjectModel::getZetValues()),			
			'exam_type' 			=> array('values' => HM_Subject_SubjectModel::getExamTypes()),		
			'isDO' 					=> array('values' => HM_Subject_SubjectModel::getFacultys()),								
			'type' 					=> array('values' => HM_Subject_SubjectModel::getTypes()),                			
			'sessions' 				=> array('callback' => array('function' => array($this, 'filterSessions'))),
			
			'tutor_name' => array(						
				'callback' => array(
					'function'	=>array($this, 'tutorFilter'),
					'params'	=>array()
				)
			),
			
			'tutor_name_in_group' => array(						
				'callback' => array(
					'function'	=>array($this, 'tutorGroupFilter'),
					'params'	=>array()
				)
			),
			
			'teacher_name' => array(					
				'callback' => array(
					'function'	=>array($this, 'teacherFilter'),
					'params'	=>array()
				)
			),
	
			'programm' => array(						
				'callback' => array(
					'function'	=>array($this, 'programmFilter'),
					'params'	=>array()
				)
			),
			
			'group_name' => array(						
				'callback' => array(
					'function'	=>array($this, 'groupFilter'),
					'params'	=>array()
				)
			),
			
			'base_id' =>
				array(						
					'callback' => array(
						'function'=>array($this, 'baseSubjectFilter'),
						'params'=>array()
				)
			),

			'classifiers' =>
				array(						
					'callback' => array(
						'function' => array($this, 'classifiersFilter'),
						'params'   => array()
				)
			),

			'max_ball_sum' =>
				array(						
					'callback' => array(
						'function' => array($this, 'maxBallSumFilter'),
						'params'   => array()
				)
			),	
        ));
		

        // Hide all unused fields for base subject
        if ($baseType != HM_Subject_SubjectModel::BASETYPE_SESSION) {        
            $grid->updateColumn('chair', 	 	array('hidden' => true));
            $grid->updateColumn('exam',  	 	array('hidden' => true));
            $grid->updateColumn('classroom', 	array('hidden' => true));
			$grid->updateColumn('self_study',	array('hidden' => true));			
            $grid->updateColumn('lection', 	 	array('hidden' => true));			
            $grid->updateColumn('lab', 		 	array('hidden' => true));			
            $grid->updateColumn('practice',  	array('hidden' => true));
            $grid->updateColumn('base_id', 	 	array('hidden' => true));           
			$grid->updateColumn('external_id',	array('hidden' => true));			
			$grid->updateColumn('end_debt', 	array('hidden' => true));
			$grid->updateColumn('isDO', 		array('hidden' => true));												
			$grid->updateColumn('programm', 	array('hidden' => true));
			$grid->updateColumn('hours_total', 	array('title' => _('Часы')));			
        } else {
            $grid->updateColumn('hours_total', 			array('hidden' => true));
            $grid->updateColumn('sessions', 			array('hidden' => true));
            $grid->updateColumn('year_of_publishing', 	array('hidden' => true));
			$grid->updateColumn('base_id', array(
                    'callback' => array(
                        'function' => array($this, 'updateBaseId'),
                        'params'   => array('{{base_id}}', $select)
                    )
                    
                )
            );
        }
		
        $grid->updateColumn('begin', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDateBegin'),
                'params' => array('{{begin}}', '{{period}}', '{{period_restriction_type}}')
            )
        ));

        $grid->updateColumn('end', array(
            'callback' => array(
                'function' => array($this, 'updateDateEnd'),
                'params' => array('{{end}}', '{{period}}', '{{longtime}}', '{{period_restriction_type}}')
            )
        ));
		
		$grid->updateColumn('end_debt', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
			'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{end_debt}}')
            )
        ));		
		
        $grid->updateColumn('type',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateType'),
                    'params'=> array('{{type}}')
                )
            )
        );

        $grid->updateColumn('sessions',
            array('callback' =>
                array('function' => array($this, 'sessionsCache'),
                      'params'   => array('{{sessions}}', $select)
                )
            )
        );

        $grid->updateColumn('max_ball_sum',
            array('callback' =>
                array('function' => array($this, 'updateMaxBallSum'),
                      'params'   => array('{{max_ball_sum}}', $select)
                )
            )
        );


        

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{source}}','{{state}}', '{{period_restriction_type}}', '{{basetype}}')
            )
        );
		
		
		// пункт "назначить тьюторов на курсы"        
		$tutorCollection = $this->userService->fetchAllJoinInner('Tutor'); //--0.5 сек.        
		$tutors 		 = array();

        if ( count($tutorCollection) ) {
            foreach ( $tutorCollection as $tutor) {
                $tutors[$tutor->MID] = $tutor->getName();
            }
        }
        asort($tutors,SORT_LOCALE_STRING);
        $tutors = array(_('Выберите тьюторов')) + $tutors;

        $grid->addMassAction(array('module' => 'subject', 'controller' => 'extended', 'action' => 'group-assign',),
								_('Назначить тьюторов на группу'),
								_('Вы уверены?')
		);
        $grid->addSubMassActionSelect($this->view->url(array('module' => 'subject', 'controller' => 'extended', 'action' => 'group-assign',)),
								'tutorsId[]',
								$tutors
		);
		
		$grid->addMassAction(array('module' => 'subject', 'controller' => 'extended', 'action' => 'group-unassign-selected',),
								_('Удалить назначение тьюторов на группу'),
								_('Вы уверены?')
		);
        $grid->addSubMassActionSelect($this->view->url(array('module' => 'subject', 'controller' => 'extended', 'action' => 'group-unassign-selected',)),
								'tutorsId[]',
								$tutors
		);
		

		$grid->addMassAction(array('module' => 'subject', 'controller' => 'extended', 'action' => 'group-unassign',),
								_('Удалить назначение ВСЕХ тьюторов на группу'),
								_('Вы уверены?')
		);		
		

        if($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){           
			$gridId  = $grid->getGridId();
			$default = new Zend_Session_Namespace('default');        		
			$filters = $default->grid['subject-extended-index'][$gridId]['filters'];
			if(empty($filters)){				
				$default->grid['subject-extended-index'][$gridId]['filters']['end[from]'] = date('d.m.Y');				
			}			
        }
		
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid            = $grid->deploy();
		$this->view->baseType        = $baseType;
		$this->view->issetDouble     = $this->getService('Subject')->getMultipleIDSubjects();
		
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			
		}
    }
	
	public function groupAssignAction(){
		try {
			$tutors	= $this->_getParam('tutorsId', array());
			$base 	= ( int ) $this->_request->getParam('base', 0);
			$rows	= explode(',', $this->_getParam('postMassIds_grid',array()));
			if(empty($rows) || empty($tutors)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не выбрано ни одной записи или тьютора'))
				);
				$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));	
			}
			$serviceSG =$this->getService('SubjectGroup');
			foreach($rows as $row){
				$tmp = explode('~', $row);
				if(empty($tmp[0]) || empty($tmp[1])){ continue; }
				
				$session_id = (int)$tmp[0];
				$group_id	= (int)$tmp[1];
				
				foreach($tutors as $tutor_id){					
					$assigned = $serviceSG->assignTutorToGroup(intval($tutor_id), $session_id, $group_id);					
				}
			}
			$this->_flashMessenger->addMessage(_('Тьюторы успешно назначены'));
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось назначить тьюторов'))
			);				
		}
		
		$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));	
	}
	
	
	public function groupUnassignSelectedAction(){
		try {
			$tutors	= $this->_getParam('tutorsId', array());
			$base 	= ( int ) $this->_request->getParam('base', 0);
			$rows	= explode(',', $this->_getParam('postMassIds_grid',array()));
			if(empty($rows) || empty($tutors)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не выбрано ни одной записи или тьютора'))
				);
				$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));	
			}
			$serviceSG =$this->getService('SubjectGroup');
			foreach($rows as $row){
				$tmp = explode('~', $row);
				if(empty($tmp[0]) || empty($tmp[1])){ continue; }
				
				$session_id = (int)$tmp[0];
				$group_id	= (int)$tmp[1];
				
				foreach($tutors as $tutor_id){
					$isUnAssigned = $serviceSG->unassignTutor($tutor_id, $session_id, $group_id);
				}				
			}
			$this->_flashMessenger->addMessage(_('Тьюторы успешно откреплены'));
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось открепить тьюторов'))
			);				
		}
		
		$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));	
	}
	
	public function groupUnassignAction(){
		try {			
			$base 	= ( int ) $this->_request->getParam('base', 0);
			$rows	= explode(',', $this->_getParam('postMassIds_grid',array()));
			if(empty($rows)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не выбрано ни одной записи'))
				);
				$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));	
			}
			$serviceSG =$this->getService('SubjectGroup');
			foreach($rows as $row){
				$tmp = explode('~', $row);
				if(empty($tmp[0]) || empty($tmp[1])){ continue; }
				
				$session_id = (int)$tmp[0];
				$group_id	= (int)$tmp[1];
								
				$assigned = $serviceSG->unassignTutorToGroup($session_id, $group_id);									
			}
			$this->_flashMessenger->addMessage(_('Назначения тьюторов на группы успешно удалены'));
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось удалить назначения'))
			);				
		}
		
		$this->_redirector->gotoSimple('index', 'extended', 'subject', array('base' => $base));
	}
	
    
    public function filterSessions($data) {
        $value = $data['value'];
        $select = $data['select'];
        $select->joinInner(
                array('ss' => 'subjects'),
                'ss.base_id = s.subid',
                array()
        );
        
        $select->where('ss.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
        $select->where('ss.name LIKE ?', '%'.$value.'%');
    }
    

    public function updateActions($source, $state, $type, $baseType, $actions)
    {

        // похоже на частное требование заказчика, просочившееся в trunk
        if (($state != HM_Subject_SubjectModel::STATE_CLOSED) || ($type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)) {
            $this->unsetAction($actions, array('controller' => 'index', 'action' => 'statement'));
        }
        if ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $this->unsetAction($actions, array('action' => 'new', 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION));
        } else {
            $this->unsetAction($actions, array('action' => 'copy-from-base'));
        }
            return $actions;
        }



    public function updateType($type)
    {
        $types = HM_Subject_SubjectModel::getTypes();
        return $types[$type];
    }

    public function updateStudents($students, $subject_id)
    {
        if (!empty($students)) {
            return '<a href="' . $this->view->url(array('module' => 'assign', 'controller' => 'student', 'action' => 'index', 'gridmod' => null, 'subject_id' => $subject_id)) . '" title="' . _('Список слушателей') . '">' . $students . '</a>';
        }
        return $students;
    }

    public function updateDateBegin($date, $period, $periodRestrictionType)
    {
        switch ($period) {
        	case HM_Subject_SubjectModel::PERIOD_FREE:
        		return _('Без ограничений');
        	case HM_Subject_SubjectModel::PERIOD_FIXED:
        		return _('Дата регистрации на курс');
        	default:
                if ($periodRestrictionType == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                    $date .= HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Плановая дата. Фактически начало/окончание обучение по курсу определяется преподавателем'), 1);
                }
                return $date;
        }
    }

    public function updateDateEnd($date, $period, $longtime, $periodRestrictionType)
    {
        $date = $this->getDateForGrid($date);
        switch ($period) {
        	case HM_Subject_SubjectModel::PERIOD_FREE:
        		return _('Без ограничений');
        	case HM_Subject_SubjectModel::PERIOD_FIXED:
        		return sprintf(_('Через %s дней'), $longtime);
        	default:
                if ($periodRestrictionType == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                    $date .= HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Плановая дата. Фактически начало/окончание обучения по курсу определяется преподавателем'), 1);
                }
                return $date;
        }
    }
	
	public function updateDate($date)
    {        
		return $date;    
    }



    public function updateBaseId($baseId, $select)
    {
        if(empty($this->_baseIdCache)){
            $fetchAll = $select->query()->fetchAll();
            $baseIds  = array();
            foreach($fetchAll as $item){
                $baseIds[] = $item['base_id'];
            }
			$subSelect = $this->getService('Subject')->getSelect();
			$subSelect->from('subjects', array('subid', 'name'));
			$subSelect->where($this->quoteInto('subid IN (?)', $baseIds));
			$res = $subSelect->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $item){
					$this->_baseIdCache[$item['subid']] = $item['name'];
				}
			}
        }

        if(!array_key_exists($baseId, $this->_baseIdCache)){
        	return _('Нет');
        }
        
        $url         = $this->view->url(array('module'=>'subject', 'controller'=>'index', 'action'=>'card', 'subject_id'=>$baseId));
        $subjectName = $this->_baseIdCache[$baseId];
        return "<a href='{$url}'>{$subjectName}</a>";
    }
    
    public function updateExamType($examType)
    {
        $examTypes = HM_Subject_SubjectModel::getExamTypes();
        return $examTypes[$examType];
    }
	
	# кэш сессий, закрепленных за переданным учебным курсом. 
	# @field - id учебного курса
    public function sessionsCache($field, $select)
	{
		$subid = $field;
		
		if(empty($this->_sessionsCache)){
			$fetchAll = $select->query()->fetchAll();
            $subjectIds  = array();
            foreach($fetchAll as $item){
                $subjectIds[] = $item['sessions'];
            }
			$subSelect = $this->getService('Subject')->getSelect();
			$subSelect->from('subjects', array('base_id', 'subid', 'name'));
			$subSelect->where($this->quoteInto('base_id IN (?)', $subjectIds));
			$subSelect->where($this->quoteInto('base=?', HM_Subject_SubjectModel::BASETYPE_SESSION));
			$res = $subSelect->query()->fetchAll();
			if(empty($res)){
				$this->_sessionsCache[0] = false; # если запрос ничего не вернул, нужно заполнить кэш чем-то, чтобы повторно не выполнять запрос и снова получить пустоту.
				return _('Нет');
			}
			
			foreach($res as $item){
				$this->_sessionsCache[$item['base_id']][$item['subid']] = $item['name'];
			}			
        }
		
        if(!array_key_exists($subid, $this->_sessionsCache)){
        	return _('Нет');
        }
		
		$subjects = $this->_sessionsCache[$subid];
		if(empty($subjects)){
			return _('Нет');
		}
		
		$count  = count($subjects);
		$result = array('<p class="total">' . sprintf(_n('сессия plural', '%s сессия', $count), $count) . '</p>');
		foreach($subjects as $subjectId => $subjectName){
			$url = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId));
            $result[] = "<p><a href='{$url}'>{$subjectName}</a></p>";
		}
		
		return implode('',$result);
    }

	# field = subid
    public function updateMaxBallSum($field, $select)
    {
    	if(empty($this->_subjectMaxBallCache)){
    		$subjectIds = array();
    		$res        = $select->query()->fetchAll();
    		foreach($res as $item){
    			if($item['mark_type'] != HM_Mark_StrategyFactory::MARK_BRS){ continue; }
    			$subjectIds[$item['subject_id']] = $item['subject_id'];
    		}
    		if(!empty($subjectIds)){
    			$subSelect = $this->getService('Subject')->getSelect();
				$subSelect->from('schedule', array('CID' => 'CID', 'max_ball_sum' => 'SUM(max_ball)'));
				$subSelect->where('required = 1');
				$subSelect->where($this->quoteInto('CID IN (?)', $subjectIds));
				$subSelect->group('CID');
				$res = $subSelect->query()->fetchAll();
				if(!empty($res)){
					foreach($res as $item){
						$this->_subjectMaxBallCache[$item['CID']] = $item['max_ball_sum'];
					}
				}
    		}
    	}
		return $this->_subjectMaxBallCache[$field];
    }

	# field = subid
    public function updateClassifiers($field, $select)
    {
    	if(empty($this->_classifierCache)){
    		$subjectIds = array();
    		$res        = $select->query()->fetchAll();
    		foreach($res as $item){
				$subjectIds[$item['subject_id']] = $item['subject_id'];
    		}
    		if(!empty($subjectIds)){
    			$subSelect = $this->getService('Subject')->getSelect();
    			$subSelect->from(array('c'  => 'classifiers'), array('c.classifier_id', 'c.name', 'cl.item_id'));
				$subSelect->join(array('cl' => 'classifiers_links'), 'cl.classifier_id = c.classifier_id', array());
				$subSelect->where($this->quoteInto('cl.item_id IN (?)', $subjectIds));
				$subSelect->where($this->quoteInto('cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT));
				$res = $subSelect->query()->fetchAll();
				if(!empty($res)){
					foreach($res as $item){
						$this->_classifierCache[$item['item_id']][$item['classifier_id']] = $item['name'];
					}
				}
    		}
    	}
    	$classifierItems = $this->_classifierCache[$field];
    	if(empty($classifierItems)){
    		return _('Нет');
    	}
    	
		$count = 0;
		$list  = array();
		foreach($classifierItems as $classifierName){
			$list[] = '<p>'.$classifierName.'</p>';
			$count++;
		}
		$result = ($count > 1) ? array('<p class="total">' . sprintf(_n('классификатор plural', '%s классификатор', $count), $count) . '</p>') : array();
		if(isset($result[0])){ array_unshift($list, $result[0]); }		
		$result = implode('',$list);
		return $result;
    }

    public function getFilterByRequest(\Zend_Controller_Request_Http $request) {
        $factory = $this->getService('ESFactory');
        /*@var $filter Es_Entity_AbstractFilter */
        $filter = $factory->newFilter();

        $userId = (int)$this->getService('User')->getCurrentUserId();
        $filter->setUserId($userId);

        $subject = $request->getParam('subject_id', null);
        if ($subject === null) {
            $group = $this->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, 0
            );
        }
        if ($group->getId() !== null) {
            $filter->setGroupId($group->getId());
        }
        /*@var $eventType Es_Entity_AbstractEventType */
        $eventType = $factory->newEventType();
        $eventType->setId(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_SCORE_TRIGGERED);
        $filter->setEventType($eventType);
        return $filter;
    }
	

	#_tutorsOnSubject
	public function getAssignedTutors($subject_id){
		
		if(isset($this->_tutorsOnSubject[$subject_id])){ return $this->_tutorsOnSubject[$subject_id]; }
		
		if(!$this->userService) { $this->userService = $this->getService('User'); }
		
        $select = $this->userService->getSelect();
		$select->from('Tutors', array('MID'));
		$select->where('CID = ?', $subject_id);
		$select->where('MID > 0');
		$res = $select->query()->fetchAll();
		if(!$res){ return array(); }
		foreach($res as $t){
			$this->_tutorsOnSubject[$subject_id][$t['MID']] = $t['MID'];
		}
		return $this->_tutorsOnSubject[$subject_id];
	}

	public function loadGroupCache($select)
	{
		if(!empty($this->_groupCache)){
			return true;
		}

		$groupIds = array();
		$res      = $select->query()->fetchAll();
		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$groupIds[$item['group_name']] = $item['group_name'];
		}

		if(empty($groupIds)){
			return false;
		}

		$subSelect = $this->getService('Subject')->getSelect();
		$subSelect->from('study_groups', array('group_id', 'name'));		
		$subSelect->where($this->quoteInto('group_id IN (?)', $groupIds));
		$res = $subSelect->query()->fetchAll();

		if(!empty($res)){
			foreach($res as $item){
				$this->_groupCache[$item['group_id']] = $item['name'];
			}
		}
		return true;
	}

	public function loadProgrammCache($select)
	{
		if(!empty($this->_programmCache)){
			return true;
		}

		$subjectIds = array();
		$res        = $select->query()->fetchAll();
		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$subjectIds[$item['subject_id']] = $item['subject_id'];
		}

		if(empty($subjectIds)){
			return false;
		}
		
		$subSelect = $this->getService('Subject')->getSelect();
		$subSelect->from(array('p' => 'programm'), array('subjectId'=>'pe.item_id', 'programmName'=>'p.name'));
		$subSelect->join(array('pe' => 'programm_events'), 'pe.programm_id = p.programm_id', array());
		$subSelect->where($this->quoteInto('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
		$subSelect->where($this->quoteInto('pe.item_id IN (?)', $subjectIds));
		$subSelect->group(array('pe.item_id', 'p.name'));
		$res = $subSelect->query()->fetchAll();
		
		if(!empty($res)){
			foreach($res as $item){
				$this->_programmCache[$item['subjectId']] = $item['programmName'];
			}
		}
		return true;
	}

	public function loadTeacherCache($select)
	{
		if(!empty($this->_teacherCache)){
			return true;
		}

		$subjectIds = array();
		$res        = $select->query()->fetchAll();
		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$subjectIds[$item['subject_id']] = $item['subject_id'];
		}

		if(empty($subjectIds)){
			return false;
		}
		
		$subSelect = $this->getService('Subject')->getSelect();
		$subSelect->from(array('t'=>'Teachers'), array('t.CID', 'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
		$subSelect->join(array('p'=>'People'), 't.MID = p.MID', array());
		$subSelect->where($this->quoteInto('t.CID IN (?)', $subjectIds));
		$subSelect->where($this->quoteInto('t.MID > ?', 0));
		$subSelect->order(array('p.LastName', 'p.FirstName', 'p.Patronymic'));
		$res = $subSelect->query()->fetchAll();
		if(!empty($res)){
			foreach($res as $item){
				$this->_teacherCache[$item['CID']][$item['MID']] = $item['LastName'] . ' ' . $item['FirstName'] . ' ' . $item['Patronymic'];
			}
		}
		return true;
	}

	public function loadTutorCache($select)
	{
		if(!empty($this->_tutorCache)){
			return true;
		}

		$subjectIds = array();
		$res        = $select->query()->fetchAll();
		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$subjectIds[$item['subject_id']] = $item['subject_id'];
		}

		if(empty($subjectIds)){
			return false;
		}
		
		$subSelect = $this->getService('Subject')->getSelect();
		$subSelect->from(array('t'=>'Tutors'), array('t.CID', 'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
		$subSelect->join(array('p'=>'People'), 't.MID = p.MID', array());
		$subSelect->where($this->quoteInto('t.CID IN (?)', $subjectIds));
		$subSelect->where($this->quoteInto('t.MID > ?', 0));
		$subSelect->order(array('p.LastName', 'p.FirstName', 'p.Patronymic'));
		$res = $subSelect->query()->fetchAll();
		if(!empty($res)){
			foreach($res as $item){
				$this->_tutorCache[$item['CID']][$item['MID']] = $item['LastName'] . ' ' . $item['FirstName'] . ' ' . $item['Patronymic'];
			}
		}
		return true;
	}

	public function loadTutorOnGroupCache($select)
	{
		if(!empty($this->_tutorOnGroupCache)){
			return true;
		}

		$subjectIds = array();
		$res        = $select->query()->fetchAll();
		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$subjectIds[$item['subject_id']] = $item['subject_id'];
		}

		$subSelect = $this->getService('Subject')->getSelect();
        $subSelect->from(array('tg'=>'Tutors_groups'), array('tg.MID', 'tg.CID', 'tg.GID'));
		$subSelect->join(array('t' => 'Tutors'), 't.CID = tg.CID AND t.MID = tg.MID', array());
        $subSelect->where('tg.CID > 0 AND tg.MID > 0 AND tg.GID > 0');
        $subSelect->where($this->quoteInto('tg.CID IN (?)', $subjectIds));
        $res = $subSelect->query()->fetchAll();

		if(empty($res)){
			return false;
		}

		foreach($res as $item){
			$this->_tutorOnGroupCache[$item['CID']][$item['MID']][$item['GID']] = $item['GID'];
		}
		return true;
	}
    
    public function updateTutors($subjectId, $select)
    {
		$this->loadTutorCache($select);		

		if(!array_key_exists($subjectId, $this->_tutorCache)){
			return _('Нет');
		}
		
		$count  = 0;
		$list   = array();
		$tutors = $this->_tutorCache[$subjectId];

		foreach($tutors as $tutorName){
			$list[] = '<p>'.$tutorName.'</p>';
			$count++;
		}

		if(empty($count)){
			return _('Нет');
		}

		$result = ($count > 1) ? array('<p class="total">' . sprintf(_n('тьютор plural', '%s тьютор', $count), $count) . '</p>') : array();
		if(isset($result[0])){ array_unshift($list, $result[0]); }
		$result = implode('',$list);
		return $result;
    }
	
	public function updateTutorsOnGroup($subjectId, $groupId, $select)
	{		        
		$this->loadTutorCache($select);
		$this->loadTutorOnGroupCache($select);

		if(!array_key_exists($subjectId, $this->_tutorCache)){
			return _('Нет');
		}

		$tutors       = $this->_tutorCache[$subjectId];
		$groupAssigns = $this->_tutorOnGroupCache[$subjectId];
		
		$count  = count($tutors);
		if(empty($count)){
			return _('Нет');
		}

		$count  = 0;
		$result = array();
		foreach($tutors as $tutorId => $tutorName){
 			$tutorGroupAssigns = $groupAssigns[$tutorId];
 			$style = '';
 			
			# тьютор назначен тлько на сессию. На группы не назначен. Доступны все группы
 			if(empty($tutorGroupAssigns)){
 				$style = 'color:green;';
 			
 			# тьютор назначен на другие группы, но не искомую. Исключаем его из списка
 			} elseif(!in_array($groupId, $tutorGroupAssigns)){
 				continue;
 			}
			$count++;
			$result[] = '<p style="' . $style . '">'.$tutorName.'</p>';
		}

		if(empty($count)){
			return _('Нет');
		}
		
		if($count>1){
			array_unshift($result, '<p class="total">' . sprintf(_n('тьютор plural', '%s тьютор', $count), $count) . '</p>');
		}
		return implode('', $result);
    }
	
	
	public function getAssignedOnAllSubjectTutors($subject_id) {
		$allTutors	  = $this->getAssignedTutors($subject_id);
		$withoutGroup = $allTutors;		
		# если тьютор назначен на конкретного студента, то это не учитывается.
		if(!empty($allTutors)){
			foreach($allTutors as $tutor_id){
				if(!isset($this->tutorGroupList[$subject_id]) || empty($this->tutorGroupList[$subject_id])){ continue; }
				foreach($this->tutorGroupList[$subject_id] as $tutors){
					if(in_array($tutor_id, $tutors)){ # тьютор назначен на группу, его ну нудо в общий список.
						unset($withoutGroup[$tutor_id]);
					}				
				}
			}
		}
		return $withoutGroup;		
	}
	
	
    
    public function updateTeachers($subjectId, $select)
    {
		$this->loadTeacherCache($select);
		
		if(!array_key_exists($subjectId, $this->_teacherCache)){
			return _('Нет');
		}

		$teachers = $this->_teacherCache[$subjectId];
		
		$count  = count($teachers);
		if(empty($count)){
			return _('Нет');
		}

		$result = ($count > 1) ? array('<p class="total">' . sprintf(_n('преподаватель plural', '%s преподаватель', $count), $count) . '</p>') : array();
		foreach($teachers as $teacherName){
 			$result[] = '<p>'.$teacherName.'</p>';
		}
		return implode('', $result);
    }
	
	public function tutorFilter($data)
	{
		$value  = trim($data['value']);
		$select = $data['select'];
		if(empty($value)){
			return;
		}
		
		if(mb_strtolower($value) == 'нет'){				
			$select->joinLeft(array('tu' => 'Tutors'), 'tu.CID = s.subjid AND tu.CID > 0', array());
			$select->where('tu.MID IS NULL');
			return;
		}
		 
		if(!$this->userService) { $this->userService = $this->getService('User'); }
		$usesrSelect = $this->userService->getSelect();
		$usesrSelect->from(array('p'=>'People'), array('t.CID'));
		$usesrSelect->join(array('t'=>'Tutors'), 't.MID = p.MID AND t.MID > 0 AND t.CID > 0', array());
		$usesrSelect->where($this->quoteInto(
			"(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)) LIKE LOWER(?))",
			'%'.$value.'%'
		));
		
		$res        = $usesrSelect->query()->fetchAll();
		$subjectIds = array();		
		if(empty($res)){
			$select->where('1=0');
			return;
		}

		foreach($res as $item){
			$subjectIds[$item['CID']] = $item['CID'];
		}

		if(empty($subjectIds)){
			$select->where('1=0');
			return;
		}
		$select->where($this->quoteInto('s.subid IN (?)', $subjectIds));
	}
	
	
	public function tutorGroupFilter($data)
	{        		
		$value  = trim($data['value']);
		$select = $data['select'];
		
		if(empty($value)){
			return;
		}

		if(mb_strlen($value) < 3){
			return; 
		}

		if(!$this->userService) { $this->userService = $this->getService('User'); }

		if(mb_strtolower($value) == 'нет'){
			$subSelect = $this->userService->getSelect();
			$subSelect->from(array('tg'=>'Tutors_groups'), array('t.CID'));
			$subSelect->joinLeft(array('t'=>'Tutors'), 't.CID = tg.CID', array());
			$subSelect->where('tg.CID IS NULL');
			$res = $subSelect->query()->fetchAll();
			if(empty($res)){
				return;
			}
			$subjectIds = array();
			foreach($res as $item){
				$subjectIds[$item['CID']] = $item['CID'];
			}
			$select->where($this->quoteInto('s.subid IN (?)', $subjectIds));
			return;
		}
		
		$subSelect = $this->userService->getSelect();
		$subSelect->from(array('p'=>'People'), array('tg.CID', 'tg.GID'));
		$subSelect->join(array('tg'=>'Tutors_groups'), 'tg.MID = p.MID AND tg.CID > 0 AND tg.GID > 0', array());
		$subSelect->where($this->quoteInto(
			"(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)) LIKE LOWER(?))",
			'%'.$value.'%'
		));
		$subSelect->group(array('tg.CID', 'tg.GID'));

		$subSelect2 = $this->userService->getSelect();
		$subSelect2->from(array('p'=>'People'), array('t.CID'));
		$subSelect2->join(array('t'=>'Tutors'), 't.MID = p.MID AND t.CID > 0 AND t.MID > 0', array());
		$subSelect2->where($this->quoteInto(
			"(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)) LIKE LOWER(?))",
			'%'.$value.'%'
		));
		$subSelect2->group(array('t.CID'));

		$res  = $subSelect->query()->fetchAll();
		$res2 = $subSelect2->query()->fetchAll();
		
		$criteria = array();
		if(!empty($res)){
			foreach($res as $item){
				$criteria[] = '(s.subid=' . intval($item['CID']) . ' AND sgu.group_id=' . intval($item['GID']) . ')';
			}
		}

		if(!empty($res2)){
			foreach($res2 as $item){
				$criteria[] = '(s.subid=' . intval($item['CID']) . ')';
			}
		}

		if(empty($criteria)){
			$select->where('1=0');
			return;
		}

		$select->where(implode(' OR ', $criteria));
		return;
	}
	
	public function teacherFilter($data)
	{		
		$value  = trim($data['value']);
		$select = $data['select'];
		if(empty($value)){
			return;
		}

		if(mb_strtolower($value) == 'нет'){				
			$select->joinLeft(array('te' => 'Teachers'), 'te.CID = s.subid AND te.CID > 0', array());
			$select->where('te.MID IS NULL');
			return;
		}

		if(!$this->userService) { $this->userService = $this->getService('User'); }
		$usesrSelect = $this->userService->getSelect();
		$usesrSelect->from(array('p' => 'People'), array('t.CID'));
		$usesrSelect->join(array('t' => 'Teachers'), 't.MID=p.MID AND t.CID > 0', array());	
		$usesrSelect->where($this->quoteInto(
			"(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)) LIKE LOWER(?))",
			'%'.$value.'%'
		));

		$res = $usesrSelect->query()->fetchAll();
		if(empty($res)){
			$select->where('1=0');
			return;
		}
		
		$subjectIds = array();
		foreach($res as $item){
			$subjectIds[$item['CID']] = $item['CID'];
		}
		
		$select->where($this->quoteInto('s.subid IN (?)', $subjectIds));
	}
	
	public function groupFilter($data)
	{
		$value  = trim($data['value']);
		$select = $data['select'];

		if(empty($value)){
			return;
		}

		if(mb_strlen($value) < 2){
			return; 
		}

		if(mb_strtolower($value) == 'нет'){	
			$select->where('sgu.group_id IS NULL');
			return;
		}

		if(!$this->userService) { $this->userService = $this->getService('User'); }
		$usesrSelect = $this->userService->getSelect();
		$usesrSelect->from('study_groups', array('group_id'));
		$usesrSelect->where($this->quoteInto("name LIKE LOWER(?)", '%'.$value.'%'));
		$res = $usesrSelect->query()->fetchAll();
		if(empty($res)){
			$select->where('1=0');
			return;
		}

		$groupIDs = array();
		foreach($res as $item){
			$groupIDs[$item['group_id']] = $item['group_id'];
		}

		$select->where($this->quoteInto('sgu.group_id IN (?)', $groupIDs));
	}
	
	
	public function programmFilter($data)
	{	
		$value  = trim($data['value']);
		$select = $data['select'];

		if(empty($value)){
			return;
		}

		if(mb_strlen($value) < 3){
			return; 
		}
			
		if(!$this->userService) { $this->userService = $this->getService('User'); }
		
		$programmSelect = $this->userService->getSelect();
		$programmSelect->from(array('p' => 'programm'), array('CID' => 'pe.item_id'));	
		$programmSelect->join(array('pe' => 'programm_events'), 'pe.programm_id = p.programm_id', array());
		$programmSelect->where('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT);
		$programmSelect->where($this->quoteInto("p.name LIKE LOWER(?)", '%'.$value.'%'));
		$programmSelect->group(array('pe.item_id'));
		$res = $programmSelect->query()->fetchAll();

		if(empty($res)){
			$select->where('1=0');
			return;
		}

		$subjectIDs = array();		
		foreach($res as $u){
			$subjectIDs[$u['CID']] = $u['CID'];
		}
			
		$select->where($this->quoteInto('s.subid IN (?)', $subjectIDs));
	}
	
	public function updateIsDO($type)
	{
		$facultList = HM_Subject_SubjectModel::getFacultys();
		return $facultList[$type];		
	}
	
	public function updateGroups($subject_id){
		if(!$subject_id){
			return _('Нет');
		}
		if(isset($this->groups[$subject_id])){
			return $this->groups[$subject_id];	
		}
		return _('Нет');
	}
	
	public function updateProgramm($subjectId, $select)
	{
		$this->loadProgrammCache($select);

		if(!array_key_exists($subjectId, $this->_programmCache)){
			return _('Нет');
		}

		return $this->_programmCache[$subjectId];
	}

	public function updateGroup($groupId, $select)
	{
		$this->loadGroupCache($select);

		if(!array_key_exists($groupId, $this->_groupCache)){
			return _('Нет');
		}

		return $this->_groupCache[$groupId];
	}
	
	public function baseSubjectFilter($data){
		$value  = trim($data['value']);
		$select = $data['select'];
		if(!$this->userService) { $this->userService = $this->getService('User'); }
		$baseSelect = $this->userService->getSelect();
		$baseSelect->from('subjects', array('subid'));
		$baseSelect->where($this->quoteInto("name LIKE LOWER(?)", '%'.$value.'%'));		
		$res = $baseSelect->query()->fetchAll();
		$subjectIDs = array();
		if(count($res)){
			foreach($res as $u){
				$subjectIDs[$u['subid']] = $u['subid'];
			}
		}
		if(count($subjectIDs)){
			$select->where($this->quoteInto('s.base_id IN (?)', $subjectIDs));
		} else {
			$select->where('1=0');
		}		
	}

	public function classifiersFilter($data)
	{
		$value  = trim($data['value']);
		$select = $data['select'];

		if(!$this->userService) { $this->userService = $this->getService('User'); }
		$subjectIDs = array();
		$classifierSelect = $this->userService->getSelect();
		$classifierSelect->from(array('c'  => 'classifiers'), array('cl.item_id'));		
		$classifierSelect->join(array('cl' => 'classifiers_links'), 'cl.classifier_id = c.classifier_id', array());
		$classifierSelect->where($this->quoteInto("c.name LIKE LOWER(?)", '%' . $value . '%'));	
		$classifierSelect->where($this->quoteInto('cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT));
		$res = $classifierSelect->query()->fetchAll();
				
		if(count($res)){
			foreach($res as $item){
				$subjectIDs[$item['item_id']] = $item['item_id'];
			}
		}
		if(count($subjectIDs)){
			$select->where($this->quoteInto('s.subid IN (?)', $subjectIDs));
		} else {
			$select->where('1=0');
		}
	}

	public function maxBallSumFilter($data)
	{
		$value  = trim($data['value']);
		$select = $data['select'];
		$subjectIDs = array();
		$subSelect = $this->getService('Subject')->getSelect();
		$subSelect->from('schedule', array('CID' => 'CID', 'max_ball_sum' => 'SUM(max_ball)'));
		$subSelect->where('required = 1');
		$subSelect->group('CID');
		$subSelect->having($this->quoteInto('SUM(max_ball) = ?',$value));
		$res = $subSelect->query()->fetchAll();

		if(!empty($res)){
			foreach($res as $item){
				$subjectIDs[$item['CID']] = $item['CID'];
			}
		}
		if(count($subjectIDs)){
			$select->where($this->quoteInto('s.subid IN (?)', $subjectIDs));
		} else {
			$select->where('1=0');
		}
	}

}