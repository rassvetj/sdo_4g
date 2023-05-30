<?php
class Journal_ResultController extends HM_Controller_Action_Subject
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
		parent::init();
        
		$lessonId 	= $this->_getParam('lesson_id', 0);        
		$subjectId 	= $this->_getParam('subject_id', 0);
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {            			
			# редирект на версию журнала студента
			$this->_redirector->gotoSimple('index', 'index', 'journal',
				array(
					'lesson_id' 	=> $lessonId,
					'subject_id' 	=> $subjectId,						
				)
			);						
        } else {
            $this->_studentId = $this->_getParam('user_id', 0);
        }
        $this->getService('Unmanaged')->setHeader(_('Журнал'));
		
		
		# TODO Унифицировать. Эта же проверка реализована в Lesson_ExecuteController. Однако, данное условие выполняется при переходе в занятие из ведомости успеваемости.
		if(
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&&
			!$this->getService('Lesson')->isAvailable($this->getService('User')->getCurrentUserId(), $lessonId, $subjectId)
		){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('У Вас нет доступа к этому занятию.')));			
			$this->_helper->redirector->gotoSimple('card', 'index', 'subject', array('subject_id' 	=> $subjectId));			
		} 
		
    }
	

	

    public function extendedAction() {
        try {
 

		
		$subjectId = intval($this->_getParam('subject_id', 0));
        $lessonId = intval($this->_getParam('lesson_id', 0));

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
        
		$lesson = $this->getService('Lesson')->find($lessonId)->current();		
		$allowTutors = $lesson->allowTutors;
		$this->getService('Unmanaged')->setHeader(_('Журнал: ').$lesson->title);
        
		
		
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
            'mark' => 'sch.V_STATUS',    
        );
        
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), $cols);
        $select->joinLeft(array('sch' => 'scheduleID'), 'sch.MID = p.MID');
        $select->joinLeft(array('sgu' => 'study_groups_users'), 'sgu.user_id = p.MID');
        $select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgu.group_id');
    
        $select->joinInner(array('st' => 'Students'), 'st.MID = p.MID AND st.CID = '.$subjectId);

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
        $select->where($userService->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		$select->order('group_name', 'fio');

        //формируем данные для шаблона
        $stmt = $select->query();
		//pr($stmt);
		
		} catch (Exception $e) {
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
        
	
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
                if ($value['is_new']) {
                    $groups[$group_id]['new_count']++;
                    $groups['show_all']['new_count']++;
                }
            }
            //ссылка на профиль студента
            $url = array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',                
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

            #$result[$key]['url'] = $this->view->url($url);
            
            if (empty($users[$sid])) {
                $users[$sid] = $result[$key];
                $users[$sid]['groups'] = array();
            }
            $users[$sid]['groups'][] = $group_id;
            $users[$sid]['groups_name'][] = $value['group_name'];
        }

		#$users = array_values($users);
		$tt = array();
		foreach($users as $key => $u){
			$tt[$u['fio'].'~'.$key] = $u;		
		}
		$users = $tt;		
		ksort($users);	
		
        $this->view->users 		 = $users;
        $this->view->groups 	 = $groups;        
    }




}