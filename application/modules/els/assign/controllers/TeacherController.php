<?php
class Assign_TeacherController extends HM_Controller_Action_Assign
{
    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id           = 0;
	protected $_currentLang = 'rus';

    protected $_assignOptions = array(
        'role'           => 'Teacher',
        'courseStatuses' => array(2),
        'table'          => 'Teachers',
        'tablePersonField'      => 'MID',
        'tableCourseField'      => 'CID',
        'courseTable' => 'subjects',
        'courseTablePrimaryKey' => 'subid',
        'courseTableTitleField' => 'name',
        'courseIdParamName'     => 'subject_id',
    );

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
        $isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;
		
		// temp hack
        if (!isset($this->_assignOptions['courseIdParamName'])) {
            $this->_assignOptions['courseIdParamName'] = 'course_id';
        }

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId = ($courseId) ? "grid{$courseId}" : 'grid';
		
		# для переключение м.у табами GridSwitcher с пустым возвратом строк
		$isEmpty 	     = (bool)$this->_getParam('isempty'.$gridId, false);
		$isSetEmptyQuery = $isEmpty ? true : $isSetEmptyQuery;
		$this->getRequest()->setParams(array('isempty'.$gridId => 0));

    	$default = new Zend_Session_Namespace('default');
    	if ($courseId && !isset($default->grid['assign-teacher-index'][$gridId])) {
    		$default->grid['assign-teacher-index'][$gridId]['filters']['course'] = $courseId; // по умолчанию показываем только перподов этого курса
    	}

    	$notAll = !$this->_getParam('all', isset($default->grid['assign-teacher-index'][$gridId]['all']) ? $default->grid['assign-teacher-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }
        if ($sorting == 'fio_ASC') {
            $this->_request->setParam("masterOrder{$gridId}", 'notempty DESC');
        }


        $select = $this->getService('User')->getSelect();
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}

        if ($courseId > 0) {

        	$subSelect = $this->getService('User')->getSelect()
        				->from(
				        	array('t1' => $this->_assignOptions['table']),
				        	array($this->_assignOptions['tablePersonField'], $this->_assignOptions['tableCourseField'])
        				)
        				->joinInner(
				        	array('t2' => $this->_assignOptions['courseTable']),
				            't1.'.$this->_assignOptions['tableCourseField'].' = t2.'.$this->_assignOptions['courseTablePrimaryKey'],
				        	array()
        				)
        				->where('t1.'.$this->_assignOptions['tableCourseField'].' = ?', $courseId);

        	$select->from(
				        	array('t1' => 'People'),
				        	array(
				        	   'MID',
                               'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
				        	   'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                               'departments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.owner_soid)'),
                               'positions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.soid)'),
				        	)
				        );

        	if($notAll){
        			$select->joinInner(
			        		array('t2' => $this->_assignOptions['table']),
				            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
			        		array()
        			);
        	}
        	else{
        			$select->joinLeft(
                            array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
                            array()
                	);
        	}

        	$select->joinLeft(
                            array('t3' => $this->_assignOptions['courseTable']),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'],
                            array()
                        )->joinLeft(
                            array('t4' => $subSelect),
                            't1.MID = t4.'.$this->_assignOptions['tablePersonField'],
                            array('courses' => new Zend_Db_Expr('GROUP_CONCAT(t3.subid)'), 'course' => 't4.'.$this->_assignOptions['tableCourseField'])
                        )
                        ->joinLeft(array('d' => 'structure_of_organ'),
                            'd.mid = t1.MID',
                            array()
                        )
                        ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic' , 't4.'.$this->_assignOptions['tableCourseField']));

            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
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
                    'course' => array(
                        'title' => _('Назначен на этот курс?'),
                        'callback' => array(
                            'function' => array($this, 'updateGroupColumn'),
                            'params' => array('{{course}}', $courseId)
                        )
                    ),
                    'time_registered' => array('title' => _('Дата начала обучения')),
                    'time_ended' => array('title' => _('Дата окончания обучения')),
                    'courses' => array(
                        'title' => _('Курсы'),
                        'callback' => array(
                            'function' => array($this, 'coursesCache'),
                            'params' => array('{{courses}}', $select)
                        )
                    ),
                ),
                array(
                    'MID' => null,
                	'time_registered' => array('render' => 'date'),
                    'time_ended' => array('render' => 'date'),
                    'fio' => null,
                ),
                $gridId
            );

        } else {

        	$select->from(
				        	array('t1' => 'People'),
				        	array('MID',
                               'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
				        	   'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                               'departments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.owner_soid)'),
                               'positions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.soid)'),
				        	   'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t5.classifier_id)'),
				        	   'courses' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t3.subid)'),
				        	)
        	);
        	if($notAll){
        		$select->joinInner(
        					array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
        					array()
        		);
        	}
        	else{
        		$select->joinLeft(
        					array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
        					array()
        		);
        	}
        	$select->joinLeft(
        					array('t3' => $this->_assignOptions['courseTable']),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'],
        					array()
        	)
            ->joinLeft(
        					array('t5' => 'classifiers_links'),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t5.item_id AND t5.type = 0', // классификатор уч.курсов
                            array()
        	)
            ->joinLeft(array('d' => 'structure_of_organ'),
                'd.mid = t1.MID',
                array()
            )
        	->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic'));

        	// #3388 - поле 'место работы' удалено из master
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
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
                    'courses' => array(
                        'title' => _('Курсы'),
                        'callback' => array(
                            'function' => array($this, 'coursesCache'),
                            'params' => array('{{courses}}', $select)
                        )
                    ),
                    'classifiers' => array(
                        'title' => _('Классификация'),
                        'callback' => array(
                            'function' => array($this, 'classifiersCache'),
                            'params' => array('{{classifiers}}', $select)
                        )
                    ),
                    'time_registered' => array('title' => _('Дата начала обучения')),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
                ),
                array(
                    'fio'         => null,
                    'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))),
                    'positions'   => array('callback' => array('function' => array($this, 'filterPositions'))),
                    'courses'     => array(
                        'callback' => array(
                            'function' => array($this, 'filterSubjects'),
                            'params'   => array('tableName' => 't3')
                        )
                    ),
                    'classifiers' => array('callback' => array('function' => array($this, 'filterClassifiers'))),
                )
            );
        }
		
		
		//var_dump( $grid->getSelect ());		
//        exit($select->__toString());

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
        
          	if ($courseId) {
                $subj = $this->getOne($this->getService($this->service)->find($courseId));
    
                $grid->setGridSwitcher(array(
                    array('name' => 'teachers', 'title' => _('преподавателей данного курса'), 'params' => array('course' => $courseId, 'all' => 0,	'isempty' => 1)),
                    array('name' => 'all_teachers', 'title' => _('всех преподавателей'), 'params' => array('course' => null, 'all' => 0,	 		'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
                    array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('course' => null, 'all' => 1,	 			'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
                 ));
                
      		} else {
    	        $grid->setGridSwitcher(array(
    	  			array('name' => 'all_teachers', 'title' => _('всех преподавателей'), 'params' => array('all' => 0,	 'isempty' => 1), 'order' => 'fio'),
    	  			array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => 1,		 'isempty' => 1), 'order' => 'fio'),
    	  		));
      		}

            if ($notAll) {
                $grid->addAction(array(
                        'module'     => 'assign',
                        'controller' => 'teacher',
                        'action'     => 'calendar',
                        'switcher'   => 'calendar',
                    ),
                    array('MID'),
                    _('календарь')
                );
            }
  		}

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $grid->updateColumn('time_registered',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_registered}}')
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

		
		
		$grid->addMassAction(array('module' => 'assign',
                                    'controller' => 'teacher',
                                    'action' => 'teacher-course-filter'),
                            _('Удалить преподавателей с курсов. Выбор курсов.'),
                            _('Вы уверены?')
		);
		
		
		$grid->addAction(array(
                        'module'     => 'assign',
                        'controller' => 'teacher',
                        'action'     => 'teacher-course-filter',
                    ),
                    array('MID'),
                    _('Удалить преподавателя с курсов. Выбор курсов.')
                );
		
        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");

		
		
		//$qq = $grid->getSelect();
		//$ee = $qq->query()->fetchAll();
		//var_dump($ee);
		//var_dump($grid->getUrl());
		//var_dump($grid->getAllParams());
		//var_dump($grid->getView());
		//var_dump($grid->getSource()->execute());
		//var_dump($grid->getParam('fio'));
		/*
		$tt = $grid->getAllParams();
		
		$tt = '1-'.$tt['fiogrid'];
		$courses = array(
			'1' => $tt
		);
		
		$grid->addMassAction(array(	'module' => 'assign2',
									'controller' => 'teacher2',
									'action' => 'unassign2',),
									_('Тест'),
									_('Вы уверены?')
		);
								 
		$grid->addSubMassActionSelect(	$this->view->url(array(	'module' => 'assign2',
																'controller' => 'teacher2',
																'action' => 'unassign2',)
										),
										'unCourseId2[]', //--если писать unCourseId[], то расценится, как мультивыбор селекта.
										$courses
										
		);	
		*/
		
		
		
		
		
        $this->_grid = $grid;
        $this->view->subjectId = $this->id;
        $this->view->editable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN) ? true : false;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
        parent::indexAction();
		

    }
    
    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
		
		if($this->_currentLang == 'eng')
			$fio = $this->translit($fio);			
		
        return $fio;
    }

    public function calendarAction()
    {

        $this->view->source   = array('module'=>'subject', 'controller'=>'list', 'action'=>'calendar', 'user_id' => $this->getRequest()->getParam('MID', null));
        $this->view->editable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN) ? true : false;
        $this->view->userId   = $this->getRequest()->getParam('MID', null);
    }
    
    public function filterClassifiers($data)
    {
        $data['value'] = trim($data['value']);
        if (strlen($data['value']) < 3) {
            return;
        }

        $subjectIdField = $this->_assignOptions['courseTablePrimaryKey'];

        $select = $data['select'];

        $select->joinInner(array('cl_f' => 'classifiers_links'),
            'cl_f.item_id = ' . $subjectIdField . ' AND cl_f.type=' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array())
            ->joinInner(array('c_f' => 'classifiers'),
                'cl_f.classifier_id=c_f.classifier_id',
                array())
            ->where($this->quoteInto(
                "c_f.name LIKE ?",
                '%'.$data['value'].'%'
            ));
//        var_dump($select->__toString());
    }
	
	
	protected function _postAssign($personId, $courseId)
    {
        
		$this->_serviceSubject = $this->getService('Subject');
		$subject = $this->getOne($this->_serviceSubject->find($courseId));
		if(!$subject){
			$this->_cacheSubjectTitle[$courseId] = false;
			return;
		}
		
		$templateId = 25; //--Шаблон для тьюторов в БД
		$courseName = $subject->getName();
		$this->_template = $this->getOne($this->getService('Notice')->fetchAll($this->getService('Notice')->quoteInto('type = ?', $templateId)));
		
		if($this->_template->enabled == 1) {
			$messageTitle = str_replace('[COURSE]', $courseName, $this->_template->title);
			$messageText = str_replace('[COURSE]', $courseName, $this->_template->message);	
			
			$this->_person = $this->getOne($this->getService('User')->find($personId));
			
			$toName = $this->_person->LastName.' '.$this->_person->FirstName.' '.$this->_person->Patronymic;
			$toEmail = $this->_person->EMail;
			
			$this->sendEmail($toEmail, $toName, $messageTitle, $messageText);
			
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
	
	public function teacherCourseFilterAction(){
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		$gridId = 'grid';
		$users = trim($this->_getParam('postMassIds_'.$gridId, '')); //--POST запрос
		
		
		if(!$users){			
			$users = trim($this->_getParam('MID', false)); //-GET запрос
		}
		
		if(!$users){			
			$this->_redirector->gotoSimple('index', 'teacher', 'assign');
		}
		
		$users_array = explode(',',$users);
		
		if(count($users_array) < 1){ //--редирект назад
			$this->_redirector->gotoSimple('index', 'teacher', 'assign');
		}
		
		$users_array = array_filter($users_array);
		
		if(count($users_array) < 1){ //--редирект назад
			$this->_redirector->gotoSimple('index', 'teacher', 'assign');		
		}
		
		$users = implode(',', $users_array);
		
		 // temp hack
        if (!isset($this->_assignOptions['courseIdParamName'])) {
            $this->_assignOptions['courseIdParamName'] = 'course_id';
        }

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId = ($courseId) ? "grid{$courseId}" : 'grid';

    	$default = new Zend_Session_Namespace('default');
    	//if ($courseId && !isset($default->grid['assign-teacher-index'][$gridId])) {
    		//$default->grid['assign-teacher-index'][$gridId]['filters']['course'] = $courseId; // по умолчанию показываем только перподов этого курса
    	//}

		$default->grid['assign-teacher-teacher-course-filter'][$gridId]['filters'] = false; //--сбразываем фильтры
		
		
    	$notAll = !$this->_getParam('all', isset($default->grid['assign-teacher-index'][$gridId]['all']) ? $default->grid['assign-teacher-index'][$gridId]['all'] : null);

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }
        if ($sorting == 'fio_ASC') {
            $this->_request->setParam("masterOrder{$gridId}", 'notempty DESC');
        }


        $select = $this->getService('User')->getSelect();


        	$select->from(
				        	array('t1' => 'People'),
				        	array('MID',
                               'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
				        	   'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                               'departments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.owner_soid)'),
                               'positions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.soid)'),
				        	   'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t5.classifier_id)'),
				        	   'courses' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t3.subid)'),
				        	)
        	);
        	if($notAll){
        		$select->joinInner(
        					array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
        					array()
        		);
        	}
        	else{
        		$select->joinLeft(
        					array('t2' => $this->_assignOptions['table']),
                            't1.MID = t2.'.$this->_assignOptions['tablePersonField'],
        					array()
        		);
        	}
        	$select->joinLeft(
        					array('t3' => $this->_assignOptions['courseTable']),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'],
        					array()
        	)
            ->joinLeft(
        					array('t5' => 'classifiers_links'),
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t5.item_id AND t5.type = 0', // классификатор уч.курсов
                            array()
        	)
            ->joinLeft(array('d' => 'structure_of_organ'),
                'd.mid = t1.MID',
                array()
            )
        	->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic'));

			
			$select->where('t1.MID IN ('.$users.')'); //--Отбор по пользователям из POST
			
        	// #3388 - поле 'место работы' удалено из master
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
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
                    'courses' => array(
                        'title' => _('Курсы'),
                        'callback' => array(
                            'function' => array($this, 'coursesCache'),
                            'params' => array('{{courses}}', $select)
                        )
                    ),
                    'classifiers' => array(
                        'title' => _('Классификация'),
                        'callback' => array(
                            'function' => array($this, 'classifiersCache'),
                            'params' => array('{{classifiers}}', $select)
                        )
                    ),
                    'time_registered' => array('title' => _('Дата начала обучения')),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
                ),
                array(
                    'fio'         => null,
                    'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))),
                    'positions'   => array('callback' => array('function' => array($this, 'filterPositions'))),
                    'courses'     => array(
                        'callback' => array(
                            'function' => array($this, 'filterSubjects'),
                            'params'   => array('tableName' => 't3')
                        )
                    ),
                    'classifiers' => array('callback' => array('function' => array($this, 'filterClassifiers'))),
                )
            );
        
//        exit($select->__toString());

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $grid->updateColumn('time_registered',
            array('callback' =>
                array('function' => array($this, 'updateDate'),
                      'params'   => array('{{time_registered}}')
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

		//--выбрать курсы, на которые назначены выбранные тьюторы.
		
		$selectCourses = $this->getService('User')->getSelect();
		$selectCourses->from(
				        	array('t2' => $this->_assignOptions['table']),
				        	array(								
				        	   'sid' 	=> 't3.subid',
							   'sname' 	=> 't3.name',
				        	)
        	);
        	
		$selectCourses->join(
						array('t3' => $this->_assignOptions['courseTable']),
						't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'],
						array()
		);
		
		$selectCourses->where('t2.'.$this->_assignOptions['tablePersonField'].' IN ('.$users.')'); //--Отбор по пользователям из POST
		$selectCourses->order(array('t3.name'));
			
		$arResult = $selectCourses->query()->fetchAll();
		
		//$courses = array('0' => '-- выберите курс --');
		$courses = array();
		
		foreach($arResult as $i){
			$courses[$i['sid']] = $i['sname'];			
		}
		//var_dump($arResult);		
	
								  
									  
		$grid->addMassAction(array(	'module' => 'assign',
									'controller' => 'teacher',
									'action' => 'unassign',),
									_('Удалить с курса'),
									_('Вы уверены?')
		);
								 
		$grid->addSubMassActionSelect(	$this->view->url(array(	'module' => 'assign',
																'controller' => 'teacher',
																'action' => 'unassign',)
										),
										'unCourseId[]', //--если писать unCourseId[], то расценится, как мультивыбор селекта. если без скобок, то обычный селект
										$courses
										
		);							  
								  
		
		
        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");
		
		//$grid->setNoFilters(true); //--отключаем поля фильтрации		
		echo '<style>.filters_tr{ display: none;}</style>'; //-Скрываем поле фильтрации, т.к. $grid->setNoFilters(true); вывывает ошибку в скрипте. Почему?
		

        echo $grid->deploy();
		
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
	
}