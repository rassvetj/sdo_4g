<?php
class StudentCertificate_AskQuestionController extends HM_Controller_Action {
	
	public function init(){
		parent::init();
	}
	
	public function indexAction()
	{
		$config           = Zend_Registry::get('config');		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->view->form = new HM_Form_AskQuestion();		
	}
	
	
	public function sendAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		 
        $form    = new HM_Form_AskQuestion();
		$request = $this->getRequest();
		
        $return = array(
            'code'    => 0,
            'message' => _('Заполните все поля')
        );
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			return false;
		}
		
		$request->setParam('fio_q',    strip_tags($request->getParam('fio_q')));
		$request->setParam('email_q',  strip_tags($request->getParam('email_q')));
		$request->setParam('question', strip_tags($request->getParam('question')));
		
		$is_send         = false; //--признак успешной отправки письма.	
		$user            = $this->getService('User')->getCurrentUser();
		$cuid            = $this->getService('User')->getCurrentUserId();
		$fio             = ($request->getParam('fio_q') != '') ? $request->getParam('fio_q') : false;				
		$email           = filter_var($request->getParam('email_q'), FILTER_VALIDATE_EMAIL) ? $request->getParam('email_q') : false;				
				
		$question 	     = ( $request->getParam('question') != '' ) ? ($request->getParam('question')) : (false);
		$groupName 	     = ( $request->getParam('group_q') != '' )  ? ($request->getParam('group_q'))  : (false);		
		$isExternalEmail = ($user->EMail != '')                     ? (false)                          : (true); //--првоеряем на email в профиле. Если его нет, то в письме уведомляем деканат об этом				
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			return false;
		}
				
							
		if($fio && $email && $question){						
			if($isExternalEmail){ //--Если это email введенный пользователем вручную, то обновляем его профиль и внешнюю таблицу email-ов
				$this->saveEmail($email);
			}					
			$is_send = $this->sendEmailQuestion($email, $fio, $question, $groupName, $isExternalEmail );
		}								
		
		if($is_send){
			$return['code']    = 1;
			$return['message'] = _('Вопрос успешно отправлен.');	
			$form = new HM_Form_AskQuestion();
		} else {
			$return['code']    = 0;
			$return['message'] = _('Не удалось отправить вопрос.');						
		}	
					
		echo $this->view->notifications(array(array(
			'type'    => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => $return['message']
		)), array('html' => true));
		
		echo $form->render();		
	}
	
	public function sendEmailQuestion($fromEmail, $fromName, $question, $groupName, $isExternalEmail = false) {
		if(!$fromEmail || !$fromName || !$question){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		$mail      = new Zend_Mail(Zend_Registry::get('config')->charset);
		
        if (!strlen($fromEmail) || !$validator->isValid($fromEmail)) {
			return false;
		}
		
		$toEmail   = $this->getToEmail();		
		$user      = $this->getService('User')->getCurrentUser();
		$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);
		
		$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО
		$mail->setSubject('Вопрос от студента');
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
            
		$messageText = '';
		$messageText .= '<b>ФИО:</b> '.$fromName.'<br>';
		$messageText .= '<b>Группа:</b> '.$groupName.'<br>';
		$messageText .= '<b>Email:</b> <a data-student-email="'.$fromEmail.'" href="mailto:'.$fromEmail.'">'.$fromEmail.'</a><br>';
		$messageText .= '<a href="mailto:student-email-begin_'.$fromEmail.'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$messageText .= '<a href="mailto:student-name-begin_'.$fromName.'_student-name-end" style="color: white;" >.</a>';
		$messageText .= '<b>Вопрос:</b> '.$question.'<br>';
			
		if($isExternalEmail){
			$messageText .= '<br><b>Примечание:</b> email, который был указан при заказе справки, не является системным и был указан вручную.<br>';				
		}
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
		$mail->addTo($toEmail);
		
		try {
			$mail->send();			
        } catch (Zend_Mail_Exception $e) {                
			return false;
		}				
		return true;
	}
	
	private function getToEmail()
	{
		$config   = Zend_Registry::get('config');		
		$partName = 'ФАКУЛЬТЕТ ДИСТАНЦИОННОГО ОБУЧЕНИЯ';
		$toEmail  = $config->mail->email->to->default;
		
		if($this->getService('Orgstructure')->isInThisPart($partName)){
			$toEmail = $config->mail->email->to->fdo;
		}
		return $toEmail;
	}
	
	
	
	
}



