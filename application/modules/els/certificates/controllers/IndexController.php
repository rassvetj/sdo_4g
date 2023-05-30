<?php
class Certificates_IndexController extends HM_Controller_Action
{
	
	public function init()
	{
		parent::init();
		
		#$is_filial = $this->getService('User')->isMainOrganization() ? false : true;
		$is_filial = false;
		if($is_filial){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Услуга НЕ доступна для студентов филиалов'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');		
			die;		
		}
	}
	
	
	
	public function confirmingStudentAction()
	{
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
		$this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'themes/rgsu/js/dev.js');
		
		$this->view->setHeader(_('Справка, подтверждающая статус студента'));
		
		$current_user 					= $this->getService('User')->getCurrentUser();		
		$service_certificates 			= $this->getService('CertificatesStudent');
		$this->view->items	  			= $service_certificates->getConfirmingStudentItems($current_user->MID);		
		$this->view->isCanCreateNewItem = $service_certificates->isCanCreateNewConfirmingStudent($current_user->MID, $this->view->items);
		
		
		$this->view->form = new HM_Form_ConfirmingStudent();
		
	}
	
	/**
	 * @return отправка формы в деканат об ошибке в справке 
	 *
	*/
	public function sendErrorAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();		
		$data = array();
		
		$reason = $this->_getParam('reason', false);
		$reason = trim(strip_tags($reason));
		
		if(empty($reason)){
			$data['message'] = _('Заполните поле "Текст"');
			$data['error']   = 1;
			echo Zend_Json::encode($data);
			exit;
		}
		
		
		$mail_to 	  = HM_CertificatesStudent_CertificatesStudentModel::MAIL_TO_ERROR;		
		$mail_subject = _('Ошибка в справке, подтверждающей статус студента');
		$validator    = new Zend_Validate_EmailAddress();
		
        if (!strlen($mail_to) || !$validator->isValid($mail_to)) {
			$data['message'] = _('Не удалось отправить сообщение. Адресат не определен');
			$data['error']   = 1;
			echo Zend_Json::encode($data);
			exit;
		}
		
		$user = $this->getService('User')->getCurrentUser();
		
		$user_mail   = $user->EMail;
		$user_fio    = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$user_group  = $this->getService('StudyGroupUsers')->getUserGroupName($user->MID);
		
		
		
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		
		$text .= '<b>ФИО:</b> '.$user_fio.'<br>';
		$text .= '<b>Группа:</b> '.$user_group.'<br>';
		$text .= '<b>Email:</b> <a href="mailto:'.$user_mail.'">'.$user_mail.'</a><br>';
		
		$text .= '<a href="mailto:student-email-begin_'.$user_mail.'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$text .= '<a href="mailto:student-name-begin_'.$user_fio.'_student-name-end" style="color: white;" >.</a>';
		
		$text .= '<b>Сообщение:</b> '.$reason.'<br>';
		
		$mail->setFromToDefaultFrom();
		$mail->addTo($mail_to);
		$mail->setSubject($mail_subject);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);			
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);
		
		try {
			$mail->send();
			
		} catch (Zend_Mail_Exception $e) {
			$data['message'] = _('Ошибка при отправке сообщения');
			$data['error']   = 1;
			echo Zend_Json::encode($data);
			exit;
        }
		
		$data['message'] = _('Ваше обращение отправлено');
		echo Zend_Json::encode($data);
	    exit;
		
	}
	
}