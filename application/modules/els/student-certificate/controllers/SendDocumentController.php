<?php
class StudentCertificate_SendDocumentController extends HM_Controller_Action {
	
	protected $_facultet = '';
	protected $_org      = NULL;
	
	public function init(){
		parent::init();
	}
	
	public function indexAction()
	{
		$config           = Zend_Registry::get('config');
		
		$this->view->form = new HM_Form_SendDocument();	
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->view->description = $this->getTypeDescriptions();
		$this->view->fields      = $this->getTypeFields();
	}
	
	
	public function sendAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		
		$data     = array();
		$form     = new HM_Form_SendDocument();
		$request  = $this->getRequest();
		$user     = $this->getService('User')->getCurrentUser();
		$type     = (int)$request->getParam('type');
		
		$this->_org      = $this->getService('Orgstructure');
		
		$return = array(
            'code'    => 0,
            'message' => _('Заполните все поля')
        );
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			return false;
		}
		
		$form->change($type);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			return false;
		}
		
		$allowFields = array(
			'fio_d',
			'email_d',
			'type_name',
			'destination',
			'd_faculty',
			'd_document_series',
			'd_document_number',
			'd_document_issue_date',
			'd_document_issue_by',
			'd_privilege_type',
			'd_privilege_type_name',
			'd_portfolio_link',
		);
		
		foreach($allowFields as $field_name){
			$param = $request->getParam($field_name);
			if(empty($param)){
				$data[$field_name] = false;
				continue;
			}
			
			if($field_name == 'email_d'){
				if(!filter_var($param, FILTER_VALIDATE_EMAIL)){
					$data[$field_name] = false;
					continue;
				}
			}
			
			$data[$field_name] = $param;			
		}
		
		$data['mid_external']          = $user->mid_external;
		$data['type_id']               = $type;		
		$data['type_name']             = HM_StudentCertificate_StudentCertificateModel::getTypeName($type);
		$data['type']                  = $data['type_name'];
		$data['d_privilege_type_name'] = HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeName($data['d_privilege_type']);
		
		if( !empty($data['email_d']) && empty($user->EMail) ){		
			$this->saveEmail($data['email_d']);
		}
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
			$file              = $this->saveDocument($form->u_photo, $type, $user->mid_external);
			$data['file_name'] = $form->u_photo->getFileName();
			
		} else {
			$file = $this->saveDocument($form->u_document, $type, $user->mid_external);
			$data['file_name'] = $form->u_document->getFileName();
		}
		
		
		if(empty($file)){
			$return['message'] = _('Не удалось загрузить файл');
			echo $this->view->notifications(array(array(
				'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' 	=> $return['message']
			)), array('html' => true));
			echo $form->render();
			return false;
		}
		
		$data['document_file_id'] = $file;
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP
		))){			
			$file = $this->saveDocument($form->d_document_file, $type, $user->mid_external);
			if(!empty($file)){
				$data['documents'] = ',' . $file . ',';
			}			
			$newData = $this->save($data);
			if(!$newData){
				$return['message'] = _('Не удалось сохранить данные');
				echo $this->view->notifications(array(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $return['message']
				)), array('html' => true));
				echo $form->render();
				return false;
			}
			
			if(is_array($form->d_document_file->getFileName())){
				foreach($form->d_document_file->getFileName() as $fileName){
					$data['pathDocuments'][] = $fileName;
				}
			} else {
				$data['pathDocuments'][] = $form->d_document_file->getFileName();
			}
		}
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
			$rowset	= $this->_org->fetchAll($this->_org->quoteInto(array('mid = ?'), array($user->MID)), false, null, array('soid'));		
			$row    = $rowset->current();	
			$this->getFacultet($row->soid);
			
			$file_name         = $form->u_photo->getFileName();
			$file_name 	       = $this->getService('StudentCertificate')->renameFile($file_name, $user->LastName.'_'.$user->FirstName.'_'.$user->Patronymic.'_'.HM_StudentCertificate_StudentCertificateModel::getShortNameFacultet($this->_facultet));
			$isUpload 	       = $this->getService('StudentCertificate')->uploadRemoteFtp($file_name);
			if(!$isUpload){
				$return['message'] = _('Не удалось сохранить файл');
				echo $this->view->notifications(array(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $return['message']
				)), array('html' => true));
				echo $form->render();
				return false;
			}
			$data['file_name'] = $file_name;
		}
		
		# отправка письма
		$data['mid_external']    = $user->mid_external;
		$data['isExternalEmail'] = empty($user->EMail) ? true : false;
		
		$isSend = $this->sendEmail($data);
		if(!$isSend){
			$return['message'] = _('Не удалось отправить письмо');
			echo $this->view->notifications(array(array(
				'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' 	=> $return['message']
			)), array('html' => true));
			echo $form->render();
			return false;			
		}
		
		$form = new HM_Form_SendDocument();
		$return['code']    = 1;
		$return['message'] = _('Данные отправлены');
		echo $this->view->notifications(array(array(
			'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' 	=> $return['message']
		)), array('html' => true));
		echo $form->render();
		
	}
	
	# описание для каждого типа документа
	public function getTypeDescriptions()
	{
		$typeComments = HM_StudentCertificate_StudentCertificateModel::getTypesComments();		
		return Zend_Json::encode($typeComments);
	}
	
	# список полей, которые должны быть показаны пользователю
	public function getTypeFields()
	{
		$data = array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_SNILS 						   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO 						   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_photo',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_PASSPORT 					   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_MILITARY_DOC 				   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL 			   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
				'd_faculty',
				'd_document_series',
				'd_document_number',
				'd_document_issue_date',
				'd_document_issue_by',
				'd_privilege_type',
				'd_portfolio_link',
				'd_document_file',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED   => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
				'd_faculty',
				'd_document_series',
				'd_document_number',
				'd_document_issue_date',
				'd_document_issue_by',
				'd_privilege_type',
				'd_portfolio_link',
				'd_document_file',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED => array(
				'fio_d',
				'email_d',
				'type',
				'destination',
				'u_document',
				'd_faculty',
				'd_document_series',
				'd_document_number',
				'd_document_issue_date',
				'd_document_issue_by',
				'd_privilege_type',
				'd_portfolio_link',
				'd_document_file',
			),
		);
		return Zend_Json::encode($data);
	}
	
	private function saveDocument($form_element, $type_id, $mid_external)
	{
		if(!is_subclass_of($form_element, 'Zend_Form_Element_File')){
			return false;
		}
		
		if(!$form_element->isUploaded()){
			return false;
		}
		$file_ids     = array();
		$file_to_path = $form_element->getFileName();
		
		if(is_array($file_to_path)){
			foreach($file_to_path as $path){
				$file_id = $this->getService('StudentCertificate')->saveDocumentFile($mid_external, $path, $type_id);
				if(!$file_id){
					return false;
				}
				$file_ids[$file_id] = $file_id;
			}
		} else {
			$file_id = $this->getService('StudentCertificate')->saveDocumentFile($mid_external, $file_to_path, $type_id);
			if(!$file_id){
				return false;
			}
			$file_ids[$file_id] = $file_id;
		}
		
		$file_ids = array_filter($file_ids);
		if(empty($file_ids)){
			return false;
		}
		
		return implode(',', $file_ids);		
	}
	
	/**
	 * - возвращает факультет студента
	*/
	public function getFacultet($userID = false)
	{		
		$owner = $this->_org->getOne($this->_org->find($userID));
		
		if($owner->soid_external == 'PR_11' || $owner->soid_external == 'ST_11' || $owner->soid_external == '11'){
			return false;
		}
		
		$this->_facultet = $owner->name;
		
		if($owner->owner_soid > 0){			
			$this->getFacultet($owner->owner_soid);
		}
		
		return;
	}
	
	private function save($raw)
	{
		$data = array(
			'StudyCode'           => $raw['mid_external'],
			'Type' 				  => $raw['type_id'],					
			'Destination' 		  => $raw['destination'],					
			'DateCreate' 		  => date('Y-m-d H:i:s'),
			'Faculty' 			  => $raw['d_faculty'],
			'document_series' 	  => $raw['d_document_series'],
			'document_number' 	  => $raw['d_document_number'],
			'document_issue_date' => $raw['d_document_issue_date'],
			'document_issue_by'   => $raw['d_document_issue_by'],
			'privilege_type' 	  => $raw['d_privilege_type'],
			'portfolio_link' 	  => $raw['d_portfolio_link'],
			'documents' 	      => $raw['documents'],
			'document_issue_date' => empty($raw['d_document_issue_date']) ? NULL : date('Y-m-d', strtotime($raw['d_document_issue_date'])),
		);		
		return $this->getService('StudentCertificate')->addStudentCertificate($data);		
	}
	
	
	//--Эта ф-ция дублируется в user контроллере. Позднее ее сделать общей, перенести в сервисный слой модели USER
	public function saveEmail($email){
		
		if(!$email) {
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($email)) {
			return false;
		}
		
		$user = $this->getService('User')->getCurrentUser();
		$user_id = $user->mid_external;
		if(!$user_id) {
			return false;
		}
		
		if($user->EMail != '') { //--Если есть e-mail в профиле, то этот мы не сохраняем
			return false;
		}
		
		$db = $this->getService('User')->getMapper()->getTable()->getAdapter();
		$result = $db->insert('student_ext_emails', array(
			'mid_external'  => $user_id,
			'email'     	=> $email,            
		));
		
		if($result) {			
			$data = array(
				'MID' => $user->MID,
				'EMail' => $email,                    
			);
                    
            $result = $this->getService('User')->update($data);
			
			if($result) {
				//--тут надо обновить кэш профиля юзера.
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	
	/**
	 * - Отправить документ. Отсылаем только студенту, если это тип - фотография
	*/
	public function sendEmail($data)
	{
		$validator   = new Zend_Validate_EmailAddress();		
		$mail        = new Zend_Mail(Zend_Registry::get('config')->charset);
		$subject     = _('Присланный документ');
		$fromEmail   = $data['email_d'];
		$fromName    = $data['fio_d'];
		$toEmail     = $this->getToEmail($data);
		$toEmailCopy = $this->getToEmail($data, true);
		
		if(!$validator->isValid($fromEmail)){
			return false;
		}
		  
		if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
			$subject = _('Отправленный документ');
		} 
			
		if(in_array($data['type_id'], array(
				HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
				HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED, 
				HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED, 
				HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP
		))){
			$subject .= ' от ' . $fromName . ' (' . $data['mid_external'] . ')';
		}
			
			
		$mail->setSubject($subject);
			
		$messageText = '';
		$messageText .= '<b>ФИО:</b> '.$fromName.'<br>';			
		$messageText .= '<b>Email:</b> <a data-student-email="'.$fromEmail.'" href="mailto:'.$fromEmail.'">'.$fromEmail.'</a><br>';
		$messageText .= '<a href="mailto:student-email-begin_'.$fromEmail.'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$messageText .= '<a href="mailto:student-name-begin_'.$fromName.'_student-name-end" style="color: white;" >.</a>';
			
			
		$messageText .= '<b>Тип документа:</b> '.$data['type'].'<br>';
		if(!empty($data['destination'])){
			$messageText .= '<b>Комментарий:</b> '.$data['destination'].'<br>';
		}
			
		if(!empty($data['d_privilege_type_name'])){
			$messageText .= '<b>Вид льготы:</b> '.$data['d_privilege_type_name'].'<br>';
		}
			
		$attachment = $this->createAttachment($data['file_name']);
		if($attachment){
			$mail->addAttachment($attachment);
			$messageText .= '<br />Документ "'.$this->getFileName($data['file_name']).'" во вложении';
		}
			
		if(!empty($data['pathDocuments'])){
			foreach($data['pathDocuments'] as $document_name){
				$attachment = $this->createAttachment($document_name);
				if(!$attachment){ continue; }
				$mail->addAttachment($attachment);
				$messageText .= '<br />Документ основание "'.$this->getFileName($document_name).'" во вложении';
			}				
		}
			
		if($data['isExternalEmail']){
			$messageText .= '<br><b>Примечание:</b> email, который был указан при заказе справки, не является системным и был указан вручную.<br>';				
		}
		
		if(empty($toEmail)){
			return false;
		}
		
		$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);	
		$mail->addTo($toEmail); 
		
		if(!empty($toEmailCopy)){
			$mail->addCc($toEmailCopy);
		}
			
		try {
			$mail->send();
			return true;
        } catch (Zend_Mail_Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			return false;
		}
		return false;
	}
	
	private function createAttachment($file_name)
	{
		$content 					= file_get_contents($file_name); 

		$finfo 						= new finfo(FILEINFO_MIME_TYPE);
		$mime_type 					= $finfo->buffer($content);
		
		$attachment 				= new Zend_Mime_Part($content);					
		$attachment->type 			= $mime_type;
		$attachment->disposition 	= Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding 		= Zend_Mime::ENCODING_BASE64;						
		$attachment->filename 		= $this->getFileName($file_name);
		
		return $attachment;
	}
	
	private function getFileName($file_name)
	{
		$path_info = pathinfo($file_name);
		return $path_info['basename'];
	}
	
	private function getToEmail($data, $copy = false)
	{
		$config  = Zend_Registry::get('config');		
		$toEmail = $config->mail->email->to->default;
		$emails  = HM_StudentCertificate_StudentCertificateModel::getEmailTo();
		if($copy){
			$emails  = HM_StudentCertificate_StudentCertificateModel::getEmailToCopy();
		}
		
		if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_MILITARY_DOC){
			return $emails[$data['type_id']];
		}
		
		if($copy){
			return false;
		}
		
		if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
			return $data['email_d']; # отсылка самому себе
		}
		
		$validator = new Zend_Validate_EmailAddress();
		$user      = $this->getService('User')->getCurrentUser();
		$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);				
		if (strlen($requisite->email) && $validator->isValid($requisite->email)) { # email для филиалов.
			return $requisite->email;
		}
		
		if(!empty($emails[$data['type_id']])){
			return $emails[$data['type_id']];
		}		
		return $toEmail;
	}
	
}



