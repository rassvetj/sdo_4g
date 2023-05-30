<?php
class Assign_StudentController extends HM_Controller_Action_Assign
{

    protected $service      = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName  = 'subid';
    protected $id           = 0;
    protected $_currentLang = 'rus';

//    protected $courseCache = array(); //#17462
    protected $_cache = array();

    //protected $_fixedRow = false;

    protected $_assignOptions = array(
        'role'                  => 'Student',
        'courseStatuses'        => array(2),
        /////////////////////////////////////////
        'table'                 => 'Students',
        ////////////////////////////////////////
        'tablePersonField'      => 'MID',
        'tableCourseField'      => 'CID',
        ////////////////////////////////////////
        'courseTable'           => 'subjects',
        ////////////////////////////////////////
        'courseTablePrimaryKey' => 'subid',
        'courseTableTitleField' => 'name',
        'courseIdParamName'     => 'subject_id'
    );
    /**@var  $_serviceSubject HM_Subject_SubjectService | null*/
    protected $_serviceSubject = null;
    protected $_hasErrors = false;
    protected $_expiredSubjectsNames = array();
    protected $_cacheSubjectExpire = array();
    protected $_cacheSubjectTitle = array();
    protected $_cacheFioUsers = array();
    protected $_periodRestrictionType;
	
	protected $_subject 		= NULL;	
	protected $_serviceUser		= NULL;
	protected $_languageList	= NULL;

    public function init()
    {
        parent::init();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if (!$this->isAjaxRequest()) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));
				$this->_subject = $subject;

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
        
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		$this->view->headScript()->appendFile($config->url->base.'/js/rgsu.js');
		
		$isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		
		
		// temp hack
        if (!isset($this->_assignOptions['courseIdParamName'])) {
            $this->_assignOptions['courseIdParamName'] = 'course_id';
        }

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId = ($courseId) ? "grid{$courseId}" : 'grid'; // ВАЖНО! это не $courseId, а скорее subjectId - id уч.курса, если мы находимся в панели управления;

		# для переключение м.у табами GridSwitcher с пустым возвратом строк
		$isEmpty 	     = (bool)$this->_getParam('isempty'.$gridId, false);
		$isSetEmptyQuery = $isEmpty ? true : $isSetEmptyQuery;
		$this->getRequest()->setParams(array('isempty'.$gridId => 0));
		
		
    	$default = new Zend_Session_Namespace('default');
        
        if (!$courseId && $default->grid['assign-student-index'][$gridId]['all'] == self::FILTER_LISTENERS_COURSE) {
            $default->grid['assign-student-index'][$gridId]['all'] = self::FILTER_LISTENERS;
        }

    	$switcher = $this->_getParam('all', isset($default->grid['assign-student-index'][$gridId]['all']) ? $default->grid['assign-student-index'][$gridId]['all'] : self::FILTER_LISTENERS_COURSE);
		
		
		
        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }
        if ($sorting == 'fio_ASC') {
            $this->_request->setParam("masterOrder{$gridId}", 'notempty DESC');
        }
		
		$group_additional = array();  //--список полей для доп группировки
        $from = array (
            'MID',
            //'dublicate', //ВНИМАНИЕ при мерже !! Дубликаты на этой странице НЕ должны выводиться; 
            'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
            'departments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.owner_soid)'),
            'positions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.soid)'),
            'groups' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(g.group_id))'),			
        );

        if (!$courseId){
            //$from['courses'] = new Zend_Db_Expr('GROUP_CONCAT(t2.CID)');
            $from['courses'] = 't2.CID';
            $from['programms'] = new Zend_Db_Expr('GROUP_CONCAT(pu.programm_id)');			
        } else {
            $from['period_restriction_type'] = 't4.period_restriction_type';
            $from['state'] = 't4.state';			            		
        }

        $from['tags'] = 't1.MID';
		
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN) &&  $courseId > 0){
			$from['tutorIDs'] = new Zend_Db_Expr('GROUP_CONCAT(tu.MID)');
		}
		
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){			
			$from['sub_groups']  = 't1.MID';
			$subjectLanguageName = '';
			$this->id 		= (int) $this->_getParam('subject_id', 0);
			
			if(!$this->_subject && $this->id){			
				$subject 		= $this->getOne($this->getService($this->service)->find($this->id));
				$this->_subject = $subject;
			}
			
			if($this->_subject){
				$subjectLanguageName = ': '.$this->getLanguageName($this->_subject->language_code).'('.$this->_subject->semester.')';
			}
		}
		

        $select = $this->getService('User')->getSelect();
        // ВНИМАНИЕ! При мерже с Билайном поле departments не нужно! У билайна уже есть соотв.столбцы
        $select->from(
            array('t1' => 'People'),
            $from
        )
        ->joinLeft(array('d' => 'structure_of_organ'),
            'd.mid = t1.MID',
            array()
        )->joinLeft(
            array('g' => 'study_groups_users'),
            'g.user_id = t1.MID',
            array()
        );
        if($switcher == self::FILTER_LISTENERS_COURSE || $switcher == self::FILTER_LISTENERS){
            $subSelectStudents =  $this->getService('User')->getSelect();
			$subSelectStudents->from('Students', array(	'MID', 	'CID'	=> new Zend_Db_Expr('GROUP_CONCAT(CID)') ) 	);
			$subSelectStudents->where('CID > 0');
			$subSelectStudents->group(array('MID'));
			
			
			$select->joinInner(
				array('t2' => $subSelectStudents),
                't1.MID = t2.MID',
                array()
			);
			$group_additional['t2.CID'] = 't2.CID';

			
			/*
			$select->joinInner(
                array('t2' => $this->_assignOptions['table']),
                't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                array()
            );
			*/
			
        }
		//echo $this->_assignOptions['tablePersonField'];
       
        if ($courseId > 0) {

        	$subSelect = $this->getService('User')->getSelect()
				        	->from(
					        	array('t1' => $this->_assignOptions['table']),
					        	array($this->_assignOptions['tablePersonField'], $this->_assignOptions['tableCourseField'],'time_registered', 'time_ended_planned',
										'time_ended_debtor'=> new Zend_Db_Expr('CASE WHEN t1.time_ended_debtor_2 IS NOT NULL THEN t1.time_ended_debtor_2 ELSE t1.time_ended_debtor END') )
					        )
					        ->joinInner(
					        	array('t2' => $this->_assignOptions['courseTable']),
					            't1.'.$this->_assignOptions['tableCourseField'].' = t2.'.$this->_assignOptions['courseTablePrimaryKey'],
					        	array('period_restriction_type', 'state')
				        	)
				        	->where('t1.'.$this->_assignOptions['tableCourseField'].' = ?', $courseId);
        	$select->joinLeft(
				        	array('t4' => $subSelect),
				        	't1.MID = t4.'.$this->_assignOptions['tablePersonField'],
				        	array('t4.time_registered', 't4.time_ended_planned', 't4.time_ended_debtor', 'course' => 't4.'.$this->_assignOptions['tableCourseField'], 't4.'.$this->_assignOptions['tableCourseField'])
        	);
        
                if ($switcher == self::FILTER_LISTENERS_COURSE) {
                    $select->where('t4.'.$this->_assignOptions['tableCourseField'].' = ?', $courseId);
                }

        } elseif($switcher == self::FILTER_ALL){
            $subSelectStudents =  $this->getService('User')->getSelect();
			$subSelectStudents->from('Students', array(	'MID', 	'CID'	=> new Zend_Db_Expr('GROUP_CONCAT(CID)') ) 	);
			$subSelectStudents->where('CID > 0');
			$subSelectStudents->group(array('MID'));
			
			
			$select->joinLeft(
				array('t2' => $subSelectStudents),
                't1.MID = t2.MID',
                array()
			);
			$group_additional['t2.CID'] = 't2.CID';

			/*
			$select->joinLeft(
                array('t2' => $this->_assignOptions['table']),
                't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                array()
            );
			*/
        }
 
        if (!$courseId) {
            $select->joinLeft(
                array('pu' => 'programm_users'),
                'pu.user_id = t1.MID',
                array()
            )->joinLeft(
                array('pr'=>'programm'),
                'pu.programm_id = pr.programm_id',
                array()
            );
        }
	
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        //    $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $select = $this->getService('DeanResponsibility')->checkUsers($select, 't1.MID', 'd.soid');
			
			if ($courseId > 0) {
				# выброрка закрепленных тьюторов				
				$select->joinLeft(
					array('tu' => 'Tutors_users'),
					'tu.SID = t1.MID AND tu.CID = t4.CID',
					array()
				);
				$select->joinLeft(
					array('tup' => 'People'),
					'tup.MID = tu.MID',
					array()
				);
			}

        }

        //$group_fields = array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't2.CID');
		$group_fields = array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic');
		$group_fields = array_merge($group_fields, $group_additional);
        if ($courseId > 0) {
			#if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){				
			#	$group_fields = array_merge($group_fields, array('tu.MID'));
			#}
			
            $group_fields = array_merge($group_fields, array('t4.'.$this->_assignOptions['tableCourseField'], 't4.time_registered', 't4.time_ended_planned', 't4.period_restriction_type', 't4.state', 't4.time_ended_debtor'));
        }
        
        $select->group($group_fields);
		
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}
		
        if ($courseId > 0) {
			
			# если тьютор		             
			$subjectService = $this->getService('Subject');	
			$userService = $this->getService('User');		
			if ($this->getService('Acl')->inheritsRole($userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
				$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $courseId);
				if($studentIDs !== false){
					if(is_array($studentIDs)){
						if(count($studentIDs)){
							$select->where($subjectService->quoteInto('t1.MID IN (?)', $studentIDs));
						} else {
							# нет доступных студентов.
							$select->where('1=0');
						}
					}
				}		
			}
		
			$select->where($subjectService->quoteInto('t1.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
			#pr($select->query());
			#die;

			

			
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
                    'period_restriction_type' => array('hidden' => true),
                    'state' => array('hidden' => true),
                    'fio' => array('title' => _('ФИО'), 'decorator' => 
                        $this->view->cardLink(
                                $this->view->url(array(
                                    'module' => 'user',
                                    'controller' => 'list',
                                    'action' => 'view',
                                    'user_id' => ''
                                ), null, true).'{{MID}}',_('Карточка пользователя')).
                                '<a href="'.$this->view->url(array(
                                    'module' => 'user',
                                    'controller' => 'list',
                                    'action' => 'view',
                                    'user_id' => ''), null, true) . '{{MID}}'.'">'.'{{fio}}</a>'),
                    'departments' => array(
                        'title' => _('Подразделение'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{departments}}', $select)
                        )
                    ),
                    'positions' => array(
                        'title' => _('Должность'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{positions}}', $select, true)
                        )
                    ),
                    'groups' => array(
                        'title' => _('Учебные группы'),
                        'callback' => array(
                            'function' => array($this, 'groupsCache'),
                            'params' => array('{{groups}}', $select, true)
                        )
                    ),
                    'time_registered' => array(
                        'title' => _('Дата начала'),
                        'callback' => array(
                            'function' => array($this, 'updateDateBegin'),
                            'params' => array('{{time_registered}}', '{{period_restriction_type}}', '{{state}}')
                        )
                    ),
                    'time_ended_planned' => array(
                        'title' => _('Дата окончания'),
                        'format' => 'date',
                    ),
					'time_ended_debtor' => array(
                        'title' => _('Продлено до'),
                        'format' => 'date',
                    ),					
                    'CID' => array('hidden' => true),
                    'course' => array(
                        'title' => _('Назначен на этот курс?'),
                        'callback' => array(
                            'function' => array($this, 'updateGroupColumn'),
                            'params' => array('{{course}}', $courseId)
                        )
                    ),
					'tutorIDs' => array(
                        'title' => _('Закрепленные тьюторы'),
                        'callback' => array(
                            'function' => array($this, 'updateTutors'),
                            'params' => array('{{tutorIDs}}', $courseId)
                        )
                    ),
                    'tags' => array('hidden' => true),
					'sub_groups' => array(
                        'title' => _('Подгруппы').($subjectLanguageName),
                        'callback' => array(
                            'function' => array($this, 'updateSubGroups'),
                            'params' => array('{{sub_groups}}', $courseId)
                        )
                    ),
                ),
                array(
                    'MID' => null,
                	'time_registered' => array('render' => 'date'),
                    'time_ended_planned' => array('render' => 'date'),
                    'time_ended_debtor' => array('render' => 'date'),
                    'fio' => null,
                    'groups'      => array('callback' => array(
                        'function' => array($this, 'groupsFilter'),
                        'params'   => array('tableName' => 'g')
                    )),
					'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))),
                ),
                $gridId
            );
			
			
			
        } 
        else 
        {
             //echo $select; die();
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
                    'courses' => array(
                        'title' => _('Курсы'),
                        'callback' => array(
                            'function' => array($this, 'coursesCache'),
                            'params' => array('{{courses}}', $select)
                        )
                    ),
                    'programms' => array('hidden' => true),
// на уровне слушателей покаываем только курсы                		
// связь с программами показываем на уровне уч.групп
//                 		array(
//                         'title' => _('Программы'),
//                         'callback' => array(
//                             'function' => array($this, 'programmsCache'),
//                             'params' => array('{{programms}}', $select)
//                         )
//                     ),
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
                                                                      )) . '{{MID}}'.'">'.'{{fio}}</a>',
																	  
                    ),
                    'departments' => array(
                        'title' => _('Подразделение'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{departments}}', $select)
                        )
                    ),
                    'positions' => array(
                        'title' => _('Должность'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{positions}}', $select, 'pluralFormPositionsCount')
                        )
                    ),
                    'groups' => array(
                        'title' => _('Учебные группы'),
                        'callback' => array(
                            'function' => array($this, 'groupsCache'),
                            'params' => array('{{groups}}', $select, true)
                        )
                    ),
                    'tags' => array(
                        'title' => _('Метки'),
                        'callback' => array(
                            'function'=> array($this, 'displayTags'),
                            'params'=> array('{{tags}}', $this->getService('TagRef')->getUserType())
                        )
                    ),
					'sub_groups' => array(
                        'title' => _('Подгруппы').($subjectLanguageName),
                        'callback' => array(
                            'function' => array($this, 'updateSubGroups'),
                            'params' => array('{{sub_groups}}')
                        )
                    ),

                ),
                                
 
                array(
                    'fio'         => null,
                    'groups'      => array('callback' => array(
                        'function' => array($this, 'groupsFilter'),
                        'params'   => array('tableName' => 'g')
                    )),
                    'tags'        => array('callback' => array('function' => array($this, 'filterTags'))),
                    'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))),
                    'positions'   => array('callback' => array('function' => array($this, 'filterPositions'))),
                    'courses'     => array(
                        'callback' => array(
                            'function' => array($this, 'filterSubjects'),
                            'params'   => array('fieldName' => 't2.CID')
                        )
                    ),
                )   
            );      
        }

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_TEACHER))
            //$this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ) {
      		if ($courseId) {
    	        $grid->setGridSwitcher(array(
    	  			array('name' => 'students', 'title' => _('слушателей данного курса'), 'params' => array('all' => self::FILTER_LISTENERS_COURSE, 'isempty' => 1)),
    	  			array('name' => 'all_students', 'title' => _('всех слушателей'), 'params' => array('all' => self::FILTER_LISTENERS, 			'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
    	  			array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => self::FILTER_ALL, 					'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
    	  		));
      		} elseif($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
    	        $grid->setGridSwitcher(array(
    	  			array('name' => 'all_students', 'title' => _('всех слушателей'), 'params' => array('all' => self::FILTER_LISTENERS, 'isempty' => 1), 'order' => 'fio'),
    	  			array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => self::FILTER_ALL, 		'isempty' => 1), 'order' => 'fio'),
    	        ));
      		}
  		}

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

//        $grid->updateColumn('time_registered',
//            array('callback' =>
//                array('function' => array($this, 'updateDate'),
//                      'params'   => array('{{time_registered}}')
//                )
//            )
//        );
//        $grid->updateColumn('time_ended_planned',
//            array('callback' =>
//                array('function' => array($this, 'updateDate'),
//                      'params'   => array('{{time_ended_planned}}')
//                )
//            )
//        );

        $grid->updateColumn('time_ended_planned',
            array('callback' =>
                array('function' => array($this, 'updateTimeEndedPlanned'),
                      'params'   => array('{{time_ended_planned}}', '{{CID}}')
                )
            )
        );
		
		$grid->updateColumn('time_ended_debtor',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_ended_debtor}}')
                )
            )
        );
		

        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");

        // в 4.5 нет индивидуального назначения прорамм, только через учебные группы
        // в будущем планируется назначение программ через профиль должности
        if (0 && !$courseId) {
            $programms = $this->getService('Programm')->fetchAll(null, 'name');
            if (count($programms)) {
                $grid->addMassAction(
                    array(
                        'module' => 'assign',
                        'controller' => 'student',
                        'action' => 'assign-programm',
                    ),
                    _('Hазначить слушателей на программы мероприятий'),
                    _('Вы уверены?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'assign',
                            'controller' => 'student',
                            'action' => 'assign-programm',
                        )
                    ),
                    'programmId[]',
                    $programms->getList('programm_id', 'name')
                );

                $grid->addMassAction(
                    array(
                        'module' => 'assign',
                        'controller' => 'student',
                        'action' => 'unassign-programm',
                    ),
                    _('Удалить слушателей c программ мероприятий'),
                    _('Вы уверены?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'assign',
                            'controller' => 'student',
                            'action' => 'unassign-programm',
                        )
                    ),
                    'programmId[]',
                    $programms->getList('programm_id', 'name')
                );
            }
        }
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)){        
            $grid->addAction(array(
                'module' => 'assign',
                'controller' => 'student',
                'action' => 'login-as'
            ),
                array('MID'),
                _('Войти от имени пользователя'),
                _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
            );
			
			
			$grid->addAction(array(
                'module' 		=> 'subject',
                'controller' 	=> 'by-programm',
                'action' 		=> 'preview'
            ),
                array('MID'),
                _('Назначить на все курсы программы пользователя')				
            );
			
			
			/*
			$subjects = $subjectService->fetchAll(array(
				'base <> ? OR base IS NULL' => HM_Subject_SubjectModel::BASETYPE_SESSION
			), 'name')->getList('subid', 'name');
			*/			
			if($courseId){	
				$grid->addMassAction(
					array(
						'module' 		=> 'assign',
						'controller' 	=> 'tutor',
						'action' 		=> 'assign-tutor-to-students'
					),
					_('Назначить тьюторов за студентами')
				);
				
				$res = $this->getService('Subject')->getAssignedTutors($courseId);
				$list = array();
				if($res){					
					foreach($res as $tutor){
						$list[$tutor->MID] = $tutor->LastName.' '.$tutor->FirstName.' '.$tutor->Patronymic;
					}
				}				
				$grid->addSubMassActionSelect(
					array($this->view->url(
					array(
						'module' 		=> 'assign',
						'controller' 	=> 'tutor',
						'action' 		=> 'assign-tutor-to-students'
					))),
					'tutor_ids[]',
					$list,
					array()
				);
				
				
				
				$grid->addMassAction(
					array(
						'module' 		=> 'assign',
						'controller' 	=> 'tutor',
						'action' 		=> 'un-assign-tutor-to-students'
					),
					_('Открепить тьютора от студентов')
				);
				
				
				$grid->addSubMassActionSelect(
					array($this->view->url(
					array(
						'module' 		=> 'assign',
						'controller' 	=> 'tutor',
						'action' 		=> 'un-assign-tutor-to-students'
					))),
					'tutor_ids[]',
					$list,
					array()
				);
			}
			
			
        }
        
        $this->_grid = $grid;
        parent::indexAction();
    }

    /**
     * Только слушателей курса
     */
    const FILTER_LISTENERS_COURSE = 0;
    /**
     * Все слушатели
     */
    const FILTER_LISTENERS = 1;
    /**
     * Все пользователи
     */
    const FILTER_ALL = 2;
 
    public function doAction()
    {
    	if ($this->_serviceSubject === null) $this->_serviceSubject = $this->getService('Subject');

        $subjectId     = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId        = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassField = 'postMassIds_' . $gridId;
        $postMassIds   = $this->_getParam($postMassField, '');
        $courseIds     = $this->_getParam('courseId', array(0));
        $grdUsers      = $this->_getGraduatedUsers(explode(',', $postMassIds), $courseIds);
        if (count($grdUsers) && !$this->_getParam('agreed', false)) {   // Проверка на повторное назначение

            $usersName = array();
            $users     = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', array_keys($grdUsers)));
            if ( count($users) ) {
                foreach ($users as $user) {
                    $usersName[$user->MID] = $user->getName();
                }
            }

            $agreedForm  = new HM_Form();

            $agreedForm->setMethod(Zend_Form::METHOD_POST)
                       ->setName('agreed');

            $agreedForm->addElement(
                'hidden',
                'agreed',
                array(
                    'required' => false,
                    'value'    => 1,
                    'filters'  => array('Int')
                )
            );

            $agreedForm->addElement(
                'hidden',
                'all_users',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => $postMassIds
                )
            );

            $agreedForm->addElement(
                'hidden',
                'filtered_users',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => implode(',', array_diff(explode(',',$postMassIds), array_keys($grdUsers)))
                )
            );

            $agreedForm -> addElement(
                'hidden',
                $postMassField,
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => ''
                )
            );

            $agreedForm->addElement(
                'submit',
                'filter_submit',
                array(
                    'Label' => _('Продолжить назначение, исключив указанных сотрудников')
                )
            );

            $agreedForm->addElement(
                'submit',
                'all_submit',
                array(
                    'Label' => _('Назначить всех, включая вышеуказанных')
                )
            );

            $agreedForm->addDisplayGroup(
                array(
                    'agreed',
                    'all_users',
                    'filtered_users',
                    $postMassField,
                    'all_submit',
                    'filter_submit'
                ),
                'agreedGroup',
                array('legend' => 'Действия')
            );
            $agreedForm->init();
            $this->view->usersName     = $usersName;
            $this->view->form          = $agreedForm;
            $this->view->postMassField = $postMassField;
            $this->view->userList      = $grdUsers;

        } else {                                                        // Если пользователь согласен с изменениями
            parent::doAction();                                         // или обучающихся ранее пользователей не найдено,
        }                                                               // работа в обычном режиме
    }

    /**
     * Функция возвращает информацию о пользователях, которых пытаются назначить слушателейм,
     * в случае, если они уже проходили обучение на каких-либо из выбранных тренингов
     * @param array $userIds - ИД пользователей для проверки
     * @param array $courseIds - ИД тренингов и сессий для проверки
     * @return array
     */
    private function _getGraduatedUsers($userIds, $courseIds)
    {
        $result    = array();
        $userIds   = (array) $userIds;
        $userIds   = array_map('intval', $userIds);
        $courseIds = (array) $courseIds;
        $courseIds = array_map('intval', $courseIds);

        $subjects  = $this->_serviceSubject->fetchAllDependence('Graduated', $this->quoteInto('subid IN (?)', $courseIds));
		
        if (!count($subjects)) return $result;
		
        $subjectsName = array();
        if (count($subjects)) {
            $subjectsName = $subjects->getList('subid','name');
        }

        $this->_graduatedDataProcess($result, $subjects, $userIds, $subjectsName);        

        return $result;
    }

    private function _graduatedDataProcess(&$result, $subjects, $userIds, $subjectsName)
    {
        foreach ($subjects as $subject) {
            if (count($subject->graduated)) {
                foreach ($subject->graduated as $graduated) {
                    if ( in_array($graduated->MID, $userIds) && !isset($result[$graduated->MID][$subject->subid]) ) {
                        $data = array(
                            'MID'     => $graduated->MID,
                            'endDate' => $graduated->end,
                        );

                        if ($subject->base_id) {
                            $data['training'] = $subjectsName[$subject->base_id];
                            $data['session']  = $subject->name;
                        } else {
                            $data['training'] = $subject->name;
                        }
                        $result[$graduated->MID][$subject->subid] = $data;
                    }
                }
            }
        }
    }

    protected function _preAssign($personId, $courseId)
    {
    	if(isset($this->_cacheSubjectExpire[$courseId])){ // Если имеется результат в кеше
    		return $this->_cacheSubjectExpire[$courseId] ? self::RETCODE_DOACTION_END_ITERATION : self::RETCODE_DOACTION_OK;
    	}

    	$subject = $this->getOne($this->_serviceSubject->find($courseId));

    	if (!$subject) {
    		return self::RETCODE_DOACTION_END_LOOP;
    	}
    	elseif ($subject->isExpired()){
    		$this->_hasErrors = true;
    		$this->_cacheSubjectExpire[$courseId] = true;
    		$this->_expiredSubjectsNames[] = $subject->getName();
    		return self::RETCODE_DOACTION_END_ITERATION;
    	}

    	$this->_cacheSubjectExpire[$courseId] = false;
    	return self::RETCODE_DOACTION_OK;
    }

    protected function _postAssign($personId, $courseId)
    {
        return true; //#10357
    	if(isset($this->_cacheSubjectTitle[$courseId])){
    		if($this->_cacheSubjectTitle[$courseId] === false) return;
    		$courseName = $this->_cacheSubjectTitle[$courseId];
    	}
    	else{
	    	$subject = $this->getOne($this->_serviceSubject->find($courseId));
	    	if(!$subject){
	    		$this->_cacheSubjectTitle[$courseId] = false;
	    		return;
	    	}
	    	$courseName = $subject->getName();
	    	$this->_cacheSubjectTitle[$courseId] = $courseName;
    	}

    	$messenger = $this->getService('Messenger');
    	$messenger->setOptions(HM_Messenger::TEMPLATE_ASSIGN_SUBJECT, array('course' => $courseName));
    	$messenger->send($this->getService('User')->getCurrentUserId(), $personId);
    }

    protected function _finishAssign()
    {
    	if ($this->_hasErrors){
    		$this->_flashMessenger->clearCurrentMessages();
    		$this->_flashMessenger->addMessage(array(
        		'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
    			'message'	=> _('Срок действия следующих курсов истёк: '.implode(', ', $this->_expiredSubjectsNames))
    		));
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
        if (!$date || ($date == "") || (!strtotime($date))){
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

    public function updateDateBegin($date, $periodRestrictionType, $state)
    {
        $date = $this->getDateForGrid($date);

        if (($periodRestrictionType == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) && ($state == HM_Subject_SubjectModel::STATE_PENDING)) {
            $date .= HM_View_Helper_Footnote::marker(1);
            $this->view->footnote(_('Плановая дата. Фактически начало обучения по курсу определяется преподавателем'), 1);
        }
        return $date;
    }

    public function updateTimeEndedPlanned($date, $CID)
    {
        if (!isset($this->_cache['subject-period'])) {
			$this->_cache['subject-period'] = array();
			$select = $this->getService('Subject')->getSelect();
			$select->from('subjects', array('subid', 'period'));
			$items = $select->query()->fetchAll();
			if(!empty($items)){
				foreach($items as $item){
					$this->_cache['subject-period'][$item['subid']] = $item['period'];
				}
			}
			#$this->_cache['subject-period'] = $this->getService('Subject')->fetchAll()->getList('subid', 'period');
        }
        return isset($this->_cache['subject-period'][$CID]) && $this->_cache['subject-period'][$CID] == HM_Subject_SubjectModel::PERIOD_FREE ? _('Нет') : $date;
    }

    public function updateFio($fio, $userId) {
        $fio = trim($fio);
		
		if($this->_currentLang == 'eng') {
			$fio =  $this->translit($fio);		
		} 		
		
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }

    public function assignProgrammAction()
    {
        $programmIds = $this->_getParam('programmId', array());

        $subjectId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'],0);
        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $ids = explode(',', $this->_getParam('postMassIds_'.$gridId, ''));

        if (count($programmIds)) {
            if (count($ids)) {
                foreach($ids as $id) {
                    foreach($programmIds as $programmId) {
                        $this->getService('Programm')->assignToUser($id, $programmId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Слушатели успешно назначены'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один слушатель')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбрана ни одна программа')
            ));
        }

        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId));
    }

    public function unassignProgrammAction()
    {
        $programmIds = $this->_getParam('programmId', array());

        $subjectId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'],0);
        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $ids = explode(',', $this->_getParam('postMassIds_'.$gridId, ''));

        if (count($programmIds)) {
            if (count($ids)) {
                foreach($ids as $id) {
                    foreach($programmIds as $programmId) {
                        $this->getService('ProgrammUser')->unassign($id, $programmId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один слушатель')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбрана ни одна программа')
            ));
        }

        $this->_redirector->gotoSimple('index', null, null, array($this->_assignOptions['courseIdParamName'] => $subjectId));
    }

    // работает при назначении курсов через оргструктуру
    public function doSoidsAction()
    {
        $do = '_' . $this->_getParam('do', 'assign');
        $subjectIds = $this->_getParam('subjectId', array());
        $soids = explode(',', $this->_getParam('postMassIds_grid', ''));
        $soids = $this->getService('Orgstructure')->getDescendansForMultipleSoids($soids);
        $positions = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('soid IN (?)', $soids));

        if (count($subjectIds) && method_exists($this, $do)) {
            
            $usersExists = false;
            
            if (count($positions)) {
                foreach($positions as $position) {
                    if (!$position->mid) {
                        continue;
                    }
                    
                    $usersExists = true;
                    
                    foreach($subjectIds as $subjectId) {
                        $this->$do($position->mid, $subjectId);
                    }
                }

            }
            
            if ($usersExists) {
                $this->_flashMessenger->addMessage(_('Курсы успешно назначены'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один сотрудник')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбран ни один курс')
            ));
        }

        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
    }
	
		/**
	 * переопределена. Оригинал в HM_Controller_Action_Assign
	*/
	public function filterSubjects($data)
    {
        $fieldName = $data['fieldName'];
        $tableName = $data['tableName'];
        
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }
		$select = $data['select'];
		
		$preSelect = $this->getService('Subject')->getSelect();
		$preSelect->from(array('s' => 'subjects'),
			array(
				'MID' => 'st.MID',
			)
		);
		$preSelect->join(array('st' => 'Students'), 'st.CID = s.subid', array() );
		$preSelect->where($this->quoteInto(
            's.name LIKE ?',
            '%'.$data['value'].'%'
        ));
		$preRes = $preSelect->query()->fetchAll();
		if(!$preRes || !count($preRes)) { $select->where('1 = 0'); return; }
		
		$userIDs = array();
		foreach($preRes as $i){
			$userIDs[$i['MID']] = $i['MID'];
		}        
		if(!count($userIDs)) { $select->where('1 = 0'); return; }
        
		$select->where($this->quoteInto('t1.MID IN (?)', $userIDs));		
    }
	
	
	public function updateTutors($data){
		if(empty($data)) { return _('Нет'); }
		$tutorsIDs = explode(',', $data);
		$tutorsIDs = array_filter($tutorsIDs);
		if(!count($tutorsIDs)) { return _('Нет'); }
		
		if(!$this->serviceUser){	$this->serviceUser = $this->getService('User'); }
		
		$list = array();
		foreach($tutorsIDs as $tutor_id){
			if(!isset($this->_cacheFioUsers[$tutor_id])){
				$u = $this->serviceUser->getById($tutor_id);					
				if($u->MID){ $this->_cacheFioUsers[$u->MID] = $u->LastName.' '.$u->FirstName.' '.$u->Patronymic; }
			}			
			
			if(isset($this->_cacheFioUsers[$tutor_id])){
				$list[$tutor_id] = '<p>'.$this->_cacheFioUsers[$tutor_id].'</p>';
			}
		}		
		$count = count($list);
		$result = array();
		if($count > 1){
			$result = array('<p class="total">' . sprintf(_n('тьютор plural', '%s тьютор', $count), $count) . '</p>');
		}
		$result = $result + $list;
		
		$result = implode('',$result);		
		
		return $result;
	}
	
	public function filterDepartments($data){
		$data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }
		
		if('нет' == trim(mb_strtolower($data['value']))){
			$select = $data['select'];			
			$select->joinLeft(array('p_filter' => 'structure_of_organ'),
				'p_filter.mid = t1.MID',
				array()
			);
			$select->joinLeft(array('d_filter' => 'structure_of_organ'),
				'd_filter.soid = p_filter.owner_soid',
				array()
			);
			$select->where('d_filter.soid IS NULL');
		} else {
			parent::filterDepartments($data);	
		}
	}
	
	public function updateSubGroups($user_id){
		if(!$this->_serviceUser){ $this->_serviceUser =  $this->getService('User'); };
		$user = $this->_serviceUser->getById($user_id);
		if(!$user || empty($user->mid_external)){ return false; }
		$select = $this->_serviceUser->getSelect();
		
		
		$select->from(array('sl' => 'Students_language'), array('sl.language_id', 'sl.semester'));        
		$select->where($this->_serviceUser->quoteInto('sl.mid_external = ?', $user->mid_external));		
		
		if($this->_subject){
			$subject_semester 		= $this->_subject->semester;
			$subject_language_code 	= $this->_subject->language_code;
		
			#$select->where($this->_serviceUser->quoteInto('sl.semester = ?', intval($subject_semester)));			
		}
		
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		
		$list = array();
		foreach($res as $i){			
			$style_name 	= '';
			$style_semestr 	= '';
			if($this->_subject){
				if($subject_language_code == $i['language_id'] && $subject_semester == $i['semester'] ){
					$style_name     = 'style="color:green;"';	

					#if($subject_semester != $i['semester']){
					#	$style_semestr  = 'style="color:red;"';				
					#}					
				}
				
			}			
            $list[$i['language_id'].'~'.$i['semester']] = '<span '.$style_name.' >'.$this->getLanguageName($i['language_id']).' (<span '.$style_semestr.' >'.$i['semester'].'</span>)</span>';

		}
		return implode('<br />', $list);
	}

	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	} 	
	
	/**
	 * получить название подгруппы (ин.яза) по его коду
	*/
	public function getLanguageName($language_code){
		if(!$this->_languageList){
			if (!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }
			$this->_languageList = $this->_serviceSubject->getSubGroupList();
		}
		return $this->_languageList[$language_code];		
	}

}