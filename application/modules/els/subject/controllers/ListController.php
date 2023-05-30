<?php
class Subject_ListController extends HM_Controller_Action_Crud {

    protected $classifierCache = array();
    protected $sessionsCache = array();
    protected $groups = array();
    protected $programms = array();
	
    protected $_subjectId = 0;
    protected $_subject = null;
    protected $_currentLang = 'rus';
    private $_classifierCache = array();
    private $_tutorCache      = array();
    private $_teacherCache    = array();
    private $_groupCache      = array();
    private $_programmCache   = array();
    private $_baseIdCache     = array();
    private $_sessionsCache   = array();
    private $_sessionIdsCache = array(); # Если key=0, значит загрузка кэша произвелась, но в БД нет записей. Это признак того, что повторно загрузку кэша делать не нужно
    private $_baseIdsCache    = array(); # Если key=0, значит загрузка кэша произвелась, но в БД нет записей. Это признак того, что повторно загрузку кэша делать не нужно

    public function init()
	{
		if(
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)
			&&
			!$this->getService('User')->isLoginAs()
		){
			$userInfo = $this->getService('UserInfo')->getCurrentUserInfo();
			if($userInfo && !$userInfo->isAvailableSubjects()){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array(
					'type'    => HM_Notification_NotificationModel::TYPE_ERROR, 
					'message' => _('У Вас нет доступа к занятиям. ' . $userInfo->getHumanizedStatus())
				));			
				$this->_helper->redirector->gotoSimple('index', 'index', 'index');
				die;
			}
		}
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

		
        $form = new HM_Form_Subjects();

        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
		
        if ($this->_subjectId > 0) {
            $this->_subject = $this->getOne(
                $this->getService('Subject')->find($this->_subjectId)
            );
            if($this->getRequest()->getActionName() != 'description'){
                $this->view->setExtended(
                    array(
                        'subjectName' => 'Subject',
                        'subjectId' => $this->_subjectId,
                        'subjectIdParamName' => 'subject_id',
                        'subjectIdFieldName' => 'subid',
                        'subject' => $this->_subject
                    )
                );
            }
            $this->_setParam('subid', $this->_subjectId);

            $form->setDefault('cancelUrl', $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $this->_subjectId)));

        } else {
            $form->setDefault('period', HM_Subject_SubjectModel::PERIOD_FREE);
        }

        if($this->_getParam('subid', 0) > 0){

            $subjectId = (int) $this->_getParam('subid', 0) ;
            $model = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            $form->getElement('icon')->setOptions(array('subject' => $model));
        }

        $test = $this->_getParam('test', 0);
        $baseType  = $this->_getParam('base', false);

        // Накидываем модификаторы формы
        if(($baseType === false) && ($subjectId > 0)){
            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if($subject){
                $baseType = $subject->getBaseType();
            }
        }

        $this->addModifier($baseType, $form);

        $this->_setForm($form);

        parent::init();

        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {
            $this->_helper->ContextSwitch()
                          ->setAutoJsonSerialization(true)
                          ->addActionContext('calendar', 'json')
                          ->addActionContext('save-calendar', 'json')
                          ->initContext('json');
        }
    }

    protected function addModifier($baseType, $form)
    {
        switch ($baseType){
            case HM_Subject_SubjectModel::BASETYPE_BASE:
                $form->addModifier(new HM_Form_Modifier_BaseTypeBase());
                break;

            case HM_Subject_SubjectModel::BASETYPE_PRACTICE:
                $form->addModifier(new HM_Form_Modifier_BaseTypePractice());
                break;

            case HM_Subject_SubjectModel::BASETYPE_SESSION:
                $form->addModifier(new HM_Form_Modifier_BaseTypeSession());
                break;
        }
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
        
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		try {
		if(!$this->userService)     { $this->userService     = $this->getService('User');       }
		if(!$this->groupService)    { $this->groupService    = $this->getService('StudyGroup'); }
		if(!$this->programmService) { $this->programmService = $this->getService('Programm');   }
		#if(!$this->isGridAjaxRequest()){
			#$this->userService->clearCache();
			#$this->groupService->clearCache();
			#$this->programmService->clearCache();
		#}
		
		$isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		
		#if(!$isSetEmptyQuery){
			#$this->tutorList = $this->userService->getTutorList();
			#$this->teacherList = $this->userService->getTeacherList();
		#}
		
		$config = Zend_Registry::get('config');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$baseType = $this->_getParam('base', 0);
		$this->view->baseType = $baseType;

        $switcher = $this->_getParam('switcher', '');

        if($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR)) && $switcher == 'programm') {
            $switcher = '';
        }
        
        if($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){
            // Если пользователь не записан на программы, то сбросить switcher
            if( $switcher == 'programm' && count($this->getService('Programm')->getUserProgramms($this->userService->getCurrentUserId())) === 0)
                    $switcher = '';
            
            if($switcher == '') $switcher = 'list';
        }elseif($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN) && $switcher == ''){
            $switcher = 'index';
        }elseif($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR)) && $switcher == ''){
            $switcher = 'list';
        }

        if($switcher && $switcher != 'index'){
        	$this->getHelper('viewRenderer')->setNoRender();
        	$action = $switcher.'Action';
			$this->$action();
			echo $this->view->render('list/'.$switcher.'.tpl');
			return true;
        }

        if(!$baseType){
            if (!$this->isGridAjaxRequest() && $this->_request->getParam('end[from]grid') == "") {
                 //так пропадают курсы с ручным стартом; нужно решить эту проблему и раскомментировать фильтр по умолчанию
                 //$this->_request->setParam('end[from]grid', date('d.m.Y', strtotime('-1 day')));
            }

            if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
                $this->_request->setParam('ordergrid', 'name_ASC');
            }
        }
		//Brs - максимальный балл за занятия в курсе.
		$selectSum = $this->getService('Subject')->getSelect();
		$selectSum->from(array('l' => 'schedule'),
			array(
				'CID' => 'l.CID',
				'max_ball_sum' => 'SUM(l.max_ball)',
			)
		);
		$selectSum->where('required = 1');
		$selectSum->group('l.CID');
		
		
		$dublIDs = $this->getService('Subject')->getMultipleIDSubjects();
		$this->view->issetDouble = $dublIDs; 

        $fields = array(
            'subid' 			=> 's.subid',
            'basetype' 			=> 's.base',
            'name' 				=> 's.name',
            'semester'          => 's.semester',
            'base_id'  			=> 's.base_id',
            'classifiers' 		=> 's.subid', #new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.name)'),
			'max_ball_sum'		=> 'l.max_ball_sum',
            'year_of_publishing'=> 's.year_of_publishing',            
			'external_id' 		=> 's.external_id',
            'begin' 			=> "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.begin IS NULL) THEN s.begin_planned ELSE s.begin END",
            'end' 				=> "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.end IS NULL) THEN s.end_planned ELSE s.end END",
			'end_debt' 			=> 's.time_ended_debt',			
			'end_debt_2'		=> 's.time_ended_debt_2',
			'tutor_name' 		=> 's.subid', //--если использовать GROUP_CONCAT при фильтрации SQL отваливается.
			'teacher_name' 		=> 's.subid',
            'zet'         		=> 's.zet',            
            'chair'       		=> 's.chair',
            'exam_type'   		=> 's.exam_type',
            'hours_total' 		=> 's.hours_total',
            'classroom'   		=> 's.classroom',
            'self_study'  		=> 's.self_study',
            'lection'     		=> 's.lection',
            'lab'         		=> 's.lab',
            'practice'    		=> 's.practice',
            'exam'        		=> 's.exam',            
            'sessions'          => 's.subid',
			'isDO'  			=> new Zend_Db_Expr('CASE WHEN s.isDO IS NULL THEN 0 ELSE s.isDO END'),
			'groups' 			=> 's.subid',
			'programm' 			=> 's.subid',
			
        );
        
        $select = $this->getService('Subject')->getSelect();
		
        $select->from(array('s' => 'subjects'), $fields)			           
			->joinLeft(array('st' => 'Students'), 'st.CID = s.subid', array())            
			->joinLeft(array('ps' => 'People'), 'st.MID = ps.MID AND ps.blocked != '.(int) HM_User_UserModel::STATUS_BLOCKED, array('students' => 'COUNT(DISTINCT ps.mid)'))
            ->joinLeft(array('base' => 'Subjects'), 's.base_id = base.subid', array())
            ->joinLeft(array('ls' => 'learning_subjects'), 's.learning_subject_id_external = ls.id_external', array())

            #->joinLeft(array('cl' => 'classifiers_links'), 's.subid = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
            #->joinLeft(array('c' => 'classifiers'),        'cl.classifier_id = c.classifier_id', array())

			->joinLeft(array('l' => $selectSum), 'l.CID = s.subid AND s.mark_type = '.HM_Mark_StrategyFactory::MARK_BRS, array()) //--Brs
			
            ->group(array(
                's.subid',				
                's.base',
                's.name',
                's.semester',
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
                's.base',
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
				'l.max_ball_sum',
				's.isDO',
				's.time_ended_debt_2',
            ));


        if ($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            if ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                $select->where('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
            } else {
                $select->where('s.base != ? OR s.base IS NULL', HM_Subject_SubjectModel::BASETYPE_SESSION);
            }
        }

        //Область ответственности
        $options = $this->getService('Dean')->getResponsibilityOptions($this->userService->getCurrentUserId());
        if($options['unlimited_subjects'] != 1 && $this->userService->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN){
            $select->joinInner(array('d2' => 'deans'), 'd2.subject_id = s.subid', array())
                   ->where('d2.MID = ?', $this->userService->getCurrentUserId());
        }

        $url = array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => '{{subid}}');

        if ($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $select->joinInner(
                array('students' => 'Students'),
                's.subid = students.CID',
                array()
            );
            $select->where('students.MID = ?', $this->userService->getCurrentUserId());
        }

        if ($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                $select->joinInner(
                    array('teachers' => 'Teachers'),
                    's.subid = teachers.CID',
                    array()
                );
                $select->where('teachers.MID = ?', $this->userService->getCurrentUserId());
        }

        if ($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
            $select->joinInner(
                array('tutors' => 'Tutors'),
                's.subid = tutors.CID',
                array()
            );
            $select->where('tutors.MID = ?', $this->userService->getCurrentUserId());
        }

        if ($this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
        if($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION){
                $cardName = _('Карточка учебной сессии');
            }else{
                $cardName = _('Карточка учебного курса');
            }
        } else {
            $cardName = _('Карточка');
        }

		#if ($baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
			#if(!$isSetEmptyQuery){	
				#$this->groups = $this->groupService->getSubjectGroupList(); //--получаем список групп.
				#$this->programms = $this->programmService->getSubjectProgrammList(); //--получаем список программ.
			#}
		#}
		
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}


		#echo $select->assemble();
		
        $grid = $this->getGrid($select, array(            
            'state'                   => array('hidden' => true),
            'period_restriction_type' => array('hidden' => true),
            'subid'                   => array('hidden' => true),
            'basetype'                => array('hidden' => true),
            'period'                  => array('hidden' => true),
            'longtime'                => array('hidden' => true),
            'exam'                    => array('hidden' => true), # КР
            'classroom'               => array('hidden' => true), # Ауд
            'self_study'              => array('hidden' => true), # СР
            'lection'                 => array('hidden' => true), # Л
            'lab'                     => array('hidden' => true), # ЛР
            'practice'                => array('hidden' => true), # ПР
            'semester'                => array('title' => _('Сем')),
            'begin'                   => array('title' => _('Дата начала')),
            'end'                     => array('title' => _('Дата окончания')),
            'end_debt'                => array('title' => _('Дата продления')),    
            'end_debt_2'              => array('title' => _('Дата продления 2')),
            'type'                    => array('title' => _('Тип')),
            'max_ball_sum'            => array('title' => _('БРС')),
            'year_of_publishing'      => array('title' => _('Год издания')),
            'external_id'             => array('title' => _('ID')),
            'base_id'                 => array('title' => _('Учебный курс')),
            'sessions'                => array('title' => _('Cессии')),
            'zet'                     => array('title' => _('ЗЕТ')),
            'chair'                   => array('title' => _('Кафедра')),

            'name' => array(
                'title'     => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'subject_id' => '')) . '{{subid}}', $cardName) . ' <a href="' . $this->view->url($url, null, true, false) . '">{{name}}</a>'
            ),

            'students' => array(
                'title'    => _('Кол слуш'),
                'callback' => array(
                    'function' => array($this, 'updateStudents'),
                    'params'   => array('{{students}}', '{{subid}}')
                )
            ),
            
            'classifiers' => array(
                'title'    => _('Классификаторы'),
                'callback' => array(
                    'function' => array($this, 'updateClassifiers'),
                    'params'   => array('{{classifiers}}', $select)
                )
            ),
            
			'tutor_name' => array(
                'title'    => _('Тьютор'),
                'callback' => array(
                    'function' => array($this, 'updateTutors'),
                    'params'   => array('{{tutor_name}}', $select)
                )
            ),

            'teacher_name' => array(
                'title'     => _('Преп.'),
                'callback'  => array(
                    'function' => array($this, 'updateTeachers'),
                    'params'   => array('{{teacher_name}}', $select)
                )
            ),  
			
			'groups' => array(
                'title'    => _('Группа'),
                'callback' => array(
                    'function' => array($this, 'updateGroups'),
                    'params'   => array('{{groups}}', $select)
                )				
            ),
			
			'programm' => array(
                'title'    => _('Программа'),
                'callback' => array(
                    'function' => array($this, 'updateProgramm'),
                    'params'   => array('{{programm}}', $select)
                )
            ),			
			
            'exam_type' => array(
                'title'    => _('Контроль'),
                'callback' => array(
                    'function' => array($this, 'updateExamType'),
                    'params'   => array('{{exam_type}}')
                )
            ),
            
			'isDO' => array(
                'title'    => _('ДО'),
                'callback' => array(
                    'function' => array($this, 'updateIsDO'),
                    'params'   => array('{{isDO}}')
                )
            ),
        ),
        array(
            'name'               => null,
            'semester'           => null,
            'students'           => null,
            'classifiers'        => null,
            'year_of_publishing' => null,
            'external_id'        => null,
            'max_ball_sum'       => null,
            'chair'              => null,
            'exam'               => null,
            'hours_total'        => null,
            'classroom'          => null,
            'self_study'         => null,
            'lection'            => null,
            'lab'                => null,
            'practice'           => null,
            'begin'      => array('render' => 'SubjectDate'),
            'end'        => array('render' => 'SubjectDate'),
            'end_debt'   => array('render' => 'DateSmart'),
            'end_debt_2' => array('render' => 'DateSmart'),            
			'type'      => array('values' => HM_Subject_SubjectModel::getTypes()),
            'zet'       => array('values' => HM_Subject_SubjectModel::getZetValues()),
            'exam_type' => array('values' => HM_Subject_SubjectModel::getExamTypes()),
            'isDO'      => array('values' => HM_Subject_SubjectModel::getFacultys()),
            
            'sessions' => array('callback' => array(
                'function' => array($this, 'filterSessions')
            )),
			
            'tutor_name' => array('callback' => array(
                'function' => array($this, 'tutorFilter'),
				'params'   => array()
            )),
			
            'teacher_name' => array('callback' => array(
                'function'  => array($this, 'teacherFilter'),
                'params'    => array()
			)),
            
            'groups' =>array('callback' => array(
                'function'=>array($this, 'groupFilter'),
				'params'=>array()
			)),
            
            'programm' =>array('callback' => array(
                'function'=>array($this, 'programmFilter'),
				'params'=>array()
			)),
            
            'base_id' => array('callback' => array(
                'function'=>array($this, 'baseSubjectFilter'),
				'params'=>array()
			)),
        ));
		

        // Hide all unused fields for base subject
        if ($baseType != HM_Subject_SubjectModel::BASETYPE_SESSION) {
			
            $grid->updateColumn('chair', array(
                    'hidden' => true
            ));
            $grid->updateColumn('exam', array(
                    'hidden' => true
            ));
            $grid->updateColumn('classroom', array(
                    'hidden' => true
            ));
            $grid->updateColumn('self_study', array(
                    'hidden' => true
            ));
            $grid->updateColumn('lection', array(
                    'hidden' => true
            ));
            $grid->updateColumn('lab', array(
                    'hidden' => true
            ));
            $grid->updateColumn('practice', array(
                    'hidden' => true
            ));
            $grid->updateColumn('base_id', array(
                    'hidden' => true
            ));
			$grid->updateColumn('external_id', array(
                    'hidden' => true
            ));			
            $grid->updateColumn('hours_total', array(
                    'title' => _('Часы')
            ));			
			$grid->updateColumn('end_debt', array(
                    'hidden' => true
            ));
			$grid->updateColumn('end_debt_2', array(
                    'hidden' => true
            ));
			$grid->updateColumn('isDO', array(
                    'hidden' => true
            ));			
			$grid->updateColumn('groups', array(
                    'hidden' => true
            ));						
			$grid->updateColumn('programm', array(
                    'hidden' => true
            ));
			
        } else {
            $grid->updateColumn('hours_total',        array('hidden' => true));
            $grid->updateColumn('sessions',           array('hidden' => true));
            $grid->updateColumn('year_of_publishing', array('hidden' => true));

            $grid->updateColumn('base_id', array('callback' => array(
                'function' => array($this, 'updateBaseId'),
                'params' => array('{{base_id}}', $select)
            )));            
            
			$grid->addMassAction(
                array('module' => 'subject', 'controller' => 'list', 'action' => 'change-faculty'),
				_('Изменить факультет'),
				_('Вы уверены?')
			);
			$grid->addSubMassActionSelect(
                $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'change-faculty')),
				'faculty',
				HM_Subject_SubjectModel::getFacultys()				
			);
        }

        $grid->updateColumn('begin', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDateBegin'),
                'params'   => array('{{begin}}', '{{period}}', '{{period_restriction_type}}')
            )
        ));
        
        $grid->updateColumn('end', array(
            'callback' => array(
                'function' => array($this, 'updateDateEnd'),
                'params'   => array('{{end}}', '{{period}}', '{{longtime}}', '{{period_restriction_type}}')
            )
        ));
        
        $grid->updateColumn('end_debt', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params'   => array('{{end_debt}}')
            )
        ));

        $grid->updateColumn('end_debt_2', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{end_debt_2}}')
            )
        ));

        // If name translation set - show the translation
        $grid->updateColumn('name', array(
            'callback' => array(
                'function'=> array($this, 'updateName'),
                'params'=> array('{{name}}')
            )
        ));
        
        $grid->updateColumn('type', array(
            'callback' => array(
                'function'=> array($this, 'updateType'),
                'params'=> array('{{type}}')
            )
        ));

        $grid->updateColumn('sessions', array(
            'callback' => array(
                'function' => array($this, 'sessionsCache'),
                'params'   => array('{{sessions}}', $select)
            )
        ));


        $grid->addAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'edit'),
            array('subid'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'delete'),
            array('subid'),
            $this->view->icon('delete')
        );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'copy'),
            array('subid'),
            _('Копировать')
        );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'statement'),
            array('subid'),
            _('Ведомость')
        );

        // if basetype вынесен в updateActions
        $grid->addAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'new', 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION),
            array('subid'),
            _('Создать учебную сессию')
        );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'copy-from-base'),
            array('subid'),
            _('Копировать содержимое из базового курса'),
            _('Вы действительно желаете удалить материалы и план занятий данной учебной сессии и скопировать их из базового курса?')
        );
    
        // пункт "назначить преподавателей на курсы"
        $teacherCollection = $this->userService->fetchAllJoinInner('Teacher'); //--1 секунда

        $teachers = array();

        if ( count($teacherCollection) ) {
            foreach ( $teacherCollection as $teacher) {
                $teachers[$teacher->MID] = $teacher->getName();
            }
        }
        asort($teachers,SORT_LOCALE_STRING);
        $teachers = array(_('Выберите преподавателей')) + $teachers;

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'teacher'),
            _('Назначить преподавателей'),
            _('Вы уверены?')
        );
        $grid->addSubMassActionSelect(
            $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'teacher')),
            'teachersId[]',
            $teachers
        );

        // пункт "назначить тьюторов на курсы"        
		$tutorCollection = $this->userService->fetchAllJoinInner('Tutor'); //--0.5 сек.        
		$tutors          = array();

        if ( count($tutorCollection) ) {
            foreach ( $tutorCollection as $tutor) {
                $tutors[$tutor->MID] = $tutor->getName();
            }
        }
        asort($tutors,SORT_LOCALE_STRING);
        $tutors = array(_('Выберите тьюторов')) + $tutors;

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'tutor'),
            _('Назначить тьюторов'),
            _('Вы уверены?')
        );
        $grid->addSubMassActionSelect(
            $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'tutor')),
            'tutorsId[]',
            $tutors
        );

        //пункт "удалить преподавателей с курсов"
        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'noteacher'),
            _('Отменить назначение всех преподавателей'),
            _('Вы уверены?')
        );

        //пункт "удалить тьюторов с курсов"
        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'notutor'),
            _('Отменить назначение всех тьюторов'),
            _('Вы уверены?')
        );
		
		# удалить назначения выбранных тьюторов.
		$grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'notutor-selected'),
            _('Отменить назначения тьюторов'),
            _('Вы уверены?')
        );
        $grid->addSubMassActionSelect(
            $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'assign', 'mode' => 'notutor-selected')),
            'tutorsId[]',
            $tutors
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );		
		$grid->addMassAction(
            array('module' => 'subject', 'controller' => 'list', 'action' => 'change-do'),
			_('Назначить преподавателей'),
			_('Вы уверены?')
		);

        

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{source}}','{{state}}', '{{period_restriction_type}}', '{{basetype}}')
            )
        );

        if(
            $this->getService('Acl')->inheritsRole($this->userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        ){			
			$gridId  = $grid->getGridId();
			$default = new Zend_Session_Namespace('default');        		
			$filters = $default->grid['subject-list-index'][$gridId]['filters'];
			if(empty($filters)){				
				$default->grid['subject-list-index'][$gridId]['filters']['end[from]'] = date('d.m.Y');
				
			}			
        }
		
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid            = $grid->deploy();
		$this->view->baseType        = $baseType;
		
		} catch (Exception $e) {}
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
    
	public function updateNameTranslation($translation) {
		
		if($translation != '') return '+';
		
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


    public function calendarAction()
    {
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            $begin = $this->getService('Subject')->getDateTime(intval($this->_getParam('start')));
            $end   = $this->getService('Subject')->getDateTime(intval($this->_getParam('end')));
            $where = $this->getService('Subject')->quoteInto(array('base=?',' AND  NOT ( begin >= ?',' AND end <= ?)'),array(HM_Subject_SubjectModel::BASETYPE_SESSION, $end, $begin));

            $collection    = $this->getService('Subject')->fetchAllManyToMany('User','Teacher',$where);
            $eventsSources = $this->getService('Subject')->getCalendarSource($collection, '0000ff', false, $this->_getParam('user_id', null));

            // добавляются выходные и праздники
            // user_id попал сюда из PM..?
            //$where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id = ?'), array($begin, $end, 0));
            $where = $this->quoteInto(array('date >= ?',' AND date <= ?'), array($begin, $end));
            $holidays = $this->getService('Holiday')->fetchAll($where);
            if ( count($holidays) ) {
                foreach ($holidays as $day) {
                    $date = new HM_Date($day->date);
                    $eventsSources[] = array(
                        'id'    => $day->id,
                        'title' => $day->title,
                        'color' => "#c2c8d3",
                        'start' => $date->getTimestamp(),
                        'end'   => $date->getTimestamp(),
                        'editable' => false,
                        'borderColor' => '#ff0000'
                    );
                }
            }

            // добавляются произвольные мероприятия пользователей
            if (!$this->_getParam('no_user_events', false)) {
                if ($this->_getParam('user_id', null)) {
                    $where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id = ?'), array($begin, $end, $this->_getParam('user_id', null)));
                } else {
                    $where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id <> ?'), array($begin, $end, 0));
                }

                $holidays = $this->getService('Holiday')->fetchAllDependence('User', $where);
                if ( count($holidays) ) {
                    foreach ($holidays as $day) {
                        $date = new HM_Date($day->date);
                        $eventsSources[] = array(
                            'id'    => $day->id,
                            'title' => $day->title . ' ' . $day->users->current()->getName(),
                            'color' => "#c2c8d3",
                            'start' => $date->getTimestamp(),
                            'end'   => $date->getTimestamp(),
                            'editable' => false,
                            'borderColor' => '#00FF00'
                        );
                    }
                }
            }

            $this->view->assign($eventsSources);
        } else {
            $this->view->source = array('module'=>'subject', 'controller'=>'list', 'action'=>'calendar', 'no_user_events' => 'y');
            $this->view->editable = !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER);
        }
    }


    public function saveCalendarAction()
    {
        $subjectId = $this->_getParam('eventid',0);
        $begin     = $this->_getParam('start',0);
        $end       = $this->_getParam('end',0);

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $subjectId && $begin && $end) {

            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if ($subject) {
                $data = array(
                    'subid' => $subject->subid,
                    'begin' => $this->getService('Subject')->getDateTime($begin/1000, true) . ' 00:00:00',
                    'end'   => $this->getService('Subject')->getDateTime($end/1000, true) . ' 23:59:59'
                );
                $res = $this->getService('Subject')->update($data);
                if ($res) {
                    $result = _('Данные успешно обновлены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }
    public function newAction()
    {
        $base =  $this->_getParam('base', 0);
        $subjectId =  $this->_getParam('subid', 0);

        if($base == HM_Subject_SubjectModel::BASETYPE_SESSION){

            $this->getService('Unmanaged')->setHeader(_('Создание учебной сессии'));

            $form = $this->_getForm();
            $this->setDefaults($form, true); // часть атрибутов не нужно наследовать от base_subject, поэтому true
            $regType = $this->_getForm()->getElement('reg_type');
            $regType->setValue(HM_Subject_SubjectModel::REGTYPE_SAP);

            $baseId = $this->_getForm()->getElement('base_id');
            $baseId->setValue($subjectId);
            
            $subject = $this->getService('Subject')->find($subjectId)->current();
            $markTypePrefix = $subject->getMarkType();
            $markService = $this->getService('MarkStrategyFactory')->getStrategy($markTypePrefix);
            
            foreach ($markService->getElementsNameArray() as $elementName) {
                $form->getElement("{$markTypePrefix}_{$elementName}")->setValue($subject->$elementName);
            }

            //$name = $this->_getForm()->getElement('name');
            //$name->setValue($name->getValue() . ' (' . _('сессия') . ')');
        }

        parent::newAction();
		
		if($base == HM_Subject_SubjectModel::BASETYPE_SESSION){		
			$dublIDs = $this->getService('Subject')->getMultipleIDSubjects();
			if(count($dublIDs)){
				$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
													'message' => _('Обнаружены задвоения сессий по внешнимему ID: '.implode(', ',$dublIDs))));
			}
		}
    }



    public function disperseAction(){

        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_getParam('subject_id', 0);
        $svitcher = $this->_getParam('switcher', 'list');


        if($subjectId > 0){
            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if($subject){

                $this->getService('Subject')->unassignStudent($subjectId, $userId);
                $this->_flashMessenger->addMessage(sprintf(_('Вы отчислены с курса %s'), $subject->name));

                // Проверяем на присутствие курсов у слушателя, если нет, то редиректим на главную пользователя
                $subjects = $this->getService('Student')->getSubjects($userId);
                if (!count($subjects)) {
                    $this->getService('User')->switchRole(HM_Role_RoleModelAbstract::ROLE_USER);
                    $this->session = new Zend_Session_Namespace('default');
                    $this->session->switch_role = 1;

                    $this->_redirector->gotoSimple('index', 'index', 'default');
                }

            }else{
                $this->_flashMessenger->addMessage(_('Курс не найден'));
            }

        }else{
            $this->_flashMessenger->addMessage(_('Курс не найден'));
        }
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher'=>$svitcher));
    }

    public function unlookAction(){

        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_getParam('subject_id', 0);


        if($subjectId > 0){
            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if($subject){

                $this->getService('Graduated')->updateWhere(array('is_lookable' => HM_Role_GraduatedModel::UNLOOKABLE), array('MID = ?' => $userId, 'CID = ?' => $subjectId));

                $this->_flashMessenger->addMessage(sprintf(_('Курс удален из списка'),$markStatus));

            }else{
                $this->_flashMessenger->addMessage(_('Курс не найден'));
            }

        }else{
            $this->_flashMessenger->addMessage(_('Курс не найден'));
        }

        $this->_redirector->gotoSimple('index', 'list', 'subject');

    }

    /**
     * Экшен приаттачивания, слушателей, преподователей к курсам
     */
    public function assignAction()
    {
        $mode = $this->_getParam('mode',false);
        switch ( $mode) {

            case 'users': //приаттачиваем пользователей к курсам
                          $users = $this->_getParam('usersId',array());
                          $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));

                          if ( $this->usersAssign($subjects,$users) ) {
                              $this->_flashMessenger->addMessage(_('Слушатели успешно назначены'));
                          } else {
                              $this->_flashMessenger->addMessage(_('При назначении некоторых слушателей возникли ошибки'));
                          }
                          break;
            case 'teacher': // приаттачиваем преподавателей к курсам
                            $teachers = $this->_getParam('teachersId',array());
                            $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
                           if ( $this->teachersAssign($subjects,$teachers) ) {
                              $this->_flashMessenger->addMessage(_('Преподаватели успешно назначены'));
                           } else {
                              $this->_flashMessenger->addMessage(_('При назначении некоторых преподавателей возникли ошибки'));
                           }
                          break;
            case 'noteacher': // убираем всех преподавателей курсов
                            $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
                           if ( $this->teachersDiscard($subjects) ) {
                              $this->_flashMessenger->addMessage(_('Назначение преподавателей успешно отменено'));
                           } else {
                              $this->_flashMessenger->addMessage(_('При отмене назначения некоторых преподавателей возникли ошибки'));
                           }
                          break;
            case 'tutor': // приаттачиваем тьюторов к курсам
                $tutors = $this->_getParam('tutorsId',array());
                $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
				$roles	  = $this->_getParam('roles',array());
                if ( $this->tutorsAssign($subjects,$tutors, $roles) ) {
                    $this->_flashMessenger->addMessage(_('Тьюторы успешно назначены'));
                } else {
                    $this->_flashMessenger->addMessage(_('При назначении некоторых тьюторов возникли ошибки'));
                }
                break;
            case 'notutor': // убираем всех тьюторов курсов
                $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
                if ( $this->tutorsDiscard($subjects) ) {
                    $this->_flashMessenger->addMessage(_('Назначение тьюторов успешно отменено'));
                } else {
                    $this->_flashMessenger->addMessage(_('При отмене назначения некоторых тьюторов возникли ошибки'));
                }
                break;
			
			case 'notutor-selected': // убираем выбранных тьюторов с курсов
                $subjects	= explode(',',$this->_getParam('postMassIds_grid',array()));
				$tutors 	= $this->_getParam('tutorsId',array());
				$roles		= $this->_getParam('roles',array());
                if ( $this->tutorsDiscardSelected($subjects, $tutors, $roles) ) {
                    $this->_flashMessenger->addMessage(_('Назначение выбранных тьюторов успешно отменено'));
                } else {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('При отмене назначения некоторых тьюторов возникли ошибки')));
                }
                break;
				
				#########
				
				
				
            default:
                    $this->_flashMessenger->addMessage(_('Выбрано некорректное действие'));
                    break;
        }
        $this->_redirectToIndex();
    }


    /**
     * Удаляем всх преподавателей для выбраных курсов
     * @param int|array $subjects
     * @return boolean
     */
    private function teachersDiscard($subjects)
    {
        if ( !$subjects ) return false;

        $subjects = (array) $subjects;
        // приводим элемнты переданного массива к int
        array_walk($subjects,create_function('&$val,$key', '$val = (int) $val;'));
        $where = implode(',', $subjects);

        return $this->getService('Teacher')->deleteBy("CID IN ({$where})");
    }
    
    private function tutorsDiscard($subjects)
    {
        if ( !$subjects ) return false;

        $subjects = (array) $subjects;
        // приводим элемнты переданного массива к int
        array_walk($subjects,create_function('&$val,$key', '$val = (int) $val;'));
        $where = implode(',', $subjects);

        return $this->getService('Tutor')->deleteBy("CID IN ({$where})");
    }
	
	
	private function tutorsDiscardSelected($subjects, $tutors, $roles = false)
	{
		if(empty($subjects) || empty($tutors)){ return false; }
		
		$subjects	= (array) $subjects;
        $tutors  	= (array) $tutors;
		
		$subjects	= array_map('intval', $subjects);
		$tutors 	= array_map('intval', $tutors);
		
		$subjects	= array_filter($subjects);
		$tutors		= array_filter($tutors);
		
		if(empty($subjects) || empty($tutors)){ return false; }
		
		
		$roles 		= (array) $roles;
		$serviceLAT = $this->getService('LessonAssignTutor');
		
		# Если есть признак удаления со всех ролей, прочие игнорируются
		if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_ALL, $roles)){
			$serviceLAT->unAssignFromSubject($tutors, $subjects);
		} else{
			
			if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR, $roles)){
				$serviceLAT->unAssignLector($subjects, $tutors);	
			}
			if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE, $roles)){
				$serviceLAT->unAssignSeminarian($subjects, $tutors);	
			}
			if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB, $roles)){
				$serviceLAT->unAssignLaborant($subjects, $tutors);	
			}
			
		}
		
		
		$where = $this->quoteInto(array('CID IN (?)', ' AND MID IN (?)'), array($subjects, $tutors));
		
		return $this->getService('Tutor')->deleteBy($where);
	}

    /**
     * приаттачиваем пользователей к курсам
     * @param int|array $subjects курсы
     * @param int|array $users пользователи
     * @return boolean
     * @todo Эту и подобную функции прорефакторить
     */
    private function usersAssign($subjects, $users)
    {
        if ( !$subjects || !$users ) return false;

        $result = true;
        $subjects = (array) $subjects;
        $users    = (array) $users;

        $subjectService = $this->getService('Subject');
        $userService = $this->getService('User');

        foreach ( $subjects as $subject ) {
         // проверка существования курса
         if ( !count($subjectService->find($subject))) {
            $result = false;
            continue;
         }
         foreach ( $users as $user ) {
            // проверка существования пользователя
            if ( !count($userService->find($user)) ) {
                $result = false;
                continue;
            }
            // если пользователь не студент данного курса - делаем его таковым
            if ( !$subjectService->isStudent($subject,$user) ) {
                $subjectService->assignUser($subject,$user);
            }
         }
        }
        return $result;
    }

    /**
     * Приаттачивание преподавателей к курсам
     * @param int|array $subjects
     * @param int|array $teachers
     * @return boolean
     */
    private function teachersAssign($subjects, $teachers)
    {
        if ( !$subjects || !$teachers ) return false;
        $result = true;
        $subjects = (array) $subjects;
        $teachers = (array) $teachers;

        $teacherService = $this->getService('Teacher');
        $subjectService = $this->getService('Subject');
		
		$teachersSend = array(); //--ключ - id препода. Значения - курсы, на которые назначены
        foreach ( $subjects as $subject ) {
           // проверка существования курса
           if ( !count($subjectService->find($subject))) {
              $result = false;
              continue;
           }
           foreach ( $teachers as $teacher ) {
              // проверка существования преподавателя
              if ( $teacherService->isUserExists($subject,$teacher) ) {
                  $result = false;
                  continue;
              }
              // если все ОК - создаем препода
              $teacherService->insert(
				array(
					'MID'=>$teacher,
					'CID' => $subject,
					'date_assign' => date('Y-m-d 23:59',time()),
				)
			  );
			  $teachersSend[$teacher][] = $subject; //--берем всех тьюторов и предметы, на которые назначены
           }
        }
		$this->prepareEmailSend($teachersSend);
        return $result;
    }

    private function tutorsAssign($subjects, $tutors, $roles = false)
    {
        if ( !$subjects || !$tutors ) return false;
        $result = true;
        $subjects = (array) $subjects;
        $tutors = (array) $tutors;
		
		$roles = (array) $roles;
		
		# Если есть признак назначения на все роли, прочие игнорируются
		if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_ALL, $roles)){
			$roles = array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_ALL);
		}
		
	
        $tutorService = $this->getService('Tutor');
        $subjectService = $this->getService('Subject');		
		$serviceLAT     = $this->getService('LessonAssignTutor');

		$tutorsSend = array(); //--ключ - id тьютора. Значения - курсы, на которые назначены
        foreach ( $subjects as $subject ) {
            // проверка существования курса
            if ( !count($subjectService->find($subject))) {
                $result = false;
                continue;
            }
            foreach ( $tutors as $tutor ) {
                $this->getService('Subject')->assignTutor($subject, $tutor);
				
				if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR, $roles)){
					$serviceLAT->assignLector($subject, $tutor);
				}
				if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE, $roles)){
					$serviceLAT->assignSeminarian($subject, $tutor);
				}
				if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB, $roles)){
					$serviceLAT->assignLaborant($subject, $tutor);
				}
				
				$tutorsSend[$tutor][] = $subject; //--берем всеть тьбторов и предметы, на которые назначены
            }
        }
		
		# Если выбрано назначение на все роли, удаляем все назначения на конкретные роли.
		if(in_array(HM_Lesson_Assign_Tutor_TutorModel::ROLE_ALL, $roles)){
			$serviceLAT->unAssignFromSubject($tutors, $subjects);
		}
		
		
		$this->prepareEmailSend($tutorsSend);
        return $result;
    }

    public function programmAction()
    {
        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER) ) {
            $user = $this->getService('User')->getCurrentUser();
            $this->view->programms = $this->getService('Programm')->getUserProgramms($user->MID);
            $this->view->user = $user;

            $now = date('Y-m-d H:i:s');


            $where = $this->quoteInto(
                array(
                    'self.MID = ? AND ',
                    '((Subject.period = ?) OR ',
                    '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                    '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                    '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                    '(Subject.period = ?',' AND self.time_ended_planned > ?))',
                ),
                array(
                    $this->getService('User')->getCurrentUserId(),
                    HM_Subject_SubjectModel::PERIOD_FREE,
                    $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                    $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                    HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                    HM_Subject_SubjectModel::PERIOD_FIXED, $now,
                )
            );


            $students = $this->getService('Student')->fetchAllDependenceJoinInner('Subject', $where);

            $studentCourseData = array();
            foreach ($students as $student) {
                $studentCourseData[$student->CID] = array(
                    'begin' => $student->time_registered,
                    'end_planned' => $student->time_ended_planned,
                );
            }

            $marks = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $this->getService('User')->getCurrentUserId()))->getList('cid', 'mark');
            foreach ($studentCourseData as $subjectId => $data) {
                if (!isset($marks[$subjectId])) $marks[$subjectId] = HM_Scale_Value_ValueModel::VALUE_NA;
            }
            $this->view->marks = $marks;

            $this->view->studentCourseData = $studentCourseData;
            $this->view->graduatedList = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));

        }
    }

    public function listAction()
    {		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		
		$now = date('Y-m-d 00:00:00');
		$current_user_id = $this->getService('User')->getCurrentUserId(); 

        $listSwitcher = $this->_getParam('list-switcher', 'current');
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
			$this->view->headScript()->appendFile($config->url->base.'/js/rgsu.js');
//[22.05.2014 #16913]

/*
Состояние полей для вариантов назначений:
                                    period  period_restriction_type
1. Начало и конец упр. препод.          0       2
2. Нестрогое                            0       1
3. Строгое                              0       0
4. Без ограничений                      1       0
5. С фикс. продолжительностью           2       0

*/

            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(self.time_registered >= ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))',
                        ),
                        array(
                            $current_user_id,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Subject_SubjectModel::PERIOD_FIXED, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.period = ?) OR ',
                            '(Subject.period = ?', ' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin <= ?',' AND Subject.end >= ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',                            
							'(self.time_registered < ?', ' AND Subject.period = ?',' AND self.time_ended_planned > ?)',
							' OR (self.time_ended_debtor = ? AND self.time_ended_debtor IS NOT NULL)', //--новое условие для должников.
							#' OR (self.time_ended_debtor > ? AND self.time_ended_debtor IS NOT NULL ))',
							
							' OR (self.time_ended_debtor > ? AND self.time_ended_debtor IS NOT NULL )',							
							' OR (self.time_ended_debtor_2 = ? AND self.time_ended_debtor_2 IS NOT NULL)', //--Второе продление
							' OR (self.time_ended_debtor_2 > ? AND self.time_ended_debtor_2 IS NOT NULL ))',
							
                        ),
                        array(
                            $current_user_id,
                            HM_Subject_SubjectModel::PERIOD_FREE,
                            HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            $now, HM_Subject_SubjectModel::PERIOD_FIXED, $now,
							$now,
							$now,
							$now,
							$now,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.end < ?', ' AND Subject.period = ?', ' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',  ' AND Subject.period = ?', ' AND Subject.period_restriction_type = ?) OR ',                            
							'(Subject.period = ?', ' AND self.time_ended_planned < ?)) ',
							#' AND ( self.time_ended_debtor < ? OR self.time_ended_debtor IS NULL )', //--новое условие для должников.
							' AND (	   (  (self.time_ended_debtor < ? OR self.time_ended_debtor IS NULL) AND self.time_ended_debtor_2 IS NULL    )',   # первое продление
							' 		OR (  (self.time_ended_debtor_2 < ? OR self.time_ended_debtor_2 IS NULL) AND self.time_ended_debtor IS NULL  )',   # второе продление
							' 		OR (   self.time_ended_debtor < ? AND self.time_ended_debtor_2 < ? ) )',  # первое и второе продление прошли
                        ),
                        array(
                            $current_user_id,
                            $now,HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_CLOSED, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Subject_SubjectModel::PERIOD_FIXED, $now,
							$now,
							$now,
							$now, $now
                        )
                    );
                    break;
				case 'ending': break;					
				default:
					$this->_redirector = $this->_helper->getHelper('Redirector');           
					$this->_redirector->gotoSimple('list', 'list', 'subject');					
            }
//
/* //[22.05.2014 #16913]
            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.period = ?) OR ',
                            '(Subject.period = ?', ' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(Subject.period = ?',' AND self.time_ended_planned > ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            HM_Subject_SubjectModel::PERIOD_FREE,
                            HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Subject_SubjectModel::PERIOD_FIXED, $now,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '(NOT(Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(Subject.period = ?',' AND self.time_ended_planned < ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::PERIOD_FIXED, $now,
                        )
                    );
                    $this->getService('EventServerDispatcher')->trigger(
                        Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                        $this,
                        array('filter' => $this->getFilterByRequest($this->getRequest()))
                    );
                    break;
            }
*/
            $studentCourseData = array();
            $courses = array();
			$examTypes = HM_Subject_SubjectModel::getExamTypes();
			if ($listSwitcher == 'ending') {				
                $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $current_user_id, 'is_lookable = ?' => HM_Role_GraduatedModel::LOOKABLE));
                $graduatedCourses = $graduated->getList('SID', 'CID');
                $courses = $graduatedCourses;
            	
                foreach ($graduated as $grad) {
					$subject = $this->getService('Subject')->getById($grad->CID);					
					$studentCourseData[$grad->CID] = array(
                		'begin' 	=> $grad->begin,
                		'end' 		=> $grad->end,							
						'semester'	=> $subject->semester,			
                	);
                }				
				$this->view->graduatedList = $graduated; #$this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));				
            } else {
				$students = $this->getService('Student')->fetchAllDependenceJoinInner('Subject', $where);
				$courses = $students->getList('SID', 'CID');
			
				foreach ($students as $student) {
					$time_ended_debtor = $student->time_ended_debtor;
					$timestamp_time_ended_debtor = strtotime($student->time_ended_debtor);
					# до тех пор, пока дата первог опродления не пройдет, дату второго продления не показываем. Стимулируем студентов сдавать все в срок и не тянуть.
					if($timestamp_time_ended_debtor < time() && !empty($student->time_ended_debtor_2)){
						$time_ended_debtor = $student->time_ended_debtor_2;
					}
					
					$studentCourseData[$student->CID] = array(
						'begin' 			=> $student->time_registered,
						'end_planned' 		=> $student->time_ended_planned,
						'time_ended_debtor' => $time_ended_debtor,
						'exam_type' 		=> $examTypes[$student->courses->current()->exam_type],						
						'semester'			=> $student->courses->current()->semester,
					);
				}
			}
			/*
			$reasonFailModule = array();
            $marks = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $current_user_id))->getList('cid', 'mark');
            foreach ($studentCourseData as $subjectId => $data) {				
            	if (!isset($marks[$subjectId])){ 
					$marks[$subjectId] = HM_Scale_Value_ValueModel::VALUE_NA; 
				} else {
					# для неДО находим оценку как сумму Итоговый текущий рейтинг  + Рубежный рейтинг
					if(!$this->getService('Subject')->isDOT($subjectId)){
						$user_balls = $this->getService('Subject')->getUserBallsSeparately($subjectId, $current_user_id);		
						$marks[$subjectId] = $user_balls['total'] + $user_balls['medium'];
						if($user_balls['isMainModule']){
							# находим причины недопуска по каждому модулю, кроме указанного в параметрах.
							$reasonFailModule[$subjectId] = $this->getService('Subject')->getFailPassMessageModule($current_user_id, $subjectId);									
						}
						#$marks[$subjectId] = $this->getService('Subject')->getUserBalls($subjectId, $current_user_id);						
					}
				}
            }
			$this->view->reasonFailModule = $reasonFailModule;
            $this->view->marks = $marks;
			*/

            if (count($courses)) {
                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ')', 'name');
            }
            $this->view->share = true; // allow facebook etc.

			$this->view->studentCourseData = $studentCourseData;
            
			
            
             /*------Выбор из таблицы SCORM_TRACKLOG------------------------*/
            
            // $userId = $this->getService('User')->getCurrentUserId();
            // $serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');
            // $sec = Array();
            // $out = explode( ',',$in);
            // /* Получение списка модулей по курсам */
            // for ($i=0;$i<count($out);$i++){
                // $selectLess = $serviceSubject->getSelect();
                // $selectLess->from(array('l' => 'lessons'),
                    // array(
                        // 'CID' => 'l.cid',
                        // 'SHEID' => 'l.sheid',
                        // 'title' => 'l.title',
                        // )
                    // )->where('l.cid = ? ', $out[$i]);    
                    
                        // if ($rowsetLess = $selectLess->query()->fetchAll()) {  
                            // foreach ($rowsetLess as $rowLess) {
                            
                            // /*Подсчет времени по каждому модулю*/
                            
                                // $select = $serviceSubject->getSelect();
                                // $select->from(array('s' => 'scorm_tracklog'),
                                // array(
                                    // 'mid' => 's.mid',
                                    // 'lesson_id' => 's.lesson_id',
                                    // 'timer' => 'SUM(UNIX_TIMESTAMP(STOP ) - UNIX_TIMESTAMP(START ))',
                                    // )
                                // )->where('mid ='. $userId .' AND lesson_id ='. $rowLess['SHEID']);
                                
                                    // if ($rowset = $select->query()->fetchAll()) {
                                        // foreach ($rowset as $row) {
                                        
                                        // /* Формирование массива для вывода данных*/
                                        
                                        // if(array_key_exists($out[$i], $sec)){ 
                                            // $sec[$out[$i]]=$sec[$out[$i]]+$row['timer'];
                                            // } else {
                                            // $sec[$out[$i]]=$row['timer'];
                                            // }                                            
                                        // }
                                    // }
                            // }                    
                        // }
        
                 // $this->view->sec = $sec;
            
        // }

        // /* Log User */
        // $logs = array();
        // foreach ($students as $student) {
            // $selectTry = $serviceSubject->getSelect();
            // $selectTry->from(array('u' => 'loguser'),
            // array(
                // 'CID' => 'u.cid',
                // 'MID' => 'u.mid',
                // )
            // )->where('CID = '.$student->CID.' AND MID = '.$student->MID); // Количество попыток пользователя в пределах курса
                // if ($rowLog = $selectTry->query()->fetchAll()) {     
                    // if(array_key_exists($student->CID, $logs)){ 
                    
                    // /* Формирование массива для вывода данных*/
                    
                        // $logs[$student->CID]=$logs[$student->CID]+count($rowLog);
                    // } else {
                        // $logs[$student->CID]=count($rowLog);
                    // }
                    // }
                // }
        // $this->view->logs = $logs;    
        
        // /*Forums Messages*/
        // $my_mess = array();
        // foreach ($students as $student) { 
            // $selectList = $serviceSubject->getSelect();
            // $selectList->from(array('m' => 'forums_list'),
            // array(
                // 'subject_id' => 'm.subject_id',
                // 'forum_id' => 'm.forum_id',
                // )
            // )->where('subject_id = '.$student->CID);  // Получение списка форумов в пределах курса.
                // if ($rowList = $selectList->query()->fetchAll()) { 
                        // foreach ($rowList as $rList) {            
                            // $selectMess = $serviceSubject->getSelect();
                            // $selectMess->from(array('mes' => 'forums_messages'),
                            // array(
                                // 'user_id' => 'mes.user_id',
                                // 'forum_id' => 'mes.forum_id',
                                // )
                            // )->where('user_id = '.$student->MID. ' AND forum_id = '.$rList['forum_id']); 
                            
                         // /*Подсчет количества сообщений в пределах курса*/
                         
                            // if ($rowMess = $selectMess->query()->fetchAll()) {     
                                // if(array_key_exists($student->CID, $my_mess)){ 
                                    // $my_mess[$student->CID]=$my_mess[$student->CID]+count($rowMess);
                                // } else {
                                    // $my_mess[$student->CID]=count($rowMess);
                                    
                                // }
                            // }
                        // }
                // }
        // }
        // $this->view->my_mess = $my_mess;    
        
    /*********------------------------------*/
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
			
			switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.period = ?) OR ',
                            '(Subject.begin <= ?',' AND Subject.end >= ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin <= ?',' AND Subject.end >= ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',                            
							'(Subject.period = ?) OR ',
							'(Subject.time_ended_debt >= ? AND Subject.time_ended_debt_2 IS NULL) OR ', //-- Первое продление							
							'(Subject.time_ended_debt_2 >= ?) ) ', //--Второе продление
							
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            HM_Subject_SubjectModel::PERIOD_FREE,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Subject_SubjectModel::PERIOD_FIXED,
							$now,
							$now,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '(	(((Subject.end < ?','   AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                               '(Subject.end < ?','   AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                               '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?)) AND Subject.time_ended_debt IS NULL AND Subject.time_ended_debt_2 IS NULL) OR', # для непродленных сессий
							   '(Subject.end < ?', '  AND ((Subject.time_ended_debt < ? AND Subject.time_ended_debt_2 IS NULL) OR ', # первое продление прошло
													    ' (Subject.time_ended_debt_2 < ? ) )))',									# второе продление прошло
                        ),
						array(
									$this->getService('User')->getCurrentUserId(),
									$now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
									$now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
									HM_Subject_SubjectModel::STATE_CLOSED, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
									$now, $now,
									$now,
						)
					);					
                    break;
				case 'ending':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '(	(((Subject.end < ?','   AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                               '(Subject.end < ?','   AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                               '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?)) AND Subject.time_ended_debt IS NULL AND Subject.time_ended_debt_2 IS NULL) OR', # для непродленных сессий
							   '(Subject.end < ?', '  AND ((Subject.time_ended_debt < ? AND Subject.time_ended_debt_2 IS NULL) OR ', # первое продление прошло
													    ' (Subject.time_ended_debt_2 < ? ) )))',									# второе продление прошло
                        ),
						array(
									$this->getService('User')->getCurrentUserId(),
									$now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
									$now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
									HM_Subject_SubjectModel::STATE_CLOSED, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
									$now, $now,
									$now,
						)
					);
                    break;										
				default:
					$this->_redirector = $this->_helper->getHelper('Redirector');           
					$this->_redirector->gotoSimple('list', 'list', 'subject');	
            }
			
			$examTypes = HM_Subject_SubjectModel::getExamTypes();
			$serviceSubject = $this->getService('Subject');			
			
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                $teachers = $this->getService('Teacher')->fetchAllDependenceJoinInner('Subject', $where);
                $courses = $teachers->getList('PID', 'CID');

                if (count($courses)) {
                    $where = $this->getService('Subject')->quoteInto(
                        array('subid IN (?)'),
                        array(
                            $courses,
                        )
                    );
                    $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' , $where, 'name');
					
					$studentCourseData = array();
					$tmpSubjects = array();					
					foreach ($subjects as $key => $subject) {												 
						if(in_array($listSwitcher, array('ending','past'))){
							$countStudents = $this->getService('Subject')->getStudentCount($subject->subid);
							
							if($listSwitcher == 'ending' && $countStudents < 1){					
								$tmpSubjects[$subject->subid] = $subject;																
							} elseif($listSwitcher == 'past' && $countStudents > 0){								
								$tmpSubjects[$subject->subid] = $subject;															
							} else {
								continue;
							}						
						}
						
						$users_groups = $serviceSubject->getUsersGroupsById($subject->subid);

						$time_ended_debt = $subject->time_ended_debt; 
						if(!empty($subject->time_ended_debt_2)){
							$time_ended_debt = $subject->time_ended_debt_2;
						}
						
						$studentCourseData[$subject->subid] = array(
							'time_ended_debtor' => $time_ended_debt,
							'exam_type' 		=> $examTypes[$subject->exam_type],
							'users_groups' 		=> $users_groups,
							'isNewActionStudent'=>$serviceSubject->isNewActionStudent($subject->subid), //--есть последнее действие студента, трубующее внимания преподавателя							
							'semester'			=> $subject->semester,
						);
					}
					if(in_array($listSwitcher, array('ending','past'))){ $subjects = $tmpSubjects; }
					$this->view->studentCourseData = $studentCourseData;				
                }
                $this->view->share = false; // allow facebook etc.
            } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)){
				
				# ограничения на продленные сессии.
				# WTF ? 
				/*
				$whereAdditional = '';
				$tSelect = $this->getService('User')->getSelect();
				$tSelect->from(array('t' => 'Tutors'), array(
					'subject_id' => 't.CID',
				));
				
				$criteria  = 't.CID = s.subid AND ( ';
				$criteria .= ' 		(s.time_ended_debt IS NULL AND s.time_ended_debt_2 IS NULL) '; # сессия не продлена. Доступна всем назначенным тьюторам
				$criteria .= ' OR 	(s.time_ended_debt IS NOT NULL AND s.time_ended_debt_2 IS NULL AND t.date_debt IS NOT NULL) '; # первое продление, доступна тьюторам первого и второго продления
				$criteria .= ' OR 	(s.time_ended_debt_2 IS NOT NULL AND t.date_debt_2 IS NOT NULL) '; # второе продление, доступна тьюторам второго продления
				$criteria .= ' ) ';
				
				$tSelect->join(array('s' => 'subjects'), $criteria, array());
				$tSelect->where('t.MID = ?', $this->getService('User')->getCurrentUserId());
				$tSelect->where('t.CID > 0');
				$res = $tSelect->query()->fetchAll();
				if(!empty($res)){
					$data = array();
					foreach($res as $r){ $data[$r['subject_id']] = $r['subject_id']; }				
					if(!empty($data)){
						$whereAdditional = $this->quoteInto('Subject.subid IN (?)', $data);
					}				
				}
				*/
				
				$current_user_id = $this->getService('User')->getCurrentUserId();
				
				if(in_array($listSwitcher, array('ending'))){
					# получакем все сессии ть.тора, в которых нет студентов доступных.
					$subject_ids = $this->getService('Tutor')->getSubjectIds($current_user_id);
					$toEnding = array();
					foreach($subject_ids as $subject_id){
						$countStudents = count($serviceSubject->getAvailableStudentsAssign($current_user_id, $subject_id));
						if(empty($countStudents)){
							$toEnding[$subject_id] = $subject_id;
						}						
					}
					
					if(!empty($toEnding)){
						$where = '('.$where.') OR Subject.subid IN ('.implode(',', $toEnding).')';						
					}
				}
				
                $tutors 	= $this->getService('Tutor')->fetchAllDependenceJoinInner('Subject', $where);
                $courses	= $tutors->getList('TID', 'CID');
				
				
				$current_user = $this->getService('User')->getCurrentUser();
				
				# для каждой сессии найти доступных тьюторов. Если среди них нет текущего тьютора, сессию не выводим.
				foreach($courses as $key => $subject_id){
						
					# Если тьютор заблокирован, до показываем его сессии, чтобы можно было зайти от его имени и сделать нужное.
					if($current_user->blocked == HM_User_UserModel::STATUS_BLOCKED){					
						$availableTutors[$current_user_id] = $current_user_id;
					} else {
						$availableTutors = $this->getService('Subject')->getAvailableDebtorTutors($subject_id);
					}
					
					
					if(!isset($availableTutors[$current_user_id])){
						unset($courses[$key]);
					} else{						
						if(in_array($listSwitcher, array('current'))){
							# из текущих убираем сессии, в которых нет назначенных студентов
							$countStudents = count($serviceSubject->getAvailableStudentsAssign($current_user_id, $subject_id));
							if(empty($countStudents)){
								unset($courses[$key]);
							}
						}
					}
				}

				#$result 	= array_intersect($courses, $data);				
				#$courses 	= $result;

                if (count($courses)) {
                    $where = $this->getService('Subject')->quoteInto(
                        array('subid IN (?)'),
                        array(
                            $courses,
                        )
                    );
                    $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Tutor' , $where, 'name');
					
					$studentCourseData = array();
					$tmpSubjects = array();	
					$current_user_id = $this->getService('User')->getCurrentUserId();
					foreach ($subjects as $key => $subject) {												 
						if(in_array($listSwitcher, array('ending','past'))){
							
							$countStudents = count($serviceSubject->getAvailableStudentsAssign($current_user_id, $subject->subid));							
							
							if($listSwitcher == 'ending' && $countStudents < 1){					
								$tmpSubjects[$subject->subid] = $subject;																
							} elseif($listSwitcher == 'past' && $countStudents > 0){								
								$tmpSubjects[$subject->subid] = $subject;															
							} else {
								continue;
							}						
						}
						
						$isGraduated  = $listSwitcher == 'ending' ? true : false;
						$users_groups = $serviceSubject->getUsersGroupsById($subject->subid, $isGraduated);						
						$users_groups = $serviceSubject->filterGroupsByAssignStudents($subject->subid, $this->getService('User')->getCurrentUserId(), $users_groups); # оставляем только те группы, на которых назначены студенты, доступные тьютору.
						
						$time_ended_debt = $subject->time_ended_debt; 
						if(!empty($subject->time_ended_debt_2)){
							$time_ended_debt = $subject->time_ended_debt_2;
						}						
						
						$studentCourseData[$subject->subid] = array(
							'time_ended_debtor' => $time_ended_debt,
							'exam_type' 		=> $examTypes[$subject->exam_type],
							'users_groups' 		=> $users_groups,
							'isNewActionStudent'=>$serviceSubject->isNewActionStudent(
									$subject->subid,
									$this->getService('Subject')->getAvailableStudents($this->getService('User')->getCurrentUserId(), $subject->subid)
								), //--есть последнее действие студента, трубующее внимания преподавателя							
							'semester'			=> $subject->semester,
						);
					}
					if(in_array($listSwitcher, array('ending','past'))){ $subjects = $tmpSubjects; }					
					$this->view->studentCourseData = $studentCourseData;
                }
                $this->view->share = false; // allow facebook etc.
            }



        } else {
            if(count($courses)){
                $this->view->is_student = true;

                $userId = $this->getService('User')->getCurrentUserId();
                $serviceClaimant = $this->getService('Claimant');
                $selectClaimants = $serviceClaimant->getSelect();
                $selectClaimants->from(array('c' => 'claimants'),
                    array(
                        'subid' => 'c.CID',
                    )
                )->where($this->quoteInto(array('CID IN (?)',' AND MID = ?', ' AND status = ?'), array(array_values($courses), $userId, HM_Role_ClaimantModel::STATUS_ACCEPTED)));
                $claimantSubjects = $selectClaimants->query()->fetchAll();

                $claimantSubjectSIDs = array();
                if(count($claimantSubjects)){
                    foreach ($claimantSubjects as $claimantSubject)
                    {
                        $subId = $claimantSubject['subid'];
                        $claimantSubjectSIDs[$subId] = $subId;
                    }
                }
            }
        }

        if($subjects){
            foreach ($subjects as $subject) {
                $subject->isUnsubscribleSubject = isset($claimantSubjectSIDs[$subject->subid]);
				#if($subject->subid == 24269){
					if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
						$subject->isGia = $this->getService('Subject')->isGia($subject);												
					}					
				#}
            }
        }

/*        switch ($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_RoleModelAbstract::ROLE_STUDENT:
                $students = $this->getService('Student')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));
                $courses = $students->getList('SID', 'CID');

                $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId(), 'is_lookable = ?' => HM_Role_GraduatedModel::LOOKABLE));

                $courses = array_merge($courses, $graduated->getList('SID', 'CID'));
                $marks = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $this->getService('User')->getCurrentUserId()));
                $this->view->marks = $marks->getList('cid', 'mark');

                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ')', 'name');
                $this->view->share = true; // allow facebook etc.


                $this->view->graduatedList = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));
                $this->view->studentCourseData = $studentCourseData;

                break;
            case HM_Role_RoleModelAbstract::ROLE_TEACHER:
                $teachers = $this->getService('Teacher')->fetchAll(
                    array(
                    	'MID = ?' => $this->getService('User')->getCurrentUserId()
                    )
                );
                $courses = $teachers->getList('PID', 'CID');

                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ') AND end > ' . $this->getService('Subject')->getSelect()->getAdapter()->quote(date('Y-m-d', strtotime('-1 day'))), 'name');
                $this->view->share = false; // allow facebook etc.

                break;

        }*/

       // pr($subjects);

        $this->view->listSwitcher = $listSwitcher;
        $this->view->subjects = $subjects;
		
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_STUDENT))){
			$subjectIds = $subjects ? $subjects->getList('subid') : false;
			if(!empty($subjectIds)){
				$this->view->marks = $this->getService('SubjectMark')->fetchAll($this->quoteInto(
										array('mid=?', ' AND cid IN (?)'),
										array($this->getService('User')->getCurrentUserId(), $subjectIds)
									 ));
			}
		}
    }



    protected function _getMessages() {


        if($this->_form->hasModifier('HM_Form_Modifier_BaseTypeSession')){
            return array(
                self::ACTION_INSERT    => _('Учебная сессия успешно создана'),
                self::ACTION_UPDATE    => _('Учебная сессия успешно обновлена'),
                self::ACTION_DELETE    => _('Учебная сессия успешно удалена'),
                self::ACTION_DELETE_BY => _('Учебные сессии успешно удалены')
            );
        }else{
            return array(
                self::ACTION_INSERT    => _('Учебный курс успешно создан'),
                self::ACTION_UPDATE    => _('Учебный курс успешно обновлён'),
                self::ACTION_DELETE    => _('Учебный курс успешно удалён'),
                self::ACTION_DELETE_BY => _('Учебные курсы успешно удалены')
            );
        }

    }


    public function setDefaults(Zend_Form $form, $newSession = false) {

        $subjectId = ( int ) $this->_request->getParam('subid', 0);

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        if ($subject) {

            if ($newSession) {
                $subject->longtime = 0; // #10957#note-10

                $today = new HM_Date();
                $subject->begin = $today->toString('dd.MM.Y');
                $today->add(10, HM_Date::DAY);
                $subject->end = $today->toString('dd.MM.Y');
            } else {
                $subject->begin = $subject->getBegin();
                $subject->end = $subject->getEnd();
            }
            $values = $subject->getValues();

            if ($form->hasModifier('HM_Form_Modifier_BaseTypeSession')) {
                $values['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
            }

            $item = $this->getService('SubjectRoom')->fetchAll($this->getService('SubjectRoom')->quoteInto('cid = ?', $subjectId))->current();
            $values['rooms'] = $item->rid;
// теперь только одна аудитория
//            if (count($collection)) {
//                $values['rooms'] = array();
//                foreach($collection as $item) {
//                    $values['rooms'][$item->rid] = $item->rid;
//                }
//            }

            $accessElements = array();

            foreach(HM_Subject_SubjectModel::getFreeAccessElements() as $key => $value){
                if($key & $values['access_elements']){
                    $accessElements[] = $key;
                }
            }
            $values['access_elements'] = $accessElements;


/*            if($values['reg_type'] == HM_Subject_SubjectModel::REGTYPE_FREE){
                $values['reg_type'] = HM_Subject_SubjectModel::REGTYPE_MODER;
            }*/

            if ($values['period_restriction_type'] == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                $begin = new HM_Date($values['begin_planned']);
                $values['begin'] = $begin->toString('dd.MM.Y');
                $end = new HM_Date($values['end_planned']);
                $values['end'] = $end->toString('dd.MM.Y');
            }
			

//            var_dump($values);die;
            $form->populate($values);
        }
    }

    public function update(Zend_Form $form) {

        $accessElements = 7;//0

        /*
         *
         * #7633

         foreach($form->getValue('access_elements') as $element){
            $accessElements = $accessElements | (int) $element;
        }
        */

        /*
        if($form->getValue('reg_type') == HM_Subject_SubjectModel::REGTYPE_MODER && $form->getValue('claimant_process_id') == 0){
            $regType = HM_Subject_SubjectModel::REGTYPE_FREE;
        }else{
            $regType = $form->getValue('reg_type');
        }*/

        $regType = $form->getValue('reg_type');

        $markType = $form->getValue('mark_type', HM_Mark_StrategyFactory::MARK_WEIGHT);
        $markTypePrefix = HM_Mark_StrategyFactory::getType($markType);
        $periodRestrictionType = $form->getValue('period_restriction_type');
        $subject = $this->getService('Subject')->update(
            array(
                'subid' => $form->getValue('subid'),
                'name' => $form->getValue('name'),
                'name_translation' => $form->getValue('name_translation'),
                'shortname' => $form->getValue('shortname'),
                'shortname_translation' => $form->getValue('shortname_translation'),
            	'supplier_id' => $form->getValue('supplier_id'),
                'description' => $form->getValue('description'),
                'description_translation' => $form->getValue('description_translation'),
                'external_id' => $form->getValue('external_id'),
                'code' => $form->getValue('code'),
                'type' => $form->getValue('type'),
                'reg_type' => $regType,
                'begin' => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
                'end' => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
                'begin_planned' => $form->getValue('begin'),
                'end_planned' => $form->getValue('end'),
                'longtime' => $form->getValue('longtime'),
                'price' => sprintf("%.2f", $form->getValue('price')),
                'price_currency' => $form->getValue('price_currency'),
                'plan_users' => $form->getValue('plan_users'),
                'period' => $form->getValue('period'),
                'period_restriction_type' => $periodRestrictionType,
                'access_elements' => $accessElements,
                'auto_done'       => $form->getValue('auto_done'),
                'base'            => $form->getValue('base'),
                'base_id'            => $form->getValue('base_id'),
                'claimant_process_id' => $form->getValue('claimant_process_id'),
                'scale_id' => $form->getValue($markTypePrefix.'_scale_id'),
                'auto_mark' => $form->getValue($markTypePrefix.'_auto_mark'),
                'auto_graduate' => ($markType == HM_Mark_StrategyFactory::MARK_WEIGHT) ? $form->getValue($markTypePrefix.'_auto_graduate'): 0,
                'formula_id' => $form->getValue($markTypePrefix.'_formula_id'),
                'threshold' => $form->getValue($markTypePrefix.'_threshold'),
                'mark_type' => $markType,
                'year_of_publishing' => $form->getValue('year_of_publishing'),
                'hours_total' => $form->getValue('hours_total'),
                'zet' => $form->getValue('zet'),
                'exam_type' => $form->getValue('exam_type'),                
                'semester' 	=> $form->getValue('semester'),                
            )
        );
		
        $subjectId = $subject->subid;
        $this->getService('Subject')->linkClassifiers($subjectId, $form->getClassifierValues());
        $this->getService('Subject')->linkRoom($subjectId, $form->getValue('rooms'));

        if ($form->getValue('icon') != null) {
            HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('icon'));
        } else {
            HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('server_icon'));
        }

        //Обрезаем все занятия выходящие за рамки курса
        if( $subject->period == HM_Subject_SubjectModel::PERIOD_DATES ) {
            $lessonService = $this->getService('Lesson');
            $lessonService->updateWhere(array('end' => $form->getValue('end') . ' 23:59:59'),
                                        $lessonService->quoteInto(array('CID = ?',' AND (end > ?',' OR end < ?)'),
                                                                  array($subjectId,
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('begin'))))));
            $lessonService->updateWhere(array('begin' => $form->getValue('begin') . ' 00:00:00'),
                                        $lessonService->quoteInto(array('CID = ?',' AND (begin > ?',' OR begin < ?)'),
                                                                  array($subjectId,
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('begin'))))));
        }

    }

    public function delete($id) {
        $this->getService('Subject')->delete($id);
    }

    public function create(Zend_Form $form) {

        $accessElements = 7; // 0

        /*
         * #7633
         * foreach($form->getValue('access_elements') as $element){
            $accessElements = $accessElements | (int) $element;
        }*/
/*
        if($form->getValue('reg_type') == HM_Subject_SubjectModel::REGTYPE_MODER && $form->getValue('claimant_process_id') == 0){
            $regType = HM_Subject_SubjectModel::REGTYPE_FREE;
        }else{
            $regType = $form->getValue('reg_type');
        }*/

        $regType = $form->getValue('reg_type');
        $markType = $form->getValue('mark_type',HM_Mark_StrategyFactory::MARK_WEIGHT);
        $markTypePrefix = HM_Mark_StrategyFactory::getType($markType);
        $periodRestrictionType = $form->getValue('period_restriction_type');
        $subject = $this->getService('Subject')->insert(
            array(
                'name'                => $form->getValue('name'),
				'name_translation'	  => $form->getValue('name_translation'),
            	'shortname'           => $form->getValue('shortname'),
            	'shortname_translation'   => $form->getValue('shortname_translation'),
                'description_translation' => $form->getValue('description_translation'),
                'external_id'         => $form->getValue('external_id'),
                'code'                => $form->getValue('code'),
                'type'                => $form->getValue('type'),
                'reg_type'            => $regType,
                'begin'               => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
                'end'                 => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
                'begin_planned'       => $form->getValue('begin'),
                'end_planned'         => $form->getValue('end'),
            	'longtime'            => $form->getValue('longtime'),
                'price'               => sprintf("%.2f", $form->getValue('price')),
                'price_currency'      => $form->getValue('price_currency'),
                'plan_users'          => $form->getValue('plan_users'),
                'period'              => $form->getValue('period'),
                'period_restriction_type' => $periodRestrictionType,
                'access_elements'     => $accessElements,
                'auto_done'           => $form->getValue('auto_done'),
                'base'                => $base = $form->getValue('base'),
                'base_id'             => $baseId = $form->getValue('base_id'),
                'base_color'          => ($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION))?  $this->getService('Subject')->getSubjectColor($baseId) : $this->getService('Subject')->generateColor(),
                'claimant_process_id' => $form->getValue('claimant_process_id'),
                'scale_id' => $form->getValue($markTypePrefix.'_scale_id'),
                'auto_mark' => $form->getValue($markTypePrefix.'_auto_mark'),
                'auto_graduate' => ($markType == HM_Mark_StrategyFactory::MARK_WEIGHT) ? $form->getValue($markTypePrefix.'_auto_graduate'): 0,
                'formula_id' => $form->getValue($markTypePrefix.'_formula_id'),
                'threshold' => $form->getValue($markTypePrefix.'_threshold'),
                'mark_type' => $markType,
                'year_of_publishing' => $form->getValue('year_of_publishing'),
                'hours_total' => $form->getValue('hours_total'),
                'zet' => $form->getValue('zet'),
                'exam_type' => $form->getValue('exam_type'),
                
            )
        );

        if($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION)) {

            try {
                $this->getService('Subject')->copyElements($baseId, $subject->subid);
            } catch (HM_Exception $e) {
                // что-то не скопировалось..(
            }

            // апдейтим родительский уч.курс - убираем ограничения по времени и месту
            // автоназначение basetype
            $changes = array(
                'base'      => HM_Subject_SubjectModel::BASETYPE_BASE,
                'period'    => HM_Subject_SubjectModel::PERIOD_FREE,
                'claimant_process_id' => array_shift(HM_Subject_SubjectModel::getTrainingProcessIds()),
            );
            $this->getService('Subject')->updateWhere($changes, array('subid = ?' => $baseId));
            $this->getService('Subject')->unlinkRooms($baseId);
        }

        // Добавление курсов для кураторов, наблюдающих за новыми курсами
        $deans = $this->getService('DeanOptions')->fetchAll(array('unlimited_subjects = ?' => 0, 'assign_new_subjects = ?' => 1, 'user_id != ?' => $this->getService('User')->getCurrentUserId()));

        foreach($deans as $value){
            $this->getService('Dean')->insert(array('MID' => $value->user_id, 'subject_id' => $subject->subid));
        }

        $this->getService('Dean')->insert(array('MID' => $this->getService('User')->getCurrentUserId(), 'subject_id' => $subject->subid));

        $classifiers = $form->getClassifierValues();
        $this->getService('Classifier')->unlinkItem($subject->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($subject->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                }
            }
        }

        $roomId = $form->getValue('rooms');
        $this->getService('Subject')->linkRoom($subject->subid, $roomId);

        $photo = $form->getElement('icon');
        if($photo->isUploaded()){
            $path = Zend_Registry::get('config')->path->upload->subject . HM_Subject_SubjectModel::getIconFolder($subject->subid) . '/' . $subject->subid . '.jpg';
            $photo->addFilter('Rename', $path, 'photo', array( 'overwrite' => true));
            $photo->receive();
            $img = PhpThumb_Factory::create($path);
	        $img->resize(90, 90);
	        $img->save($path);
        }



    }

    public function cardAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $this->view->subject = false;
        $this->view->subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($subjectId)
        );

    }

    public function descriptionAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $userId = (int)$this->getService('User')->getCurrentUserId();
        
        $this->view->isStudent = $this->getService('Subject')->isStudent($subjectId, $userId);
        $this->view->subjectId = $subjectId;
        
        $this->view->subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($subjectId)
        );

        $this->view->clType = $this->_getParam('type', 0);
        $this->view->clItem = $this->_getParam('item', 0);
        $this->view->clClassifierId = $this->_getParam('classifier_id', 0);

        $this->view->regText = _('Подать заявку');
        /*if($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_GUEST){
            $this->view->regText = _('Зарегистрироваться');
        }*/


    }
	
	public function updateName($name)
	{
		if($this->_currentLang == 'eng' && $translation != '')
			return $translation;
		else
			return $name;
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

    public function editAction() 
    {
        		
		if ($subid = $this->_getParam('subject_id')) {
            $this->_setParam('subid', $subid);
        }
        $form = $this->_getForm();
        $request = $this->getRequest();
		
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);
				/*
				$dublIDs = $this->getService('Subject')->getMultipleIDSubjects();
				if(count($dublIDs)){
					$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
														'message' => _('Обнаружены задвоения сессий по внешнему ID: '.implode(', ',$dublIDs))));
				}
				*/

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
            $subjectId =  $this->_getParam('subid');
            $subject = $this->getService('Subject')->find($subjectId)->current();
            $markTypePrefix = $subject->getMarkType();
            $markService = $this->getService('MarkStrategyFactory')->getStrategy($markTypePrefix);
            foreach ($markService->getElementsNameArray() as $elementName) {
                $form->getElement("{$markTypePrefix}_{$elementName}")->setValue($subject->$elementName);
            }
			$this->view->subject_id = $subjectId;
        }
		
        $this->view->form = $form;

    }

    public function copyAction()
    {
        $subid = (int) $this->_getParam('subid', 0);
        if ($subid) {
            $subject = $this->getService('Subject')->copy($subid);
            if ($subject) {
                if($this->_form->hasModifier('HM_Form_Modifier_BaseTypeSession')){
                    $this->_flashMessenger->addMessage(_('Учебная сессия успешно скопирована.'));
                }else{
                    $this->_flashMessenger->addMessage(_('Учебный курс успешно скопирован.'));
                }
            }
        }

        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher' => 'index', 'subject_id' => $this->_getParam('subject_id', null), 'base' => $this->_getParam('base', 0)));
        //$this->_redirectToIndex();
    }

    public function copyFromBaseAction()
    {
        $subid = (int) $this->_getParam('subid', 0);
        if ($subid) {
            $collection = $this->getService('Subject')->find($subid);
            if (count($collection)) {
                $subject = $collection->current();
                if ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                    try {
                        $this->getService('Subject')->copyElements($subject->base_id, $subject->subid);
                    } catch (HM_Exception $e) {
                        // что-то не скопировалось..(
                    }
                    $this->_flashMessenger->addMessage(_('Содержимое базового курса успешно скопировано.'));
                }
            }
        }
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher' => 'index', 'subject_id' => $this->_getParam('subject_id', null), 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION));
        //$this->_redirectToIndex();
    }


    public function updateBaseId($baseId, $select)
    {
        if(empty($this->_baseIdCache)){
            $baseIds = $this->getBaseIds($select);
            if(empty($baseIds)){
                return _('Нет');
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
            $subjectIds = $this->getSubjectIds($select);
            
            if(empty($subjectIds)){
                return _('Нет');
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

    public function loadTeacherCache($select)
    {
        if(!empty($this->_teacherCache)){
            return true;
        }

        $subjectIds = $this->getSubjectIds($select);
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
	
	
	
	/**
	 * - формирует данные для отправки письма. Оповещение о назначении на укрс
	*/
	public function prepareEmailSend($tSend){
		$templateId = 25; //--Шаблон для тьюторов в БД		
		$this->_template = $this->getOne($this->getService('Notice')->fetchAll($this->getService('Notice')->quoteInto('type = ?', $templateId)));
		if($this->_template->enabled == 1) {
			$messageTitle = str_replace('[COURSE]', '', $this->_template->title);
			$this->_serviceSubject = $this->getService('Subject');
			
			foreach($tSend as $personId => $subjectsIDs){			
				$this->_person = $this->getService('User')->getOne($this->getService('User')->find($personId));			
				$toName = $this->_person->LastName.' '.$this->_person->FirstName.' '.$this->_person->Patronymic;
				$toEmail = $this->_person->EMail;
				
				$subjectsName = array();
				foreach($subjectsIDs as $courseId){
					$subject = $this->getOne($this->_serviceSubject->find($courseId));
					$subjectsName[] = $subject->getName();								
				}
				$subjectsName = str_replace('[COURSE]', '/ курсов:<br><br><b>'.implode('</b><br><b>', $subjectsName).'</b>', $this->_template->message);	
				
				$this->sendEmail($toEmail, $toName, $messageTitle, $subjectsName);			
			}
		}
	}	
		
	
	public function sendEmail($toEmail, $toName, $messageTitle = '', $messageText) {		
		
		if(!$toEmail || !$toName || !$messageText){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($toEmail) && $validator->isValid($toEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
			$mail->addTo($toEmail, $toName);
            
			$mail->setSubject($messageTitle);
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setFromToDefaultFrom();
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);			
			try {
				$mail->send();
				            
				return true;
            } catch (Zend_Mail_Exception $e) {		
                return false;
            }
		}			
		return false;		
	}
    
    
	public function tutorFilter($data){        		
		$value=trim($data['value']);
		$select=$data['select'];
		if(!empty($value)){
			if(mb_strtolower($value) == 'нет'){				
				$select->joinLeft(array('tu' => 'Tutors'), 'tu.CID = s.subid AND tu.CID > 0', array());
				$select->where('tu.MID IS NULL'); 
			} else {
				if(!$this->userService) { $this->userService = $this->getService('User'); }
				$usesrSelect = $this->userService->getSelect()->from('People', array('MID'))->where(
					$this->quoteInto("(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' ') , FirstName), ' '), Patronymic)) LIKE LOWER(?))", '%'.$value.'%')		
				);
				$res = $usesrSelect->query()->fetchAll();
				$userIDs = array();		
				if(count($res)){
					foreach($res as $u){
						$userIDs[$u['MID']] = $u['MID'];
					}
				}			
				if(count($userIDs)){				
					$select->joinLeft(array('tu' => 'Tutors'), 'tu.CID = s.subid AND tu.CID > 0', array());										
					$select->where($this->quoteInto('tu.MID IN (?)', $userIDs));						
				} else {
					$select->where('1=0');		
				}
			}
		}		
	}
	
	public function teacherFilter($data){		
		$value=trim($data['value']);
		$select=$data['select'];
		if(!empty($value)){
			if(mb_strtolower($value) == 'нет'){				
				$select->joinLeft(array('te' => 'Teachers'), 'te.CID = s.subid AND te.CID > 0', array());
				$select->where('te.MID IS NULL'); 
			} else {
				if(!$this->userService) { $this->userService = $this->getService('User'); }
				$usesrSelect = $this->userService->getSelect()->from('People', array('MID'))->where(
					$this->quoteInto("(LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' ') , FirstName), ' '), Patronymic)) LIKE LOWER(?))", '%'.$value.'%')		
				);
				$res = $usesrSelect->query()->fetchAll();
				$userIDs = array();		
				if(count($res)){
					foreach($res as $u){
						$userIDs[$u['MID']] = $u['MID'];
					}
				}			
				if(count($userIDs)){				
					$select->joinLeft(array('te' => 'Teachers'), 'te.CID = s.subid AND te.CID > 0', array());										
					$select->where($this->quoteInto('te.MID IN (?)', $userIDs));						
				} else {
					$select->where('1=0');		
				}
			}
		}
	}
	
	public function groupFilter($data){				
		try {
			$value=trim($data['value']);
			$select=$data['select'];
			
			
			if(!$this->userService) { $this->userService = $this->getService('User'); }
			$groupSelect = $this->userService->getSelect();
			$groupSelect->from(
				array('sg' => 'study_groups'),						
				array(				
					'CID' => 'st.CID',				
				)
			);				
			$groupSelect->join(array('sgc' => 'study_groups_custom'), 'sgc.group_id = sg.group_id', array());
			$groupSelect->join(array('st' => 'Students'), 'st.MID = sgc.user_id', array());
			$groupSelect->where($this->quoteInto("sg.name LIKE LOWER(?)", '%'.$value.'%'));
			$groupSelect->group(array('st.CID'));
			$res = $groupSelect->query()->fetchAll();
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
	
	
	
	
	public function updateIsDO($type){
		$facultList = HM_Subject_SubjectModel::getFacultys();
		return $facultList[$type];		
	}
	
	
	//--меняет принадлежность сессии - ФДО, ФДО_Б или прочие.
	public function changeFacultyAction(){
		
		$faculty = (int)$this->_getParam('faculty', HM_Subject_SubjectModel::FACULTY_OTHER);
		$subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
		
		if(!count($subjects)){
			$this->_flashMessenger->addMessage(_('Не удалось изменить записи.'));
		} else {		
			try {
				$isUpdate = $this->getService('Subject')->updateWhere(
					array('isDO' => $faculty),
					array($this->quoteInto('subid IN (?)', $subjects))
				);
				if($isUpdate){
					$this->_flashMessenger->addMessage(_('Операция выполнена успешно'));
				} else {
					$this->_flashMessenger->addMessage(_('Не удалось изменить записи'));
				}		
			} catch (Exception $e) {
				$this->_flashMessenger->addMessage(_('Не удалось изменить записи'));
			}
		}
        $this->_redirectToIndex();
	}

    public function updateGroups($subjectId, $select)
    {
        $this->loadGroupCache($select);

        if(!array_key_exists($subjectId, $this->_groupCache)){
            return _('Нет');
        }

        $groups = $this->_groupCache[$subjectId];
        if(empty($groups)){
            return _('Нет');   
        }

        $count  = count($groups);
        $result = ($count > 1) ? array('<p class="total">' . sprintf(_n('группы plural', '%s группы', $count), $count) . '</p>') : array();
        foreach($groups as $groupId => $groupName){
            $result[] = '<p>' . $groupName . '</p>';
        }
        return implode(' ', $result);
    }

    public function loadGroupCache($select)
    {
        if(!empty($this->_groupCache)){
            return true;
        }

        $subjectIds = $this->getSubjectIds($select);
        if(empty($subjectIds)){
            return false;
        }

        if(!$this->userService) { $this->userService = $this->getService('User'); }
        $subSelect = $this->userService->getSelect();
        $subSelect->from(array('st'  => 'Students'), array('subjectId' => 'st.CID', 'groupId' => 'sg.group_id', 'groupName' => 'sg.name')); 
        $subSelect->join(array('sgu' => 'study_groups_users'), 'sgu.user_id = st.MID', array());
        $subSelect->join(array('sg'  => 'study_groups'), 'sg.group_id = sgu.group_id', array());
        $subSelect->where($this->quoteInto('st.CID IN (?)', $subjectIds));
        $subSelect->where('st.CID > 0');
        $subSelect->where('st.MID > 0');
        $subSelect->order('sg.name');
        $subSelect->group(array('st.CID', 'sg.group_id', 'sg.name'));
        $res = $subSelect->query()->fetchAll();
        if(empty($res)){
            return false;
        }
        foreach($res as $item){
            $this->_groupCache[$item['subjectId']][$item['groupId']] = $item['groupName'];
        }
        return true;
    }
	
	public function updateProgramm($subjectId, $select) 
    {
        $this->loadProgrammCache($select);

        if(!array_key_exists($subjectId, $this->_programmCache)){
            return _('Нет');
        }

        return $this->_programmCache[$subjectId];
    }

    public function loadProgrammCache($select)
    {
        if(!empty($this->_programmCache)){
            return true;
        }

        $subjectIds = $this->getSubjectIds($select);
        if(empty($subjectIds)){
            return false;
        }
        
        $subSelect = $this->getService('Subject')->getSelect();
        $subSelect->from(array('p'  => 'programm'), array('subjectId'=>'pe.item_id', 'programmName'=>'p.name'));
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

    private function loadCacheFromQuery($select)
    {
        if(
            !empty($this->_subjectIdsCache)
            &&
            !empty($this->_baseIdsCache)
        ){
            return false;
        }

        $res = $select->query()->fetchAll();
        if(empty($res)){
            # признак того, что кэш был загружен, но записей в БД нет
            $this->_subjectIdsCache[0] = false;
            $this->_baseIdsCache[0]    = false;
            return false;
        }

        foreach($res as $item){
            $this->_subjectIdsCache[$item['subid']] = $item['subid'];
            $this->_baseIdsCache[$item['base_id']]  = $item['base_id'];
        }
        return true;
    }

    private function getSubjectIds($select)
    {
        $this->loadCacheFromQuery($select);
        return array_filter($this->_subjectIdsCache);
    }

    private function getBaseIds($select)
    {
        $this->loadCacheFromQuery($select);
        return array_filter($this->_baseIdsCache);
    }


    # field = subid
    public function updateClassifiers($field, $select)
    {
        if(empty($this->_classifierCache)){
            $subjectIds = $this->getSubjectIds($select);
            
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

    public function loadTutorCache($select)
    {
        if(!empty($this->_tutorCache)){
            return true;
        }

        $subjectIds = $this->getSubjectIds($select);        
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

}

