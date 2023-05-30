<?php
//class Certificate_IndexController extends HM_Controller_Action {
class StudentCertificate_IndexController extends HM_Controller_Action {
	
	protected $_studCertService = null;

    protected $_studentCertificateID  = 0;
	protected $_facultet = '';
	
	public function init()
	{
		$this->_helper->getHelper('Redirector')->gotoSimple('index', 'certificate', 'student-certificate');
		die;
		
		
		$this->_studentCertificateID = (int) $this->_getParam('student_certificate_id', 0);
		$this->_studCertService = $this->getService('StudentCertificate');
		
		//--определяем email для отправки письма от студента
		$partName = 'ФАКУЛЬТЕТ ДИСТАНЦИОННОГО ОБУЧЕНИЯ';				
		if($this->getService('Orgstructure')->isInThisPart($partName)){
			$this->_defaultEmailTo = 'do@rgsu.net';
		} else {
			$this->_defaultEmailTo = 'dekanat@rgsu.net';			
		}
		
		if($_SERVER['LOCAL_ADDR'] == '192.168.132.220' || $_SERVER['SERVER_ADDR'] == '192.168.132.220') { //--Если это тестовый сайт.
			$this->_testEmailTo = 'HramovSV@rgsu.net'; //--email, на который будут приходить письма с тестового сайта.
		} else {
			$this->_testEmailTo = false;
		}
		
		parent::init();
	}
	
	
	
	
	public function sendAction()
    {
		
        $this->getHelper('viewRenderer')->setNoRender();
        
        $form = new HM_Form_StudentCertificate();
		
		$return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );

        $request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			
			$request->setParam('fio_c', strip_tags($request->getParam('fio_c')));
			$request->setParam('email_c', strip_tags($request->getParam('email_c')));
			$request->setParam('faculty_c', strip_tags($request->getParam('faculty_c')));
			$request->setParam('type_name', strip_tags($request->getParam('type_name')));
			$request->setParam('type', strip_tags($request->getParam('type')));
			$request->setParam('count', strip_tags($request->getParam('count')));
			$request->setParam('destination', strip_tags($request->getParam('destination')));
			
			$type = ($request->getParam('type') != '') ? $request->getParam('type') : false;
			
			$form->getElement('direction_c'	)->setOptions(array('Required' => false));
			$form->getElement('course_c'	)->setOptions(array('Required' => false));
			#$form->getElement('year_c'		)->setOptions(array('Required' => false));
			$form->getElement('place_c'		)->setOptions(array('Required' => false));
			$form->getElement('employer_c'	)->setOptions(array('Required' => false));
			$form->getElement('date_from'	)->setOptions(array('Required' => false));
			$form->getElement('date_to'		)->setOptions(array('Required' => false));
			
			if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_GIA){				 
				$form->getElement('employer_c')->setOptions(array('Required' => true));												
			} elseif($type == HM_StudentCertificate_StudentCertificateModel::TYPE_LICENSE){
				$form->getElement('direction_c'	)->setOptions(array('Required' => true));
				$form->getElement('course_c'	)->setOptions(array('Required' => true));
				#$form->getElement('year_c'		)->setOptions(array('Required' => true));
				$form->getElement('place_c'		)->setOptions(array('Required' => true));				
			} 
			
			if(in_array($type, array(HM_StudentCertificate_StudentCertificateModel::TYPE_VALIDATION, HM_StudentCertificate_StudentCertificateModel::TYPE_GIA))){
				$form->getElement('place_work')->setOptions(array('Required' => true));
			} else {
				$form->getElement('place_work')->setOptions(array('Required' => false));
			}
			
			
			if(in_array($type, array(HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT))){
				$form->getElement('period')->setOptions(array('Required' => true));
			} else {
				$form->getElement('period')->setOptions(array('Required' => false));
			}
			
			$is_type_grant = false;
			
			if(in_array($type, array(
							HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
							HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED,
							HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED,
							HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP,
			))){
				$is_type_grant = true;
				
				$form->getElement('document_series'		)->setOptions(array('Required' => true));
				$form->getElement('document_number'		)->setOptions(array('Required' => true));
				$form->getElement('document_issue_date'	)->setOptions(array('Required' => true));
				$form->getElement('document_issue_by'	)->setOptions(array('Required' => true));
				$form->getElement('privilege_type'		)->setOptions(array('Required' => true));
				$form->getElement('document_file'		)->setOptions(array('Required' => true));	
				
				$form->getElement('place_work'			)->setOptions(array('Required' => false));					
				$form->getElement('destination'			)->setOptions(array('Required' => false));					
			} else {
				$form->getElement('document_series'		)->setOptions(array('Required' => false));
				$form->getElement('document_number'		)->setOptions(array('Required' => false));
				$form->getElement('document_issue_date'	)->setOptions(array('Required' => false));
				$form->getElement('document_issue_by'	)->setOptions(array('Required' => false));
				$form->getElement('privilege_type'		)->setOptions(array('Required' => false));
				$form->getElement('document_file'		)->setOptions(array('Required' => false));
				
				$form->getElement('destination'			)->setOptions(array('Required' => true));
			}
			
			
			if ($form->isValid($request->getParams())) {
				
				$user = $this->getService('User')->getCurrentUser();
				
				$file_obj = $form->file_c;	
				if ($file_obj->isUploaded()) {					
					$file_name = $file_obj->getFileName();					
				}
				
				$hasError 		= false;
				
				if($is_type_grant){
					$document_file_tmp_name = false;
					$document_file_obj  = $form->document_file;	
					if ($document_file_obj->isUploaded()) {					
						$document_file_tmp_name = $document_file_obj->getFileName();					
					}
					$document_file_id = $this->_studCertService->saveDocumentFile($user->mid_external, $document_file_tmp_name, $type);
					if(empty($document_file_id)){
						$hasError = true;
						$return['code']    = 0;
						$return['message'] = _('Не удалось загрузить файл');
					}
				}
				
				if(!$hasError){
				
					$fio 			= ($request->getParam('fio_c') != '') 								? $request->getParam('fio_c') 			: false;				
					$email 			= filter_var($request->getParam('email_c'), FILTER_VALIDATE_EMAIL) 	? $request->getParam('email_c') 		: false;				
					$faculty 		= ($request->getParam('faculty_c') != '') 							? $request->getParam('faculty_c') 		: false;	
					#$type_name 		= ($request->getParam('type_name') != '') 							? $request->getParam('type_name') 		: false;
					
					$count 			= intval($request->getParam('count')) > 0 							? intval($request->getParam('count')) 	: 1;
					$destination	= ( $request->getParam('destination') != '' ) 						? ($request->getParam('destination')) 	: (NULL);
					
					$Postcode		= ( $request->getParam('Postcode') != '' ) 							? ($request->getParam('Postcode')) 		: (NULL);
					$GroupName		= ( $request->getParam('group_q') != '' ) 							? ($request->getParam('group_q')) 		: (NULL);
					$City			= ( $request->getParam('city_c') != '' ) 							? ($request->getParam('city_c')) 		: (NULL);
					$Street			= ( $request->getParam('street_c') != '' ) 							? ($request->getParam('street_c')) 		: (NULL);
					$Employer		= ( $request->getParam('employer_c') != '' ) 						? ($request->getParam('employer_c')) 	: (NULL);
					$date_from		= ( $request->getParam('date_from') != '' ) 						? ($request->getParam('date_from')) 	: (NULL);
					$date_to		= ( $request->getParam('date_to') != '' ) 							? ($request->getParam('date_to')) 		: (NULL);
					
					
					$directions 	= $this->getService('StudentCertificate')->getDirectionList();
					$direction_id 	= intval($request->getParam('direction_c'));
					$Direction		= isset($directions[$direction_id]) 								? ($directions[$direction_id]) 			: (NULL);
				
					$Course			= ( $request->getParam('course_c') != '' ) 							? ($request->getParam('course_c')) 		: (NULL);
					$Year			= ( $request->getParam('year_c') != '' ) 							? ($request->getParam('year_c')) 		: (NULL);
					$Submission		= ( $request->getParam('place_c') != '' ) 							? ($request->getParam('place_c')) 		: (NULL);				
					$FileInfo 		= ''; # отправляем только в письме. На сервере не храним.
					
					$place_work		= ( $request->getParam('place_work') != '' ) 						? ($request->getParam('place_work')) 	: (NULL);
					$period			= ( $request->getParam('period') != '' ) 							? ($request->getParam('period')) 		: (NULL);
					
					
					
					$document_series		= ( $request->getParam('document_series') 		!= '' ) 	? ($request->getParam('document_series')) 		: (NULL);
					$document_number		= ( $request->getParam('document_number') 		!= '' ) 	? ($request->getParam('document_number')) 		: (NULL);
					$document_issue_date	= ( $request->getParam('document_issue_date') 	!= '' ) 	? ($request->getParam('document_issue_date')) 	: (NULL);
					$document_issue_by		= ( $request->getParam('document_issue_by') 	!= '' ) 	? ($request->getParam('document_issue_by')) 	: (NULL);
					$privilege_type			= ( $request->getParam('privilege_type') 		!= '' ) 	? ($request->getParam('privilege_type')) 		: (NULL);
					$privilege_date			= ( $request->getParam('privilege_date') 		!= '' ) 	? ($request->getParam('privilege_date')) 		: (NULL);
					#$period			= ( $request->getParam('document_file') != '' ) 				? ($request->getParam('document_file')) 		: (NULL);
				
				
				
				
					$data = array(
						'StudyCode'		=> str_replace(' ', '', $user->mid_external), //--На случай, если встретится код вида "XXX XXX".
						'Type' 			=> $type,					
						'Destination' 	=> $destination,					
						'DateCreate' 	=> date('Y-m-d H:i:s'),
						'Faculty' 		=> $faculty,
						'GroupName' 	=> $GroupName,
						'Postcode' 		=> $Postcode,
						'City' 			=> $City,
						'Street' 		=> $Street,
						'Employer' 		=> $Employer,					
						'Direction' 	=> $Direction,
						'Course' 		=> $Course,
						'Year' 			=> $Year,
						'Submission' 	=> $Submission,
						'Number'		=> $count,
						'place_work'	=> $place_work,
						'period'		=> $period,
						
						'document_series' 		=> $document_series,
						'document_number' 		=> $document_number,
						'document_issue_date' 	=> $document_issue_date,
						'document_issue_by' 	=> $document_issue_by,
						'privilege_type' 		=> $privilege_type,
						'privilege_date' 		=> $privilege_date,
						'document_file_id' 		=> $document_file_id,
					);
				
					if(!empty($date_from)){ $data['date_from'] = date('Y-m-d', strtotime($date_from));	}
					if(!empty($date_to))  { $data['date_to']   = date('Y-m-d', strtotime($date_to));	}
					if(!empty($document_issue_date)){ $data['document_issue_date'] 	= date('Y-m-d', strtotime($document_issue_date)); 	}
					if(!empty($privilege_date))		{ $data['privilege_date'] 		= date('Y-m-d', strtotime($privilege_date)); 		}
					
					$isExternalEmail = ($user->EMail != '') ? (false) : (true); //--првоеряем на email в профиле. Если его нет, то в письме уведомляем деканат об этом
					
					$is_send 	= false; //--признак успешной отправки письма.
					$is_create 	= false;
				
				
				
				
					if($fio && $email && $type && $faculty){
						
						if($isExternalEmail){ //--Если это email введенный пользователем вручную, то обновляем его профиль и внешнюю таблицу email-ов
							$this->saveEmail($email);
						}
						
						$result = $this->_studCertService->addStudentCertificate($data);

					

					
						if($result === false){							
						}
						else {
							$is_create = true;
							/*	
							$data = array(	
								'type_id' 		=> $type,
								'type' 			=> $type_name,
								'count' 		=> $count,
								'destination' 	=> $destination,
								'faculty' 		=> $faculty,							
							);						
							*/
							$types = HM_StudentCertificate_StudentCertificateModel::getTypes();
							$data['file_name'] = $file_name; # путь до временного файла						
							$data['type_name'] = $types[$type];
							
							if($is_type_grant){
								$is_send = true;
							} else {
								$is_send = $this->sendEmail($email, $fio, $data, $isExternalEmail);
							}
							#$is_send = true;
						}
					}				
					
					if($is_create){
						if($is_send){
							$return['code']    = 1;
							$return['message'] = _('Заявка успешно отправлена');	
							$form = new HM_Form_StudentCertificate(); //--костыль для обнуления полей формы по умолчанию
						} else {							
							$this->_studCertService->deleteStudentCertificate($result->CertID); //--/Если не удалось отправить письмо, то удаляем запись в БД						
							$return['code']    = 0;
							$return['message'] = _('Не удалось отправить заявку.');						
						}
					} else {
						$return['code']    = 0;
						$return['message'] = _('Не удалось создать заявку.');					
					}
				}
			}			
			echo $this->view->notifications(array(array(
				'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => $return['message']
			)), array('html' => true));
			
		}
		
		echo $form->render();		
			
	}
	
	/**
	 * - Заказ справки. Отылаем в деканат
	*/
	public function sendEmail($fromEmail, $fromName, $data, $isExternalEmail = false) {
		if(!$fromEmail || !$fromName || !$data){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($fromEmail) && $validator->isValid($fromEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
			$messageText = '';
			$messageText .= '<b>ФИО:</b> '.$fromName.'<br>';
			$messageText .= '<b>Email:</b> <a data-student-email="'.$fromEmail.'" href="mailto:'.$fromEmail.'">'.$fromEmail.'</a> ';
			$messageText .= '<a href="mailto:student-email-begin_'.$fromEmail.'_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
			$messageText .= '<a href="mailto:student-name-begin_'.$fromName.'_student-name-end" style="color: white;" >.</a> <br>';
			
			if($data['Destination']){
				$mail->setSubject('Заказ справки');
				$messageText .= '<b>Тип справки:</b> '.$data['type_name'].'<br>';
				$messageText .= '<b>Количество:</b> '.$data['Number'].'<br>';
				$messageText .= '<b>Место назначения:</b> '.$data['Destination'].'<br>';				
			}
			if($data['Faculty'])	{	$messageText .= '<b>Факультет:</b> '				.$data['Faculty'].'<br>';	}
			if($data['GroupName'])	{	$messageText .= '<b>Группа:</b> '					.$data['GroupName'].'<br>';	}
			if($data['Postcode'])	{	$messageText .= '<b>Индекс:</b> '					.$data['Postcode'].'<br>';	}
			if($data['City'])		{	$messageText .= '<b>Город/область/край:</b> '		.$data['City'].'<br>';		}
			if($data['Street'])		{	$messageText .= '<b>Улица, дом, квартира:</b> '		.$data['Street'].'<br>';	}
			if($data['Employer'])	{	$messageText .= '<b>Наименование работодателя:</b> '.$data['Employer'].'<br>';	}
			if($data['Direction'])	{	$messageText .= '<b>Направление:</b> '				.$data['Direction'].'<br>';	}
			if($data['Course'])		{	$messageText .= '<b>Курс:</b> '						.$data['Course'].'<br>';	}
			if($data['Year'])		{	$messageText .= '<b>Год:</b> '						.$data['Year'].'<br>';		}
			if($data['Submission'])	{	$messageText .= '<b>Место представления:</b> '		.$data['Submission'].'<br>';}
			if($data['date_from'])	{	$messageText .= '<b>с:</b> '						.$data['date_from']; 		}
			if($data['date_to'])	{	$messageText .= ' <b>по:</b> '						.$data['date_to'].'<br>';   }
			if($data['place_work'])	{	$messageText .= ' <b>Место работы:</b> '			.$data['place_work'].'<br>';}
			if($data['period'])		{	$messageText .= ' <b>Период:</b> '					.$data['period'].'<br>';	}
			
			
			if(!empty($data['file_name'])){
				$content 	= file_get_contents($data['file_name']); 
				$finfo 		= new finfo(FILEINFO_MIME_TYPE);
				$mime_type 	= $finfo->buffer($content);			
				$path_info = pathinfo($data['file_name']);
				$file_name = $path_info['basename'];
				$messageText .= 'Документ "'.$file_name.'" во вложении';
				
				$attachment = new Zend_Mime_Part($content);					
				$attachment->type = $mime_type;
				$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
				$attachment->filename = $file_name;
				
				$mail->addAttachment($attachment);
			}
			
			if($isExternalEmail){
				$messageText .= '<br><b>Примечание:</b> email, который был указан при заказе справки, не является системным и был указан вручную.<br>';				
			}
			
			#$messageText .= '<hr style="border: none;border-bottom: 1px dotted #ccc;">
		#<p>C любовью, Ваш РГСУ.</p><p style="font-size:12px;line-height:16px;">Call-центр: +7 (495) 748-67-67  |  Приемная комиссия: +7 (495) 748-67-77<br/><a href="http://rgsu.net/">Сайт РГСУ</a>  |  <a href="http://vk.com/rgsu_official">РГСУ Вконтакте</a>  |  <a href="https://twitter.com/RGSU_official">Twitter РГСУ</a>  |  <a href="https://www.facebook.com/rgsu.official">Facebook РГСУ</a></p>';

			
			
			
			
			
			
			$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО


			if($this->_testEmailTo){				
				$mail->addTo($this->_testEmailTo);
			} else {								
				$user = $this->getService('User')->getCurrentUser();
				$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);				
				if (strlen($requisite->email) && $validator->isValid($requisite->email)) { # email для филиалов.
					$toEmail = $requisite->email;
				} else {
					$toEmail = $this->setEmailTo($data['type_id']); //--Если для данногг типа стрпвки задан иной email	
				}
		
				if($toEmail){	$mail->addTo($toEmail); 				}
				else 		{	$mail->addTo($this->_defaultEmailTo);	}
			}	

			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);

			try {
				$mail->send();
				return true;
            } catch (Zend_Mail_Exception $e) {                
				#echo $e->getMessage();
				return false;
            }
		}		
		return false;		
	}
	
	
	
	
	
	
	
	
	/**
	 * - Отправить документ. Отсылаем только студенту, если это тип - фотография
	*/
	public function sendEmailWithDocument($fromEmail, $fromName, $data, $isExternalEmail = false) {
		if(!$fromEmail || !$fromName || !$data){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($fromEmail) && $validator->isValid($fromEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
			if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
				$subject = _('Отправленный документ');
			} else {
				$subject = _('Присланный документ');
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
			if($data['destination'] != ''){
				$messageText .= '<b>Комментарий:</b> '.$data['destination'].'<br>';
			}
			
			if(empty($data['d_privilege_type_name'])){
				$messageText .= '<b>Вид льготы:</b> '.$data['d_privilege_type_name'].'<br>';
			}
			
			$attachment = $this->createAttachment($data['file_name']);
			if($attachment){
				$mail->addAttachment($attachment);
				$messageText .= '<br />Документ "'.$this->getFileName($data['file_name']).'" во вложении';
			}
			
			if(!empty($data['documents'])){
				foreach($data['documents'] as $document_name){
					$attachment = $this->createAttachment($document_name);
					if(!$attachment){ continue; }
					$mail->addAttachment($attachment);
					$messageText .= '<br />Документ основание "'.$this->getFileName($document_name).'" во вложении';
				}				
			}
			
			
			
			if($isExternalEmail){
				$messageText .= '<br><b>Примечание:</b> email, который был указан при заказе справки, не является системным и был указан вручную.<br>';				
			}
			
			$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО
			
			if($this->_testEmailTo){
				$mail->addTo($this->_testEmailTo);				
			} else {
				if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
					$mail->addTo($fromEmail); //--отсылка семому себе						
				
				} else {	
				
					if($data['type_id'] == HM_StudentCertificate_StudentCertificateModel::TYPE_MILITARY_DOC){
						$toEmail 		= $this->setEmailTo($data['type_id']);
						$toEmailCopy	= $this->setEmailToCopy($data['type_id']);
					} else {
						$user = $this->getService('User')->getCurrentUser();
						$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);				
						if (strlen($requisite->email) && $validator->isValid($requisite->email)) { # email для филиалов. Если в реквизитах задан email
							$toEmail = $requisite->email;
						} else {
							$toEmail = $this->setEmailTo($data['type_id']);	
						}
					}
					
					if($toEmail){ $mail->addTo($toEmail); 				}
					else 		{ $mail->addTo($this->_defaultEmailTo); }
					
					if($toEmailCopy){
						$mail->addCc($toEmailCopy);
					}
				}
			} 
			
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);	
			try {
				$mail->send();				
				return true;
            } catch (Zend_Mail_Exception $e) {                
				return false;
            }
		}		
		return false;		
	}
	
	
	
	
	
	/**
	 * - Задать вопрос. Отсылаем в центральный офис.
	*/
	public function sendEmailQuestion($fromEmail, $fromName, $question, $groupName, $isExternalEmail = false) {
		if(!$fromEmail || !$fromName || !$question){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($fromEmail) && $validator->isValid($fromEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
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
			
			$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО
			
			if($this->_testEmailTo){				
				$mail->addTo($this->_testEmailTo);
			} else {
				$user = $this->getService('User')->getCurrentUser();
				$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);				
				if (strlen($requisite->email) && $validator->isValid($requisite->email)) { # email для филиалов. Если в реквизитах задан email					
					$mail->addTo($requisite->email);
				} else {
					$mail->addTo($this->_defaultEmailTo);	
				}	
			}
			
			#$subject = 'Вопрос от студента : '.$fromEmail;
			$mail->setSubject('Вопрос от студента');			
            
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);			
			try {
				$mail->send();
				return true;
            } catch (Zend_Mail_Exception $e) {                
				return false;
            }
		}		
		return false;		
	}
	
	
	
	
	public function indexAction()
    {
		
		//$this->view->setContextNavigation('course'); // course - секция меню файла application/settings/context.xml
		
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Все заявки'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		
		$select = $this->_studCertService->getIndexSelect();
		//var_dump($select);
		
		
		$user = $this->getService('User')->getCurrentUser();
		if(empty($user->mid_external)){
			$where = '1=0';
		} else {
			$uCode = str_replace(' ', '', $user->mid_external); //--На случай, если встретится код вида "XXX XXX".		
			$where = $this->_studCertService->quoteInto(array('StudyCode=?'), array($uCode));
		}
		$select->where($where);
	
		$gridId = 'grid';
		
		
		$grid = $this->getGrid(
            $select,
            array(
                'student_certificate_id' => array('hidden' => true),                
                'StudyCode' 	=> array('hidden' => true),				     
				'CertID'	=> array('hidden' => true),
				'Type' 		=> array(
					'title' => _('Тип'),
					'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{Type}}')),
				),   
				'Number'	=> array('title' => _('Кол-во')),                
				'Destination'	=> array('title' => _('Место требования')),                
				'Status' 	=> array('hidden' => true),
				/*
				'Status'	=> array(
					'title' => _('Статус'),
					'callback' => array('function' => array($this, 'updateStatus'), 'params' => array('{{Status}}')),					
				),  
				*/				
				'DateCreate'	=> array(
					'title' => _('Дата'),
					'callback' => array('function' => array($this, 'updateDate'), 'params' => array('{{DateCreate}}'))
				),    
				'Faculty'		=> array('title' => _('Факультет')),                
				'GroupName'		=> array('title' => _('Группа')),                
				'Address'		=> array('title' => _('Адрес')),                
				'Additional'	=> array('title' => _('Дополнительно')), 
				
				
				'Employer' 		=> array('hidden' => true),
				'Direction' 	=> array('hidden' => true),
				'Course' 		=> array('hidden' => true),
				'Year' 			=> array('hidden' => true),
				'Submission' 	=> array('hidden' => true),
				'date_from' 	=> array('hidden' => true),
				'date_to' 		=> array('hidden' => true),
				'place_work' 	=> array('hidden' => true),
				'period' 		=> array('hidden' => true),
				
				'document_series' 		=> array('hidden' => true),
				'document_number' 		=> array('hidden' => true),
				'document_issue_date' 	=> array('hidden' => true),
				'document_issue_by' 	=> array('hidden' => true),
				'privilege_type' 		=> array('hidden' => true),
				'privilege_date' 		=> array('hidden' => true),
				'document_status' 		=> array('hidden' => true),
				'date_update' 			=> array('hidden' => true),
				
            ),
            array(
				'Type' 			=> array('values' => HM_StudentCertificate_StudentCertificateModel::getTypes()),
				'Status' 		=> array('values' => HM_StudentCertificate_StudentCertificateModel::getStatuses()),
                'Destination' 	=> null,                
				'DateCreate' 	=> array('render' => 'DateSmart'),				
				'Faculty' 		=> null,
				'GroupName' 	=> null,
				'Address' 		=> null,
				'Additional' 	=> null,
            ),
            $gridId
        );		
		
		
		$grid->updateColumn('DateCreate', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                //array('date_format' => Zend_Locale_Format::getDateFormat())
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{DateCreate}}')
            )
        )
        );
		
		
		$grid->updateColumn('Additional', array(            
            'callback' => array(
                'function' 	=> array($this, 'updateAdditional'),
                'params' 	=> array(
								'{{Employer}}', '{{Direction}}', '{{Course}}', '{{Year}}', '{{Submission}}', '{{date_from}}', '{{date_to}}', '{{place_work}}', '{{period}}',
								'{{document_series}}', '{{document_number}}', '{{document_issue_date}}', '{{document_issue_by}}', '{{privilege_type}}', '{{privilege_date}}',
								'{{document_status}}', '{{date_update}}'
								)
            )
        ));
		
		
		
		
		
		$content_grid = $grid->deploy();
		
		$form = new HM_Form_StudentCertificate();		
		//$this->view->form = $form;						
		//$content_form = $this->view->render('index/certificateForm.tpl'); //--для виджета и этой формы разные шаблоны tpl
		
		$form_q = new HM_Form_StudentQuestion();
        //$this->view->form_q = $form_q;
		//$content_form_q = $this->view->render('index/questionForm.tpl'); //--для виджета и этой формы разные шаблоны tpl
		
		$form_sd = new HM_Form_StudentSendDocument();
        //$this->view->form_sd = $form_sd;
		//$content_form_sd = $this->view->render('index/sendDocumentForm.tpl'); //--для виджета и этой формы разные шаблоны tpl
		
		
		
		
		// Не аыводит контент в шаблон без явного указания пути и setNoRender
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		
		$this->view->content_grid = $content_grid;		
		
		//$this->view->content_form = $content_form;		
		//$this->view->content_form_q = $content_form_q;		
		//$this->view->content_form_sd = $content_form_sd;		
		
		$this->view->content_form = $form;		
		$this->view->content_form_q = $form_q;		
		$this->view->content_form_sd = $form_sd;		
		
		$content = $this->view->render('index/index.tpl');
		
		echo $content;
		
	}
	
	
	public function descriptionAction(){
		
		$this->getHelper('viewRenderer')->setNoRender();
        		
        $request = $this->getRequest();

        if ($request->isPost() || $request->isGet()) {
			$type = ($request->getParam('type') != '') ? $request->getParam('type') : false;	
			
			//$params = explode('_', $type);
			
			//$type_form = isset($params[0]) ? ($params[0]) : (false);
			//$type_item = isset($params[1]) ? ($params[1]) : (false);
			
			
			
			//if($type) {				
			//if($type_item) {				
			if($type) {				
				$desc_types = HM_StudentCertificate_StudentCertificateModel::getTypesComments();				
				//if($desc_types[$type_item] && $desc_types[$type_item] != '') {
				if($desc_types[$type] && $desc_types[$type] != '') {
					//echo trim($desc_types[$type_item]);
					echo trim($desc_types[$type]);
				}				
				else {					
					echo '';
				}
			}
			
			/*
			if($type_form) {				
				$form_types = HM_StudentCertificate_StudentCertificateModel::getHideShowGroups();				
				if($form_types[$type_form]) {
					echo $form_types[$type_form];
				}
			}
			*/
			
		}
		
	}
	
	public function sendDocumentAction()
    {
		$this->getHelper('viewRenderer')->setNoRender();
		
        $form = new HM_Form_StudentSendDocument();
		
		$request = $this->getRequest();
		
		$type = ($request->getParam('type') != '') ? $request->getParam('type') : false;
			
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){			
			$form->getElement('u_document')->setOptions(array('Required' => false,));					
			$form->getElement('u_photo')->setOptions(array('Required' => true,)); 			
		} else {						
			$form->getElement('u_photo')->setOptions(array('Required' => false,));

			$u_doc = $form->getElement('u_document');
			$u_doc->setOptions(array('Required' => true,));
		}
		
		$file_name 		= false;
		$is_type_grant	= false;
		$hasError 		= false;
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED, 
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP
		))){
			$is_type_grant = true;
			
			$form->getElement('d_document_series'		)->setOptions(array('Required' => true));
			$form->getElement('d_document_number'		)->setOptions(array('Required' => true));
			$form->getElement('d_document_issue_date'	)->setOptions(array('Required' => true));
			$form->getElement('d_document_issue_by'		)->setOptions(array('Required' => true));
			$form->getElement('d_privilege_type'		)->setOptions(array('Required' => true));
			$form->getElement('d_portfolio_link'		)->setOptions(array('Required' => true));
			$form->getElement('d_faculty'				)->setOptions(array('Required' => true));
							
		} else {
			$form->getElement('d_document_series'		)->setOptions(array('Required' => false));
			$form->getElement('d_document_number'		)->setOptions(array('Required' => false));
			$form->getElement('d_document_issue_date'	)->setOptions(array('Required' => false));
			$form->getElement('d_document_issue_by'		)->setOptions(array('Required' => false));
			$form->getElement('d_privilege_type'		)->setOptions(array('Required' => false));
			$form->getElement('d_faculty'				)->setOptions(array('Required' => false));
		}
		
		
		
        $return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );

        //$request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
		
			$request->setParam('fio_d', 		strip_tags($request->getParam('fio_d')));
			$request->setParam('email_d', 		strip_tags($request->getParam('email_d')));
			$request->setParam('type_name', 	strip_tags($request->getParam('type_name')));
			$request->setParam('destination', 	strip_tags($request->getParam('destination')));
			
			
			
			$request->setParam('d_faculty', 			strip_tags($request->getParam('d_faculty')));
			$request->setParam('d_document_series', 	strip_tags($request->getParam('d_document_series')));
			$request->setParam('d_document_number', 	strip_tags($request->getParam('d_document_number')));
			$request->setParam('d_document_issue_date', strip_tags($request->getParam('d_document_issue_date')));
			$request->setParam('d_document_issue_by', 	strip_tags($request->getParam('d_document_issue_by')));
			$request->setParam('d_privilege_type', 		strip_tags($request->getParam('d_privilege_type')));
			$request->setParam('d_privilege_type_name', strip_tags($request->getParam('d_privilege_type_name')));
			$request->setParam('d_portfolio_link', 		strip_tags($request->getParam('d_portfolio_link')));

			
			if ($form->isValid($request->getParams())) {
				
				
				$fio 			= ($request->getParam('fio_d') 			!= '')						? $request->getParam('fio_d') 			: false;				
				$email 			= filter_var($request->getParam('email_d'), FILTER_VALIDATE_EMAIL) 	? $request->getParam('email_d') 		: false;				
				$type_name 		= ($request->getParam('type_name') 		!= '')						? $request->getParam('type_name') 		: false;				
				$destination 	= ( $request->getParam('destination') 	!= '' )						? ($request->getParam('destination')) 	: false;
				
				
				$d_faculty				= ($request->getParam('d_faculty') 				== '')	? false : $request->getParam('d_faculty');
				$d_document_series		= ($request->getParam('d_document_series') 		== '') 	? false : $request->getParam('d_document_series');
				$d_document_number		= ($request->getParam('d_document_number') 		== '') 	? false : $request->getParam('d_document_number');
				$d_document_issue_date	= ($request->getParam('d_document_issue_date') 	== '') 	? false : $request->getParam('d_document_issue_date');
				$d_document_issue_by	= ($request->getParam('d_document_issue_by') 	== '') 	? false : $request->getParam('d_document_issue_by');
				$d_privilege_type		= ($request->getParam('d_privilege_type') 		== '') 	? false : $request->getParam('d_privilege_type');
				$d_privilege_type_name	= ($request->getParam('d_privilege_type_name') 	== '') 	? false : $request->getParam('d_privilege_type_name');
				$d_portfolio_link		= ($request->getParam('d_portfolio_link') 		== '') 	? false : $request->getParam('d_portfolio_link');
				
				
				$user 			= $this->getService('User')->getCurrentUser();
				
				
				$email_data = array(													
					'type_id' 				=> $type,
					'type' 					=> $type_name,
					'destination' 			=> $destination,
					'd_faculty'				=> $d_faculty,
					'd_document_series'		=> $d_document_series,
					'd_document_number'		=> $d_document_number,
					'd_document_issue_date'	=> $d_document_issue_date,
					'd_document_issue_by'	=> $d_document_issue_by,
					'd_privilege_type'		=> $d_privilege_type,
					'd_privilege_type_name'	=> $d_privilege_type_name,
					'd_portfolio_link'		=> $d_portfolio_link,
					'mid_external'			=> str_replace(' ', '', $user->mid_external),
				);
				
				
				$data = array(
					'StudyCode'				=> str_replace(' ', '', $user->mid_external),
					'Type' 					=> $type,					
					'Destination' 			=> $destination,					
					'DateCreate' 			=> date('Y-m-d H:i:s'),
					'Faculty' 				=> $d_faculty,
					'document_series' 		=> $d_document_series,
					'document_number' 		=> $d_document_number,
					'document_issue_date' 	=> $d_document_issue_date,
					'document_issue_by' 	=> $d_document_issue_by,
					'privilege_type' 		=> $d_privilege_type,
					'portfolio_link' 		=> $d_portfolio_link,
				);				
				if(!empty($data['document_issue_date'])){ $data['document_issue_date'] 	= date('Y-m-d', strtotime($data['document_issue_date'])); 	}
					
				
				
				
				
				if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){ 
					$file_obj = $form->u_photo;					
				} else {
					$file_obj = $form->u_document;
				}
				
				
				
				
				if($is_type_grant){
					
					$document_file_obj  	= $form->d_document_file;
					$document_file_tmp_name = false;
					$document_file_ids		= array();
					if ($document_file_obj->isUploaded()) {					
						$document_file_tmp_name = $document_file_obj->getFileName();					
					}
					
					if($document_file_tmp_name){
						if(is_array($document_file_tmp_name)){
							foreach($document_file_tmp_name as $file_tmp_name){
								$document_file_id = $this->_studCertService->saveDocumentFile($user->mid_external, $file_tmp_name, $type);
								if(empty($document_file_id)){
									$hasError 			= true;
									$return['code']    	= 0;
									$return['message'] 	= _('Не удалось загрузить файл');
									break;
								}
								$document_file_ids[] 		= $document_file_id;
								$email_data['documents'][] 	= $file_tmp_name;
							}
						} else {
							$document_file_id = $this->_studCertService->saveDocumentFile($user->mid_external, $document_file_tmp_name, $type);
							if(empty($document_file_id)){
								$hasError 			= true;
								$return['code']    	= 0;
								$return['message'] 	= _('Не удалось загрузить файл');							
							}
							$document_file_ids[] 		= $document_file_id;
							$email_data['documents'][] 	= $document_file_tmp_name;
						}
					}
					$document_file_ids = array_filter($document_file_ids);
					if(!empty($document_file_ids)){
						$data['documents'] = ','.implode(',',$document_file_ids).',';
					}
					
					
					if ($file_obj->isUploaded()) {					
						$file_name 			= $file_obj->getFileName();
						$document_file_id 	= $this->_studCertService->saveDocumentFile($user->mid_external, $file_name, $type);	
						if(empty($document_file_id)){
							$hasError 			= true;
							$return['code']    	= 0;
							$return['message'] 	= _('Не удалось загрузить файл');							
						}	
						if(!empty($document_file_id)){
							$data['document_file_id'] = $document_file_id;
						}
					}
					
					if(!$hasError){
						$result = $this->_studCertService->addStudentCertificate($data);
					}					
				}
				
				if(!$hasError){
					if ($file_obj->isUploaded()) {					
						$file_name = $file_obj->getFileName();
					}	
					
					
					$isExternalEmail	= ($user->EMail != '') ? (false) : (true); //--првоеряем на email в профиле. Если его нет, то в письме уведомляем деканат об этом
					$uCode 				= str_replace(' ', '', $user->mid_external); //--На случай, если встретится код вида "XXX XXX".
					
					$is_send = false; //--признак успешной отправки письма.
					
					if($fio && $email && $type_name && $file_name){
						
						if($isExternalEmail){ //--Если это email введенный пользователем вручную, то обновляем его профиль и внешнюю таблицу email-ов
							$this->saveEmail($email);
						}
						
						$isUpload = true;
						if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO){
							//--определяем факультет студента
							$this->_org 		= $this->getService('Orgstructure');
							$this->_facultet 	= '';
							$rowset 			= $this->_org->fetchAll($this->_org->quoteInto(array('mid = ?'), array($user->MID)), false, null, array('soid'));		
							$row 				= $rowset->current();						
							$this->getFacultet($row->soid);
							
							$file_name 	= $this->_studCertService->renameFile($file_name, $user->LastName.'_'.$user->FirstName.'_'.$user->Patronymic.'_'.HM_StudentCertificate_StudentCertificateModel::getShortNameFacultet($this->_facultet));
							$isUpload 	= $this->_studCertService->uploadRemoteFtp($file_name);						
						}					
						
						if($isUpload){
							$email_data['file_name'] 	= $file_name;
							$is_send 			= $this->sendEmailWithDocument($email, $fio, $email_data, $isExternalEmail);
						}
						
						unlink($file_name); //--Если удалить файл, то ругается валидатор формы по загрузке документа, что файл удален.					
					}				
					
					if(!$isUpload){
						$return['code']    = 0;
						$return['message'] = _('Не удалось загрузить файл.');	
					} elseif($is_send){
						$return['code']    = 1;
						$return['message'] = _('Документ успешно отправлен');											
						$form->destination->setValue('');
					} else {							
						//$this->_studCertService->deleteStudentCertificate($result->CertID); //--/Если не удалось отправить письмо, то удаляем запись в БД					
						$return['code']    = 0;
						$return['message'] = _('Не удалось отправить документ.');						
					}
				}
			}	
			
			
			
			echo $this->view->notifications(array(array(
				'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' 	=> $return['message']
			)), array('html' => true));
			
		}
		
		echo $form->render();
	}
	
	public function questionsendAction()
    {
		
        $this->getHelper('viewRenderer')->setNoRender();
        
        
        $form = new HM_Form_StudentQuestion();
				
        $return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );

        $request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			
			$request->setParam('fio_q', strip_tags($request->getParam('fio_q')));
			$request->setParam('email_q', strip_tags($request->getParam('email_q')));
			$request->setParam('question', strip_tags($request->getParam('question')));
			
            $systemInfo = $request->getParam('systemInfo');
			if ($form->isValid($request->getParams())) {
				
				$fio = ($request->getParam('fio_q') != '') ? $request->getParam('fio_q') : false;				
				$email = filter_var($request->getParam('email_q'), FILTER_VALIDATE_EMAIL) ? $request->getParam('email_q') : false;				
				
				$question 	= ( $request->getParam('question') != '' ) ? ($request->getParam('question')) : (false);
				$groupName 	= ( $request->getParam('group_q') != '' ) ? ($request->getParam('group_q')) : (false);
				
				
				$user = $this->getService('User')->getCurrentUser();
				$isExternalEmail = ($user->EMail != '') ? (false) : (true); //--првоеряем на email в профиле. Если его нет, то в письме уведомляем деканат об этом
				
				$cuid = $this->getService('User')->getCurrentUserId();
				
				$is_send = false; //--признак успешной отправки письма.				
				
				if($fio && $email && $question){
						
					if($isExternalEmail){ //--Если это email введенный пользователем вручную, то обновляем его профиль и внешнюю таблицу email-ов
						$this->saveEmail($email);
					}
					
					$is_send = $this->sendEmailQuestion($email, $fio, $question, $groupName, $isExternalEmail );
					//$is_send = true;
					
				}								
				
				if($is_send){
					$return['code']    = 1;
					$return['message'] = _('Вопрос успешно отправлен.');	
					$form = new HM_Form_StudentQuestion(); //--костыль для обнуления полей формы по умолчанию
				}
				else {					
					$return['code']    = 0;
					$return['message'] = _('Не удалось отправить вопрос.');						
				}
			
			}			
			echo $this->view->notifications(array(array(
				'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => $return['message']
			)), array('html' => true));
			
		}
		
		echo $form->render();
	}
	
	
	
	public function updateType($type) {
        $types = HM_StudentCertificate_StudentCertificateModel::getTypes();
        return $types[$type];
    }
	
	public function updateStatus($status) {
        $statuses = HM_StudentCertificate_StudentCertificateModel::getStatuses();
        return $statuses[$status];
    }
	
	public function updateDate($date)
    {
		if (!strtotime($date)) return '';
		
        return $date;
    }
	
	public function setEmailTo($type) {
        $emails = HM_StudentCertificate_StudentCertificateModel::getEmailTo();
		
        return $emails[$type];
    }
	
	public function setEmailToCopy($type) {
        $emails = HM_StudentCertificate_StudentCertificateModel::getEmailToCopy();
		
        return $emails[$type];
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
	 * - возвращает факультет студента
	*/
	public function getFacultet($userID = false){		
			
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
	
	
	public function updateAdditional($employer, $direction, $course, $year, $submission, $date_from, $date_to, $place_work, $period,
									$document_series, $document_number, $document_issue_date, $document_issue_by, $privilege_type, $privilege_date, $document_status, $date_update
	){
		$data = array();
		
		if(!empty($employer)){
			$data[] = '<p> <span style="color: #A8A6A6;">Работодатель:</span> '.$employer.'</p>';
		}
		
		if(!empty($direction)){
			$data[] = '<p><span style="color: #A8A6A6;">Направление:</span> '.$direction.'</p>';
		}
		
		if(!empty($course)){
			$data[] = '<p><span style="color: #A8A6A6;">Курс:</span> '.$course.'</p>';
		}
		
		if(!empty($year)){
			$data[] = '<p><span style="color: #A8A6A6;">Год:</span> '.$year.'</p>';
		}
		
		if(!empty($submission)){
			$data[] = '<p><span style="color: #A8A6A6;">Место представления:</span> '.$submission.'</p>';
		}
		
		if(!empty($date_from)){
			$data[] = '<p><span style="color: #A8A6A6;">Период с</span> '.date('d.m.Y', strtotime($date_from)).' <span style="color: #A8A6A6;">по</span> '.date('d.m.Y', strtotime($date_to)).'</p>';
			
		}
		
		if(!empty($place_work)){
			$data[] = '<p><span style="color: #A8A6A6;">Место работы:</span> '.$place_work.'</p>';
		}
		
		if(!empty($period)){
			$data[] = '<p><span style="color: #A8A6A6;">Период начисления стипендии:</span> '.$period.'</p>';
		}
		
		if(!empty($document_series)){
			$data[] = '<p><span style="color: #A8A6A6;">Серия:</span> '.$document_series.'</p>';
		}
		
		if(!empty($document_number)){
			$data[] = '<p><span style="color: #A8A6A6;">Серия:</span> '.$document_number.'</p>';
		}
		
		if(!empty($document_issue_date)){
			$data[] = '<p><span style="color: #A8A6A6;">Дата выдачи:</span> '.date('d.m.Y', strtotime($document_issue_date)).'</p>';
		}
		
		if(!empty($document_issue_by)){
			$data[] = '<p><span style="color: #A8A6A6;">Кем выдан:</span> '.$document_issue_by.'</p>';
		}
		
		if(!empty($privilege_type)){			
			$list_privilege = HM_StudentCertificate_StudentCertificateModel::getPrivilegeTypeList();			
			$data[] = '<p><span style="color: #A8A6A6;">Вид льготы:</span> '.$list_privilege[$privilege_type].'</p>';
		}
		
		if(!empty($privilege_date)){
			$data[] = '<p><span style="color: #A8A6A6;">Срок действия льготы:</span> '.date('d.m.Y', strtotime($privilege_date)).'</p>';
		}
		
		if(!empty($document_status)){
			$data[] = '<p><span style="color: #A8A6A6;">Статус:</span> '.$document_status.'</p>';
		}
		
		if(!empty($date_update)){
			$data[] = '<p><span style="color: #A8A6A6;">Изменен:</span> '.date('d.m.Y', strtotime($date_update)).'</p>';
		}
		
		
		if(count($data) > 1){
			array_unshift($data, '<p class="total">Подробно</p>');
		}
		
		return implode('', $data);
		
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
	
}



