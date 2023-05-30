<?php
class Subject_MessageController extends HM_Controller_Action_Subject
{
    
	/**
	 * выводит мотивированное заключение по курсу.
	*/	
    public function motivationAction()
    {
		$subjectId = (int) $this->_getParam('subject_id', 0);		
		
		$subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
		$this->view->setHeader(_('Мотивированное заключение по курсу: ').$subject->name);
		
		$message = $this->getService('WorkloadSheet')->getMotivationMessage($subjectId);
		if(!$message){
			$message = _('У Вас нет мотивированног заключения по этому курсу.');
		}			
		$this->view->message = $message;
	}    
    
}
