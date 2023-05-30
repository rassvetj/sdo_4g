<?php
class Recruits_IndexController extends HM_Controller_Action
{
    public function init(){
		$user = $this->getService('User')->getCurrentUser();
		$recruitInfo = $this->getService('Recruits')->getRecruitInfo($user->mid_external);
		if(empty($recruitInfo)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('message' => 'Нет доступной информации для подтверждения.', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect('/');
		}		 
	}
	
	public function indexAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Военно-учетный стол'));
		$form = new HM_Form_Recruits();				
		$this->view->form = $form;
    }
    
   
}