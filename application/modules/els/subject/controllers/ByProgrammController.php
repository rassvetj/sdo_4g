<?php
class Subject_ByProgrammController extends HM_Controller_Action
{
    public function previewAction()
    {
		$user_id   = (int) $this->_getParam('MID', 0);
		$config    = Zend_Registry::get('config');
		$student   = false;
		$programms = false;
		$subjects  = false;
		$languages = $this->getService('Languages')->getAll();
		
		if(empty($user_id)){
			$this->view->error = _('Не выбран студент');
		} else {
			$student            = $this->getService('User')->getById($user_id);					
		}
		
		if(!$student){
			$this->view->error = _('Студент не найден');
		} else {
			$student->group     = $this->getService('StudyGroupUsers')->getUserGroup($student->MID);			
			$student->languages = $this->getService('LanguagesAssignBase')->getByUserCode($student->mid_external);
			
			foreach($student->languages as $language){
				$language->name = $languages->exists('code', $language->language_id)->name;
			}			
			#$student->languages = $student->languages->getList('name', 'semester');	
		
			$programms = $this->getService('ProgrammUser')->getProgramms($user_id);
			$programm  = $programms->exists('id_external', $student->group->programm_id_external);			
			$subjects  = $this->getService('Subject')->getByProgramms($programm->programm_id);
			
			if(!$subjects || !count($subjects)){
				$this->view->error = _('Нет доступных сессий для назначения');
			} else {
				foreach($subjects as $subject){
					$subject->errors      = $this->getService('Student')->getAssignErrorsAsText($user_id, $subject->subid);
					$subject->language    = $languages->exists('code', $subject->language_code)->name;
					$subject->isGraduated = $this->getService('Graduated')->isUserExists($subject->subid, $user_id) ? true : false;
				}		
			}
		}		
		
		
		$this->view->setHeader(_('Сессии студента по программе обучения'));
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		$this->view->headScript()->appendFile($config->url->base.'themes/rgsu/js/dev.js');		
		
		$this->view->student     = $student;
		$this->view->programm    = $programm;
		
		$this->view->subjects 	 = $subjects;
		$this->view->description = $this->view->render('by-programm/partials/description.tpl');
		
		if($this->view->student){
			$this->view->info = $this->view->render('by-programm/partials/info.tpl');
		}
		
		if($this->view->subjects){
			$this->view->items = $this->view->render('by-programm/partials/items.tpl');
		}
		
	}    
	
	public function assignAction()
    {
		
		$user_id     = (int) $this->_getParam('MID', 0);
		$subject_ids = $this->_getParam('cid', array());
		
		$subject_ids = array_map('intval', $subject_ids);
		$subject_ids = array_filter($subject_ids);
		
		if(empty($user_id)){
			$this->_flashMessenger->addMessage(array(
				'message' => _('Не указан студент'), 
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR
			));
			$this->_redirector->gotoSimple('preview', 'by-programm', 'subject', array('MID' => $user_id));
		}
		
		if(empty($subject_ids)){
			$this->_flashMessenger->addMessage(array(
				'message' => _('Выберите сессию для назначения'), 
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR
			));
			$this->_redirector->gotoSimple('preview', 'by-programm', 'subject', array('MID' => $user_id));
		}
		
		foreach($subject_ids as $subjectId){
			$this->getService('Subject')->assignStudent($subjectId, $user_id);
		}
		
		$this->_flashMessenger->addMessage(array(
			'message' => _('Назначения выполнены'), 
			'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS
		));
		$this->_redirector->gotoSimple('preview', 'by-programm', 'subject', array('MID' => $user_id));
		
	}  
	
	
    
}
