<?php
class Timetable_TeachersController extends HM_Controller_Action
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
		$this->view->setHeader(_('Расписание преподавателей'));					
	}
    
}