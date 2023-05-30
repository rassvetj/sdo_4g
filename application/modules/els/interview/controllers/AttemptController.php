<?php
class Interview_AttemptController extends HM_Controller_Action_Subject
{
   /**
	 * Добавляет еще одны попытку на прикрепление задания на проверку или выставления оценки приподаавтелем нескольким студентам по группе тудента
	**/
	public function addByGroupAction() {		
        $group_id 			= (int)$this->_getParam('group_id', 0);
        $group_name 		= (string)$this->_getParam('group_name');
        $lesson_id 			= (int)$this->_getParam('lesson_id', 0);		
        $subject_id 		= (int)$this->_getParam('subject_id', 0);		
        $request 			= $this->getRequest();
		$current_user_id 	= $this->getService('User')->getCurrentUserId();
		
		$isAjax = $request->isXmlHttpRequest() ? true : false;
		
		if (!$request->isPost()) {
			$message = _('Некорректные данные');			
			if($isAjax)	{ echo json_encode(array('error' => $message)); }
			else 		{ $this->_flashMessenger->addMessage($message); $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']); }
			die; 
		}
		
		$studentIDs 	  = $this->getService('Subject')->getAvailableStudents($current_user_id, $subject_id);
		$studentIDs_group = $this->getService('StudyGroup')->getUsers($group_id);
		
		if($studentIDs === false)	{ $studentIDs = $studentIDs_group; 								 }
		else 						{ $studentIDs = array_intersect($studentIDs, $studentIDs_group); }
		
		$studentIDs_subject = $this->getService('Student')->getUsersIds($subject_id);
		$studentIDs = array_intersect($studentIDs, $studentIDs_subject);
		
		if(empty($studentIDs)){
			$message = _('Нет доступных студентов в группе').' '.$group_name;			
			if($isAjax)	{ echo json_encode(array('error' => $message)); }
			else 		{ $this->_flashMessenger->addMessage($message); $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']); }
			die; 
		}
		
		
		$updated_users = $this->getService('Lesson')->addAttemptToUsers($studentIDs, $lesson_id);
		
		
		$message = _('Попытка добавлена студентам группы').' "'.$group_name.'"';
		if($isAjax)	{ echo json_encode(array('message' => $message)); }
		else 		{ $this->_flashMessenger->addMessage($message); $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']); }
		die; 
			
    }
    
}