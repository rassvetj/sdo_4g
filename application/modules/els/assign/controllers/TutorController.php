<?php
class Assign_TutorController extends HM_Controller_Action_Assign
{
    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id           = 0;
	protected $_currentLang = 'rus';

    protected $_assignOptions = array(
        'role'           => 'Tutor',
        'courseStatuses' => array(2),
        'table'          => 'Tutors',
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
    	if ($courseId && !isset($default->grid['assign-tutor-index'][$gridId])) {
    		$default->grid['assign-tutor-index'][$gridId]['filters']['course'] = $courseId; // по умолчанию показываем только перподов этого курса
    	}

    	$notAll = !$this->_getParam('all', isset($default->grid['assign-tutor-index'][$gridId]['all']) ? $default->grid['assign-tutor-index'][$gridId]['all'] : null);

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
                            array('courses' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t3.subid)'), 'course' => 't4.'.$this->_assignOptions['tableCourseField'])
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
                            't3.'.$this->_assignOptions['courseTablePrimaryKey'].' = t2.'.$this->_assignOptions['tableCourseField'] . ' AND t3.type = ' . HM_Subject_SubjectModel::TYPE_DISTANCE,
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
                	'fio' => null,
					 'departments' => array('callback' => array('function' => array($this, 'filterDepartments'))),
					 'positions'   => array('callback' => array('function' => array($this, 'filterPositions'))),
					 'classifiers' => array('callback' => array('function' => array($this, 'filterClassifiers'))),
					 'courses'     => array(
                        'callback' => array(
                            'function' => array($this, 'filterSubjects'),
                            'params'   => array('tableName' => 't3')
                        )
                    ),
                )
            );
        }
//        exit($select->__toString());

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
        
          	if ($courseId) {
                $subj = $this->getOne($this->getService($this->service)->find($courseId));
    
                $grid->setGridSwitcher(array(
                    array('name' => 'tutors', 'title' => _('тьюторов данного курса'), 'params' => array('course' => $courseId, 'all' => 0,	'isempty' => 1)),
                    array('name' => 'all_tutors', 'title' => _('всех тьюторов'), 'params' => array('course' => null, 'all' => 0,	 		'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
                    array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('course' => null, 'all' => 1,	 	'isempty' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
                 ));
                
      		} else {
    	        $grid->setGridSwitcher(array(
    	  			array('name' => 'all_tutors', 'title' => _('всех тьюторов'), 'params' => array('all' => 0,	 	'isempty' => 1), 'order' => 'fio'),
    	  			array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('all' => 1,	'isempty' => 1), 'order' => 'fio'),
    	  		));
      		}

            /*
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
            */
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
                                    'controller' => 'tutor',
                                    'action' => 'tutor-course-filter'),
                            _('Удалить тьюторов с курсов. Выбор курсов.'),							
                            _('Вы уверены?')
		);
		
		
		
		$grid->addAction(array(
                        'module'     => 'assign',
                        'controller' => 'tutor',
                        'action'     => 'tutor-course-filter',						
						
                    ),
                    array('MID'),
                    _('Удалить тьютора с курсов. Выбор курсов.')
                );
		

        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");

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

    /*
    protected function _postAssign($personId, $courseId)
    {
        $collection = $this->getService('Student')->fetchAll(
            $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($personId, $courseId))
        );

        if (!count($collection)) {
            $this->getService('Student')->insert( // #11928
                array(
                    'MID' => $personId,
                    'CID' => $courseId,
                    'Registered' => time(),
                    'time_registered' => $this->getService('Student')->getDateTime(),
	                'time_ended_planned' => $this->getService('Student')->getDateTime(strtotime('+5 year'))
                )
            );
        }
    }
    */
	
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
	
	
	
	public function tutorCourseFilterAction(){
		
		$this->_helper->viewRenderer->setNoRender(true);
		//var_dump(111);
		$gridId = 'grid';
		//$users = trim($this->_getParam('postMassIds_'.$gridId, ''));
		$users = trim($this->_getParam('postMassIds_'.$gridId, false)); //--POST запрос
		
		//var_dump($users);
		if(!$users){			
			$users = trim($this->_getParam('MID', false)); //-GET запрос
		}
		
		if(!$users){			
			$this->_redirector->gotoSimple('index', 'tutor', 'assign');
		}
		
		$users_array = explode(',',$users);
		
		
		if(count($users_array) < 1){ //--редирект назад
			$this->_redirector->gotoSimple('index', 'tutor', 'assign');
		}
		
		$users_array = array_filter($users_array);
		
		if(count($users_array) < 1){ //--редирект назад
			$this->_redirector->gotoSimple('index', 'tutor', 'assign');		
		}
		
		$users = implode(',', $users_array);
		
		 // temp hack
        if (!isset($this->_assignOptions['courseIdParamName'])) {
            $this->_assignOptions['courseIdParamName'] = 'course_id';
        }

        $courseId = (int) $this->_getParam($this->_assignOptions['courseIdParamName'], 0);
        $gridId = ($courseId) ? "grid{$courseId}" : 'grid';

    	$default = new Zend_Session_Namespace('default');
		
    	//if ($courseId && !isset($default->grid['assign-tutor-index'][$gridId])) {
    		//$default->grid['assign-tutor-index'][$gridId]['filters']['course'] = $courseId; // по умолчанию показываем только перподов этого курса
    	//}
		//if ($courseId && !isset($default->grid['assign-tutor-tutor-course-filter'][$gridId])) {
    		//$default->grid['assign-tutor-tutor-course-filter'][$gridId]['filters']['course'] = $courseId; // по умолчанию показываем только перподов этого курса
    	//}
		$default->grid['assign-tutor-tutor-course-filter'][$gridId]['filters'] = false; //--сбразываем фильтры
		
		

    	//$notAll = !$this->_getParam('all', isset($default->grid['assign-tutor-index'][$gridId]['all']) ? $default->grid['assign-tutor-index'][$gridId]['all'] : null);
    	$notAll = !$this->_getParam('all', isset($default->grid['assign-tutor-tutor-course-filter'][$gridId]['all']) ? $default->grid['assign-tutor-index'][$gridId]['all'] : null);

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
									'controller' => 'tutor',
									'action' => 'unassign',),
									_('Удалить с курса'),
									_('Вы уверены?')
		);
								 
		$grid->addSubMassActionSelect(	$this->view->url(array(	'module' => 'assign',
																'controller' => 'tutor',
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
	
	
	public function assignTutorToStudentsAction(){		

		$this->getHelper('viewRenderer')->setNoRender();
		
		$subject_id = $this->_getParam('subject_id', false);
		$tutors 	= $this->_getParam('tutor_ids', array());		
		$students 	= $this->_getParam('postMassIds_grid'.$subject_id, false);
		
		
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		
		
		if(!$subject_id){ 		
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не задан курс'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		if(!$students){						
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбраны студенты'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		$tutors = array_filter($tutors);
		
		if(empty($tutors)){						
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран тьютор'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		$studentsIDs = explode(',', $students);
		$studentsIDs = array_filter($studentsIDs);
				
		if(empty($studentsIDs)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран студент'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));						
		}
		
		$serviceSubjectUser = $this->getService('SubjectUser');		
		foreach($tutors as $tutor_id){
			foreach($studentsIDs as $student_id){
				$serviceSubjectUser->assignTutorToUsers($tutor_id, $subject_id, $student_id);			
			}
		}		
		
		$this->_flashMessenger->addMessage(_('Назначение успешно выполнено'));		
		$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));						
		die;				
	}
	
	/**
	 * удаление назначения тьюторов за студентами
	*/
	public function unAssignTutorToStudentsAction(){		

		$this->getHelper('viewRenderer')->setNoRender();
		
		$subject_id = $this->_getParam('subject_id', false);
		$tutors 	= $this->_getParam('tutor_ids', array());		
		$students 	= $this->_getParam('postMassIds_grid'.$subject_id, false);
		
		
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		
		
		if(!$subject_id){ 		
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не задан курс'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		if(!$students){						
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбраны студенты'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		$tutors = array_filter($tutors);
		
		if(empty($tutors)){						
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран тьютор'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));			
		}
		
		$studentsIDs = explode(',', $students);
		$studentsIDs = array_filter($studentsIDs);
				
		if(empty($studentsIDs)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран студент'))
			);		
			$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));						
		}
		
		$serviceSubjectUser = $this->getService('SubjectUser');		
		foreach($tutors as $tutor_id){
			foreach($studentsIDs as $student_id){
				$serviceSubjectUser->unAssignTutorToUsers($tutor_id, $subject_id, $student_id);			
			}
		}		
		
		$this->_flashMessenger->addMessage(_('Назначение удалено'));		
		$this->_redirector->gotoSimple('index', 'student', 'assign', array('base' => $this->getRequest()->getParam('base', 0), 'subject_id' => $subject_id));						
		die;				
	}
	
	
	
	public function _lessonAssignPrepare(){
		$this->subject_id 		= (int) $this->_getParam('subject_id', false);
		$lessons 				= $this->_getParam('postMassIds_grid', false);		
		$tutors 				= $this->_getParam('tutor_ids', array());				
		$this->tutors			= array_filter($tutors);
		$this->serviceAT  		= $this->getService('LessonAssignTutor');		
		$this->serviceUser		= $this->getService('User');		
		$this->serviceLesson	= $this->getService('Lesson');		
		
		if(empty($this->tutors)){						
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран тьютор.'))
			);		
			$this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));			
		}
		
		$lessonIDs 		 = explode(',', $lessons);
		$this->lessonIDs = array_filter($lessonIDs);
		
		if(empty($this->lessonIDs)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбрано занятие.'))
			);		
			$this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		}		
	}
	
	
	/**
	 * назначение тьютора на занятие. Из плана занятий.
	*/
	public function onLessonAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$this->_lessonAssignPrepare();
		$serviceTutorAssign = $this->getService('LessonAssignTutor');
		
		foreach($this->tutors as $tutor_id){
			foreach($this->lessonIDs as $lesson_id){
				$lesson 	 = $this->serviceLesson->getLesson($lesson_id);
				$role_lesson = $serviceTutorAssign->getLessonRoleType($lesson); # получить роль, для которой должен выводиться это занятие.
				$isAssign = $this->serviceAT->assignTutor($tutor_id, $lesson_id, $this->subject_id, $role_lesson);
				if(!$isAssign){
					$user   = $this->serviceUser->getById($tutor_id);
					
					
					$this->_helper->getHelper('FlashMessenger')->addMessage(
						array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Не удалось назначить "'.$user->LastName.' '.$user->FirstName.' '.$user->Patronymic.'" на занятие "'.$lesson->title.'"'))
					);
				}
			}
		}

		$this->_flashMessenger->addMessage(_('Тьюторы назначены'));	
		$this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		die;		
	}
	
	
	/**
	 * Удаление назначения тьютора с занятия. Из плана занятий.
	*/
	public function unassignLessonAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$this->_lessonAssignPrepare();
		
		foreach($this->tutors as $tutor_id){
			foreach($this->lessonIDs as $lesson_id){
				$isAssign = $this->serviceAT->unAssignTutor($tutor_id, $lesson_id);
				if(!$isAssign){
					$user   = $this->serviceUser->getById($tutor_id);
					$lesson = $this->serviceLesson->getLesson($lesson_id);
					
					$this->_helper->getHelper('FlashMessenger')->addMessage(
						array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Не удалось отменить назначение или тьютор "'.$user->LastName.' '.$user->FirstName.' '.$user->Patronymic.'" не назначен на занятие "'.$lesson->title.'"'))
					);
				}
			}
		}

		$this->_flashMessenger->addMessage(_('Назначения удалены'));	
		$this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $this->subject_id));
		die;		
	}
	
	
	public function unassignAction(){
		$subject_id = $this->_getParam('subject_id', false);
		$tutors 	= $this->_getParam('postMassIds_grid'.$subject_id, false);
		
		$tutorsIDs = explode(',', $tutors);
		$tutorsIDs = array_filter($tutorsIDs);
				
		if(empty($tutorsIDs)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не выбран тьютор'))
			);		
			$this->_redirector->gotoSimple('index', 'tutor', 'assign', array('subject_id' => $subject_id));						
		}
		$serviceSubjectUser 		= $this->getService('SubjectUser');
		$serviceSubjectGroup 		= $this->getService('SubjectGroup');
		$serviceLessonAssignTutor 	= $this->getService('LessonAssignTutor');
		
		foreach($tutorsIDs as $tutor_id){			
			$serviceSubjectUser->unAssignTutorToUsersAll($tutor_id, $subject_id);
			$serviceSubjectGroup->unassignTutorToGroupAll($tutor_id, $subject_id);
			$serviceLessonAssignTutor->unAssignFromSubject($tutor_id, $subject_id);			
		}
		parent::unassignAction();		
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