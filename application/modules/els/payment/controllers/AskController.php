<?php
class Payment_AskController extends HM_Controller_Action
{
	const MAX_SIZE_ATTACHMENT = 26214400; # 25 Мб - Максимальный размер вложения
	
	#private $_toEmail = 'HramovSV@rgsu.net';
	private $_toEmail = 'findolg@rgsu.net';
	
	
	public function questionAction(){
	
		$this->_redirector	= $this->_helper->getHelper('Redirector'); 
	
		$request = $this->getRequest();
		if (!$request->isPost()){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Неверные данные') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		
		$form = new HM_Form_Ask();
		if (!$form->isValid($request->getParams())){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Заполните поля формы') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;			
		}
		
		$user 			= $this->getService('User')->getCurrentUser();
		$question		= strip_tags($this->_getParam('question', false));
		$theme_id		= (int)$this->_getParam('theme_id', 0);
		
		$theme_list		= $this->getService('Payment')->getThemeList();
		$theme			= $theme_list[$theme_id];
		
		$groups 		= $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		$group_names 	= array();
		if(!empty($groups)){
			foreach($groups as $i){
				$group_names[$i['name']] = $i['name'];
			}
		}
		
		$data = array(
			'fio'		=> $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
			'email' 	=> $user->EMail,
			'group' 	=> implode(', ', $group_names),
			'theme'		=> $theme,
			'question'	=> $question,
			
		);
		
		
		
		
		if(!$form->files){			
			if($this->send($data)){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Вопрос успешно отправлен') ));
				$this->_redirector->gotoSimple('index', 'index', 'payment');
				die;
			}
			
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось отправить сообщение') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		if(!$form->files->isUploaded()){			
			if($this->send($data)){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Вопрос успешно отправлен') ));
				$this->_redirector->gotoSimple('index', 'index', 'payment');
				die;
			}
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось отправить сообщение') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		$form->files->receive();
		
		if(!$form->files->isReceived()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось загрузить файлы') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		
		
		$files = $form->files->getFileName();
		if(count($files) <= 1){
			$files = array($files);
		}
		
		$total_size = 0;
		foreach($files as $file){								
			$total_size += filesize($file);
			$data['files'][] =  $file;			
		}
		
		
		if(self::MAX_SIZE_ATTACHMENT < $total_size){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Общий объем файлов не должен превышать 25 Мб. Вопрос не отправлен') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		if($this->send($data)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Вопрос успешно отправлен') ));
			$this->_redirector->gotoSimple('index', 'index', 'payment');
			die;
		}
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось отправить сообщение') ));
		$this->_redirector->gotoSimple('index', 'index', 'payment');
		die;		
	}
	
	
	private function send($data){
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->setSubject('СДО. '.$data['theme']);     
		$messageText = '';
		$messageText .= '<table>';
		$messageText .= '<tr><td><b>Тип вопроса</b></td><td>'.$data['theme'].'</td></tr>';
		$messageText .= '<tr><td><b>Студент</b></td><td>'.$data['fio'].'</td></tr>';
		$messageText .= '<tr><td><b>Группа</b></td><td>'.$data['group'].'</td></tr>';
		$messageText .= '<tr><td><b>Email</b></td><td>'.$data['email'].'</td></tr>';
		$messageText .= '<tr><td><b>Вопрос</b></td><td>'.$data['question'].'</td></tr>';
		$messageText .= '</table>';
		if(!empty($data['files'])){
			$messageText .= '<br />Файлы во вложении.';
		}
		$mail->setFromToDefaultFrom();
		$mail->addTo($this->_toEmail);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);			
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
		
		
		if(empty($data['files'])){
			try {
				$mail->send();
			} catch (Exception $e) {
				return false;
			}	
			return true;
		}
			
		foreach($data['files']as $path){
			if(!file_exists($path)){ continue; }
				
			$content = file_get_contents($path); 
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime_type = $finfo->buffer($content);
			$path_info = pathinfo($path);				
			$file_name = $path_info['basename'];

			$attachment = new Zend_Mime_Part($content);					
			$attachment->type = $mime_type;
			$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
			$attachment->filename = $file_name;
			$mail->addAttachment($attachment);
		}
		try {
			$mail->send();
		} catch (Exception $e) {
			return false;
		}	
		return true;		
	}
	
	
	
	
}