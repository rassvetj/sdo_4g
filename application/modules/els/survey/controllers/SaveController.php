<?php

class Survey_SaveController extends HM_Controller_Action
{
	
	public function indexAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
		
		$form 		= new HM_Form_Vaccination();
		$request	= $this->getRequest();
		$user 		= $this->getService('User')->getCurrentUser();
		$type	 	= intval($request->getParam('type', NULL));
		$status	 	= intval($request->getParam('status', NULL));
		$result 	= array();
		$file 		= array();
		$answer		= array();
		
		if (!$request->isPost() && !$request->isGet()) {
			$result['error'] = _('Неккоректный метод');
			echo Zend_Json::encode($result);
			die;
		}
		
		if($status == HM_Survey_SurveyModel::STATUS_VACCINATION_1){
			$form->getElement('policy_number')->setOptions(array('Required' => true));
			$form->getElement('vaccination_confirm')->setOptions(array('Required' => true));			
		} else {
			$form->getElement('policy_number')->setOptions(array('Required' => false));
			$form->getElement('vaccination_confirm')->setOptions(array('Required' => false));			
		}
		
		
		if (!$form->isValid($request->getParams())) {
			$result['error']      = _('Заполните поля формы');
			$result['error_form'] = $form->getMessages();
			echo Zend_Json::encode($result);
			die;
		}
		
		$file['path'] = $_FILES['vaccination_bid']['tmp_name'];
		
		if(file_exists($file['path'])){		
			$allow_file_types = preg_split( "/(\*|\.|\;|\,|\ )/", $form->vaccination_bid->file_types);
			$allow_file_types = array_filter($allow_file_types);		
			$file['ext']      = pathinfo($_FILES['vaccination_bid']['name'], PATHINFO_EXTENSION);
			$file['name']	  = $_FILES['vaccination_bid']['name'];
			
			if(!in_array($file['ext'], $allow_file_types)){				
				$result['error']      = _('Загружен файл с недопустимым типом');			
				echo Zend_Json::encode($result);
				die;
			}
		}
		
		$answer['status'] 			    = $status;
		$answer['fio'] 					= $user->getName(); #trim($request->getParam('fio', NULL));
		$answer['birth_date'] 			= trim($request->getParam('birth_date', NULL));
		$answer['registration_address'] = trim($request->getParam('registration_address', NULL));
		$answer['passport_series'] 	    = trim($request->getParam('passport_series', NULL));
		$answer['passport_number'] 	    = trim($request->getParam('passport_number', NULL));
		$answer['policy_number'] 	    = trim($request->getParam('policy_number', NULL));
		
		$answer['vaccination_date']     = trim($request->getParam('vaccination_date', NULL));
		$answer['vaccine_name'] 	    = trim($request->getParam('vaccine_name', NULL));
		$answer['vaccine_series'] 	    = trim($request->getParam('vaccine_series', NULL));
		$answer['vaccine_institution']  = trim($request->getParam('vaccine_institution', NULL));
		
		$answer['vaccination_confirm'] 	= (int)$request->getParam('vaccination_confirm', NULL);
		$answer['data_confirm'] 	    = (int)$request->getParam('data_confirm', NULL);
		
		
		/*
		$dt_birth_date = DateTime::createFromFormat('d.m.Y', $answer['birth_date']);
		if(!$dt_birth_date){
			$result['error']      = _('Укажите дату рождения в формате дд.мм.гггг');			
			echo Zend_Json::encode($result);
			die;
		}
		$answer['birth_date']       = $dt_birth_date->format('d.m.Y');
		*/
		/*
		if($status == HM_Survey_SurveyModel::STATUS_VACCINATION_4){
			$dt_vaccination_date = DateTime::createFromFormat('d.m.Y', $answer['vaccination_date']);
			if(!$dt_vaccination_date){
				$result['error']      = _('Укажите дату вакцинации в формате дд.мм.гггг');			
				echo Zend_Json::encode($result);
				die;
			}
			$answer['vaccination_date'] = $dt_vaccination_date->format('d.m.Y');
		}
		*/
		
		if($status == HM_Survey_SurveyModel::STATUS_VACCINATION_1){
			if(empty($answer['vaccination_confirm'])){
				$result['error']      = _('Подтвердите согласие на вакцинацию');			
				echo Zend_Json::encode($result);
				die;
			}
		}
		
		/*
		if(empty($answer['data_confirm'])){
			$result['error']      = _('Подтвердите достоверность предоставленных данных');			
			echo Zend_Json::encode($result);
			die;
		}
		*/
		
		
		$data = array(
			'mid_external'						=> $user->mid_external,
			'type'								=> $type,
			'data'								=> json_encode($answer),					
			'DateCreated' 						=>  new Zend_Db_Expr("NOW()"),					
		);
		
		$isInsert = $this->getService('Survey')->insert($data);	
		
		if(!$isInsert){
			$result['error']      = _('Не удалось сохранить данные');
			echo Zend_Json::encode($result);
			die;
		}
		
		$data['type']   			= $type;
		$data['file']   			= $file;
		$data['fio']    			= $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic;
		$data['status'] 			= $answer['status'];
		$data['vaccination_date'] 	= $answer['vaccination_date'];
		$data['vaccine_name'] 		= $answer['vaccine_name'];
		$data['vaccine_series'] 	= $answer['vaccine_series'];
		
		$isSend = true;
		#$isSend = $this->sendMail($data);
		
		if(!$isSend){			
			$result['error']      = _('Не удалось отправить данные');
			echo Zend_Json::encode($result);			
			die;
		}
		
		$serviceAgreement = $this->getService('StudentNotificationAgreement');
		
		if($serviceAgreement->hasAgreement($user->MID, HM_StudentNotification_Agreement_AgreementModel::TYPE_VACCINATION)){
			$result['error'] = _('Вы уже подтвердили ранее');
			echo Zend_Json::encode($result);
			die;
		}
		
		$isAdd = $serviceAgreement->add($user->MID, HM_StudentNotification_Agreement_AgreementModel::TYPE_VACCINATION);
		
		if( !$isAdd ){
			$result['error'] = _('Не удалось сохранить данные');
			echo Zend_Json::encode($result);
			die;
		}
		
		$result['message'] = _('Информация передана');
		echo Zend_Json::encode($result);			
		die;
    }
	
	public function sendMail($data){
		if(empty($data)){ return false; }
		
		$email_to = HM_Survey_SurveyModel::getEmailToByType($data['type']);
		if(empty($email_to)){
			return false;
		}
		
		$subject	= HM_Survey_SurveyModel::getEmailSubjectByType($data['type']);
		$attachment = $this->addAttachment($data);
		
		$content  = '<p>На сайте СДО студент ' 	. $data['fio'] . ' заполнил(а) анкету на вакцинацию против гриппа.</p><br />';
		$content .= '<p>Статус: ' 				. HM_Survey_SurveyModel::getVaccinationStatusName($data['status']) . '</p>';
		
		if(!empty($data['vaccination_date'])){
			$content .= '<p>Дата вакцинации: ' 		. $data['vaccination_date'] . '</p>';
		}
		if(!empty($data['vaccine_name'])){
			$content .= '<p>Название вакцины: ' 	. $data['vaccine_name'] . '</p>';
		}
		if(!empty($data['vaccine_series'])){
			$content .= '<p>Серия вакцины: ' 		. $data['vaccine_series'] . '</p>';
		}
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->addTo($email_to);
		$mail->setSubject($subject);
		
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setFromToDefaultFrom();
		
		if($attachment){
			$mail->addAttachment($attachment);
			$content .= '<p>Заявление "' . $data['file']['name'] . '" во вложении</p>';
		}		
		$mail->setBodyHtml($content, Zend_Registry::get('config')->charset);
		
		try {
			$mail->send();						
			return true;
		} catch (Zend_Mail_Exception $e) {			
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			return false;
		}
	}
	
	private function addAttachment($data)
	{
		if(empty($data['file']['path'])){ return false; }
		$attachment_content = file_get_contents($data['file']['path']);
		if(empty($attachment_content)){
			return false;
		}
		
		$finfo 						= new finfo(FILEINFO_MIME_TYPE);
		$mime_type 					= $finfo->buffer($attachment_content);
		$attachment 				= new Zend_Mime_Part($attachment_content);					
		$attachment->type 			= $mime_type;
		$attachment->disposition 	= Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding 		= Zend_Mime::ENCODING_BASE64;						
		$attachment->filename 		= $data['file']['name'];
		
		return $attachment;
	}
	
	

}