<?php
class EntryEvent_IndexController extends HM_Controller_Action_Crud
{
    public function init(){		
		parent::init();		
	}
	
	public function indexAction()
    {			
		
    }
	
	
	public function sendAction()
    {			
		$user_id	= $this->getService('User')->getCurrentUserId();
		$request 	= $this->getRequest();		
		$form 		= new HM_Form_Entry();
		if ($request->isPost() || $request->isGet()) {			
			if ($form->isValid($request->getParams())) {
				$isConfirm = intval($request->getParam('confirm', NULL));
				$event_id  = intval($request->getParam('event_id', NULL));
				
				if($isConfirm && $event_id){
					$hasAgreement = $this->getService('EntryEvent')->hasAgreement($user_id, $event_id);
					
					if($hasAgreement){
						#echo 'Вы уже ранее приняли приглашение';
						$this->_helper->getHelper('FlashMessenger')->addMessage(
							array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
							'message' => _('Заявка уже принята'))
						);						
					} else {					
						$isAdd = $this->getService('EntryEvent')->addAgreement($user_id, $event_id);
						if($isAdd){
							#echo 'Успешно добавлено';
							$this->_helper->getHelper('FlashMessenger')->addMessage(
								array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
								'message' => _('Заявка принята'))
							);
						} else {
							#echo 'Не удалось добавить';
							$this->_helper->getHelper('FlashMessenger')->addMessage(
								array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
								'message' => _('Не удалось подать заявку'))
							);
						}
					}
				}
			}
		}		
		$this->_redirect('/');
		die;
    }
	
	
	
    
   
}