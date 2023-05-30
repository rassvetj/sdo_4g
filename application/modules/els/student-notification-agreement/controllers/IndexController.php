<?php
class StudentNotificationAgreement_IndexController extends HM_Controller_Action
{
	private $_serviceAgreement 	= null;
   
    public function init()
    {
        parent::init();
    }
    
    
    public function indexAction()
    {
        
    }
	
	
	public function confirmAction()
    {
        
		$isAgree = (int)$this->_request->getParam('debt-agree', false);
		$type	 = (int)$this->_request->getParam('type', false);
		
		if(empty($isAgree)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Необходимо согласиться'),
			));			
			$this->_redirect('/');
			die;
		}
		
		if(!$this->_serviceAgreement) { $this->_serviceAgreement = $this->getService('StudentNotificationAgreement'); }	
		
		$isAdd = $this->_serviceAgreement->add($this->getService('User')->getCurrentUserId(), $type);
		
		if(empty($isAdd)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось сохранить'),
			));		
			$this->_redirect('/');
			die;
		}
		
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(array(
			'type' 		=> HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' 	=> _('Информация сохранена'),
		));		
		$this->_redirect('/');
		die;
    }
	
	
	
	
    
    
}