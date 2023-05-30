<?php
class Subject_IndexController extends HM_Controller_Action_Subject
{
    const COURSE_TYPE_LOCAL = 'Учебный курс';

    private $_testsCache = array();
    private $_lessonsCache = array();
	protected $_currentLang = 'rus';

    public function indexAction()
    {

        $subjectId = (int) $this->_getParam('subject_id');
        $courseId = (int) $this->_getParam('course_id', 0);
        $lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id', 0);

        $this->initLessonTabs();	

        $courses = array();
        if ($subjectId && $courseId == 0) {
            $courses = $this->getService('Subject')->getCourses($subjectId, HM_Course_CourseModel::STATUS_ACTIVE);
            $this->view->courses = $courses;
        }elseif($courseId != 0){
            $opened                   = $this->getService('CourseItem')->getOpenedBranch($courseId);
            $course                   = $this->getService('CourseItem')->getTreeContent($courseId, $opened, $subjectId);
            $courseObject             = $this->getOne($this->getService('Course')->find($courseId));
            $this->view->courseObject = $courseObject;
            $userId                   = $this->getService('User')->getCurrentUserId();

            $lesson = $this->getService('LessonAssign')
                           ->getOne(
                                    $this->getService('LessonAssign')
                                         ->fetchAll(array('SHEID = ?'=> $lessonId,
                                                          'MID = ?'  => $this->getService('User')->getCurrentUserId()
                                                    ))
                    );

            // @tocheck
            // обновляем scheduleID: для курса из конструктора - успешное прохождение,
            // для импортируемых - статус "в просессе" и нулевой процент прохождения если он еще не был начат
            if ( $lesson ) {
                if ( $lesson->isfree == HM_Lesson_LessonModel::MODE_FREE ) { //|| $lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                    if ( $courseObject->format == HM_Course_CourseModel::FORMAT_FREE) {
                        $lesson->V_STATUS = 100;
                        $lesson->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE;
                    } elseif( $lesson->V_DONE == HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART ) {
                        $lesson->V_STATUS = 0;
                        $lesson->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS;
                    }

                    $this->getService('LessonAssign')
                        ->update($lesson->getData());
                }
            }


            if(
                !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
                //$this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_STUDENT
            ){
                $tmpSubjectId = 0;
            }else{
                $tmpSubjectId = $subjectId;
            }
            $current = $this->getService('CourseItemCurrent')->getCurrent($userId, $tmpSubjectId, $courseId, $lessonId);
            $this->view->current=$current;
            $isDegeneratedTree=$this->getService('CourseItem')->isDegeneratedTree($courseId);
            if ($courseObject->new_window && $isDegeneratedTree)
                $this->_redirector->gotoSimple('view','item','course',array('subject_id' => $tmpSubjectId,'course_id' => $courseId,'item_id' => $current));
            if ($this->view->current) {
                $this->view->itemCurrent = $this->getService('CourseItem')->getOne(
                    $this->getService('CourseItem')->find($this->view->current)
                );
            }
            $this->view->tree  = $course;
            $this->view->isDegeneratedTree = $isDegeneratedTree;

            // @tocheck
                $this->view->setHeader($this->_subject->getName());
                if ($this->view->courseObject) {
                    $this->view->setSubHeader($this->view->courseObject->getName());
                }

        }
        $this->view->subjectId = $subjectId;
        $this->view->courseContent = true;


    }


    public function cardAction() {

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

		$subjectId = $this->_subject->subid;
		$userRole  = $this->getService('User')->getCurrentUserRole();
		$isEndUser = $this->getService('Acl')->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER);		
		$groups	   = array();
		
		if (!$isEndUser) {
			
			$serviceSubject = $this->getService('Subject');
			$users_groups 	= $serviceSubject->getUsersGroupsById($this->_subject->subid);
			if(!empty($users_groups)){
				if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {					
					$users_groups = $serviceSubject->filterGroupsByAssignStudents($this->_subject->subid, $this->getService('User')->getCurrentUserId(), $users_groups);					
				}
			}
			
			$group_collection = $this->getService('StudyGroup')->getBySubject($subjectId);
			$group_collection = $this->filteredGroups($group_collection, $users_groups);
			
			if(!count($group_collection)){
				$groups[] = _('Нет');
			} else {
				foreach($group_collection as $group){ $groups[] = $group->getName(); }
			}			 
			$this->_subject->groups = implode(', ', $groups);
			
			#$examTypes = HM_Subject_SubjectModel::getExamTypes();
            
			#		
			#$this->_subject->subject_exam_type = $examTypes[$this->_subject->exam_type];
			
			#
			#	$this->_subject->users_groups = implode(', ', $users_groups);	
			#} else {
			#	$this->_subject->users_groups = _('Нет');	
			#}
		}
		$examTypes = HM_Subject_SubjectModel::getExamTypes();
		$this->_subject->subject_exam_type = $examTypes[$this->_subject->exam_type];   
		
		$this->view->subject = $this->_subject;  
        

        if ($isEndUser) {

            /* Логирование захода пользователя в курс */
            $this->getService('Session')->toLog(array('course_id' => $subjectId));

            $graduated = $this->getService('Graduated')->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $this->getService('User')->getCurrentUserId()));
            $this->view->graduated = count($graduated);
        }
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&& $this->_subject->isLanguageLeveling()
		) {	
			$this->view->addContextMenu(_('Распределение языков'), 'languages', array('subject_id' => $subjectId));
		}

    }

    public function happyEndAction()
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            
            $mark = $this->getService('SubjectMark')->setConfirmed($this->_subject->subid, $this->getService('User')->getCurrentUserId());
            if ($mark !== false) {
            
                $this->view->scale = $this->_subject->getScale();
                $this->view->value = $mark->mark;
                
                $this->view->redirectUrl = $this->getService('Subject')->getDefaultUri($this->_subject->subid);
                return true;
            }
        } 
        $this->_redirector->gotoSimple('index', 'list', 'subject');
    }

    public function editAction()
    {
        if ($subid = $this->_getParam('subject_id')) {
            $this->_setParam('subid', $subid);
        }
        $subjectId = (int) $this->_getParam('subid', 0);

        $form = new HM_Form_Subjects();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $subjectId = $form->getValue('subid');
                $subject = $this->getService('Subject')->update(
                    array(
                        'subid' => $subjectId,
                        'name' => $form->getValue('name'),
                        'shortname' => $form->getValue('shortname'),
                        'supplier_id' => $form->getValue('supplier_id'),
                        'description' => $form->getValue('description'),
                        'external_id' => $form->getValue('external_id'),
                        'code' => $form->getValue('code'),
                        'type' => $form->getValue('type'),
                        'reg_type' => $form->getValue('reg_type'),
                        'begin' => $form->getValue('begin'),
                        'end' => $form->getValue('end'),
                        'price' => $form->getValue('price'),
                        'plan_users' => $form->getValue('plan_users'),
                        'period' => $form->getValue('period')
                    )
                );

                $this->getService('Subject')->linkClassifiers($subjectId, $form->getClassifierValues());
                $this->getService('Subject')->linkRooms($subjectId, $form->getValue('rooms'));
                if ($form->getValue('icon') != null) {
                    HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('icon'));
                } else {
                    HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('server_icon'));
                }

                $this->_flashMessenger->addMessage(_('Учебный курс успешно изменён'));
                $this->_redirector->gotoSimple('index', 'index', 'subject', array('subject_id' => $subjectId));
            }
        } else {
            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if ($subject) {
                $form->setDefaults(
                    $subject->getValues()
                );
            }
        }
        $this->view->form = $form;
    }

    public function coursesAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $default = new Zend_Session_Namespace('default');
        if ($subjectId && !isset($default->grid['subject-index-courses'][$gridId])) {
            $default->grid['subject-index-courses'][$gridId]['filters']['subid'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $select = $this->getService('Subject')->getSelect();
        $select->from(
            array('c' => 'Courses'),
            array(
                'c.CID',
                'c.Title',
                'c.chain',
                'chaintemp' =>'c.chain',
                'subid' => 's.subject_id',
                'c.new_window',
                'c.format',
                'tags' => 'c.CID',
                'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(cf.name)"),
            ));
        $select->joinLeft(array('s' => 'subjects_courses'), "c.CID = s.course_id AND subject_id = '".$subjectId."'", array());

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            'cl.item_id = c.CID AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_COURSE,
            array()
        );
        $select->joinLeft(
            array('cf' => 'classifiers'),
            'cl.classifier_id = cf.classifier_id',
            array()
        );        
                
        $select->where('(s.subject_id = ? OR s.subject_id IS NULL)', $subjectId)
        ->where(new Zend_Db_Expr($this->getService('Subject')->quoteInto(array("c.Status = ? ", "OR c.Status = ?"), array(HM_Course_CourseModel::STATUS_STUDYONLY, HM_Course_CourseModel::STATUS_ACTIVE))))
        ->where('c.chain IS NULL OR c.chain = 0 OR c.chain = ?', $subjectId);

        $select->group(array(
            'c.CID',
            'c.Title',
            'c.chain',
            'c.chain',
            's.subject_id',
            'c.new_window',
            'c.format',
            
        ));

        $grid = $this->getGrid($select, array(
                'CID' => array('hidden' => true),
                'new_window' => array('hidden' => true),
                'chaintemp' => array('hidden' => true),
                'Title' => array('title' => _('Название')),
                'chain' => array(
                    'title' => _('Место хранения'),
                    'callback' => array(
                        'function' => array($this, 'updateTypeColumn'),
                        'params' => array('{{chain}}', $subjectId)
                    )
                ),
                'subid' => array(
                    'title' => _('Доступ для слушателей'),
                    'callback' => array(
                        'function' => array($this, 'updateSubjectColumn'),
                        'params' => array(HM_Event_EventModel::TYPE_COURSE, '{{CID}}', '{{subid}}', $subjectId)
                    )
                ),
                'format' => array(
                    'title' => _('Формат'),
                    'callback' => array(
                        'function' => array($this, 'updateFormatColumn'),
                        'params' => array('{{format}}')
                    )
                ),
                'tags' => array('title' => _('Метки')),
                'classifiers' => array(
                    'title' => _('Классификаторы'),
                    'callback' => array(
                        'function' => array($this, 'updateClassifiers'),
                        'params' => array('{{classifiers}}')
                    )
                )
            ),
            array(
                'Title' => null,
                'chain' => array(
                    'values' => array(
                        $subjectId => _(self::COURSE_TYPE_LOCAL),
                        0 => _('База знаний')
                    )
                ),
                'format' => array('values' => HM_Course_CourseModel::getFormats()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
                'classifiers' => null,
            ), 
        $gridId);

        if($this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')){
            $options = array(
                    'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subid' => $subjectId)),
                    'global' => array('name' => 'global', 'title' => _('все, включая учебные модули из Базы знаний'), 'params' => array('subid' => null), 'order' => 'subid', 'order_dir' => 'DESC'),
            );
            
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();
            
            $grid->setGridSwitcher($options);            
        }
        $grid->setClassRowCondition("'{{subid}}' != ''", "selected");


        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'assign', 'subject_id' => $subjectId),
            _('Использовать в курсе и открыть свободный доступ для слушателей'),
            _('Вы уверены?')
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'unassign', 'subject_id' => $subjectId),
            _('Не использовать в курсе и закрыть свободный доступ для слушателей'),
            _('Вы уверены?')
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'course-delete-by', 'subject_id' => $subjectId),
            _('Удалить'),
            _('Вы уверены?')
        );

        $subj = $this->getOne($this->getService('Subject')->find($subjectId));

            $grid->addSubMassActionSelect(
                array(
                $this->view->url(array('action' => 'assign', 'isfree' => HM_Lesson_LessonModel::MODE_PLAN))
                ),
                'lesson',
            array(0 => '', 1 => _('Автоматически сгенерировать занятие'))
            );
        $grid->updateColumn('Title',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{Title}}', '{{CID}}', '{{new_window}}')
                )
            )
        );


        $grid->addAction(
            array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'edit',
                'subject_id' => $subjectId
            ),
            array(
                'CID'
            ),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array(
                'module' => 'subject',
                'controller' => 'index',
                'action' => 'course-delete',
                'subject_id' => $subjectId
            ),
            array(
                'CID'
            ),
            $this->view->icon('delete')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateCoursesActions'),
                  'params'   => array('{{chain}}')
            )
        );

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getCourseType(),$subjectId ,'{{chaintemp}}' )
            )
        ));


        $this->view->subjectId = $subjectId;
        $this->view->isGridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();

        /*
        $form = new HM_Form_Courses();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $courses = $form->getValue('courses');
                $this->getService('Subject')->unlinkCourses($form->getValue('subject_id'));
                if (is_array($courses) && count($courses)) {
                    foreach($courses as $courseId) {
                        $this->getService('Subject')->linkCourse($form->getValue('subject_id'), $courseId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Связи с электронными курсами успешно изменены'));
                $this->_redirector->gotoSimple('index', 'index', 'subject', array('subject_id' => $subjectId));
            }
        } else {
            $form->setDefaults(array('subject_id' => $subjectId));
        }
        $this->view->form = $form;

         */
    }

    public function courseDelete($subjectId, $courseId)
    {
        $course = $this->getOne($this->getService('Course')->find($courseId));
        if ($course) {
            if ($course->chain == $subjectId) {
                if ($this->getService('Teacher')->isUserExists($subjectId, $this->getService('User')->getCurrentUserId())) {
                    $this->getService('Course')->delete($course->CID);

                    $this->getService('Course')->clearLesson(null, $courseId);

                    return true;
                } else {
                    throw new HM_Exception(_('Вы не являетесь преподавателем на данном учебном курсе.'));
                    //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не являетесь преподавателем на данном учебном курсе.')));
                }
            } else {
                throw new HM_Exception(_('Учебный модуль не используется в данном учебном курсе.'));
                //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Учебный модуль не используется в данном учебном курсе.')));
            }
        }
    }

    public function courseDeleteAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseId = (int) $this->_getParam('CID', 0);

        if ($subjectId && $courseId) {
            try {
                $this->courseDelete($subjectId, $courseId);
            } catch (HM_Exception $e) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()));
            }
        }

        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));

    }

    public function courseDeleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $error = false;
            if (count($ids)) {
                foreach($ids as $id) {
                    try {
                        $this->courseDelete($subjectId, $id);
                    } catch (HM_Exception $e) {
                        $error = true;
                    }
                }

                if($error === false){
                    $this->_flashMessenger->addMessage(_('Учебные модули успешно удалены.'));
                }else{
                    $this->_flashMessenger->addMessage(_('Глобальные учебные модули невозможно удалить из учебного курса.'));
                }
            }
        }

        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));
    }

    public function updateCoursesActions($chain, $actions)
    {
        if (false !== strstr($chain, _(self::COURSE_TYPE_LOCAL))) {
            //return str_replace('gridmod//', '', $actions);
            return $actions;
        }
        return '';
    }


    public function incrementTestLimitAction()
    {
        $status = $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',false), $this->_getParam('subject_id',0));
         if ( $status ) {
             $this->_flashMessenger->addMessage(($status == 1)? _('Число попыток для пользователя успешно увеличено'): _('Нельзя превышать количество попыток, установленных в настройках теста'));
         } else {
             $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }
         $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject','controller' => 'index','action' => 'result','subject_id'=>$this->_getParam('subject_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }


    public function decrementTestLimitAction()
    {
    if ( $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',0), $this->_getParam('subject_id',0), 'decrement') ) {
             $this->_flashMessenger->addMessage(_('Число попыток для пользователя успешно уменьшено'));
         } else {
             $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }

         $this->_redirector->gotoUrl($this->view->url(array('module' => 'subject','controller' => 'index','action' => 'result','subject_id'=>$this->_getParam('subject_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }

    /**
     * Изменение количества попыток пользователя пройти тест
     * @param $mid
     * @param $lessonId
     * @param $subjectId
     * @param string $operation
     * @return int 0-ошибка 1-успешно 2-успешно, результат установлен в 0 (поптка сделать меньше 0)
     */
    private function _changeTestLimit($mid, $lessonId, $subjectId, $operation = 'increment')
    {
        $status = 0;
        if (!$mid || !$lessonId || !$subjectId) return $status;
        $testCount = $this->getOne($this->getService('TestAttempt')->fetchAll(
                        $this->getService('TestAttempt')->quoteInto(
                            array('mid = ?', ' AND cid = ?', ' AND lesson_id = ?'),
                            array($mid, $subjectId, $lessonId)
                        )
                    ));
        if ( $testCount ) {
            $status = 1;
            $testCount->qty = ($operation == 'increment')? $testCount->qty - 1 : $testCount->qty + 1;
            if ($testCount->qty < 0) {
                $testCount->qty = 0;
                $status         = 2;
            }
            $this->getService('TestAttempt')->update($testCount->getValues());
            return $status;
        }
        return $status;
    }



    public function resultAction()
    {

        $subjectId = $this->_getParam('subject_id', 0);
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))
        ) {
            $isTeacher = true;
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if($this->_getParam('progressgrid', '') != '' && strpos($this->_getParam('progressgrid', ''), '=') !==0){
            $this->_setParam('progressgrid', '=' . $this->_getParam('progressgrid', ''));
        }


/*
        $lessons = $this->getService('Lesson')->fetchAllJoinInner('Assign', 'Assign.MID = ' . (int) $userId . ' AND self.CID = ' . (int) $subjectId);
        if(count($lessons) == 1){
            $lesson = $this->getOne($lessons);
//            $this->_redirector->gotoUrl($lesson->getResultsUrl()); // не надо этого делать
        }
*/
        $select = $this->getService('Subject')->getSelect();

        $columnOptions = array(
           'SHEID' => array('hidden' => true),
           'MID' => array('hidden' => true),
           'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink(
                $this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',
                _('Карточка пользователя')).'{{fio}}'),
           'Title' => array(
               'title' => _('Название'),
            ),
               'typeID' => array(
                'hidden' => true,
                   'title' => _('Тип'),
                   'callback' => array(
                       'function' => array($this, 'updateType'),
                       'params' => array('{{typeID}}')
                   )
            ),
               'V_DONE' => array(
               'title' => _('Состояние'),
                   'callback' => array(
                       'function' => array($this, 'updateDoneStatus'),
                       'params' => array('{{V_DONE}}')
                   )
            ),
               'progress' => array(
               'title' => _('Результат'),
                   'callback' => array(
                       'function' => array($this, 'updateProgress'),
                       'params' => array('{{progress}}')
                   )
            ),
            'tryLast' => array(
                'title' => _('Дата последней попытки'),
                'format' => array(
                    'DateTime',
                    array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            ),
        );

        $group = array(
                            'schid.MID',
                            'sch.SHEID',
                            'sch.Title',
                            'sch.typeID',
                            'schid.V_STATUS',
                            'schid.V_DONE',
            'schid.launched',
                    );
        if ($isTeacher) {
            $select->joinInner(array('p' => 'People'), 'p.MID = schid.MID', array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")));
            array_push($group,'p.LastName', 'p.FirstName', 'p.Patronymic');
        } else {
            $select->where('schid.MID = ?', $userId);
            unset($columnOptions['fio']);
        }

        $select->from(array('schid' => 'scheduleID'), array('MID'))
            ->joinInner(array('sch' => 'schedule'),
                        'sch.SHEID = schid.SHEID',
                        array(
                                'SHEID',
                                'Title',
                                'typeID',
                                'V_DONE'         => 'schid.V_DONE',
                                'progress'       => 'schid.V_STATUS',
                                'tryLast'           => 'schid.launched',
                        ))
            ->where($this->quoteInto(array('sch.CID = ?'), array($subjectId,0)))
            ->group($group);

        $people = $this->getService('User')->fetchAllJoinInner('Student', 'Student.CID = ' . (int) $subjectId );
        $fios = array();
        foreach($people as $man){
            //Адский хак
            $fios['=' . $man->getName()] = $man->getName();
        }
        asort($fios);
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $subjectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE
        ), 'title');
        $lessonsName = $lessons->getList('title', 'title');

        $statuses = array('0' => _('Не начат'), '2' => _('Пройден'), '1' => _('В процессе'));

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'Title_ASC');
        }


        $filterOptions =  array(
               'fio' => array('values' => $fios),
               'Title' => array('values' => $lessonsName),
               'V_DONE' => array('values' => $statuses),
               'progress' => array(null),
               'tryLast' => array('render' => 'date')
//               'tryLast' => array('render' => 'DateTimeStamp')
        );

        if ( $this->_getParam('lesson_id',0) ) {
            $select->where('sch.SHEID = ?', $this->_getParam('lesson_id',0)); // занятие
            $columnOptions['Title'] = array('hidden' =>true);
            $columnOptions['V_DONE'] = array('hidden' =>true);
        } else {
            $select->where('sch.isfree = ?', HM_Lesson_LessonModel::MODE_FREE);
        }

        $grid = $this->getGrid(
            $select,
            $columnOptions,
            $filterOptions,
           'grid'
       );

        $grid->addAction(
            array('module' => 'subject', 'controller' => 'index', 'action' => 'redirect-result'),
            array('SHEID','MID'),
            _('Подробнее')
        );

//        $grid->updateColumn('tryLast', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
            $grid->updateColumn('Title',
                                 array(
                                       'callback' =>
                                        array(
                                            'function' => array($this, 'getTitleString'),
                                            'params' => array('{{Title}}','{{typeID}}')
                                        )
                                    )
                                );

        $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                        'params'   => array('{{progress}}')
                )
        );

        $this->view->grid = $grid->deploy();
    }


    public function updateActions($progress, $actions)
    {
        if ($progress) return $actions;
        }

    public function getTitleString($title,$typeID)
    {
        return '<span class="' . HM_Lesson_LessonModel::getIconClass($typeID) . '">' . $title . '</span>';
    }

    public function getTryCountString($count)
    {
        return (intval($count) > 0)? (int) $count : '';
    }

    public function getLeftCountString($count, $SHEID, $lessons) {
        $lesson = $lessons->exists('SHEID',$SHEID);
        $count = (int) $count;
        if ($lesson && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
            if (isset($this->_testsCache[$lesson->getModuleId()])) {
                $test = $this->_testsCache[$lesson->getModuleId()];
            } else {
            $test = $this->getService('Test')->getOne($this->getService('Test')->find($lesson->getModuleId()));
                $this->_testsCache[$lesson->getModuleId()] = $test;
            }
            $startLimit = (int) $test->startlimit;

            if ($startLimit == 0) {
                 return _('Без ограничения');
            } else {
                return ( ($startLimit - $count) > 0)? $startLimit - $count : 0;
            }
        }

        return '';
    }

    public function redirectResultAction()
    {
        $lessonId = $this->_getParam('SHEID', 0);
        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
        if($lesson){
            $this->_redirector->gotoUrl($lesson->getResultsUrl(array(
                                                                       'user_id'    => $this->_getParam('MID', 0),
                   'subject_id' => $this->_getParam('subject_id', 0),
                   'userdetail' => 'yes',
            )));
        }
    }

    public function assignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = ($this->id) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        $section = $this->getService('Section')->getDefaultSection($subjectId);
        $currentOrder = $this->getService('Section')->getCurrentOrder($section);

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $assign = $this->getOne(
                    $this->getService('SubjectCourse')->fetchAll(
                        $this->getService('SubjectCourse')->quoteInto(
                            array('subject_id = ?', ' AND course_id = ?'),
                            array($subjectId, $id)
                        )
                    )
                );
                if (!$assign) {
                    $this->getService('Subject')->linkCourse($subjectId, $id);
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                }

                $this->getService('Course')->createLesson($this->_subject->subid, $id, HM_Lesson_LessonModel::MODE_FREE, $section, ++$currentOrder);
                }
            }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));
    }

    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = ($this->id) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {

                    $this->getService('Course')->clearLesson($this->_subject, $id);
                $this->getService('Subject')->unlinkCourse($subjectId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));

    }

    public function updateTypeColumn($gridSubjectId, $subjectId)
    {
        if ($gridSubjectId == $subjectId) {
            $return = _('Учебный курс');
        } else {
            $return = _('База знаний');
        }
        return "<span class='nowrap'>{$return}</span>";
    }

    public function coursesListAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->findDependence('CourseAssign', $subjectId));

        $q = urldecode($this->_getParam('q', ''));

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $where = 'Status = 1';
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where .= ' AND '.$this->getService('Course')->quoteInto('LOWER(Title) LIKE LOWER(?)', $q);
        }

        $collections = $this->getService('Course')->fetchAll($where, 'Title');
        $courses = $collections->getList('CID', 'Title');
        if (is_array($courses) && count($courses)) {
            $count = 0;
            foreach($courses as $courseId => $title) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($subject && $subject->isCourseExists($courseId)) {
                    $courseId .= '+';
                }
                echo sprintf("%s=%s", $courseId, $title);
                $count++;
            }
        }
    }

    public function updateName($title, $courseId, $newWindow)
    {
        if($newWindow == 1) {
            $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('subject_id', 0), $courseId);
            if ($itemId != false){
                return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $title . '</a>';
            }
        }


        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
        //    $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){
            if ($lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->fetchAll(array(
                'CID = ?' => $this->_subject->subid,
                "params LIKE '%module_id=" . $courseId . ";'",
                'isfree != ?' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED
            )))) {
            return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID)). '">'. $title.'</a>';
            } else {
                return $title;
        }
        }

        return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'course', 'action' => 'index', 'course_id' => $courseId)). '">'. $title.'</a>';
    }

    public function updateFormatColumn($format)
    {
        return HM_Course_CourseModel::getFormat($format);
    }

    public function updateType($type)
    {
        $types = HM_Event_EventModel::getTypes();
        return $types[$type];
    }

    public function updateDoneStatus($status)
    {
        if(!$status)     return _('Не начат');  // $status ==0 OR IS NULL
        if($status == 2) return _('Пройден');   // $status == 2

        return _('В процессе');                 // $status == 1
    }

    public function updateProgress($score)
    {
        if(empty($score) || $score < 0) return '';
        return $score;
                 }

    /**
     * Смена режима прохождения курса
     **/
    public function changemodeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $subject_id  = $this->_getParam('subject_id',0);
        $access_mode = (int) $this->_getParam('access_mode',0);

        $subject = $this->getService('Subject')
                        ->getOne( $this->getService('Subject')
                                       ->findDependence(array('Lesson',
                                                              'CourseAssign',
                                                              'ResourceAssign',
                                                              'TestAssign',
                                                              'TaskAssign'),
                                                        $subject_id));
        if ($subject) {
            $subject->access_mode = $access_mode;

            $this->getService('Subject')
                 ->update($subject->getValues());

            // Удаляем плановые занятия и информацию о их прохождении и создаем новые записи
            if ( $subject->access_mode == HM_Subject_SubjectModel::MODE_FREE ) {

                // Удаление: записи в SheduleID удаляются автоматом (onDelete' => self::CASCADE)
                if ( count($subject->lessons) ) {
                    foreach ( $subject->lessons as $lesson ) {
                        $this->getService('Lesson')->delete($lesson->SHEID);
                    }
                }

                // Создание занятий для уч. модулей
                if ( count($subject->courses) ) {
                    foreach ($subject->courses as $course) {
                        $this->getService('Course')->createLesson($subject->subid, $course->course_id);
                    }
                }

                // Создание занятий для ресурсов
                if ( count($subject->resources) ) {
                    foreach ($subject->resources as $resource) {
                        $this->getService('Resource')->createLesson($subject->subid, $resource->resource_id);
                    }
                }

                // Создание занятий для тестов
                if ( count($subject->tests) ) {
                    foreach ($subject->tests as $test) {
                        $this->getService('TestAbstract')->createLesson($subject->subid, $test->test_id);
                    }
                }
            }
        }

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'subject',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'subject_id' => $subject_id),
                                    null,
                                    true));
    }

    public function changeStateAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $subjectId  = $this->_getParam('subject_id',0);
        $state = (int) $this->_getParam('state', 0);

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->findDependence(array('Student'), $subjectId));
        if ($subject && $subject->isStateAllowed($state)) {

            switch ($state) {
                case HM_Subject_SubjectModel::STATE_ACTUAL:

                    $this->getService('Subject')->updateWhere(array(
                        'begin' => date('Y-m-d'),
                        'state' => $state,
                    ), array('subid = ?' => $subjectId));

                    foreach ($subject->students as $student) {
                        $this->getService('Subject')->startSubjectForStudent($subjectId, $student->MID);
                    }

                    break;
                case HM_Subject_SubjectModel::STATE_CLOSED:

                    $this->getService('Subject')->updateWhere(array(
                        'end' => date('Y-m-d H:i:s'),
                        'state' => $state,
                    ), array('subid = ?' => $subjectId));

                    foreach ($subject->students as $student) {
                        $this->getService('Subject')->assignGraduated($subjectId, $student->MID);
                    }

                    break;

                default:
                    // something wrong..
                    return false;
                    break;
            }
        }

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'subject',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'subject_id' => $subjectId),
                                    null,
                                    true));

    }

    public function pinAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        if ($uri = $this->_getParam('uri')) {
            $this->getService('Subject')->setDefaultUri($uri, $subjectId);
        }

        exit('1');
    }

    public function unpinAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        $this->getService('Subject')->setDefaultUri(null, $subjectId);
        exit('1');
    }
    public function statementAction()
    {
        $subjectID = $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $subject   = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectID));

        if (!$subject) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не найден')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        if ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не закрыт')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $score = $this->getService('Lesson')->getUsersScore($subject->subid ,'' ,'',null, true);
        $this->view->persons    = $score[0];
        $this->view->schedules  = $score[1];
        $this->view->scores     = $score[2];
    }
    
    public function setBrsSubjectMarksAction() {
        $subjectService = $this->getService('Subject');
        $markBrsStrategyService = $this->getService('MarkBrsStrategy');
        
        $select = $subjectService->getSelect();
        
        $select->from(array('s' => 'subjects'), array(
            'subject_id'   => 'subid',
            'subject_name' => 'name',
        ));
        $select->join(array('st' => 'students'), 's.subid = st.CID', array('user_id' => 'st.MID'));
        $select->join(array('p' => 'People'), 'st.MID = p.MID', array(
            'user_name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
        ));
        
        $select->joinLeft(array('cm' => 'courses_marks'), 'cm.MID = p.MID AND cm.CID = s.subid', array('mark'));
        
        $select->where('s.mark_type = ?', HM_Mark_StrategyFactory::MARK_BRS);
        
        $usersSubjects = $select->query()->fetchAll();
        
        $result = array();
        foreach ($usersSubjects as $value) {
            if($value['mark'] <= 0){
                $mark = $markBrsStrategyService->onLessonScoreChanged($value['subject_id'], $value['user_id']);
                $result[] = array(
                    'Курс'           => $value['subject_name'],
                    'Пользователь'   => $value['user_name'],
                    'ID кусра'       => $value['subject_id'],
                    'ID пользовтеля' => $value['user_id'],
                    'Старая оценка'  => $mark,
                    'Новая оценка'   => $mark,
                );
            }
        }
        
        $this->view->result = $result;
        
    }
	
	protected function filteredGroups($groups, $user_groups)
	{
		$collection = new HM_Collection();
		$collection->setModelClass($groups->getModelClass());
		
		if(empty($user_groups)){ return $collection; }
		
		foreach($user_groups as $name){
			$group = $groups->exists('name', $name);
			if(!$group){ continue; }
			$collection[count($collection)] = $group;
		}
		return $collection;
	}
    
    
}
