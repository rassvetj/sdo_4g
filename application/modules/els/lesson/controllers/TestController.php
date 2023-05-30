<?php
class Lesson_TestController extends HM_Controller_Action_Subject
{
    public function init()
    {        
		parent::init();        
    }

    public function resultAction()
    {        
		
		$this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));
        if ($this->_lesson) {
            $this->getService('Unmanaged')->setSubHeader($this->_lesson->title);
        }
		
		$subject_id			= intval($this->_getParam('subject_id', 0));
        $lesson_id 			= intval($this->_getParam('lesson_id', 0));
		
		$serviceUser 		= $this->getService('User');
		$serviceAcl 		= $this->getService('Acl');
		
		$currentUserRole 	= $serviceUser->getCurrentUserRole();
		$currentUserId 		= $serviceUser->getCurrentUserId();
		$isCanSetMark 		= $this->getService('Lesson')->find($lesson_id)->current()->isCanSetMark;
		
		
		if(!$isCanSetMark){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Нельзя выставлять оценки в этом занятии'))
			);
			$this->_redirector->gotoSimple('index', 'list', 'lesson', array('subject_id' => $subject_id));
			die;
		}
		
		$currentUser_InheritsFrom_Tutor = $serviceAcl->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_TUTOR);
		
		$cols = array(
            'MID' 			=> 'p.MID',
            'SID' 			=> 'st.SID',
            'fio' 			=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'group_id' 		=> 'sg.group_id',
            'group_name' 	=> 'sg.name',
            'mark' 			=> 'sch.V_STATUS',            
        );
        
        $select = $serviceUser->getSelect();
        $select->from(array('p' 		=> 'People'), $cols);
        $select->joinLeft(array('sch' 	=> 'scheduleID'), 			'sch.MID 		= p.MID');
        $select->joinLeft(array('sgu' 	=> 'study_groups_users'), 	'sgu.user_id 	= p.MID');
        $select->joinLeft(array('sg'	=> 'study_groups'), 		'sg.group_id 	= sgu.group_id');
		$select->joinInner(array('st'	=> 'Students'), 			'st.MID 		= p.MID AND st.CID = '.$subject_id);
        $select->where('sch.SHEID = ?', $lesson_id);
		
		# если тьютор		
		if ($currentUser_InheritsFrom_Tutor) {
			$studentIDs = $this->getService('Subject')->getAvailableStudents($currentUserId, $subject_id);
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($serviceUser->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}
		$select->where($serviceUser->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
        $select->order('group_name', 'fio');
        $result = $select->query()->fetchAll();
		
		$users	= array();
		$groups = array('show_all' => array(
										'name' => _('Показать всех'),
										'new_count' => 0
						),		
		); //список групп $key = id, $val['name'] = название
		
		foreach ($result as $key => $value) {
            
			$mid = $value['MID'];
            $sid = $value['SID'];

            $result[$key]['group_id'] = intval($result[$key]['group_id']);
            $group_id = 0;
			
			
            $group_id = $result[$key]['group_id'];
			if (empty($groups[$group_id])) {
				$groups[$group_id] = array(
					'name' => $value['group_name'],                    
				);
                if ($group_id == 0) {
					$groups[$group_id]['name'] = _('Без группы');
				}
			}
            

			$result[$key]['card'] = $this->view->cardLink($this->view->url(array(
					'module' 		=> 'user',
					'controller' 	=> 'list',
					'action' 		=> 'view',
					'user_id' 		=> $mid
				)),
				_('Карточка пользователя')
			);
			
			 
             
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
        $this->view->users		= $users;
        $this->view->groups	 	= $groups;
		
		$this->view->lesson_id	= $lesson_id;
		$this->view->subject_id	= $subject_id;
		$this->view->form		= new HM_Form_TestMark();
    }
	
	# проверка на доступность студентов тьютору не выполняется.
	# доступ настраивается через Acl, поэтому проверку на роли не надо делать
	public function changeMarkAction()
	{
		$request 			= $this->getRequest();		
		$subject_id			= (int)$request->getParam('subject_id', false);
		$lesson_id			= (int)$request->getParam('lesson_id', false);
		$student_id			= (int)$request->getParam('student_id', false);
		$ball				= (int)$request->getParam('ball', false);
		
		if(empty($lesson_id)){
			echo json_encode(array('error' => 1, 'message' => _('Не задано занятие')));
			die;
		}
		
		if(empty($student_id)){
			echo json_encode(array('error' => 1, 'message' => _('Не выбран студент')));
			die;
		}
		
		$lesson 		= $this->getService('Lesson')->find($lesson_id)->current();
		
		if(empty($lesson)){
			echo json_encode(array('error' => 1, 'message' => _('Не найдено занятие')));
			die;
		}
		
		$isCanSetMark 	= $lesson->isCanSetMark;
		
		if(!$isCanSetMark){			
			echo json_encode(array('error' => 1, 'message' => _('Нельзя выставлять оценки в этом занятии')));
			die;
		}
		
		$serviceLessonAssign = $this->getService('LessonAssign');
		
		$assign_on_lesson = $serviceLessonAssign->getRow($lesson_id, $student_id);
		if(empty($assign_on_lesson)){
			echo json_encode(array('error' => 1, 'message' => _('Студент не назначен на занятие')));
			die;
		}
		
		$ball_old	= $assign_on_lesson->V_STATUS;
		$ball		= $this->prepareNewBall($ball, $lesson_id, $lesson);
		
		if($ball_old == $ball){
			echo json_encode(array('error' => 1, 'message' => _('Новый балл совпадает с текущим')));
			die;
		}
		
		$data = array(
			'V_STATUS'	=> $ball,
			'MID'		=> (int)$student_id,
			'SHEID'		=> (int)$lesson_id,
			'SSID'		=> (int)$assign_on_lesson->SSID,			
		);
		
		try {
			$isUpdated = $serviceLessonAssign->update($data);
		} catch (Exception $e) {
			echo json_encode(array('error' => 1, 'message' => _('Не удалось изменить балл')));
			die;
		}
		
		if(!$isUpdated){
			echo json_encode(array('error' => 1, 'message' => _('Не удалось сохранить балл')));
			die;
		}
		
		$student 		= $this->getService('User')->getById($student_id);
		$student_fio 	= $student->LastName.' '.$student->FirstName.' '.$student->Patronymic;
		$message 		= _('Балл изменен студенту').' '.$student_fio.' с '.$ball_old.' на '.$ball;
		
		echo json_encode(array('message' => $message, 'additional' => array('ball_new' => $ball)));
		die;
		
	}
	
	# подобная штука дублируется в Interview Controller (formProccess)
	# Сделать общий метод!
	private function prepareNewBall($ball, $lesson_id, $lesson = false)
	{
		$subject	= $this->getService('Lesson')->getSubjectByLesson($lesson_id);
		
		if(!$lesson){
			$lesson = $this->getService('Lesson')->find($lesson_id)->current();
		}
		
		$markType	= $subject->mark_type;						
		if($markType == HM_Mark_StrategyFactory::MARK_BRS){
			if (isset($lesson->max_ball)) {
				switch($lesson->getType()) {				
					default:
					$ball = round($ball * $lesson->max_ball / 100, 2);
				}
			}
		} elseif($markType == HM_Mark_StrategyFactory::MARK_WEIGHT){
			$ball = round($ball, 2);
		}
		return 	$ball;			
	}

    


}