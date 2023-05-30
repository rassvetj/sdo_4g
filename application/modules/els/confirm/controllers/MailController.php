<?php
class Confirm_MailController extends HM_Controller_Action
{
    public function init()
    {
		parent::init();
    }
    
    
    public function indexAction()
    {
		$key		= '330d6a5baa5912bd4ca87a81c45c18ef';		
		$request	= $this->getRequest();
		$user_key	= $request->getParam('v', false);
		$email		= trim($request->getParam('m', false));
		
		if(!empty($email)){
			$email = base64_decode($email);
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = false;
			}
		}
		
		if( $user_key != $key || empty($email) ){
			$this->_redirect('/');
			die;
		}
		
		$serviceUser 	= $this->getService('User');
		$select 		= $serviceUser->getSelect();
		$select->from('mail_confirm', array('confirm_id'));
		$select->where($this->quoteInto('email = ?', $email));
		$row = $select->query()->fetchObject();
		
		if(!empty($row->confirm_id)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => _('Уже подтверждено'))
			);			
			$this->_redirect('/');
			die;
		}
		$data = array(
			'MID' 			=> $serviceUser->getCurrentUserId(),
			'email' 		=> $email,
			'date_created' 	=> new Zend_Db_Expr('NOW()'),
			'ip'			=> $_SERVER['REMOTE_ADDR'],
			'user_agent'	=> $_SERVER['HTTP_USER_AGENT'],
		);
		$tbl 		= new Zend_Db_Table('mail_confirm');
		$tbl->insert($data);
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(
			array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => _('Подтверждение успешно завершено'))
		);			
		$this->_redirect('/');
		die;	
		
	}
	
}




