<?php
class Internships_ToController extends HM_Controller_Action
{
    public function init()
    {
		parent::init();
    }
    
	# Китай
	public function chinaAction()
	{		
		$type 		= HM_Internships_InternshipsModel::TYPE_CHINA;
		$type_list	= HM_Internships_InternshipsModel::getTypeListAllow();
		
		if(!array_key_exists($type, $type_list)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Срок подачи заявки на стажировку в Китай истек'))
			);	
			$this->_redirector->gotoSimple('index', 'index', 'internships');
			die;
		}
		
		$this->_redirector->gotoSimple('index', 'index', 'internships', array('type' => $type));
		die;
	}
	
	# Чехия
	public function czechAction()
	{
		$type 		= HM_Internships_InternshipsModel::TYPE_CZECH;
		$type_list	= HM_Internships_InternshipsModel::getTypeListAllow();
		
		if(!array_key_exists($type, $type_list)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Срок подачи заявки на стажировку в Чехию истек'))
			);	
			$this->_redirector->gotoSimple('index', 'index', 'internships');
			die;
		}
		
		$this->_redirector->gotoSimple('index', 'index', 'internships', array('type' => $type));
		die;
	}
	
	# Словения
	public function sloveniaAction()
	{
		$type 		= HM_Internships_InternshipsModel::TYPE_SLOVENIA;
		$type_list	= HM_Internships_InternshipsModel::getTypeListAllow();
		
		if(!array_key_exists($type, $type_list)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Срок подачи заявки на стажировку в Словению истек'))
			);	
			$this->_redirector->gotoSimple('index', 'index', 'internships');
			die;
		}
		
		$this->_redirector->gotoSimple('index', 'index', 'internships', array('type' => $type));
		die;
	}
	
	# Словакия
	public function slovakiaAction()
	{
		$type 		= HM_Internships_InternshipsModel::TYPE_SLOVAKIA;
		$type_list	= HM_Internships_InternshipsModel::getTypeListAllow();
		
		if(!array_key_exists($type, $type_list)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Срок подачи заявки на стажировку в Словакию истек'))
			);	
			$this->_redirector->gotoSimple('index', 'index', 'internships');
			die;
		}
		
		$this->_redirector->gotoSimple('index', 'index', 'internships', array('type' => $type));
		die;
	}
	
    

}