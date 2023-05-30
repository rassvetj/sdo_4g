<?php
class Journal_IndexController extends HM_Controller_Action_Subject
{
    public function indexAction(){
		$lessonId 	= $this->_getParam('lesson_id', 0);
        $currentId 	= $this->getService('User')->getCurrentUserId();
        $userId 	= $this->_getParam('user_id', $currentId);
        $subjectId 	= $this->_getParam('subject_id', 0);
		
		$lesson = $this->getService('Lesson')->find($lessonId)->current();				
		$this->getService('Unmanaged')->setHeader(_('Журнал: ').$lesson->title);
		
		$this->view->mark = $this->getService('LessonAssign')->getLessonScore($lessonId, $currentId);

        $subjectService = $this->getService('Subject');
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR ))){
			# редирект на расширенную версию журнала со всеми студентами.
			$this->_redirector->gotoSimple('extended', 'result', 'journal',
				array(
					'lesson_id' 	=> $lessonId,
					'subject_id' 	=> $subjectId,						
				)
			);			
		}
	}
	
	public function saveAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		
		$lessonId 	= $this->_getParam('lesson_id', 0);
		$subjectId 	= $this->_getParam('subject_id', 0);
		$currentId 	= $this->getService('User')->getCurrentUserId();
		
		$request = $this->getRequest();
		$postData = $request->getPost();
		if(empty($postData)){
			$this->_flashMessenger->addMessage(array(
					'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
					'message' 	=> _('Нет данных для изменения.')
			));
			$this->_redirector->gotoSimple('extended', 'result', 'journal',
				array(
					'lesson_id' 	=> $lessonId,
					'subject_id' 	=> $subjectId,						
				)
			);
			die;
		}
		$isChange = false;
		foreach($postData as $name => $val){						
			if (strpos($name, 'ball_') !== false) {
				$user_id = intval(str_replace('ball_', '', $name));
				if($user_id && $val >= 0 ){
					$this->getService('LessonAssign')->setUserScore($user_id, $lessonId, $val);					
					$isChange = true;
				}				 				
			}						
		}
		
		if($isChange){
			$this->_flashMessenger->addMessage(_('Данные успешно изменены.'));
		} else {
			$this->_flashMessenger->addMessage(array(
					'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
					'message' 	=> _('Нет данных для изменения.')
			));
		}
		$this->_redirector->gotoSimple('extended', 'result', 'journal',
			array(
				'lesson_id' 	=> $lessonId,
				'subject_id' 	=> $subjectId,						
			)
		);
		die;		
	}
}