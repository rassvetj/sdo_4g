<?php
class Lesson_ResultController extends HM_Controller_Action_Subject
{
    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    private $_studentId = 0;
    //private $_subject_id; nobody is need it
    private $_lesson = null;

    private $_maxScoreCache = null;

    public function init()
    {
        
		$lesson_id = $this->_getParam('lesson_id', 0);
		$subject_id = $this->_getParam('subject_id', 0);
		
		# TODO Унифицировать. Эта же проверка реализована в Lesson_ExecuteController. Однако, данное условие выполняется при переходе в занятие из ведомости успеваемости.
		if(
			$lesson_id
			&&
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&&
			!$this->getService('Lesson')->isAvailable($this->getService('User')->getCurrentUserId(), $lesson_id, $subject_id)
		){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('У Вас нет доступа к этому занятию.')));			
			$this->_helper->redirector->gotoSimple('card', 'index', 'subject', array('subject_id' 	=> $subject_id));			
		} 
		
		parent::init();
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $this->_studentId = $this->getService('User')->getCurrentUserId();
        } else {
            $this->_studentId = $this->_getParam('user_id', 0);
        }
        $this->getService('Unmanaged')->setHeader(_('Результаты занятия'));

        $reports = $this->getService('ScormReport')->fetchAll(array(
            'mid = ?' => $this->_studentId,
            'lesson_id = ?' => $lesson_id,
        ));
        $this->view->disabledMods = empty($reports[0]->report_data) ? array('skillsoft') : array(); // не показываем этот переключатель если отчета нет
    }

    public function indexAction()
    {        
		$this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));
        if ($this->_lesson) {
            $this->getService('Unmanaged')->setSubHeader($this->_lesson->title);
        }

        if ($this->_lesson) {
            // не показываем переключатель
            $disabledMods = $this->view->disabledMods;
            $this->view->disabledMods = array('index','skillsoft','listlecture');
            switch($this->_lesson->getType()) {
                case HM_Event_EventModel::TYPE_RESOURCE:
                    $params = $this->_lesson->getParams();
                	$url = $this->view->url(array('module' => 'resource','controller' => 'index','action' => 'index','resource_id' => array('resource_id' => $params['module_id'])));
                	$this->_redirector->gotoUrl($url);
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                    // это было для свободного режима
                    // сейчас все результаты тестов - через test()
                    /*if (0 && !$this->_getParam('user_id', 0)) {
                        $this->_redirect($this->view->url(array('action' => 'result', 'controller' => 'index', 'module' => 'subject', 'subject_id' => $this->_getParam('subject_id', 0), 'lesson_id' => $this->_getParam('lesson_id', 0))));
                    } else*/
                    {
                        $this->test();
                    }
                    break;
                 case HM_Event_EventModel::TYPE_COURSE:
                 case HM_Event_EventModel::TYPE_LECTURE:
                    if(!$this->_getParam('userdetail',false))
                    {
                        $this->courseMain();
                    }
                    else
                    {
                        // показываем переключатель
                        $this->view->disabledMods = $disabledMods;
                        $result = $this->course();
                        // if (empty($result))
                        //выводим результаты прохождения учебного модуля по умолчанию...
                        //    $this->defaultResult();
                    }
                    break;
                case HM_Event_EventModel::TYPE_POLL;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER;
                    $this->poll();
                    break;
                case HM_Event_EventModel::TYPE_TASK;
                    $this->task();
                    break;
                case HM_Activity_ActivityModel::ACTIVITY_FORUM:
                    $url = $this->_lesson->getResultsUrl();
                	$this->_redirector->gotoUrl($url);
                    break;
                default:
                    $feedback = HM_Event_EventModel::getExcludedTypes();
                    if(isset($feedback[$this->_lesson->typeID])) $this->pollLeader();
                    else $this->lecture();
            }
        }		
        //$this->_flashMessenger->addMessage(array('message' => _('Для данного типа занятия подробная статистика отсутствует.'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        //$this->_redirector->gotoUrl($url);
    }

    public function extendedAction() {
        try {
 

		$this->view->headLink()->appendStylesheet('\css\rgsu_style.css');
		$subjectId = intval($this->_getParam('subject_id', 0));
        $lessonId = intval($this->_getParam('lesson_id', 0));
		$showGraduated = (int)$this->_getParam('graduated', 0);

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        
		$lessonEvaluatorsService = $this->getService('LessonEvaluators');

        $currentUserRole = $userService->getCurrentUserRole();
        $currentUserId = $userService->getCurrentUserId();
        $currentUser_InheritsFrom_Tutor = $aclService->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_TUTOR);
        $currentUser_InheritsFrom_Student = $aclService->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_STUDENT);
        $allowTutors = $this->getService('Lesson')->find($lessonId)->current()->allowTutors;
        
		
		
		
		
		
        //дополнительная проверка для студентов и тьюторов, только оценщики имеют сюда доступ
        $currentUserIsEvaluator = false;
        if ($currentUser_InheritsFrom_Student) {
            $evaluatorsCollection = $lessonEvaluatorsService->isEvaluator($currentUserId, $lessonId);
            if ($evaluatorsCollection) {
                $currentUserIsEvaluator = true;
            } else {
                #throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не хватает прав доступа.'))
				);
				$this->_redirect('/');
            }
        } else if($currentUser_InheritsFrom_Tutor && !$allowTutors) {
            #throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не хватает прав доступа.'))
			);
			$this->_redirect('/');
        }
		

        $cols = array(
            'MID' => 'p.MID',
            'SID' => 'st.SID',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'group_id' => 'sg.group_id',
            'group_name' => 'sg.name',
            'interview_title' => 'i.title',
            'i_last_user_id' => 'i2.user_id',
            'mark' => 'sch.V_STATUS',
            'is_new' => new Zend_Db_Expr('CASE WHEN p.MID = i2.user_id THEN 1 ELSE 0 END'),
        );
        
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), $cols);
        $select->joinLeft(array('sch' => 'scheduleID'), 'sch.MID = p.MID');
        $select->joinLeft(array('sgu' => 'study_groups_users'), 'sgu.user_id = p.MID');
        $select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgu.group_id');
        //присоединяем данные первого сообщения
        $select->joinLeft(array('i' => 'interview'), 'i.to_whom = p.MID AND i.user_id = 0 AND i.lesson_id = '.$lessonId);

        //выбираем самое последнее сообщение, чтобы знать, кто ответил последним
        $subSelect = $this->getService('User')->getSelect();
        $subSelect->from('interview', array(
                'interview_hash' => 'interview.interview_hash',
                'last_interview_id' => 'MAX(interview.interview_id)'
            ))
            ->where('interview.lesson_id = ?', $lessonId)
            ->group(array('interview.interview_hash'));
        $select->joinInner(array('i_last' => $subSelect), 'i_last.interview_hash = i.interview_hash');
        $select->joinLeft(array('i2' => 'interview'), 'i2.interview_hash = i_last.interview_hash AND i2.interview_id = i_last.last_interview_id');
		
		if($showGraduated){
			
			$subUsers = $this->getService('User')->getSelect();
			$subUsers->from(array('s' => 'Students'),	array('s.SID', 's.MID', 's.CID'));
			$subUsers->join(array('p' => 'People'), 'p.MID = s.MID AND p.blocked = ' . HM_User_UserModel::STATUS_BLOCKED, array());
			$subUsers->where('s.CID = ?', intval($subjectId));
			
			$subGrad = $this->getService('User')->getSelect();
			$subGrad->from(array('graduated'),	array('SID', 'MID','CID'));
			$subGrad->where('CID = ?', intval($subjectId));
			
			$subUSelect = $this->getService('User')->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			$select->joinInner(array('st' => $subUSelect), 'st.MID = p.MID AND st.CID = '.$subjectId);	
		} else {
			$select->joinInner(array('st' => 'Students'), 'st.MID = p.MID AND st.CID = '.$subjectId);
		}
		

        $select->where('sch.SHEID = ?', $lessonId);
		
		
		
		# если тьютор		
		if ($currentUser_InheritsFrom_Tutor) {
			$studentIDs = $this->getService('Subject')->getAvailableStudents($currentUserId, $subjectId);
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($userService->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}

        //показываем только оцениваемых
        if ($currentUserIsEvaluator) {
            $select->where('p.MID IN (?)', $evaluatorsCollection->getList('MID_evaluated'));
        }
		
		if(!$showGraduated){		
			$select->where($userService->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		}
        $select->order('group_name', 'fio');

        //формируем данные для шаблона
        $stmt = $select->query();
		//pr($stmt);
		
		} catch (Exception $e) {
    //echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
}
        $result = $stmt->fetchAll();
        $groups = array('show_all' => array(
										'name' => _('Показать всех'),
										'new_count' => 0
						),
						'show_new' => array(
										'name' => _('Показать новые'),
										'new_count' => 0
						),
									
		); //список групп $key = id, $val['name'] = название, $val['new_count'] = количество новых сообщений

        $midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
        $users 			= array();
        $showNewCount 	= array();
		foreach ($result as $key => $value) {
            if(is_array($midWithHalfAccess) && in_array($value['MID'], $midWithHalfAccess)){
                continue;
            }
            $mid = $value['MID'];
            $sid = $value['SID'];

            $result[$key]['group_id'] = intval($result[$key]['group_id']);
            $group_id = 0;
            //прячем группы от студентов
            if (!$currentUser_InheritsFrom_Student) {
                $group_id = $result[$key]['group_id'];
                if (empty($groups[$group_id])) {
                    $groups[$group_id] = array(
                        'name' => $value['group_name'],
                        'new_count' => 0
                    );
                    if ($group_id == 0) {
                        $groups[$group_id]['name'] = _('Без группы');
                    }
                }
                if ($value['is_new']) {
                    $groups[$group_id]['new_count']++;
                    #$groups['show_all']['new_count']++;
                    $showNewCount[$mid] = $mid;					
                }
            }			
            //ссылка на переписку с пользователем
            $url = array(
                'module' => 'interview',
                'controller' => 'index',
                'action' => 'index',
                'subject_id' => $subjectId,
                'user_id' => $mid,
            );

            //карточка пользователя
            if ($currentUser_InheritsFrom_Student) {
                //прячем имена от студентов
                $result[$key]['card'] = '';
                $result[$key]['fio'] = 'Слушатель '.($key+1);
                //прячем id пользователя, и передаём id студента,
                //что бы оценщик-студент не знал, кого он оценивает
                unset($url['user_id']);
                $url['student_id'] = $sid;
            } else {
                $result[$key]['card'] = $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'user_id' => $mid
                        )
                    ),
                    _('Карточка пользователя')
                );
            }

            $result[$key]['url'] = $this->view->url($url);

            if (empty($users[$sid])) {
                $users[$sid] = $result[$key];
                $users[$sid]['groups'] = array();
            }
            $users[$sid]['groups'][] = $group_id;
        }
		$groups['show_all']['new_count'] = count($showNewCount);
		$groups['show_new']['new_count'] = count($showNewCount);

		#$users = array_values($users);
		$tt = array();
		foreach($users as $key => $u){
			$tt[$u['fio'].'~'.$key] = $u;		
		}
		$users = $tt;		
		ksort($users);	
		
		
		$urlTypeStudentsParams = array(
			'module'     => $this->_request->getModuleName(), 
			'controller' => $this->_request->getControllerName(), 
			'action'     => $this->_request->getActionName(), 
			'subject_id' => $subjectId, 
			'lesson_id'  => $lessonId,			
		);
		
		if(empty($showGraduated)){
			$urlTypeStudentsParams['graduated'] = 1;
		}
		
		
		
        $this->view->users = $users;
        $this->view->groups = $groups;
		
		$this->view->isShowAttemptButton = $currentUser_InheritsFrom_Tutor ? true : false;
		$this->view->lesson_id 			 = $lessonId;
		$this->view->subject_id			 = $subjectId;
		$this->view->showGraduated       = $showGraduated;
		$this->view->urlTypeStudents     = $this->view->url($urlTypeStudentsParams, 'default', true);
		$this->view->readOlny            = $showGraduated ? true : false;
		
    }

    //откат до выяснения 22.03.2013
    /**
     * метод выводит результаты прохождения учебного модуля
     * @author Glazyrin_Andrey <glazyrin.andre@mail.ru>
     * @date 14.01.2013
     */
    /*public function defaultResult()
    {
        $subjectId = $this->_getParam('subject_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();
        if($this->_getParam('progressgrid', '') != '' && strpos($this->_getParam('progressgrid', ''), '=') !==0){
            $this->_setParam('progressgrid', '=' . $this->_getParam('progressgrid', ''));
        }
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
            'updated' => array(
                'title' => _('Дата последнего просмотра')
            ),
        );
        //для mssql т.к. при group по одному параметру не работает сколько полей столько и группируем
        //параметров
        $group = array(
                        'schid.MID',
                        'sch.SHEID',
                        'sch.Title',
                        'sch.typeID',
                        'schid.V_STATUS',
                        'schid.V_DONE',
                        'schid.launched',
                        'schid.updated'
                      );
        $select->where('schid.MID = ?', $userId);
        unset($columnOptions['fio']);
        $select->from(array('schid' => 'scheduleID'), array('MID'))
            ->joinInner(array('sch' => 'schedule'),
                              'sch.SHEID = schid.SHEID',
                        array(
                                'SHEID',
                                'Title',
                                'typeID',
                                'V_DONE'     => 'schid.V_DONE',
                                'progress'   => 'schid.V_STATUS',
                                'updated'=>'schid.updated'
                        ))
            ->where($this->quoteInto(array('sch.CID = ?'), array($subjectId,0)))
            ->group($group);
        $people = $this->getService('User')->fetchAllJoinInner('Student', 'Student.CID = ' . (int) $subjectId );
        $statuses = array('0' => _('Не начат'), '2' => _('Пройден'), '1' => 'В процессе');
        $filterOptions =  array(
               'V_DONE' => array('values' => $statuses),
        );
        if ( $this->_getParam('lesson_id',0) ) {
            $select->where('sch.SHEID = ?', $this->_getParam('lesson_id',0)); // занятие
        } else {
            $select->where('sch.isfree = ?', HM_Lesson_LessonModel::MODE_FREE);
        }
        $grid = $this->getGrid(
            $select,
            $columnOptions,
            $filterOptions,
           'grid'
       );
       $grid->updateColumn('Title',
                                 array(
                                       'callback' =>
                                        array(
                                            'function' => array($this, 'getTitleString'),
                                            'params' => array('{{Title}}','{{typeID}}')
                                        )
                                    )
                                );
        $grid->updateColumn('updated', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $this->view->grid = $grid->deploy();
    }
    public function updateDoneStatus($status)
    {
        if(!$status)     return _('Не начат');  // $status ==0 OR IS NULL
        if($status == 2) return _('Пройден');   // $status == 2

        return _('В процессе');                 // $status == 1
    }
    public function updateProgress($score)
    {
        if(empty($score) || $score < 0)
            return "Не пройден";
        return "Успешно пройден";
    }
    public function getTitleString($title,$typeID)
    {
        return '<span class="' . HM_Lesson_LessonModel::getIconClass($typeID) . '">' . $title . '</span>';
    }
    */
    public function lecture()
    {
        $switcher = $this->_getParam('switcher', 0);
        if($switcher && $switcher != 'index'){
        	$this->getHelper('viewRenderer')->setNoRender();
        	$action = $switcher.'Action';
			$this->$action();
			$this->view->render('result/'.$switcher.'.tpl');
			return true;
        }

        $select = $this->getService('Lesson')->getSelect();
        $select->from(
                    array('l' => 'scorm_tracklog'),
                    array(
                        'l.trackID',
                        'MID' => 'l.mid',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        'item' => 'o.title',
                        'l.score',
                        'l.scoremax',
                        'l.scoremin',
                        'start1'=>'l.start', //[che 5.06.2014 #16976] 
                        'l.stop',
                        'l.status'
                    )
                )
                ->joinLeft(
                    array('p' => 'People'),
                    'p.MID = l.mid',
                    array()
                )->joinLeft(array('o' => 'organizations'), 'o.oid = l.ModID', array())
                ->where('l.lesson_id = ?', $this->_lesson->getLessonId());

        if ($this->_studentId) {
            $select->where('l.mid = ?', $this->_studentId);
        }

        $grid = $this->getGrid($select,
            array(
                'trackID' => array('hidden' => true),
                'MID' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{MID}}') . '{{fio}}'),
                'item' => array('title' => _('Материал')),
                'score' => array('title' => _('Балл')),
                'scoremax' => array('title' => _('Мин/Mакс'), 'decorator' => '{{scoremin}}<br/>{{scoremax}}'),
                'scoremin' => array('hidden' => true),
                'start1' => array('title' => _('Начало')),//[che 5.06.2014 #16976] 
                'stop' => array('title' => _('Конец')),
                'status' => array('title' => _('Статус'))
            ),
            array(
                'fio' => null,
                'item' => null,
                'score' => null,
                'scoremax' => null,
                'start1' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976]
                'stop' => array('render' => 'DateSmart'),  // добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
                'status' => array('values' => HM_Scorm_Track_Data_DataModel::getStatuses())
            )
        );

        $grid->updateColumn('start1', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat())))); //[che 5.06.2014 #16976] 
        $grid->updateColumn('stop', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTrackStatusString'),
                    'params' => array('{{status}}')
                )
            )
        );

        if ($this->_studentId) {
            $grid->updateColumn('fio', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }


    public function listlectureAction(){

        $this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));

        $this->getService('Unmanaged')->setHeader(_('Результаты работы с учебным модулем') . HM_View_Helper_Footnote::marker(1));
        if ($this->_lesson) {
            if ($user = $this->getService('User')->find($this->_studentId)->current()) {
                $this->getService('Unmanaged')->setSubHeader($this->_lesson->title . ' - ' . $user->getName());
            }
        }

        $items = array();
        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true);
            }
        }
                
        // вынес логику в ScormTrack
        list($itemResults, $fullProgress) = $this->getService('ScormTrack')->getAggregatedResults($courseId, $this->_lesson->getLessonId(), $this->_studentId, $items);

        $this->view->items = $itemResults;
        $this->view->fullProgress = $fullProgress;
        $this->view->footnote(_('Отображается результат последней (хронологически) попытки'), 1);
    }

    public function courseMain()
    {        
		$select = $this->getService('Lesson')->getSelect();

        //if ($this->_lesson->typeID == HM_Event_EventModel::TYPE_COURSE) {
        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
            $courseId = $courseId?$courseId:$this->_lesson->CID;//$this->_lesson->getModuleId(); //[che 5.06.2014] // В процессе решения #16976, оказалоь просто не поасть на страницу - заодно починил еее
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = array($itemId); 
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
                  
            }
        }

        $subSelect = $this->getService('Lesson')->getSelect();
        $subSelect->from( array('l' => 'scorm_tracklog'),
                          array('l.mid',
                                'l.cid',
                                'count' => new Zend_Db_Expr('COUNT(trackID)'),
                                'mscore' => new Zend_Db_Expr('MAX(score)')) )
                  ->where( 'l.cid = ?', $courseId)
                  ->group( array('l.mid','l.cid') );

        if (count($items)) {
            $subSelect->where('l.ModID IN (?)', $items);
        }
        
        if ($this->_lesson) {
            $subSelect->where('l.lesson_id = ?', $this->_lesson->getLessonId());
        }

        if ($this->_studentId) {
            $subSelect->where('l.mid = ?', $this->_studentId);
        }

        $select->from( array('t1' => 'Students'),
                       array('t1.MID',
                             'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")) )
               ->joinInner(array('p' => 'People'),
                           'p.MID = t1.MID',
                           array())
               ->joinLeft( array('t3' => $subSelect),
                           't1.MID = t3.mid',
                           array('t3.count', 't3.mscore'))
               ->joinLeft(array('les' => 'scheduleID'),
                          $this->getService('Lesson')->quoteInto('les.SHEID = ? AND les.MID = t1.MID',$this->_lesson->getLessonId()),
                          array('status' =>'les.V_DONE'))
               ->where('t1.CID = ?', $this->_getParam('subject_id', 0));

        //exit($select->__toString());

       $columns = array('trackID' => array('hidden' => TRUE),
       					'MID' => array('hidden' => TRUE),
                        'fio' => array('title' => _('ФИО'),
                                       'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view',
                                       															   'controller' => 'list',
                                       															   'module' => 'user',
                                       															   'user_id' => '')) . '{{MID}}') .
                                       			      '{{fio}}'),
                        'status' => array('hidden' => true), // не будем показывать статус; с введением шкал 2- и 3-состояния поле статус потеряло смысл
       					'count' => array(
       					    'title' => _('Количество сеансов'),
       					    'decorator' => '<a href="' . $this->view->url(array('userdetail'=>'yes', 'user_id' => '')) . '{{MID}}' . '">{{count}}</a>'
   					    ),
                        'mscore' => array('title' => _('Балл')),);

       $filters = array('fio' =>NULL);

       $grid = $this->getGrid($select,$columns,$filters);

       $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getModuleStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

       $this->view->grid = $grid->deploy();
    }

    /**
     * текстовка для типа модуля
     * @param unknown_type $modId
     * @param unknown_type $count
     * @return string
     * @todo сделать корректное определение типа "завершен"
     */
    public function getModuleStatus($status)
    {
        return HM_Lesson_Assign_AssignModel::getProgressStatusName($status);
    }

    /**
     *  Максимальный результат материала выделяется стилем
     * @param $item int
     * @param $score int
     * @param $select Zend_Db_Select
     */
    public function updateScore($item, $score, $select) {
        if ($this->_maxScoreCache === null) {
            $this->_maxScoreCache = array();

            $result = $select->query()->fetchAll();

            if ($result) {
                foreach ($result as $val) {
                    if ((!isset($this->_maxScoreCache[$val['item']])) || (isset($this->_maxScoreCache[$val['item']]) && $this->_maxScoreCache[$val['item']] < $val['score']) ) {
                        $this->_maxScoreCache[$val['item']] = $val['score'];
                    }
                }
            }
        }

        if ($this->_maxScoreCache[$item] == $score) {
            return '<div style="color:coral">' . $score . '</div>';
        }
        return $score;
    }

    public function course()
    {
        if ($this->_lesson) {
            if ($user = $this->getService('User')->find($this->_studentId)->current()) {
                $this->getService('Unmanaged')->setSubHeader($this->_lesson->title . ' - ' . $user->getName());
            }
        }
        
        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = array($itemId); 
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
                  
            }
        }

        $select = $this->getService('Lesson')->getSelect();
        $select->from(
                    array('l' => 'scorm_tracklog'),
                    array(
                        'l.trackID',
                        'MID' => 'mid',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        'item' => 'o.title',
                    	'parent' => 'o.oid',
                        'course_id' => 'l.cid',
                        'l.score',
                        'l.scoremax',
                        'l.scoremin',
                        'start1'=>'l.start', //[che 5.06.2014 #16976] 
                        'l.stop',
                        'l.status'
                    )
                )
                ->joinLeft(
                    array('p' => 'People'),
                    'p.MID = l.mid',
                    array()
                )->joinLeft(array('o' => 'organizations'), 'o.oid = l.ModID', array())
                ->where('l.cid = ?', $courseId);

        if (count($items)) {
            $select->where('l.ModID IN (?)', $items);
        }

        if ($this->_lesson) {
            $select->where('l.lesson_id = ?', $this->_lesson->getLessonId());
        }

        if ($this->_studentId) {
            $select->where('l.mid = ?', $this->_studentId);
        }

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
             $select->order(array('item ASC', 'score DESC', 'start1 DESC'));
        }

        $grid = $this->getGrid($select,
            array(
                'trackID' => array('hidden' => true),
                'MID' => array('hidden' => true),
            	'course_id' => array('hidden' => true),
            	'parent' => array('title' => _('Раздел модуля')),
                'fio' => array('hidden' => true), //array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{MID}}') . '{{fio}}'),
                'item' => array('title' => _('Материал')),
                'score' => array('title' => _('Балл')),
                'scoremax' => array('title' => _('Мин/Mакс'), 'decorator' => '{{scoremin}}/{{scoremax}}'),
                'scoremin' => array('hidden' => true),
                'start1' => array('title' => _('Начало сеанса')),
                'stop' => array('title' => _('Окончание  сеанса')),
                'status' => array('title' => _('Статус'))
            ),
            array(
                'fio' => null,
                'item' => null,
                'score' => null,
                'scoremax' => null,
                'start1' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976
                'stop' => array('render' => 'DateSmart'),  // добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
                'status' => array('values' => HM_Scorm_Track_Data_DataModel::getStatuses())
            )
        );

        $grid->updateColumn('start1', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $grid->updateColumn('stop', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTrackStatusString'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('parent',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateParent'),
                    'params' => array('{{parent}}', '{{course_id}}')
                )
            )
        );

        $grid->updateColumn('score',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateScore'),
                    'params' => array('{{item}}', '{{score}}', $select)
                )
            )
        );


        // если студент просматривает свои результаты - скрываем поле с ФИО
        if ( $this->_studentId == $this->getService('User')->getCurrentUserId()
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
             //&& in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_STUDENT))
        ) {
            $grid->updateColumn('fio', array('hidden' => true));
        } else {
            $this->view->allowBack = true;
            $this->view->subjectId = $this->_lesson->CID;
            $this->view->lessonId = $this->_lesson->getLessonId();
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function getTrackStatusString($status)
    {
        $status = HM_Scorm_Track_Data_DataModel::getStatus($status);
        return "<span class='nowrap'>{$status}</span>";
    }

    public function testMiniAction()
    {
        $this->getService('Unmanaged')->setHeader(_('Протокол выполнения'));
        $c         = $_GET['c'] = 'mini';
        $stid      = $_GET['stid'] = $this->_getParam('stid', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId  = $this->_getParam('lesson_id', 0);
        $lesson    = $this->getOne($this->getService('Lesson')->find($lessonId));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $log = $this->getService('TestResult')->getOne($this->getService('TestResult')->find($stid));
            if ( $log ) {
                // Проверка. Может ли пользователь просматривать отчеты по своим попыткам.
                $test = $this->getService('Test')->getOne($this->getService('Test')->find($log->tid));
                if(!$test->allow_view_log){
                    $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                             'message' => _('Вы не можете просматривать подробный отчет данного теста')));
                    $this->_redirector->gotoSimple('my','list','lesson',array('subject_id' => $subjectId));
                }
                if ($log->mid != $this->getService('User')->getCurrentUserId()) {
                    $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                             'message' => _('Вы можете просматривать результаты только своих тестов')));
                    $this->_redirector->gotoSimple('my','list','lesson',array('subject_id' => $subjectId));
                }
            }
        }

        if ($lesson) {
            $this->getService('Unmanaged')->setSubHeader($lesson->title);
        }

        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }


        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();
        ob_start();

        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        include(APPLICATION_PATH.'/../public/unmanaged/test_log.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);

        $this->view->content = $content;
    }

    public function test()
    {        
		/**
         * Исправлена ошибка со start.
         * @author Artem Smirnov
         * @date 22.02.2013
         */
        $select = $this->getService('Lesson')->getSelect();
        $select->from(
                    array('l' => 'loguser'),
                    array(
                        'l.stid',
                        'MID' => 'l.mid',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        'start_date' => 'l.start',
                        'questions' => new Zend_Db_Expr("CONCAT(CONCAT(l.questdone, '/'), l.questall)"),
                        'bal' => new Zend_Db_Expr("ROUND(l.bal, 2)"),
                        'percent' => new Zend_Db_Expr("CONCAT(ROUND(100 * (l.bal - l.balmin2) / (l.balmax2 - l.balmin2),2), '%')"),
                        'l.fulltime',
                        'l.status',
                    )
                )
                ->joinLeft(
                    array('p' => 'People'),
                    'p.MID = l.mid',
                    array()
                )->where('l.sheid = ?', $this->_lesson->getLessonId())
                ->where('l.teachertest = ?', 0)
                ->where('p.MID > ?', 0);
				
				
		# если тьютор		             
		$subjectService = $this->getService('Subject');	
		$userService = $this->getService('User');		
		if ($this->getService('Acl')->inheritsRole($userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
			$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $this->_getParam('subject_id', 0));
			if($studentIDs){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($subjectService->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}
		
		$select->where($subjectService->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
        if ($this->_studentId) {
            $select->where('l.mid = ?', $this->_studentId);
        }
        /** @var $grid Bvb_Grid */
        $grid = $this->getGrid($select,
            array(
                'stid' => array('hidden' => true),
                'MID' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => '')) . '{{MID}}') . '{{fio}}'),
                'start_date' => array(
                    'title' => _('Дата попытки'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'result', 'action' => 'test-mini', 'stid' => '')).'{{stid}}">{{start_date}}</a>'
                ),
                'questions' => array('title' => _('Вопросов Ответил/Всего')),
                'bal' => array('title' => _('Балл')),
                'percent' => array('title' => _('Процент')),
                'fulltime' => array('title' => _('Затрачено времени')),
                'status' => array('title' => _('Статус')),
            ),
            array(
                'fio' => null,
                'bal' => null,
                'percent' => null,
                //'questions' => null,
//                'start_date' => array('render' => 'DateTimeStamp'),
	    	'start_date' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976] //добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
                'fulltime' => null,
                'status' => array('values' => HM_Test_Result_ResultModel::getStatuses())
            )
        );
        $grid->updateColumn('start_date', array('format' => array('DateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $grid->updateColumn('fulltime',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getDurationString'),
                    'params' => array('{{fulltime}}')
                )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getStatusString'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('needmoder',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getModerString'),
                    'params' => array('{{needmoder}}', '{{moder}}', '{{moderby}}', '{{modertime}}')
                )
            )
        );

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER))
	        $grid->addMassAction(array(
	            'module' => 'lesson',
	            'controller' => 'result',
	            'action' => 'delete-attempt'
	        ),
	            _('Аннулировать попытки'),
	            _('Вы уверены, что хотите аннулировать отмеченные попытки? При этом у соответствующих пользователей появятся дополнительные попытки для прохождения данного теста.')
	        );

         // если студент просматривает свои результаты - скрываем поле с ФИО
        if ( $this->_studentId == $this->getService('User')->getCurrentUserId()
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
        //     && in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_STUDENT))
        ) {

            $grid->updateColumn('fio', array('hidden' => true));
        }
/*
        $grid->addAction(
            array('module' => 'lesson', 'controller' => 'result', 'action' => 'test-mini'),
            array('stid'),
            $this->view->icon('print')
        );
*/
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function deleteAttemptAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);

        $stids = $this->_request->getParam('postMassIds_grid');
        $stids = explode(',', $stids);

        if (count($stids)) {
            foreach($stids as $stid) {
                $result = $this->getOne($this->getService('TestResult')->find($stid));
                if ($result) {
                    // обновление попыток
                    $attempt = $this->getOne($this->getService('TestAttempt')->fetchAll(
                        $this->getService('TestAttempt')->quoteInto(
                            array('mid = ?', ' AND tid = ?', ' AND cid = ?', ' AND lesson_id = ?'),
                            array($result->mid, $result->tid, $subjectId, $lessonId)
                        )
                    ));

                    if ($attempt) {
                        $attempt->qty--;
                        if ($attempt->qty < 0) $attempt->qty = 0;
                        $this->getService('TestAttempt')->update($attempt->getValues());
                    }
                }
                // удаление результатов
                $this->getService('TestResult')->delete($stid);
                $this->getService('QuestionResult')->deleteBy($this->getService('QuestionResult')->quoteInto('stid = ?', $stid));
            }
        }

        $this->_flashMessenger->addMessage(_('Попытки успешно удалены'));
        $this->_redirector->gotoSimple('index', 'result', 'lesson', array('subject_id' => $subjectId, 'lesson_id' => $lessonId));
    }

    public function task()
    {        
		/**
         * Добавлено правильное отображение для Фио пользователя,
         * Добавлено отображение пользователей которым назначено задание, но нет результата.
         *
         * @author Artem Smirnov
         * @date 19.02.2013
         */
		$userService = $this->getService('User');
		$currentUserRole = $userService->getCurrentUserRole();
        if(
            $this->getService('Acl')->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT
        )
        {
            $this->test();
            return ;
        }

        $lessonId = (int) $this->_getParam('lesson_id', 0);


//[[[[[che 25.05.2014 #16617]
// "Полностью рефакторил этот фарш" - это Lex писал 03.2014. Возможно, проблема решалась. Однозначно правильно логически решить невозможно.
// Будем решать итерационно - время покажет
// Вопрос конечно интересный с полями user_id и to_whom !!!
// Логика моя такая: для определения субъекта, берем поле to_whom, если оно нулевое - берем user_id
// Оставляю старый код на всякий случай //
/*
        $subSelect = $this->getService('Interview')->getSelect()->from(
            array('i' => 'interview'),
            array(
                '	real_user_id' => new Zend_Db_Expr('GREATEST(user_id, to_whom)'), // кто придумал такую структуру БД..?!!
                'last_interview_id' => 'MAX(interview_id)',
                'first_interview_id' => 'MIN(interview_id)',
        ))
        ->where('lesson_id = ?', $lessonId)
        ->group('GREATEST(user_id, to_whom)');
*/
        $subSelect = $this->getService('Interview')->getSelect()->from(
            array('i' => 'interview'),
            array(
                'real_user_id' => '(CASE when(to_whom=0) THEN user_id else to_whom END)',
                'last_interview_id' => 'MAX(interview_id)',
                'first_interview_id' => 'MIN(interview_id)',
        ))
        ->where('lesson_id = ?', $lessonId)
        ->group('(CASE when(to_whom=0) THEN user_id else to_whom END)'); //[che 04.06.2014 #14875]
//[che 25.05.2014 #16617]]]]]

        $select = $this->getService('LessonAssign')->getSelect()->from(
            array('s' => 'scheduleID'),
            array(
                'user_id' => 'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'date' => 'i.date',
                'variant' => 'l.qtema',
                'i.type',
        ))->joinInner(array('p' => 'People'),
            's.MID = p.MID',
            array()
        )->joinInner(array('ss' => $subSelect),
            's.MID = ss.real_user_id',
            array()
        )->joinInner(array('i' => 'interview'), // последняя запись в interview
            'i.interview_id = ss.last_interview_id',
            array()
        )->joinInner(array('ii' => 'interview'), // первая запись в interview
            'ii.interview_id = ss.first_interview_id',
            array()
        #)->joinInner(array('l' => 'list'),
        )->joinLeft(array('l' => 'list'),
            'ii.question_id = l.kod',
            array()
//[che 03.06.2014 #14875]
        )->joinLeft(array('gr' => 'graduated'),
            'gr.MID = p.MID and gr.CID = '.$this->_getParam('subject_id', 0),
            array()
        )
        ->where('SHEID = ? and gr.MID is null', $lessonId)
//
        ->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'l.qtema', 'i.type', 'i.date'));
		
		
		
		# если тьютор		             
		$subjectService = $this->getService('Subject');		
		if ($this->getService('Acl')->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
			$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $this->_getParam('subject_id', 0));
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($subjectService->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}
		$select->where($subjectService->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		
       $url = $this->view->url(array(
            'action' => 'card',
            'controller' => 'edit',
            'module' => 'user',
            'lesson_id' => $lessonId,
       ));

       $columns = array(
            'user_id' => array('hidden' => true),
            'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{user_id}}') . "<a href='{$url}/user_id/{{user_id}}'>{{fio}}</a>"),
            'date' => array(
                'title' => _('Дата последнего изменения'),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{date}}')
                )
            ),
            'type' => array(
                'title' => _('Текущий статус'),
                'callback' => array(
                    'function' => array($this, 'getTaskTypeString'),
                    'params' => array('{{type}}')
                )
            ),
            'variant' => array('title' => _('Название')),
       );

       $filters = array(
            'fio' => NULL,
//            'date' => array('render' => 'date'),
	    'date' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976] //добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL

            'type' => array('values' => HM_Interview_InterviewModel::getTypes())
       );

       $grid = $this->getGrid($select, $columns, $filters);

        $isTutor = $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_RoleModelAbstract::ROLE_TUTOR
        );
//        if(!$isTutor){
            $grid->addAction(
                array(
                    'action' => 'index',
                    'controller' => 'index',
                    'module' => 'interview',
                    'lesson_id' => $lessonId,
                ),
                array('user_id'),
                _('Просмотр')
            );
//        }
//        var_dump($select->__toString());
       $this->view->grid = $grid->deploy();

       return true;
       /*   следующий кусок кода попал сюда, как я думаю, за-за некорректного мержа.
        *   оставлю его в комменте, после return он все равно не работал.
\
            $this->_setParam('CID', $subjectId);

            $s = Zend_Registry::get('session_namespace_unmanaged')->s;
            $params = $this->_getAllParams();
            if (is_array($params) && count($params)) {
                foreach($params as $key => $value) {
                    $$key = $value;
                }
            }
            $paths = get_include_path();
            set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
            $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
            $currentDir = getcwd();
            ob_start();

            chdir(APPLICATION_PATH.'/../public/unmanaged/');
            $res = include(APPLICATION_PATH.'/../public/unmanaged/test_moder.php');
            $content = ob_get_contents();
            ob_end_clean();
            set_include_path(implode(PATH_SEPARATOR, array($paths)));
            chdir($currentDir);

            if($res == 'update_ok'){
                $this->_flashMessenger->addMessage(_('Балл и комментарий успешно сохранены. Для перерасчета балла за задание выберите соответствующую опцию в списке "Выполнить действие" в нижней части страницы.'));
                $this->_redirector->gotoSimple('index', 'result', 'lesson', array('subject_id' =>array('subject_id' => $subjectId ),'lesson_id' =>array('lesson_id' => (int) $this->_getParam('lesson_id', 0) )));
            }


            if($_POST['action'] == 'complete' || $_POST['action'] == 'clearsence'){
                $this->_flashMessenger->addMessage(_('Результаты успешно сохранены!'));
                $this->_redirector->gotoSimple('index', 'result', 'lesson', array('subject_id' =>array('subject_id' => $subjectId ),'lesson_id' =>array('lesson_id' => (int) $this->_getParam('lesson_id', 0) )));
            }



            $this->view->content = $content;
        
        //*/
    }

    public function poll()
    {
    	//$quizId = $this->_lesson->getQuizId();
    	$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_lesson->getModuleId()));
		$quizId = $test->test_id;

        $select = $this->getService('Poll')->getSelect();
        $select->from(
                    array('qa' => 'quizzes_answers'),
                    array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title')
                )
                ->joinLeft(
                	array('qr' => 'quizzes_results'),
                	'qr.quiz_id=qa.quiz_id AND qr.question_id = qa.question_id AND qr.answer_id=qa.answer_id',
                	array('count' => 'COUNT(qr.user_id)')
                )
                ->where('qa.quiz_id = ?', $quizId)
                ->where('qr.lesson_id = ?', $this->_lesson->SHEID)
                ->group(array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title'));
                //->order(array('qa.question_title', 'qa.answer_title')) ORDER не нужно использовать в селекте для грида
                ;

        $grid = $this->getGrid($select,
            array(
                'quiz_id' => array('hidden' => true),
                'question_id' => array('hidden' => true),
                'question_title' => array('title' => _('Текст вопроса')),
                'answer_title' => array('title' => _('Вариант ответа')),
                'answer_id' => array('hidden' => true),
                'count' => array('title' => _('Количество таких ответов')),
            ),
            array(
                'question_title' => null,
                'answer_title' => null,
            	'count' => null
            )
        );
        $grid->updateColumn('count',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'modifCount'),
                    'params' => array('{{count}}', '{{answer_title}}','{{question_id}}','{{quiz_id}}')
                )
            )
        );
		$grid->updateColumn('question_id',
					array('value' => '!!'));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }
    public function modifCount($count,$answer_title,$question_id,$quiz_id)
    {
//        $subjectId = $this->_getParam('subject_id', 0);
//        if ($answer_title == "свободный ответ")
//            return $count."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$this->view->lightDialogLink($this->view->baseUrl('test_vopros.php?kod='.$question_id.'&cid='.$subjectId.'&mode=2&quiz_id='.$quiz_id.'&all=all',_('Карточка вопроса')), "Просмотр");
//        else
//            return $count;

        if ($answer_title == "свободный ответ" && $count>0){
            /*$select = $this->getService('Poll')->getSelect();
            $select->from(
                        array('qr' => 'quizzes_results'),
                        array('qr.freeanswer_data', 'qr.quiz_id', 'qr.question_id')
                    )
                    ->where('qr.quiz_id = ?', $quiz_id)
                    ->where('qr.question_id = ?', $question_id);

            $query = $select->query();
            $fetch = $query->fetchAll();
             * 
             */
            $where = $this->quoteInto(array(
                'quiz_id = ?', ' AND question_id = ? AND ', 'lesson_id = ?'
            ), array(
                $quiz_id, $question_id, $this->_lesson->SHEID
            ));
            
            $results = $this->getService('PollResult')->fetchAll($where);
            $result = array('<p class="total">' . $results->count() . '</p>');
            foreach ($results as $value){
               $result[]='<p>' . $value->freeanswer_data . '</p>';
            }
            return implode('', $result);
        }
        else
            return $count;
    }
    public function pollLeader(){

    	//$quizId = $this->_lesson->getQuizId();
    	$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_lesson->getModuleId()));
		$quizId = $test->test_id;

        $select = $this->getService('Poll')->getSelect();
        $select->from(
                    array('qa' => 'quizzes_answers'),
                    array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title')
                )
                ->joinLeft(
                	array('qr' => 'quizzes_results'),
                	'qr.quiz_id=qa.quiz_id AND qr.question_id = qa.question_id AND qr.answer_id=qa.answer_id',
                	array('count' => 'COUNT(qr.user_id)')
                )
                ->where('qa.quiz_id = ?', $quizId)
                ->group(array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title'))
                ;

        $grid = $this->getGrid($select,
            array(
                'quiz_id' => array('hidden' => true),
                'question_id' => array('hidden' => true),
                'question_title' => array('title' => _('Текст вопроса')),
                'answer_title' => array('title' => _('Вариант ответа')),
                'answer_id' => array('hidden' => true),
            	'count' => array('title' => _('Количество таких ответов'))
            ),
            array(
                'question_title' => null,
                'answer_title' => null,
            	'count' => null
            )
        );

		$grid->updateColumn('question_id',
					array('value' => '!!'));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    // данные проведения опроса для конкретного юзера (ссылка с названия опроса в сборе обратной связи)
    public function pollByUserAction(){

        $subjectId = $this->_getParam('subject_id', 0);
    	$userId = $this->_getParam('user_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);
    	//$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($lesson->getModuleId()));
		//$quizId = $test->test_id;
        $lesson = $this->getOne($this->getService('Lesson')->findDependence('Teacher', $lessonId));
        if ($lesson) {
            $this->getService('Unmanaged')->setSubHeader($lesson->title);
        }

        $claimant = $this->getOne($this->getService('Claimant')->fetchAllDependence(array('Teacher', 'Provider'), $this->getService('Claimant')->quoteInto('CID = ? AND MID = ?', $subjectId, $userId)));
        if($claimant) {
        	$this->view->date = $claimant->begin;
        	$this->view->place = $claimant->place;
        	$this->view->provider = $claimant->provider;
        	$this->view->teacher = $lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic;
        }
        elseif($lesson){
        	$this->view->date = new Zend_Date($lesson->begin, Zend_Locale_Format::getDateFormat()); // $lesson->begin;
        	$this->view->place = _('дистанционно');
        	$this->view->provider = '—';
        	$this->view->teacher = $lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic;
        }

        $log = $this->getService('TestResult')->fetchAll($this->getService('TestResult')->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $userId)))->asArray();

       	$content = '';

        if(is_array($log) && count($log)){

	        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
	        $params = $this->_getAllParams();
	        if (is_array($params) && count($params)) {
	            foreach($params as $key => $value) {
	                $$key = $value;
	            }
	        }
	        $c = $_GET['c'] = 'mini';
	        $paths = get_include_path();
	        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));
	        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
	        $currentDir = getcwd();

	        foreach ($log as $attempt){

				$stid = $attempt['stid'];
		        ob_start();
		        chdir(APPLICATION_PATH.'/../public/unmanaged/');
		        include(APPLICATION_PATH.'/../public/unmanaged/test_log.php');
		        $content .= ob_get_contents();
		        ob_end_clean();
		        set_include_path(implode(PATH_SEPARATOR, array($paths)));

			}

	        chdir($currentDir);


        }

        $this->view->content = $content;

    }


    public function dateChanger($date)
    {
        $dateObject = new Zend_Date($date);
        return $dateObject->getTimestamp();
    }

    public function updateDate($date)
    {
        $dateObject = new Zend_Date($date);
        return $dateObject->toString();
    }


    /**
     * Возвращает наименование типа по его числовому представлению
     * @param int $type
     * @return string
     */
    public function getTaskTypeString( $type )
    {
        $ivModel = HM_Interview_InterviewModel::factory(array('type' => intval($type)));
        return  $ivModel->getType();
    }


    /**
     * Возвращает ФИО студента с которым ведется диалог
     * @param int $hash - уникальный идентификатор диалога
     * @return string
     */
    public function getInterviewUserName($hash,$fio)
    {
        $interview = $this->getService('Interview')->getOne($this->getService('Interview')->fetchAll(array('interview_hash=?' => $hash, 'type=?'=>HM_Interview_InterviewModel::MESSAGE_TYPE_TASK)));
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        if ( count($interview) ) {
            $user = $this->getService('User')->getOne($this->getService('User')->find($interview->to_whom));
            if ( $user ) {
                return $this->view->cardLink(
                    $this->view->url(
                        array(
                        	'action' => 'view',
						   'controller' => 'list',
						   'module' => 'user',
						   'user_id' => $user->MID
                        )
                    )
                    ) . '<a href="' . $this->view->url(
                            array(
                            	'action' => 'index',
						   		'controller' => 'index',
						   		'module' => 'interview',
						   		'user_id' => $user->MID,
                                'lesson_id' => $lessonId
                            )
                        ) . '">' . $user->getName() . '</a>';
            }
        }
        return _('Пользователь удален');
    }


    public function getDurationString($seconds) {
        $date = new HM_Date();
        return $date->getDurationString($seconds);
    }

    public function getStatusString($status)
    {
        $statuses = HM_Test_Result_ResultModel::getStatuses();
        return $statuses[$status];
    }
    public function getModerString($needmoder, $moder, $moderby, $modertime)
    {
        if ($needmoder == 0 && $moder == 0) return '&nbsp;';
        else {
            if ($needmoder == 1) return _("Сеанс еще не проверен преподавателем");
            else {
                $user = $this->getOne($this->getService('User')->find($moderby));
                if ($user) {
                    return sprintf('%s, %s', $user->getName(), date('d.m.Y H:i', $modertime)); // todo: HM_Date
                }
            }
        }
        return false;
    }

    public function updateParent($oid, $courseId){

        $separator = ' > ';

        if($this->_orgStructure == Null){
            $this->_orgStructure = $this->getService('CourseItem')->fetchAll(array('cid = ?' => $courseId));
        }

        $currItem = null;
        foreach($this->_orgStructure as $item){
            if($item->oid == $oid){
                $currItem = $item;
            }
        }

        if($currItem->level > 0){
            $currLevel = $currItem->level;
            $string = array();
            while($currItem->level > 0){
                foreach($this->_orgStructure as $item){
                    if($item->oid == $currItem->prev_ref){
                        $currItem = $item;
                        $string[] = $currItem->title;

                        if($currItem->prev_ref == -1){
                            continue 2;
                        }

                    }
                }
            }
            return implode($separator, $string);
        }else{
            return _('Нет');
        }
        return $currItem->title;
    }

    public function skillsoftAction()
    {
        $this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));
        $userId = $this->_getParam('user_id', $this->getService('User')->getCurrentUserId());

        if ($this->_lesson) {
            if ($user = $this->getService('User')->find($userId)->current()) {
                $this->getService('Unmanaged')->setSubHeader($this->_lesson->title . ' - ' . $user->getName());
            }
        }

        if(
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){
            $userId =  $this->_getParam('user_id', 0);
            $subjectId = $this->_getParam('subject_id', 0);
            $students = $this->getService('Subject')->getAssignedUsers($subjectId);

            $resStudents = array(0 => _('Выберите слушателя'));

            foreach($students as $student){
                $resStudents[$student->MID] = $student->getName();
            }
            $this->view->students = $resStudents;

            if($userId == 0){
                return;
            }
        }
        $this->view->userId = $userId;
        $this->view->lessonId = $this->_lesson->SHEID;

    }

    public function reportAction()
    {

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

		$reports = $this->getService('ScormReport')->fetchAll(array(
            'mid = ?' => $this->_getParam('user_id', 0),
            'lesson_id = ?' => $this->_getParam('lesson_id', 0),
        ));

        exit($reports[0]->report_data);
    }
	
	
	public function allAction() {
		
		$this->view->headLink()->appendStylesheet('\css\rgsu_style.css');	
        
		$subjectId = intval($this->_getParam('subject_id', 0));
        $lessonId = intval($this->_getParam('lesson_id', 0));
		$showGraduated = (int)$this->_getParam('graduated', 0);

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        
		$lessonService = $this->getService('Lesson');
		$subjectService = $this->getService('Subject');
		
		$lessonEvaluatorsService = $this->getService('LessonEvaluators');

        $currentUserRole = $userService->getCurrentUserRole();
        $currentUserId = $userService->getCurrentUserId();
        $currentUser_InheritsFrom_Tutor = $aclService->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_TUTOR);
		
		# назначен на эту сессию
		if(!$subjectService->isTutor($subjectId, $currentUserId) && !$subjectService->isTeacher($subjectId, $currentUserId)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Вы не назначены на этот курс.'))
			);
			$this->_redirect('/');
		}
		
		$this->getService('Unmanaged')->setHeader(_('Результаты занятий'));
		
        $cols = array(
            'MID' 			=> 'p.MID',
            'SID' 			=> 'st.SID',
            'fio' 			=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'group_id' 		=> 'sg.group_id',
            'group_name' 	=> 'sg.name', 
			'courses_mark'	=>	'cm.mark',		
        );
        
        $select = $this->getService('User')->getSelect();
        $select->from(array('p'	=> 'People'), $cols);        
		$select->joinLeft(array('sgu' => 'study_groups_users'), 'sgu.user_id = p.MID');
        $select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgu.group_id');		        
		
		if($showGraduated){
			$select->joinInner(array('st' => 'graduated'), 'st.MID = p.MID AND st.CID = '.$subjectId);            
		} else {
			$select->joinInner(array('st' => 'Students'), 'st.MID = p.MID AND st.CID = '.$subjectId);
		}
		
		$select->joinLeft(array('cm' => 'courses_marks'), 'cm.mid = st.MID AND cm.cid = st.CID');
		
		# если тьютор		
		if ($currentUser_InheritsFrom_Tutor) {
			$studentIDs = $subjectService->getAvailableStudents($currentUserId, $subjectId);
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($userService->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}

        //показываем только оцениваемых
        if ($currentUserIsEvaluator) {
            $select->where('p.MID IN (?)', $evaluatorsCollection->getList('MID_evaluated'));
        }
        $select->where($userService->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		$select->order('group_name', 'fio');
        $stmt = $select->query();		
        $result = $stmt->fetchAll();
        $groups = array('show_all' => array(
            'name' => _('Показать всех'),
            'new_count' => 0
        )); //список групп $key = id, $val['name'] = название, $val['new_count'] = количество новых сообщений

        $users = array();
        foreach ($result as $key => $value) {
            $mid = $value['MID'];
            $sid = $value['SID'];

            $result[$key]['group_id'] = intval($result[$key]['group_id']);
            $group_id = 0;
            //прячем группы от студентов
            if (!$currentUser_InheritsFrom_Student) {
                $group_id = $result[$key]['group_id'];
                if (empty($groups[$group_id])) {
                    $groups[$group_id] = array(
                        'name' => $value['group_name'],
                        'new_count' => 0
                    );
                    if ($group_id == 0) {
                        $groups[$group_id]['name'] = _('Без группы');
                    }
                }               
            }
            //ссылка на переписку с пользователем
            $url = array(
                'module' => 'interview',
                'controller' => 'index',
                'action' => 'all',
                'subject_id' => $subjectId,
                'user_id' => $mid,
            );

            //карточка пользователя
            if ($currentUser_InheritsFrom_Student) {
                //прячем имена от студентов
                $result[$key]['card'] = '';
                $result[$key]['fio'] = 'Слушатель '.($key+1);
                //прячем id пользователя, и передаём id студента,
                //что бы оценщик-студент не знал, кого он оценивает
                unset($url['user_id']);
                $url['student_id'] = $sid;
            } else {
                $result[$key]['card'] = $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'user_id' => $mid
                        )
                    ),
                    _('Карточка пользователя')
                );
            }

            $result[$key]['url'] = $this->view->url($url);

            if (empty($users[$sid])) {
                $users[$sid] = $result[$key];
                $users[$sid]['groups'] = array();
            }
            $users[$sid]['groups'][] = $group_id;
        }

		$tt = array();
		foreach($users as $key => $u){
			$tt[$u['fio'].'~'.$key] = $u;		
		}
		$users = $tt;		
		ksort($users);	
		
		$urlTypeStudentsParams = array(
			'module'     => $this->_request->getModuleName(), 
			'controller' => $this->_request->getControllerName(), 
			'action'     => $this->_request->getActionName(), 
			'subject_id' => $subjectId, 
			'lesson_id'  => $lessonId,			
		);
		
		if(empty($showGraduated)){
			$urlTypeStudentsParams['graduated'] = 1;
		}
		
		
		$this->view->users = $users;		
        $this->view->groups = $groups;
		$this->view->showGraduated   = $showGraduated;
		$this->view->urlTypeStudents = $this->view->url($urlTypeStudentsParams, 'default', true);
		$this->view->readOlny        = $showGraduated ? true : false;
    }


}