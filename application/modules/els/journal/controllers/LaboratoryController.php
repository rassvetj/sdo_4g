<?php
class Journal_LaboratoryController extends HM_Controller_Action_Subject
{
    public function init(){
		$this->lesson_id 		= (int) $this->_getParam('lesson_id', 0);
		$this->subject_id 		= (int) $this->_getParam('subject_id', 0);
		
		
		$this->lesson			= $this->getService('Lesson')->find($this->lesson_id)->current();					
		$controllerName 		= Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$actionName 			= Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		$this->curent_user_role = $this->getService('User')->getCurrentUserRole();
		$this->_aclService 		= $this->getService('Acl');
		
		$newActionName  = (($this->_aclService->inheritsRole($this->curent_user_role, HM_Role_RoleModelAbstract::ROLE_STUDENT))) ? 'index' : 'extended';
		
		switch ($this->lesson->typeID) {
			case HM_Event_EventModel::TYPE_JOURNAL_LECTURE:
				$newControllerName = 'lecture';
				break;
			case HM_Event_EventModel::TYPE_JOURNAL_PRACTICE:	
				$newControllerName = 'practice';				
				break;
			case HM_Event_EventModel::TYPE_JOURNAL_LAB:
				$newControllerName = 'laboratory';				
				break;
		}
		
		if($newControllerName != $controllerName || $actionName != $newActionName){			
			$this->_helper->redirector->gotoSimple($newActionName, $newControllerName, 'journal',
				array(
					'lesson_id' 	=> $this->lesson_id,
					'subject_id' 	=> $this->subject_id,						
				)
			);			
		}
		

		$this->curent_user_id   = $this->getService('User')->getCurrentUserId();
		
		# TODO Унифицировать. Эта же проверка реализована в Lesson_ExecuteController. Однако, данное условие выполняется при переходе в занятие из ведомости успеваемости.
		if(
			$this->_aclService->inheritsRole($this->curent_user_role, HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&&
			!$this->getService('Lesson')->isAvailable($this->curent_user_id, $this->lesson_id, $this->subject_id)
		){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('У Вас нет доступа к этому занятию.')));			
			$this->_helper->redirector->gotoSimple('card', 'index', 'subject', array('subject_id' 	=> $this->subject_id));			
		} 
		
		$this->_serviceJournal  = $this->getService('LessonJournal');
		$this->_serviceJResult  = $this->getService('LessonJournalResult');
		
		
	
		return parent::init();		
	}
	
	public function indexAction(){
		$this->getService('Unmanaged')->setHeader($this->lesson->title);
		$this->view->dayList 			= $this->_serviceJournal->getDayList($this->lesson_id);
		$journalResult					= $this->_serviceJResult->getJournalResult($this->lesson_id);
		$this->view->journalResultUser	= $journalResult[$this->curent_user_id];
		$this->view->userMark 			= $this->getService('LessonAssign')->getLessonScore($this->lesson_id, $this->curent_user_id); 
	}
	
	
	public function extendedAction()
	{		
		$this->_serviceJournal->generateDefaultDays($this->lesson);
		
		$this->getService('Unmanaged')->setHeader($this->lesson->title);
		
		try {        
		$lessonEvaluatorsService 			= $this->getService('LessonEvaluators');
        $currentUser_InheritsFrom_Tutor 	= $this->_aclService->inheritsRole($this->curent_user_role, HM_Role_RoleModelAbstract::ROLE_TUTOR);
        $currentUser_InheritsFrom_Student 	= $this->_aclService->inheritsRole($this->curent_user_role, HM_Role_RoleModelAbstract::ROLE_STUDENT);
        
		
        
        //дополнительная проверка для студентов и тьюторов, только оценщики имеют сюда доступ
        $currentUserIsEvaluator = false;
        if ($currentUser_InheritsFrom_Student) {
            $evaluatorsCollection = $lessonEvaluatorsService->isEvaluator($this->curent_user_id, $this->lesson_id);
            if ($evaluatorsCollection) {
                $currentUserIsEvaluator = true;
            } else {                
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Не хватает прав доступа.'))
				);
				$this->_redirect('/');
            }
        } else if($currentUser_InheritsFrom_Tutor && !$this->lesson->allowTutors) {            
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не хватает прав доступа.'))
			);
			$this->_redirect('/');
        }
		
		#
		$group    = $this->_getParam('groupname', null);
		
		$this->view->groupname	= $this->getService('Lesson')->createFilterGroupList($this->subject_id, $this->getService('User')->getCurrentUserId());		
		
		if ( $this->_request->isPost() ) {
			Zend_Registry::get('session_namespace_default')->lessonFilter[$this->subject_id] = array('group' => $group);			   
			#if (!$group) {
				#Zend_Registry::get('session_namespace_default')->lessonFilter = null;
				#Zend_Registry::get('session_namespace_default')->lessonFilter[$this->subject_id] = null;			   
			#} else {
			#	Zend_Registry::get('session_namespace_default')->lessonFilter[$this->subject_id] = array('group' => $group);
			#}
		} else {			
			if(!isset(Zend_Registry::get('session_namespace_default')->lessonFilter[$this->subject_id])){				
				# первый элемент массива - это 0 - все группы.
				# Поэтому берем второй
				if(!empty($this->view->groupname)){					
					next($this->view->groupname);
					$group = key($this->view->groupname);										
					//$this->view->current_groupname 	= $group;
				}
			} else {			
				$dates = Zend_Registry::get('session_namespace_default')->lessonFilter[$this->subject_id];           
				$group = $dates['group'];
			}
		}		   
		
		$this->view->current_groupname 	= $group;
		
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
        $select->joinInner(array('st' => 'Students'), 'st.MID = p.MID AND st.CID = '.$this->subject_id);

        $select->where('sch.SHEID = ?', $this->lesson_id);
		
		if(!empty($group)){
			$group_id = (int) preg_replace('~[^0-9]+~','',$group); 
			if(!empty($group_id)){
				$select->where('sg.group_id = ?', $group_id);				
			}
		}
		
		# если тьютор		
		if ($currentUser_InheritsFrom_Tutor) {
			$studentIDs = $this->getService('Subject')->getAvailableStudents($this->curent_user_id, $this->subject_id);
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($this->_serviceJournal->quoteInto('p.MID IN (?)', $studentIDs));
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
        $select->where($this->_serviceJournal->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		$select->order('group_name', 'fio');

        //формируем данные для шаблона
        $stmt = $select->query();
		
		
		} catch (Exception $e) {
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
        
	
		$result = $stmt->fetchAll();
		
		$groups = array('show_all' => array(
            'name' => 'Показать всех',
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
                        $groups[$group_id]['name'] = 'Без группы';
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
		
        $this->view->users 		 	= $users;
        $this->view->groups 	 	= $groups; 		
		$this->view->journalResult	= $this->getService('LessonJournalResult')->getJournalResult($this->lesson_id);
		
		
		
		$currentDay = $this->_getParam('day', null);
		$dayListAll = $this->_serviceJournal->getDayList($this->lesson_id); # все дни без фильтрации
		
		if($currentDay == 'all'){
			$this->view->isShowingAll = true; # показаны все дни.
			$firstEmptyDay = false; # все дни, без ограничений
		} elseif(empty($currentDay)){
			$firstEmptyDay = $this->_serviceJournal->getFirstEmptyDay($this->lesson_id); # находим перввый день, в котором нет оценок.
			
			# Если все дни заполнены и пустого дня нет, то берем последний, максимальный.
			$firstEmptyDay = (empty($firstEmptyDay)) ? max(array_keys($dayListAll)) : $firstEmptyDay;
			
		} else {
			$firstEmptyDay = $currentDay;
		}
		
		$dayList 		= $this->_serviceJournal->getLimitedDayList($firstEmptyDay, $dayListAll); # Если указано ограничение в выводе дней, то применяем его		
		$nextCurrentDay = $this->_serviceJournal->getNextCurrentDay($dayList, $dayListAll); # +2 дня от текущего дня.
		$prevCurrentDay = $this->_serviceJournal->getPrevCurrentDay($dayList, $dayListAll); # -2 дня от текущего дня.
		
		$this->view->dayList 			= $dayList;
		$this->view->firstDay 			= min(array_keys($dayListAll));
		$this->view->lastDay 			= max(array_keys($dayListAll));
		$this->view->nextDay 			= $nextCurrentDay;
		$this->view->prevDay 			= $prevCurrentDay;
		$this->view->subject_id			= $this->subject_id;
		$this->view->lesson_id			= $this->lesson_id;
		$this->view->form 				= new HM_Form_Base();
		$this->view->ballWeightPractic 	= $this->_serviceJResult->getWeightPractic($this->lesson);
		
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/marksheet.css');
		
		
			
	}
	

}