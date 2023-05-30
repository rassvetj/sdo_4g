<?php
class StudentCertificate_CertificateController extends HM_Controller_Action {
	
	private $_pdf           = false;
	private $_orderCaption  = 'Заявление на перевод';
	private $_orderFileName = 'transfer_order';
	
	public function init(){
		parent::init();
	}
	
	public function indexAction()
	{
		$config           = Zend_Registry::get('config');
		
		$this->view->form = new HM_Form_Certificate();
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->view->description = $this->getTypeDescriptions();
		$this->view->fields      = $this->getTypeFields();
		
	}
	
	public function getFormAction()
	{
		$request       = $this->getRequest();
		$type          = (int)$request->getParam('type');		
		$form          = $this->getFormByType($type);
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$this->view->description = HM_StudentCertificate_StudentCertificateModel::getDescription($type);
		
		$form->getElement('type')->setValue($type);
		
		echo $form->render();		
		echo $this->view->render('certificate/partials/description.tpl');		
	}
	
	
	public function createAction()
	{
		$request      = $this->getRequest();
		$type         = (int)$request->getParam('type');
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE){
			$this->createAcademicLeaveOrder();
			return false;
		}
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER){
			$this->createTransferOrder();
			return false;
		}
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION){
			$this->createExpulsionOrder();
			return false;
		}
		
		
		
		
		
		$data         = array();
		$user         = $this->getService('User')->getCurrentUser();
		$groups       = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		$groupName    = $groups[0]['name'];
		$organization = trim($request->getParam('organization'));
		$directions   = $this->getService('StudentCertificate')->getDirectionList();
		$direction_id = (int)$request->getParam('direction_c');
		$form         = $this->getFormByType($type);
		
		$this->view->description = HM_StudentCertificate_StudentCertificateModel::getDescription($type);
		
		
		$return = array(
            'code'    => 0,
            'message' => _('Заполните все поля')
        );
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			echo $this->view->render('certificate/partials/description.tpl');
			return false;
		}
		
		$additional['organization'] = $organization;		
		$form->change($type, $additional);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			echo $this->view->render('certificate/partials/description.tpl');
			return false;
		}
		
		$allowFields = array(
			'fio_c','email_c','faculty_c','type','count','destination','place_work','period','Postcode','city_c','street_c','employer_c','date_from','date_to',
			'direction_c','course_c','document_series','document_number','document_issue_date','document_issue_by','privilege_type','privilege_date','place_c',
			'transfer_type', 'organization', 'program', 'direction_desired', 'study_form', 'basis_learning', 'phone', 'faculty', 'signature_type', 'delivery_method',
		);
		
		foreach($allowFields as $field_name){
			$param = $request->getParam($field_name);
			if(empty($param)){
				$data[$field_name] = false;
				continue;
			}
			
			if($field_name == 'email_c'){
				if(!filter_var($param, FILTER_VALIDATE_EMAIL)){
					$data[$field_name] = false;
					continue;
				}
			}
			
			if($field_name == 'count'){
				$param = empty($param) ? 1 : $param;
			}
			
			if(in_array($field_name, array('date_from','date_to','document_issue_date','privilege_date'))){
				$param = date('Y-m-d', strtotime($param));
			}
			
			$data[$field_name] = $param;			
		}
		
		# ниже в коде используются переменные в таком формате, поэтому переопределяем.
		$data['StudyCode']       = $user->mid_external;
		$data['DateCreate']      = date('Y-m-d H:i:s');
		$data['GroupName']       = $groupName;
		$data['Direction']       = $directions[$direction_id];
		$data['type_name']       = HM_StudentCertificate_StudentCertificateModel::getTypeName($type);
		$data['isExternalEmail'] = empty($user->EMail) ? true : false;
		
		$data['Type'] 		 = $data['type'];
		$data['Destination'] = $data['destination'];
		$data['Faculty']     = $data['faculty_c'];
		$data['City']        = $data['city_c'];
		$data['Street']      = $data['street_c'];
		$data['Employer']    = $data['employer_c'];
		$data['Course']      = $data['course_c'];
		$data['Year']        = $data['year_c'];
		$data['Submission']  = $data['place_c'];		
		$data['Number']      = $data['count'];
		
		$data['signature_type']  = $data['signature_type'];
		$data['delivery_method'] = $data['delivery_method'];
		
		if(empty($data['Faculty'])){
			$data['Faculty'] = $data['faculty'];
		}
		
		
		$data['transfer_type_name']     = empty($data['transfer_type'])     ? NULL : HM_StudentCertificate_StudentCertificateModel::getTransferTypeById($data['transfer_type']);
		$data['program_name']           = empty($data['program'])           ? NULL : HM_StudentCertificate_StudentCertificateModel::getProgramById($data['program']);		
		$data['study_form_name']        = empty($data['study_form'])        ? NULL : HM_StudentCertificate_StudentCertificateModel::getStudyFormById($data['study_form']);
		$data['basis_learning_name']    = empty($data['basis_learning'])    ? NULL : HM_StudentCertificate_StudentCertificateModel::getBasisLearningById($data['basis_learning']);
		
		if(!empty($data['direction_desired'])){
			$direction_desired              = $this->getService('StudentCertificate')->getDirectionById($data['direction_desired']);
			$data['direction_desired_name'] = $direction_desired->name;
		}
		
		
		if(in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP,
		))){
			$file = $this->saveDocument($form->document_file, $type, $user->mid_external);
			if(empty($file)){
				$return['message'] = _('Не удалось загрузить документ основание');
				echo $this->view->notifications(array(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $return['message']
				)), array('html' => true));
				echo $form->render();
				echo $this->view->render('certificate/partials/description.tpl');
				return false;
			}			
			$data['document_file_id'] = $file;
		}
		
		if( !empty($data['email_c']) && empty($user->EMail) ){		
			$this->saveEmail($data['email_c']);
		}
		
		$newData = $this->save($data);
		if(!$newData){
			$return['message'] = _('Не удалось сохранить данные');
			echo $this->view->notifications(array(array(
				'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' 	=> $return['message']
			)), array('html' => true));
			echo $form->render();
			echo $this->view->render('certificate/partials/description.tpl');
			return false;
		}
		
		$data['file_name'] = $form->file_c->getFileName();
		if(!empty($data['file_name'])){
			if(!$form->file_c->isUploaded()){
				$return['message'] = _('Не удалось загрузить файл');
				echo $this->view->notifications(array(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $return['message']
				)), array('html' => true));
				echo $form->render();
				echo $this->view->render('certificate/partials/description.tpl');
				return false;
			}
		}
		
		###############
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER_CLAIM){			
			$data['file_order_name'] = $form->file_order->getFileName();
			if(!empty($data['file_order_name'])){
				if(!$form->file_order->isUploaded()){
					$return['message'] = _('Не удалось загрузить заявление');
					echo $this->view->notifications(array(array(
						'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
						'message' 	=> $return['message']
					)), array('html' => true));
					echo $form->render();
					echo $this->view->render('certificate/partials/description.tpl');
					return false;
				}
			}
			
			$data['file_passport_name'] = $form->file_passport->getFileName();
			if(!empty($data['file_passport_name'])){
				if(!$form->file_passport->isUploaded()){
					$return['message'] = _('Не удалось загрузить скан-копию паспорта');
					echo $this->view->notifications(array(array(
						'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
						'message' 	=> $return['message']
					)), array('html' => true));
					echo $form->render();
					echo $this->view->render('certificate/partials/description.tpl');
					return false;
				}
			}
		}
		
		if(!in_array($type, array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED,
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP,
		))){
			$isSend = $this->sendEmail($data);
			if(!$isSend){
				$this->getService('StudentCertificate')->deleteStudentCertificate($newData->CertID); // Не удалось отправить письмо, удаляем запись в БД						
				$return['message'] = _('Не удалось отправить заявку');
				echo $this->view->notifications(array(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $return['message']
				)), array('html' => true));
				echo $form->render();
				echo $this->view->render('certificate/partials/description.tpl');
				return false;
			}
		}
		
		$return['code']    = 1;
		$return['message'] = _('Заявка успешно отправлена');
		$form              = new HM_Form_Certificate();
		echo $this->view->notifications(array(array(
			'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => $return['message']
		)), array('html' => true));
		echo $form->render();
		echo $this->view->render('certificate/partials/description.tpl');
	}
	
	public function generateOrderAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		
		$data         = array();
		$form         = new HM_Form_Certificate();
		$request      = $this->getRequest();
		$type         = (int)$request->getParam('type');
		$organization = trim($request->getParam('organization'));
		
		$return = array(
            'code'    => 0,
            'message' => _('Заполните все поля')
        );
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			return false;
		}
		
		$additional['organization'] = $organization;		
		$form->change($type, $additional);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			return false;
		}
		
		
		$return['code']    = 1;
		$return['message'] = _('Заявление сформировано');
		
		echo $this->view->notifications(array(array(
			'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => $return['message']
		)), array('html' => true));
		echo $form->render();
	}
	
	public function getOrderAction()
	{
		$request   = $this->getRequest();
		$validator = new Zend_Validate_EmailAddress();
		$data      = array();
		$this->_flashMessenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		
		$required_fields = array(
			'phone'             => _('Телефон'),
			'email_c'           => _('Email'),
			'transfer_type'     => _('Тип перевода'),
			'organization'      => _('Организация'),
			'course_c'          => _('Курс'),
			'direction_desired' => _('Желаемое направление подготовки'),
			'program'           => _('Программа обучения'),
			'study_form'        => _('Форма обучения'),
			'basis_learning'    => _('Основа обучения'),
		);
		
		
		
		foreach($required_fields as $field_name => $field_caption){
			
			$value = trim($request->getParam($field_name));
			
			if($field_name == 'direction_desired'){
				$direction_desired = $this->getService('StudentCertificate')->getDirectionById($value);
				$value             = $direction_desired->name;
			}
			
			if($field_name == 'transfer_type'){
				$value = HM_StudentCertificate_StudentCertificateModel::getTransferTypeById($value);
			}
			
			if($field_name == 'program'){
				$value = HM_StudentCertificate_StudentCertificateModel::getProgramById($value);
			}
			
			if($field_name == 'study_form'){
				$value = HM_StudentCertificate_StudentCertificateModel::getStudyFormById($value);
			}
			
			if($field_name == 'basis_learning'){
				$value = HM_StudentCertificate_StudentCertificateModel::getBasisLearningById($value);
			}
			
			if(empty($value)){
				$this->_flashMessenger->addMessage(array(
					'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Заполните поле "' . $field_caption . '"')
				));
				$this->_redirect('/');
				die;
			}
			
			if($field_name == 'email_c'){
				if(!$validator->isValid($value)){
					$this->_flashMessenger->addMessage(array(
						'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Некорректно заполнено поле "' . $field_caption . '"')
					));
					$this->_redirect('/');
					die;
				}
			}
			
			$data[$field_name] = $value;
		}
		
		$this->initPdf();
		$this->generateOrderPdf($data);		
		echo $this->outputOrderPdf();
		die;
	}
	
	private function initPdf()
	{
		require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';
		
		$this->_pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		$this->_pdf->SetProtection(array('copy','modify','annot-forms','fill-forms','extract','assemble'));
		
		$this->_pdf->SetFont('times', 'BI', 20, '', 'false');
		
		$this->_pdf->SetCreator(PDF_CREATOR);
		$this->_pdf->SetAuthor('RGSU');
		$this->_pdf->SetTitle($this->_orderCaption);
		$this->_pdf->SetSubject($this->_orderCaption);
		$this->_pdf->SetKeywords($this->_orderCaption);

		$this->_pdf->setPrintHeader(false);
		$this->_pdf->setPrintFooter(false);
		#$this->_pdf->SetMargins(5, 0, 2);
		$this->_pdf->SetMargins(5, 0, 10);
		$this->_pdf->SetFooterMargin(5);
		$this->_pdf->SetAutoPageBreak(false);
		return true;
	}
	
	private function generateOrderPdf($data)
	{
		$user     = $this->getService('User')->getCurrentUser();
		$userInfo = $this->getService('UserInfo')->getByCode($user->mid_external);		
		
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(15, 5, 15);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		$this->_pdf->addTTFfont('museosanscyrl100', 'TrueTypeUnicode', 'museosanscyrl100.php');
		$this->_pdf->addTTFfont('museosanscyrl300', 'TrueTypeUnicode', 'museosanscyrl300.php');
		$this->_pdf->addTTFfont('museosanscyrl500', 'TrueTypeUnicode', 'museosanscyrl500.php');
		$this->_pdf->addTTFfont('museosanscyrl700', 'TrueTypeUnicode', 'museosanscyrl700.php');
		#$this->_pdf->addTTFfont('museosanscyrl900', 'TrueTypeUnicode', 'museosanscyrl900.php');
		
		$this->_pdf->SetFont('museosanscyrl100', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl300', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl500', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl700', '', 14, '', false);
		#$this->_pdf->SetFont('museosanscyrl900', '', 14, '', false);
		
		
		$this->view->fio_genitive   = $userInfo->fio_genitive;
		$this->view->fio            = $user->getName();
		$this->view->phone          = $data['phone'];
		$this->view->email          = $data['email_c'];
		$this->view->transfer_type  = $data['transfer_type'];
		$this->view->organization   = $data['organization'];
		$this->view->course         = $data['course_c'];
		$this->view->direction      = $data['direction_desired'];
		$this->view->program        = $data['program'];
		$this->view->study_form     = $data['study_form'];
		$this->view->basis_learning = $data['basis_learning'];
		$this->view->day            = date('d');
		$this->view->month          = $this->getHumanMonth(date('n'));
		$this->view->year           = date('Y');
		
		$this->view->url_checkbox_empty   = 'C:/Program Files (x86)/Hypermethod/eLearningServer/public/images/icons/checkbox_empty.png';
		$this->view->url_checkbox_checked = 'C:/Program Files (x86)/Hypermethod/eLearningServer/public/images/icons/checkbox_checked.png';
		
		
		$content = $this->view->render('certificate/export/pdf/transfer_claim.tpl');
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	private function outputOrderPdf()
	{
		return $this->_pdf->Output($this->_orderFileName . '_' . date('Y.m.d_H.i') . '_.pdf', 'I');
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
			HM_StudentCertificate_StudentCertificateModel::TYPE_STUDY_COGNIZANCE      => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_STUDY                 => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT                 => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'period',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_GIA                   => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'place_work',
				'Postcode',
				'city_c',
				'street_c',
				'employer_c',
				'date_from',
				'date_to',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_DOC_EDU               => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_VALIDATION            => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'place_work',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_SOLDIER               => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_LICENSE               => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
				'direction_c',
				'course_c',
				'place_c',			
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_OUT_OF_ORDER          => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'signature_type',
				'delivery_method',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_RECORD_BOOK_TRUE_COPY => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'destination',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP         => array(
				'fio_c',
				'email_c',
				'faculty_c',
				'type',
				'count',
				'document_series',
				'document_number',
				'document_issue_date',
				'document_issue_by',
				'privilege_type',
				'privilege_date',
				'document_file',
				'Postcode',
				'city_c',
				'street_c',
				'file_c',
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER_CLAIM        => array(
				'fio_c',
				'email_c',
				'phone',
				'group',
				'course_c',
				'transfer_type',
				'organization',
				'program',
				'faculty',
				'direction_desired',
				'study_form',
				'basis_learning',
				'btn_get_order',
				'file_order',
				'file_passport',
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
	
	private function save($raw)
	{
		$data = array(
			'StudyCode'           => $raw['StudyCode'],
			'Type' 				  => $raw['Type'],					
			'Destination' 		  => $raw['Destination']       ? $raw['Destination']       : NULL,					
			'DateCreate' 		  => date('Y-m-d H:i:s'),
			'Faculty' 			  => $raw['Faculty']           ? $raw['Faculty']           : NULL,
			'GroupName' 		  => $raw['GroupName']         ? $raw['GroupName']         : NULL,
			'Postcode' 		      => $raw['Postcode']          ? $raw['Postcode']          : NULL,
			'City' 		          => $raw['City']              ? $raw['City']              : NULL,
			'Street' 		      => $raw['Street']            ? $raw['Street']            : NULL,
			'Employer' 		      => $raw['Employer']          ? $raw['Employer']          : NULL,
			'Direction' 		  => $raw['Direction']         ? $raw['Direction']         : NULL,
			'Course' 		      => $raw['Course']            ? $raw['Course']            : NULL,
			'Year' 		          => $raw['Year']              ? $raw['Year']              : NULL,
			'Submission' 		  => $raw['Submission']        ? $raw['Submission']        : NULL,
			'Number' 		      => $raw['Number'],
			'place_work' 		  => $raw['place_work']        ? $raw['place_work']        : NULL,
			'period' 		      => $raw['period']            ? $raw['period']            : NULL,
			'document_series' 	  => $raw['document_series']   ? $raw['document_series']   : NULL,
			'document_number' 	  => $raw['document_number']   ? $raw['document_number']   : NULL,
			'document_issue_by'   => $raw['document_issue_by'] ? $raw['document_issue_by'] : NULL,
			'privilege_type' 	  => $raw['privilege_type']    ? $raw['privilege_type']    : NULL,
			'document_file_id' 	  => $raw['document_file_id'],
			'date_from'           => empty($raw['date_from'])           ? NULL : date('Y-m-d', strtotime($raw['date_from'])),
			'date_to'             => empty($raw['date_to'])             ? NULL : date('Y-m-d', strtotime($raw['date_to'])),
			'document_issue_date' => empty($raw['document_issue_date']) ? NULL : date('Y-m-d', strtotime($raw['document_issue_date'])),
			'privilege_date'      => empty($raw['privilege_date'])      ? NULL : date('Y-m-d', strtotime($raw['privilege_date'])),
			
			'transfer_type'     => $raw['transfer_type']     ? $raw['transfer_type']     : NULL,
			'organization'      => $raw['organization']      ? $raw['organization']      : NULL,
			'program'           => $raw['program']           ? $raw['program']           : NULL,
			'direction_desired' => $raw['direction_desired'] ? $raw['direction_desired'] : NULL,
			'study_form'        => $raw['study_form']        ? $raw['study_form']        : NULL,
			'basis_learning'    => $raw['basis_learning']    ? $raw['basis_learning']    : NULL,
			'phone'             => $raw['phone']             ? $raw['phone']             : NULL,
			'education_type'    => $raw['education_type']    ? $raw['education_type']    : NULL,
			'expulsion_type'    => $raw['expulsion_type']    ? $raw['expulsion_type']    : NULL,
			'university'        => $raw['university']        ? $raw['university']        : NULL,
			
			'signature_type'     => $raw['signature_type']   ? $raw['signature_type']    : NULL,
			'delivery_method'    => $raw['delivery_method']  ? $raw['delivery_method']   : NULL,
		
		);
				
		if(!empty($raw['type'])){
			$data['Type'] = (int)$raw['type'];
		}
		
		if(!empty($raw['direction'])){
			$data['Direction'] = $raw['direction'];
		}
		
		if(!empty($raw['academic_leave_type'])){
			$data['academic_leave_type'] = (int)$raw['academic_leave_type'];
		}
		
		if(!empty($raw['mid_external'])){
			$data['StudyCode'] = $raw['mid_external'];
		}
		
		if(!empty($raw['course'])){
			$data['Course'] = $raw['course'];
		}
		return $this->getService('StudentCertificate')->addStudentCertificate($data);
	}
	
	public function sendEmail($data)
	{	
		# TODO Сделать для перевода и отчисления!!!
		
		$validator = new Zend_Validate_EmailAddress();		
		$mail      = new Zend_Mail(Zend_Registry::get('config')->charset);
		$type      = (int)$data['type'];		
		$subject   = $this->getEmailSubject($type);
		$user      = $this->getService('User')->getCurrentUser();
		
		$to        = $this->getEmailTo($type);
		
		$toCopy    = $this->getEmailToCopy($type);
		
		$from      = !empty($data['email_c']) ? $data['email_c'] : $data['email'];
		
		$data['from'] = $from;
		$data['academic_leave_type_name'] = HM_StudentCertificate_StudentCertificateModel::getAcademicLeaveTypeName($data['academic_leave_type']);
		$data['study_form_name']          = HM_StudentCertificate_StudentCertificateModel::getStudyFormById($data['study_form']);
		$data['basis_learning_name']      = HM_StudentCertificate_StudentCertificateModel::getBasisLearningById($data['basis_learning']);
		$data['type_name']                = HM_StudentCertificate_StudentCertificateModel::getTypeName($type);
		$data['isExternalEmail']          = empty($user->EMail) ? true : false;		
		
		$data['transfer_type_name']       = HM_StudentCertificate_StudentCertificateModel::getTransferTypeById($data['transfer_type']);
		$data['program_name']             = HM_StudentCertificate_StudentCertificateModel::getProgramById($data['program']);
		$data['education_type_name']      = HM_StudentCertificate_StudentCertificateModel::getEducationTypeName($data['education_type']);
		
		$data['expulsion_type_name']      = HM_StudentCertificate_StudentCertificateModel::getExpulsionTypeName($data['expulsion_type']);
		
		
		$data['signature_type_name']      = HM_StudentCertificate_StudentCertificateModel::getSignatureTypeName($data['signature_type']);
		$data['delivery_method_name']     = HM_StudentCertificate_StudentCertificateModel::getDeliveryMethodName($data['delivery_method']);
		
		if(!empty($data['direction_desired'])){
			$direction_desired              = $this->getService('StudentCertificate')->getDirectionById($data['direction_desired']);
			$data['direction_desired_name'] = $direction_desired->name;
		}
		$bodyHtml  = $this->renderBodyHtml($data);
		
		if(!$validator->isValid($to)){
			return false;
		}
		
		if(!empty($data['file_name'])){
			$attachment = $this->createAttachment($data['file_name']);
			if($attachment){ 
				$mail->addAttachment($attachment);				
			}
		}
		
		if(!empty($data['file_order_name'])){
			$attachment = $this->createAttachment($data['file_order_name']);
			if($attachment){ 
				$mail->addAttachment($attachment);
			}
		}
		
		if(!empty($data['file_passport_name'])){
			if(!is_array($data['file_passport_name'])){
				$attachment = $this->createAttachment($data['file_passport_name']);
				if($attachment){ 
					$mail->addAttachment($attachment);					
				}
			} else {
				foreach($data['file_passport_name'] as $file_passport_name){
					$attachment = $this->createAttachment($file_passport_name);
					if($attachment){
						$mail->addAttachment($attachment);
					}
				}
			}
		}

		if(!empty($data['file_order_tmp_path'])){
			$attachment = $this->createAttachment($data['file_order_tmp_path']);
			if($attachment){ 
				$mail->addAttachment($attachment);				
			}
		}
		
		if(!empty($data['file_document_tmp_path'])){
			$attachment = $this->createAttachment($data['file_document_tmp_path']);
			if($attachment){ 
				$mail->addAttachment($attachment);				
			}
		}
		
		$mail->setFromToDefaultFrom();
		$mail->setSubject($subject);
		$mail->addTo($to);
		
		if($validator->isValid($toCopy)){
			$mail->addCc($toCopy);
		}
		
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setBodyHtml($bodyHtml, Zend_Registry::get('config')->charset);	
		
		
		try {
			$mail->send();
			return true;
        } catch (Zend_Mail_Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return false;
	}
	
	private function renderBodyHtml($data)
	{
		$fio       = !empty($data['fio_c']) ? $data['fio_c'] : $data['fio'];
		$from      = $data['from'];
		$direction = !empty($data['Direction']) ? $data['Direction'] : $data['direction'];
		$course    = !empty($data['Course'])    ? $data['Course']    : $data['course'];
		
		$html  = '';
		$html .= '<b>ФИО:</b> ' . $fio . '<br>';
		$html .= '<b>Email:</b> <a data-student-email="' . $from . '" href="mailto:' . $from . '">' . $from . '</a> ';
		$html .= '<a href="mailto:student-email-begin_' . $from . '_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$html .= '<a href="mailto:student-name-begin_' . $fio . '_student-name-end" style="color: white;" >.</a> <br>';
		if($data['phone']){ $html .= '<b>Номер телефона:</b> ' . $data['phone'] . '<br>'; }
		
		if($data['type_name'])  { $html .= '<b>Тип справки:</b> ' . $data['type_name'] . '<br>'; }
		if($data['Number'])     { $html .= '<b>Количество:</b> ' . $data['Number'] . '<br>'; }
		if($data['Destination']){ $html .= '<b>Место назначения:</b> ' . $data['Destination'] . '<br>'; }		
		if($data['Faculty'])	{ $html .= '<b>Факультет:</b> ' . $data['Faculty'] . '<br>'; }
		if($data['GroupName'])	{ $html .= '<b>Группа:</b> ' . $data['GroupName'] . '<br>'; }
		if($data['Postcode'])	{ $html .= '<b>Индекс:</b> ' . $data['Postcode'] . '<br>'; }
		if($data['City'])		{ $html .= '<b>Город/область/край:</b> ' . $data['City'] . '<br>'; }
		if($data['Street'])		{ $html .= '<b>Улица, дом, квартира:</b> ' . $data['Street'] . '<br>'; }
		if($data['Employer'])	{ $html .= '<b>Наименование работодателя:</b> ' . $data['Employer'] . '<br>'; }
		if($direction)	        { $html .= '<b>Направление:</b> ' . $direction . '<br>'; }
		if($course)		        { $html .= '<b>Курс:</b> ' . $course . '<br>'; }		
		if($data['Year'])		{ $html .= '<b>Год:</b> ' . $data['Year'] . '<br>'; }
		if($data['Submission'])	{ $html .= '<b>Место представления:</b> ' . $data['Submission'] . '<br>'; }
		if($data['date_from'])	{ $html .= '<b>с:</b> ' . $data['date_from']; }
		if($data['date_to'])	{ $html .= '<b>по:</b> ' . $data['date_to'] . '<br>'; }
		if($data['place_work'])	{ $html .= '<b>Место работы:</b> ' . $data['place_work'] . '<br>'; }
		if($data['period'])		{ $html .= '<b>Период:</b> ' . $data['period'] . '<br>'; }
		if($data['transfer_type_name'])    { $html .= '<b>Желаемый перевод:</b> ' . $data['transfer_type_name'] . '<br>'; }
		if($data['organization'])          { $html .= '<b>Организация:</b> ' . $data['organization'] . '<br>'; }
		if($data['program_name'])          { $html .= '<b>Программа обучения:</b> ' . $data['program_name'] . '<br>'; }
		if($data['direction_desired_name']){ $html .= '<b>Желаемое направление подготовки:</b> ' . $data['direction_desired_name'] . '<br>'; }
		if($data['study_form_name'])       { $html .= '<b>Форма обучения:</b> ' . $data['study_form_name'] . '<br>'; }
		if($data['basis_learning_name'])   { $html .= '<b>Основа обучения:</b> ' . $data['basis_learning_name'] . '<br>'; }
		if($data['education_type_name'])   { $html .= '<b>На базе:</b> ' . $data['education_type_name'] . '<br>'; }
		if($data['expulsion_type_name'])   { $html .= '<b>Тип отчисления:</b> ' . $data['expulsion_type_name'] . '<br>'; }
		if($data['university'])            { $html .= '<b>Вуз, в который осуществляется перевод:</b> ' . $data['university'] . '<br>'; }
		if($data['signature_type_name'])  { $html .= '<b>Вид справки:</b> ' . $data['signature_type_name'] . '<br>'; }
		if($data['delivery_method_name']) { $html .= '<b>Способ получения справки:</b> ' . $data['delivery_method_name'] . '<br>'; }
		
		if($data['academic_leave_type_name']) { $html .= '<b>Вид академического отпуска:</b> ' . $data['academic_leave_type_name'] . '<br>'; }
		
		if(!empty($data['file_name'])){
			$html .= '<br />Документ "'.$this->getFileName($data['file_name']).'" во вложении';			
		}
		
		if(!empty($data['file_order_tmp_path'])){
			$html .= '<br />Заявление "'.$this->getFileName($data['file_order_tmp_path']).'" во вложении';			
		}
		
		if(!empty($data['file_document_tmp_path'])){
			$html .= '<br />Скан-копия документа, подтверждающего основание предоставления отпуска "'.$this->getFileName($data['file_document_tmp_path']).'" во вложении';			
		}
		
		if(!empty($data['file_order_name'])){			
			$html .= '<br />Заявление "'.$this->getFileName($data['file_order_name']).'" во вложении';
		}
		
		if(!empty($data['file_passport_name'])){
			if(!is_array($data['file_passport_name'])){				
				$html .= '<br />Скан-копия паспорта "'.$this->getFileName($data['file_passport_name']).'" во вложении';				
			} else {
				foreach($data['file_passport_name'] as $file_passport_name){				
					$html .= '<br />Скан-копия паспорта "'.$this->getFileName($file_passport_name).'" во вложении';					
				}
			}
		}
			
		if($data['isExternalEmail']){
			$html .= '<br><b>Примечание:</b> email, который был указан при заказе справки, не является системным и был указан вручную.<br>';				
		}
		
		return $html;
	}
	

	
	private function getEmailSubject($type)
	{
		return _('Заказ справки');
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
	
	private function getEmailTo($type)
	{
		$config       = Zend_Registry::get('config');		
		$emailDefault = $config->mail->email->to->default;
		$validator    = new Zend_Validate_EmailAddress();
		$email        = HM_StudentCertificate_StudentCertificateModel::getEmailTo($type);
		$user         = $this->getService('User')->getCurrentUser();
		$requisite    = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);
		
		if($validator->isValid($requisite->email)){
			return $requisite->email;
		}
		
		if(!empty($email)){
			return $email;
		}
		return $emailDefault;
	}
	
	private function getEmailToCopy($type)
	{
		$email = HM_StudentCertificate_StudentCertificateModel::getEmailToCopy($type);		
		if(!empty($email)){
			return $email;
		}
		return false;
	}
	
	public function getHumanMonth($month_number)
	{	
		$months = array(
		  1  => 'января',
		  2  => 'февраля',
		  3  => 'марта',
		  4  => 'апреля',
		  5  => 'мая',
		  6  => 'июня',
		  7  => 'июля',
		  8  => 'августа',
		  9  => 'сентября',
		  10 => 'октября',
		  11 => 'ноября',
		  12 => 'декабря'
		);
		return $months[$month_number];
	}
	
	private function getFormByType($type)
	{
		$formClassName = 'HM_Form_Certificate';
		
		if($type == HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE){
			$formClassName = 'HM_Form_CertificateAcademicLeave';
			
		} elseif($type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER){
			$formClassName = 'HM_Form_CertificateTransfer';
			
		} elseif($type == HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION){
			$formClassName = 'HM_Form_CertificateExpulsion';
			
		}
		
		if(!class_exists($formClassName)){
			$formClassName = 'HM_Form_Certificate';
		}		
		return new $formClassName;
	}
	
	private function createExpulsionOrder()
	{
		$data      = array();
		$request   = $this->getRequest();
		$type      = (int)$request->getParam('type');
		$user      = $this->getService('User')->getCurrentUser();
		$form      = $this->getFormByType($type);
		$validator = new Zend_Validate_EmailAddress();
		$email     = $request->getParam('email');
		
		$this->view->description = HM_StudentCertificate_StudentCertificateModel::getDescription($type);
		$html_description        = $this->view->render('certificate/partials/description.tpl');
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$form->change($type);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if(!$validator->isValid($email)){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Указан некорректный Email'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		# Заявление 
		if(!$form->file_order->isUploaded()){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось загрузить заявление'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if( !empty($email) && empty($user->EMail) ){		
			$this->saveEmail($email);
		}
		
		$data = array(
			'type'                => $type,
			'mid_external'        => $user->mid_external,
			'fio'                 => $request->getParam('fio'),
			'email'               => $email,
			'phone'               => $request->getParam('phone'),
			'course'              => (int)$request->getParam('course'),
			'direction'           => $request->getParam('direction'),
			'study_form'          => (int)$request->getParam('study_form'),
			'basis_learning'      => (int)$request->getParam('basis_learning'),
			'expulsion_type'      => (int)$request->getParam('expulsion_type'),			
			'university'          => $request->getParam('university'),
			'file_order_tmp_path' => $form->file_order->getFileName(),
		);
		$newOrder = $this->save($data);
		
		if(!$newOrder){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось создать заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$isSendMail = $this->sendEmail($data);
		if(!$isSendMail){			
			$this->getService('StudentCertificate')->deleteStudentCertificate($newOrder->CertID);
			
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось отправить заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$form = $this->getFormByType($type);
		echo $this->view->notifications(array(array(
			'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => _('Заявка успешно отправлена')
		)), array('html' => true));
		echo $form->render();
		echo $html_description;
	}
	
	private function createTransferOrder()
	{
		$data      = array();
		$request   = $this->getRequest();
		$type      = (int)$request->getParam('type');
		$user      = $this->getService('User')->getCurrentUser();
		$form      = $this->getFormByType($type);
		$validator = new Zend_Validate_EmailAddress();
		$email     = $request->getParam('email');
		
		$this->view->description = HM_StudentCertificate_StudentCertificateModel::getDescription($type);
		$html_description        = $this->view->render('certificate/partials/description.tpl');
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$form->change($type);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if(!$validator->isValid($email)){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Указан некорректный Email'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		# Заявление 
		if(!$form->file_order->isUploaded()){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось загрузить заявление'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if( !empty($email) && empty($user->EMail) ){		
			$this->saveEmail($email);
		}
		
		$data = array(
			'type'                   => $type,
			'mid_external'           => $user->mid_external,
			'fio'                    => $request->getParam('fio'),
			'email'                  => $email,
			'phone'                  => $request->getParam('phone'),
			'course'                 => (int)$request->getParam('course'),
			'transfer_type'          => (int)$request->getParam('transfer_type'),
			'direction_desired'      => (int)$request->getParam('direction_desired'),
			'education_type'         => (int)$request->getParam('education_type'),
			'program'                => (int)$request->getParam('program'),
			'study_form'             => (int)$request->getParam('study_form'),
			'basis_learning'         => (int)$request->getParam('basis_learning'),			
			'file_order_tmp_path'    => $form->file_order->getFileName(),
		);
		$newOrder = $this->save($data);
		
		if(!$newOrder){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось создать заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$isSendMail = $this->sendEmail($data);
		if(!$isSendMail){			
			$this->getService('StudentCertificate')->deleteStudentCertificate($newOrder->CertID);
			
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось отправить заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		
		$form = $this->getFormByType($type);
		echo $this->view->notifications(array(array(
			'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => _('Заявка успешно отправлена')
		)), array('html' => true));
		echo $form->render();
		echo $html_description;
	}
	
	private function createAcademicLeaveOrder()
	{
		$data      = array();
		$request   = $this->getRequest();
		$type      = (int)$request->getParam('type');
		$user      = $this->getService('User')->getCurrentUser();
		$form      = $this->getFormByType($type);
		$validator = new Zend_Validate_EmailAddress();
		$email     = $request->getParam('email');
		
		$this->view->description = HM_StudentCertificate_StudentCertificateModel::getDescription($type);
		$html_description        = $this->view->render('certificate/partials/description.tpl');
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		$form->change($type);
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if(!$validator->isValid($email)){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Указан некорректный Email'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		# Заявление 
		if(!$form->file_order->isUploaded()){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось загрузить заявление'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		# скан-копия документа
		if(!$form->file_document->isUploaded()){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось загрузить скан-копию документа, подтверждающего основание предоставления отпуска'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		if( !empty($email) && empty($user->EMail) ){		
			$this->saveEmail($email);
		}
		
		$data = array(
			'type'                   => $type,
			'mid_external'           => $user->mid_external,
			'fio'                    => $request->getParam('fio'),
			'email'                  => $email,
			'phone'                  => $request->getParam('phone'),
			'direction'              => $request->getParam('direction'),
			'study_form'             => (int)$request->getParam('study_form'),
			'basis_learning'         => (int)$request->getParam('basis_learning'),
			'academic_leave_type'    => (int)$request->getParam('academic_leave_type'),
			'file_order_tmp_path'    => $form->file_order->getFileName(),
			'file_document_tmp_path' => $form->file_document->getFileName(),
			'DateCreate'             => date('Y-m-d H:i:s'),
		);		
		$newOrder = $this->save($data);
		
		if(!$newOrder){
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось создать заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		
		$isSendMail = $this->sendEmail($data);
		if(!$isSendMail){			
			$this->getService('StudentCertificate')->deleteStudentCertificate($newOrder->CertID);
			
			echo $this->view->notifications(array(array(
				'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message' 	=> _('Не удалось отправить заявку'),
			)), array('html' => true));
			echo $form->render();
			echo $html_description;
			return false;
		}
		
		
		$form = $this->getFormByType($type);
		echo $this->view->notifications(array(array(
			'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' => _('Заявка успешно отправлена')
		)), array('html' => true));
		echo $form->render();
		echo $html_description;
	}
	
}



