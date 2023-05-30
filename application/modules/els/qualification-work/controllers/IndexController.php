<?php
class QualificationWork_IndexController extends HM_Controller_Action
{
	private $_serviceQualification 	= null;
	
	public function init()
    {
        parent::init();
    }
    
    
    public function indexAction()
    {		
		
    }
	
	
	# ajax html-контент уведомления
	public function renderNotificationAction()
	{
		$user = $this->getService('User')->getCurrentUser();
		
		if(empty($user)){
			die;
		}
		
		
		if($this->getService('QualificationWorkAgreement')->hasAgreement($user->MID)){
			die;
		}
		
		$qualification_work = $this->getService('QualificationWork')->getByUser($user->mid_external);
		if(empty($qualification_work)){
			die;
		}
		
		$this->view->fio     		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$this->view->form   	 	= new HM_Form_NotificationAgreement();
		$this->view->form_confirm   = new HM_Form_NotificationAgreementConfirm();
		
		$this->view->theme   = $qualification_work->theme;
		$this->view->manager = $qualification_work->manager;
		
		$content = $this->view->render('index/ajax/notification.tpl');
		echo $content;		
		die;
	}
	
	public function saveAgreementAction()
	{
		$is_confirm = (int)$this->_request->getParam('is_confirm', false);
		if(empty($is_confirm)){
			$result['error'] = _('Необходимо подтвердить корректность данных');
			echo Zend_Json::encode($result);
			die;
		}
		
		$result = array();
		
		$user				= $this->getService('User')->getCurrentUser();
		$qualification_work = $this->getService('QualificationWork')->getByUser($user->mid_external);
		
		$data = array(
			'mid' 		  	=> (int)$user->MID,
			'description'	=> '',
			'theme'			=> $qualification_work->theme,
			'manager'		=> $qualification_work->manager,
		);		
		if( !$this->getService('QualificationWorkAgreement')->add($data) ){
			$result['error'] = _('Не удалось сохранить данные');
			echo Zend_Json::encode($result);
			die;
		}
		
		$result['message'] = _('Данные успешно подтверждены');
		echo Zend_Json::encode($result);
		die;
	}
	
	public function sendCorrectDataAction()
	{
		$description = $this->_request->getParam('description', false);
		$result = array();
		
		if(empty($description)){ 
			$result['error'] = _('Заполните поле "Обращение"');
			echo Zend_Json::encode($result);
			die;
		}
		
		$user				= $this->getService('User')->getCurrentUser();
		$qualification_work = $this->getService('QualificationWork')->getByUser($user->mid_external);
		
		$data = array(
			'mid' 		  	=> (int)$user->MID,
			'description' 	=> $description,
			'theme'			=> $qualification_work->theme,
			'manager'		=> $qualification_work->manager,
		);		
		if( !$this->getService('QualificationWorkAgreement')->add($data) ){
			$result['error'] = _('Не удалось сохранить данные');
			echo Zend_Json::encode($result);
			die;
		}
		
		$data['fio'] 	 		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$data['email'] 	 		= $user->EMail;
		$data['manager_email'] 	= $qualification_work->manager_email;
		
		if( !$this->sendEmail($data) ){
			$result['error'] = _('Не удалось отправить уведомление');
			echo Zend_Json::encode($result);
			die;
		}
		
		$result['message'] = _('Данные успешно отправлены');
		echo Zend_Json::encode($result);
		die;
	}
	
	private function getEmailTo()
	{
		return HM_QualificationWork_Agreement_AgreementModel::EMAIL_TO;
	}
	
	
	private function sendEmail($data)
	{
		
		$to 		= $this->getEmailTo();		
		$to_manager = $data['manager_email'];
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($to)) {
			return false;
		}
		
		
		$from_name	= _('Сайт РГСУ');
		$subject	= _('Корректировка данных по ВКР');
		$text 		= '';
		$text 	   .= '<b>Студент</b>: '.$data['fio'];		
		$text      .= '<br /><b>Email студента</b>: <a data-student-email="'.$data['email'].'" href="mailto:'.$data['email'].'">'.$data['email'].'</a>';
		$text      .= '<a href="mailto:student-email-begin_'.$data['email'].'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$text      .= '<a href="mailto:student-name-begin_'.$data['fio'].'_student-name-end" style="color: white;" >.</a>'; 
		$text 	   .= '<br /><b>Тема ВКР</b>: '.$data['theme'];		
		$text 	   .= '<br /><b>Руководитель</b>: '.$data['manager'];
		$text 	   .= '<br /><b>Обращение студента</b>: '.$data['description'];

		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);
		$mail->setFromToDefaultFrom();
		$mail->setSubject($subject);
		
		$mail->addTo($to);		
		
		$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($to_manager)) {
			$mail->addCc($to_manager);
		}
		

		try {	
			$mail->send();
		} catch (Exception $e) {		
			return false;
		}
		return true;
	}
	
	
	
	
	
	
	
    
    
}