<?php
class MyPayments_SendController extends HM_Controller_Action
{
	
  
    public function questionAction()
    {
		
		$current_user				= $this->getService('User')->getCurrentUser();
		$request 					= $this->getRequest();
		
		$theme_id					= (int)			$request->getParam('theme', 			false); #тема
		$message					= trim(strip_tags(	$request->getParam('message', 			false))); # текст
		$contract_number			= strip_tags(	$request->getParam('contract_number', 	false)); # номер договора
		$total_debt					= strip_tags(	$request->getParam('total_debt', 		false)); # сумма долга
		$contract_date_timestamp	= (int)			$request->getParam('contract_date', 	false); # дата договора timestamp		
		$update_date_timestamp		= (int)			$request->getParam('update_date', 		false); # дата актуализации данных
		$email						= trim(strip_tags(	$request->getParam('email', 		$current_user->EMail))); # email
		
		
		if(empty($message)){
			echo json_encode(array('error' => 1, 'message' => _('Заполните Ваше обращение')));
			die;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($email)) {
			echo json_encode(array('error' => 1, 'message' => _('Укажите корректный email')));
			die;
		}	
		
		
		$data = array(			
			'fio' 				=> $current_user->LastName.' '.$current_user->FirstName.' '.$current_user->Patronymic,
			'email' 			=> empty($email) ? 'email не задан' : $email,
			'theme_id' 			=> $theme_id,
			'message' 			=> $message,
			'contract_number' 	=> $contract_number,
			'total_debt' 		=> empty($total_debt) ? 0 : $total_debt,
			'contract_date' 	=> date('d.m.Y', $contract_date_timestamp),			
			'update_date' 		=> date('d.m.Y', $update_date_timestamp),
		);
		
		$is_send = $this->send($data);
		if(!$is_send){
			echo json_encode(array('error' => 1, 'message' => _('Не удалось отправить вопрос')));
			die;
		}
		
		$this->getService('User')->saveEmail($email, false);
		
		echo json_encode(array('message' => _('Ваше обращение отправлено')));
		die;
	}
	
	
	private function send($data)
	{
		$mail_to 	= HM_MyPayments_MyPaymentsModel::getMailByTheme($data['theme_id']);
		$validator	= new Zend_Validate_EmailAddress();
		
		if (!strlen($mail_to) || !$validator->isValid($mail_to)){
			return false;
		}
		
		$theme_list = HM_MyPayments_MyPaymentsModel::getThemeList();
		$theme_name = $theme_list[$data['theme_id']];
		
		$text  = '';
		$text .= 'Тема: '.$theme_name.'<br />';
		$text .= 'Обращение: '.$data['message'].'<br />';
		$text .= 'ФИО: '.$data['fio'].' ('.$data['email'].')'.'<br />';
		
		$text .= '<a href="mailto:student-email-begin_'.$data['email'].'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$text .= '<a href="mailto:student-name-begin_'.$data['fio'].'_student-name-end" style="color: white;" >.</a>';
		
		$text .= 'Договор №'.$data['contract_number'].' от '.$data['contract_date'].'<br />';
		$text .= 'Сумма долга по данным с сайта: '.$data['total_debt'].' р.'.'<br />';		
		$text .= 'Данные на сайте обновлены '.$data['update_date'].'<br />';
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		
		$is_add_attachment = $this->addAttachment($mail);
		if($is_add_attachment){
			$text .= 'Файлы во вложении'.'<br />';
		}
		
		
		$mail->setFromToDefaultFrom();
		$mail->addTo($mail_to);
		$mail->setSubject(HM_MyPayments_MyPaymentsModel::MAIL_THEME_QUESTION);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);			
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);
		
		try {				
			$mail->send();
        } catch (Zend_Mail_Exception $e) {                
			 return false;
		}		
		return true;
	}
	
	
	private function addAttachment($mail)
	{
		$form = new HM_Form_Question();
		
		#var_dump($form->files);
		#var_dump($form->files->isUploaded());
		#var_dump($form->files->getName());
		#var_dump($form->files->getFileName());
		#var_dump($form->files->receive());
		
		#die;
		
		
		if(empty($form->files)){ return false; }
		
		if(!$form->files->isUploaded()){ return false; }
		if(!$form->files->receive()){ return false; }
		
		$files = $form->files->getFileName();
		
		if(count($files) == 1){
			return $this->addAttachmentOne($files, $mail);			
		}
		
		$has_attachments = false;
		foreach($files as $file){
			if($this->addAttachmentOne($file, $mail)){
				$has_attachments = true;
			}
		}
		return $has_attachments;
	}
	
	private function addAttachmentOne($file_path, $mail)
	{
		if(empty($file_path)){ return false; }
		if(!realpath($file_path)){ return false; }
		
		$content 				= file_get_contents($file_path); 
		if(empty($content)){ return false; }
			
		$finfo 					= new finfo(FILEINFO_MIME_TYPE);
		$mime_type 				= $finfo->buffer($content);
		$path_info 				= pathinfo($file_path);				
		$file_name 				= $path_info['basename'];
		$attachment 			= new Zend_Mime_Part($content);					
		$attachment->type 		= $mime_type;
		$attachment->disposition= Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding 	= Zend_Mime::ENCODING_BASE64;						
		$attachment->filename 	= $file_name;
		$mail->addAttachment($attachment);
		return true;
	}
	
	
}




