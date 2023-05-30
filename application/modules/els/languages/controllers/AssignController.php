<?php
class Languages_AssignController extends HM_Controller_Action_Crud
{
    public function init()
	{
		$subject_id	= (int)$this->_getParam('subject_id', 0);
		$subject 	= $this->getService('Subject')->getByid($subject_id);
		if(!$subject->isLanguageLeveling()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Данная сессия не является языковой'))
			);			
			$this->_redirect('/');	
			die;
		}
	}
	
	public function indexAction()
	{		
		$user_id	= $this->getService('User')->getCurrentUserId();
		$subject_id	= (int)$this->_getParam('subject_id', 0);
		
		$available_students = $this->getService('Subject')->getAvailableStudentsAssign($user_id, $subject_id);
		$students 			= $this->getService('Subject')->getAssignedUsers($subject_id);
		$languages			= $this->getService('Languages')->getAll();
		$assigns			= $this->getService('LanguagesAssign')->getBySubject($subject_id); 
		$balls				= $this->getService('SubjectMark')->getBySubject($subject_id);
		$subject			= $this->getService('Subject')->getById($subject_id);
		$isCanEdit			= true;
		
		
		$groups = $this->getService('StudyGroupUsers')->fetchAllDependence(array('StudyGroup'), $this->getService('StudyGroupUsers')->quoteInto(
			array('user_id IN (?)'),
			array($students->getList('MID'))
		));
		
		$collection = $this->getService('Lesson')->fetchAllDependenceJoinInner(
            'Assign',
            $this->quoteInto(array('self.CID  = ?', ' AND self.vedomost = ?', ' AND isfree = ?'), array($subject_id, 1, HM_Lesson_LessonModel::MODE_PLAN)),
            'self.order'
        );
		
		
		$this->view->lessons 					= $collection;
		$this->view->hasNotAvailableStudents	= false;
		
		foreach($students as $key => $student){
			if(in_array($student->MID, $available_students)){
				$student->available = true;
			} else {
				$this->view->hasNotAvailableStudents = true;
			}
			
			$student->assign				= $assigns->exists('MID', $student->MID);
			$student->assign->language		= $languages->exists('code', $student->assign->language_code);
			
			$ball 							= $balls->exists('mid', $student->MID);
			$student->ball					= $ball ? $balls->exists('mid', $student->MID)->getBall() : false;
			
			if($student->assign){
				$isCanEdit = false;
			}
			
			$user_groups 	= $groups->exists('user_id', $student->MID)->groups;			
			$student->group = $user_groups ? $user_groups->current()->name : '';			
			
		}
		
		$students 			= $this->getService('LanguagesAssign')->sortByBall($students);
		
		foreach($students as $key => $student){
			$student->recommended			= new HM_Languages_Assign_AssignModel(array());
			$student->recommended->language	= $languages->exists('code', $this->getService('LanguagesAssign')->getRecommendedLanguageCode($student, $students));			
		}
		
		$this->view->isCanEdit 	= $isCanEdit;
		$this->view->students 	= $students;
		$this->view->form 		= new HM_Form_Assign();
		
		$this->view->setHeader(_('Распределение языков'));
		$this->view->setSubHeader($subject->getName());
		
		$this->view->setExtended(
            array(
                'subjectName' 			=> 'Subject',
                'subjectId' 			=> $subject_id,
                'subjectIdParamName' 	=> 'subject_id',
                'subjectIdFieldName' 	=> 'subid',
                'subject' 				=> $subject
            )
        );
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			&& $subject->isLanguageLeveling()
		) {	
			$this->view->addContextMenu(_('Распределение языков'), 'languages', array('subject_id' => $subject_id));
		}
	}
	
	
	
	
	
	public function saveAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		
		$subject_id 		= (int)$this->_request->getParam('subject_id', false);
		$new_language_codes = $this->_request->getParam('current_language_code', array());
		$assigns			= $this->getService('LanguagesAssign')->getBySubject($subject_id);
		$return				= array('error' => 0);
		
		if(empty($subject_id)){
			$return = array('error' => 1, 'message' 	=> _('Не выбран курс'));
		}
		
		if(empty($new_language_codes)){
			$return = array('error' => 1, 'message' 	=> _('Не выбран уровень языка'));
		}
		
		if(empty($return['error'])){
			foreach($new_language_codes as $user_id => $language_code){
				$data = array(
					'MID' 			=> $user_id,
					'CID' 			=> $subject_id,
					'language_code' => $language_code,
				);
				
				$assign = $assigns->exists('MID', $user_id);
				if(!$assign){
					if(empty($data['language_code'])){ continue; }
					$this->getService('LanguagesAssign')->insert($data);
				}
				
				if($assign->language_code == $language_code){ continue; }
				
				$data['assign_id'] = (int)$assign->assign_id;
				$this->getService('LanguagesAssign')->update($data);
			}
		}
		
		if(empty($return['error'])){
			$return = array('error' => 0, 'message'	=> _('Данные сохранены'));
		}
		
		echo json_encode(array(
			'error' => $return['error'],
			'data'  => $this->view->notifications(array(array(
							'type' 		=> $return['error'] == 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
							'message' 	=> $return['message']
						)), array('html' => true)),
		));		
		die;
		
		
	}	
	

	
}