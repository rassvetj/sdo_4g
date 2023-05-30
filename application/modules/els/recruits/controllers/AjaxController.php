<?php

class Recruits_AjaxController extends HM_Controller_Action
{
    public function confirmedAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$serviceRecruit = $this->getService('Recruits');
       
	   
        $status_id 					= $this->_getParam('status_id','');
        $attitude_id 				= $this->_getParam('attitude_id', '');
        $category_id 				= $this->_getParam('category_id', '');
        $recruitment_office_code 	= $this->_getParam('recruitment_office_code', '');        
        $reserve_category_id 		= $this->_getParam('reserve_category_id', '');
        $rank_id 					= $this->_getParam('rank_id', '');
        $profile_id 				= $this->_getParam('profile_id', '');
        $specialty 					= $this->_getParam('specialty', '');
		
		$ranks 		= $serviceRecruit->getRanks();
		$profiles 	= $serviceRecruit->getProfiles();		
		
		$data = array(
			'isConfirmed' 				=> 1,
			'status' 					=> $status_id,
			'attitude' 					=> $attitude_id,
			'category' 					=> $category_id,
			'recruitment_office_code' 	=> $recruitment_office_code,
			'reserve_category' 			=> $reserve_category_id,
			'rank' 						=> $ranks[$rank_id],
			'profile' 					=> $profiles[$profile_id],
			'specialty' 				=> $specialty,
			'date_update' 				=>  new Zend_Db_Expr('NOW()'),
		);
		
		$user = $this->getService('User')->getCurrentUser();
		$serviceRecruit->updateWhere($data, array('mid_external = ?' => $user->mid_external));
		$this->sendEmailConfirmed($user);
		
		$serviceAgreement = $this->getService('StudentNotificationAgreement');
		if(!$serviceAgreement->hasAgreement($user->MID, HM_StudentNotification_Agreement_AgreementModel::TYPE_MILITARY_5, array())){
			if(!$serviceAgreement->add($user->MID, HM_StudentNotification_Agreement_AgreementModel::TYPE_MILITARY_5, array())){
				$this->_flashMessenger->addMessage(array(
					'message' => _('Не удалось сохранить подтверждение'),
					'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				));
			}
		}
		
		$this->_flashMessenger->addMessage(_('Спасибо! Данные успешно сохранены.'));
        $this->_redirect('/');
	    exit;
    }
	
	/**
	 * уведомление студенту.
	*/
	public function sendEmailConfirmed($user) {		
		if( !($user instanceof  HM_User_UserModel)){ return false; }
		$to = $user->EMail;	
		#$to =  'HramovSV@rgsu.net';
		$validator = new Zend_Validate_EmailAddress();		
		if (!$validator->isValid($to)) { return false; }	
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->setSubject('ВУС СДО'); 
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setFromToDefaultFrom();
		$mail->addTo($to);
		
		$messageText =  'Уважаемый(ая) '.$user->LastName.' '.$user->FirstName.''.$user->Patronymic.',';
		$messageText .= '<br> Вам необходимо предоставить в централизованный деканат оригинал документов по военному учету.';
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
		try {
			$mail->send();
			return true;
		} catch (Zend_Mail_Exception $e) {                
			#echo $e->getMessage();
			return false;
		}
	}	

}