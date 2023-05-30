<?php
class StudentId_IndexController extends HM_Controller_Action
{
	
	public function indexAction()
	{
		/*
		$current_user	= $this->getService('User')->getCurrentUser();
		$is_no_photo	= (basename($current_user->getPhoto()) == 'nophoto.gif') ? true : false;
		
		if($is_no_photo){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Загрузите фотографию в профиле'))
			);			
			$this->_redirector->gotoSimple('index', 'edit', 'user');		
			die;
		}
		*/
		
		
		$current_user		= $this->getService('User')->getCurrentUser();	
		$recordbook_number	= $this->getService('RecordCard')->getRecordbookNumber($current_user->mid_external);
		$isStudent			= empty($recordbook_number) ? false : true;
		
		if(!$isStudent){
			$user_info		= $this->getService('UserInfo')->getByCode($current_user->mid_external);
			if(empty($user_info->fio_dative)){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Файл не сформирован, т.к. не заполнен падеж ФИО')));
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Обратитесь в деканат').': dekanat@rgsu.net'));
				$this->_redirector->gotoSimple('index', 'index', 'services');
				die;
			}
			
			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Студенческий билет сейчас не доступен. Попробуйте завтра'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');
			die;	
		}
		
		
		header('Location: /student-id/export/pdf/');
		die;
	}
	
}