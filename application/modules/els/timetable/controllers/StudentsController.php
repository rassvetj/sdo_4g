<?php
class Timetable_StudentsController extends HM_Controller_Action
{
    public function init()
    {		
		#if(!$this->getService('User')->isMainOrganization()){
		#	$this->_helper->getHelper('FlashMessenger')->addMessage(
		#		array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
		#			  'message' => _('У вас нет доступа к этому разделу'))
		#	);			
		#	$this->_redirect('/');		
		#}
	}
    
    
    public function indexAction()
    {        
		$this->view->setHeader(_('Расписание занятий'));	

		$this->_redirector = $this->_helper->getHelper('Redirector'); 
		$this->_redirector->gotoSimple('index', 'student', 'timetable');		
		
		
		$groups = $this->getService('StudyGroupUsers')->getUserGroups($this->getService('User')->getCurrentUserId());
		
		$this->view->group_name = '';
		if(!empty($groups)){
			$first_group 	  = reset($groups);
			$this->view->group_name = $first_group['name'];
			$this->view->faculty_name = $first_group['faculty'];
			
		}
		#$this->view->group_name = 'ПДО-Б-03.01-Д-2019-1';
		#$this->view->faculty_name = 'ФАКУЛЬТЕТ ФИЗИЧЕСКОЙ КУЛЬТУРЫ';
		
		# заочка
		if($this->getService('User')->isExtramural()){
			$this->view->url = 'https://rgsu.net/for-students/timetable/extramural/?template=131&group='.$this->view->group_name.'&faculty='.$this->view->faculty_name;
		} else {		
			$this->view->url = 'https://rgsu.net/for-students/timetable/?template=131&group='.$this->view->group_name.'&faculty='.$this->view->faculty_name;
		}
		
	}
    
}