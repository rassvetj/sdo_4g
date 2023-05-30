<?php
class Dormitory_RefundController extends HM_Controller_Action {
	
	private $_serviceRefund = null;
	
	private $_mail_to 		= 'dekanat@rgsu.net';
	#private $_mail_to 		= 'HramovSV@rgsu.net';
	
	public function init(){
		$this->_serviceRefund = $this->getService('DormitoryRefund');
		
		parent::init();
	}
	
	
	public function indexAction()
    {		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Оплата общежития'));
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$gridId = 'grid';
		$user 	= $this->getService('User')->getCurrentUser();
		
		
		if($user->MID){
			$fields = array(
				'type',
				'fio',
				'email',
				#'course',
				#'specialty',
				#'faculty',
				'phone',
				'bank_name',
				'inn',
				'kpp',
				'bik',
				'correspondent_account',
				'settlement_account',
				'recipient_name',
				'recipient_personal_account',
				'date_created',
			);
			
			
			$select = $this->_serviceRefund->getSelect();
			$select->from($this->_serviceRefund->getMapper()->getAdapter()->getTableName(), $fields);
			$select->where($this->_serviceRefund->quoteInto('MID = ?', $user->MID));
			
			$grid = $this->getGrid(
				$select,
				array(
					'type' 		=> array(
						'title' 	=> _('Тип'),
						'callback' 	=> array('function' => array($this, 'updateType'), 'params' => array('{{type}}')),
					),   
					'fio'							=> array('title' => _('ФИО')),                
					'email'							=> array('title' => _('Email')),                
					#'course'						=> array('title' => _('Курс')),                
					#'specialty'						=> array('title' => _('Специальность')),                
					#'faculty'						=> array('title' => _('Факультет')),                
					'phone'							=> array('title' => _('Контактный телефон')),                
					'bank_name'						=> array('title' => _('Наименование банка')),                
					'inn'							=> array('title' => _('ИНН')),                
					'kpp'							=> array('title' => _('КПП ')),                
					'bik'							=> array('title' => _('БИК ')),                
					'correspondent_account'			=> array('title' => _('Корреспондентский счет')),                
					'settlement_account'			=> array('title' => _('Расчетный счет')),                
					'recipient_name'				=> array('title' => _('Ф.И.О. получателя платежа')),                
					'recipient_personal_account'	=> array('title' => _('Номер лицевого счета получателя платежа')),
					'date_created'					=> array('title' => _('Дата создания')),
				),
				array(
					'type' 							=> array('values' => HM_Dormitory_Refund_RefundModel::getTypes()),
					'fio' 							=> null,                
					'email' 						=> null,                
					#'course' 						=> null,                
					#'specialty' 					=> null,                
					#'faculty' 						=> null,                
					'phone' 						=> null,                
					'bank_name' 					=> null,                
					'inn' 							=> null,                
					'kpp' 							=> null,                
					'bik' 							=> null,                
					'correspondent_account' 		=> null,                
					'settlement_account' 			=> null,                
					'recipient_name' 				=> null,                
					'recipient_personal_account' 	=> null,                
					'date_created' 					=> array('render' => 'DateSmart'),				
				),
				$gridId
			);		
			
			
			$grid->updateColumn('date_created', array(
				'format' => array(
					'DateTime',
					array('date_format' => Zend_Locale_Format::getDateTimeFormat())
				),
				'callback' => array(
					'function' => array($this, 'updateDate'),
					'params' => array('{{date_created}}')
				)
			));
			$this->view->grid 				= $grid->deploy();	
		}
		
		
		$this->view->gridAjaxRequest	= $this->isAjaxRequest();
		
		$this->view->form 				= new HM_Form_DormitoryRefund();

		
	}
	
	
	public function sendAction()
    {
		$this->getHelper('viewRenderer')->setNoRender();
        
        $form = new HM_Form_DormitoryRefund();
		
		$return = array(
            'code' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
            'message' 	=> _('Заполните все поля')
        );
		
		
		$data 		= array();
        $request 	= $this->getRequest();
		$user 		= $this->getService('User')->getCurrentUser();
		
		
		$is_ajax  = $request->isXmlHttpRequest() ? true : false;
		$url_back = $this->view->url(array('module' => 'dormitory', 'controller' => 'refund', 'action' => 'index'));
		
		
		if (!$request->isPost() && !$request->isGet()) {
			$return['message'] = _('Некорректный метод');
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;
		}
		
		$validator 	= new Zend_Validate_EmailAddress();
		$user_email = $validator->isValid($user->EMail) ? $user->EMail : $request->getParam('email');
		$user_fio   = trim($user->LastName.' '.$user->FirstName.' '.$user->Patronymic);
		$tupe_id	= intval($request->getParam('type'));
		
		$request->setParam('type', 							$tupe_id);
        $request->setParam('fio', 							strip_tags($request->getParam('fio')));
		$request->setParam('email', 						strip_tags($user_email));
		#$request->setParam('course', 						intval($request->getParam('course')));		
		#$request->setParam('specialty', 					strip_tags($request->getParam('specialty')));
		#$request->setParam('faculty', 						strip_tags($request->getParam('faculty')));
		$request->setParam('phone', 						strip_tags($request->getParam('phone')));
		$request->setParam('bank_name', 					strip_tags($request->getParam('bank_name')));
		$request->setParam('inn', 							strip_tags($request->getParam('inn')));
		$request->setParam('kpp', 							strip_tags($request->getParam('kpp')));
		$request->setParam('bik', 							strip_tags($request->getParam('bik')));
		$request->setParam('correspondent_account', 		strip_tags($request->getParam('correspondent_account')));
		$request->setParam('settlement_account', 			strip_tags($request->getParam('settlement_account')));
		$request->setParam('recipient_name', 				strip_tags($request->getParam('recipient_name')));
		$request->setParam('recipient_personal_account', 	strip_tags($request->getParam('recipient_personal_account')));
		
		
		if(!empty($user_fio)){
			$request->setParam('fio', strip_tags($user_fio));			
		}
		
		
		if(!$validator->isValid($request->getParam('email'))){
			$return['message']	= _('Введен невалидный email');
			$return['form']		= $form->render();
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;			
		}			
	
		
		if($tupe_id == HM_Dormitory_Refund_RefundModel::TUPE_IN_PAYMENT){
			$form->getElement('bank_name'					)->setOptions(array('Required' => false));
			$form->getElement('inn'							)->setOptions(array('Required' => false));
			$form->getElement('kpp'							)->setOptions(array('Required' => false));
			$form->getElement('bik'							)->setOptions(array('Required' => false));
			$form->getElement('correspondent_account'		)->setOptions(array('Required' => false));
			$form->getElement('settlement_account'			)->setOptions(array('Required' => false));
			$form->getElement('recipient_name'				)->setOptions(array('Required' => false));
			$form->getElement('recipient_personal_account'	)->setOptions(array('Required' => false));
			
		} else {			
			$form->getElement('bank_name'					)->setOptions(array('Required' => true));
			$form->getElement('inn'							)->setOptions(array('Required' => true));
			$form->getElement('kpp'							)->setOptions(array('Required' => true));
			$form->getElement('bik'							)->setOptions(array('Required' => true));
			$form->getElement('correspondent_account'		)->setOptions(array('Required' => true));
			$form->getElement('settlement_account'			)->setOptions(array('Required' => true));
			$form->getElement('recipient_name'				)->setOptions(array('Required' => true));
			$form->getElement('recipient_personal_account'	)->setOptions(array('Required' => true));
		}
		

		
		if (!$form->isValid($request->getParams())) {
			$return['message']	= _('Заполните обязательные поля');
			$return['form']		= $form->render();
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;
		}
		
		
		
		$file_obj  = $form->statement;	
		if (!$file_obj->isUploaded()) {					
			$return['message']	= _('Не удалось загрузить файл');
			$return['form']		= $form->render();
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;
		}
				
		$data['type'] 						= $request->getParam('type');		
		$data['MID'] 						= $user->MID;
		$data['fio'] 						= $request->getParam('fio');
		$data['email'] 						= $request->getParam('email');
		$data['course'] 					= $request->getParam('course');
		$data['specialty'] 					= $request->getParam('specialty');
		$data['faculty'] 					= $request->getParam('faculty');
		$data['phone'] 						= $request->getParam('phone');
		$data['bank_name'] 					= $request->getParam('bank_name');
		$data['inn'] 						= $request->getParam('inn');
		$data['kpp'] 						= $request->getParam('kpp');
		$data['bik'] 						= $request->getParam('bik');
		$data['correspondent_account'] 		= $request->getParam('correspondent_account');
		$data['settlement_account'] 		= $request->getParam('settlement_account');
		$data['recipient_name'] 			= $request->getParam('recipient_name');
		$data['recipient_personal_account'] = $request->getParam('recipient_personal_account');
		$data['date_created'] 				= $request->getParam('date_created');
		$data['file_tmp_name'] 				= $file_obj->getFileName();	


		$new_item = $this->_serviceRefund->insert($data);

		if($new_item === false){
			$return['message']	= _('Не удалось создать заявление');
			$return['form']		= $form->render();
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;
		}
							
		$is_send = $this->sendEmail($data);
		
		if(!$is_send){
			$return['message']	= _('Не удалось отправить письмо');
			$return['form']		= $form->render();
			
			if($is_ajax){
				echo json_encode($return);
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
				$this->_redirect($url_back);	
			}
			die;
		}
		
		$form 				= new HM_Form_DormitoryRefund();
		
		$return['code'] 	= HM_Notification_NotificationModel::TYPE_SUCCESS;
		$return['message']	= _('Заявление успешно отправлено');	
		$return['form']		= $form->render(); #new HM_Form_DormitoryRefund();
							
		if($is_ajax){
			echo json_encode($return);
		} else {
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => $return['code'], 'message' => $return['message']));			
			$this->_redirect($url_back);	
		}
		die;
	}
	
	/**
	 * - Заказ справки. Отылаем в деканат
	*/
	public function sendEmail($data) {
		
		$validator = new Zend_Validate_EmailAddress();
		
		if (!$validator->isValid($data['email'])) {
			return false;
		}
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
        
		
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setSubject(HM_Dormitory_Refund_RefundModel::getMailTheme($data['type']));
		$mail->setBodyHtml($this->renderMailText($data), Zend_Registry::get('config')->charset);
		
		$attachment = $this->createAttachment($data);
		if($attachment){
			$mail->addAttachment($attachment);
		}
		
		$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО

		$mail->addTo($this->_mail_to);

		try {
			$mail->send();
			return true;
        } catch (Zend_Mail_Exception $e) {                
			echo $e->getMessage();
			die;
			return false;
        }			
		return false;		
	}
	
	
	
	private function renderMailText($data)
	{
		$messageText = '';
		$messageText .= '<b>ФИО:</b> ' . $data['fio'] . '<br>';
		$messageText .= '<b>Email:</b> <a data-student-email="' . $data['email'] . '" href="mailto:' . $data['email'] . '">' . $data['email'] . '</a> ';
		$messageText .= '<a href="mailto:student-email-begin_' . $data['email'] . '_student-email-end" style="color: white;" >.</a>'; # нужно для заявочной базы. data-student-email= выше строкой потом удалить
		$messageText .= '<a href="mailto:student-name-begin_' . $data['fio'] . '_student-name-end" style="color: white;" >.</a> <br>';
		$messageText .= '<b>Тип возврата:</b> ' . HM_Dormitory_Refund_RefundModel::getTypeName($data['type']) . '<br />';
		$messageText .= empty($data['course']) ? '' : '<b>Курс:</b> ' . $data['course'] . '<br />';
		$messageText .= empty($data['specialty']) ? '' : '<b>Специальность:</b> ' . $data['specialty'] . '<br />';
		$messageText .= empty($data['faculty']) ? '' : '<b>Факультет:</b> ' . $data['faculty'] . '<br />';
		$messageText .= empty($data['phone']) ? '' : '<b>Контактный телефон:</b> ' . $data['phone'] . '<br />';
		$messageText .= empty($data['bank_name']) ? '' : '<b>Наименование банка:</b> ' . $data['bank_name'] . '<br />';
		$messageText .= empty($data['inn']) ? '' : '<b>ИНН:</b> ' . $data['inn'] . '<br />';
		$messageText .= empty($data['kpp']) ? '' : '<b>КПП:</b> ' . $data['kpp'] . '<br />';
		$messageText .= empty($data['bik']) ? '' : '<b>БИК:</b> ' . $data['bik'] . '<br />';
		$messageText .= empty($data['correspondent_account']) ? '' : '<b>Корреспондентский счет:</b> ' . $data['correspondent_account'] . '<br />';
		$messageText .= empty($data['settlement_account']) ? '' : '<b>Расчетный счет:</b> ' . $data['settlement_account'] . '<br />';
		$messageText .= empty($data['recipient_name']) ? '' : '<b>Ф.И.О. получателя платежа:</b> ' . $data['recipient_name'] . '<br />';
		$messageText .= empty($data['recipient_personal_account']) ? '' : '<b>Номер лицевого счета получателя платежа:</b> ' . $data['recipient_personal_account'] . '<br />';
		
		$attachment_name = $this->generateAttachmentName($data);		
		if(!empty($attachment_name)){
			$messageText .= 'Документ "'.$attachment_name.'" во вложении';
		}	
		
		return $messageText;
	}
	
	
	private function createAttachment($data)
	{
		$content 	= file_get_contents($data['file_tmp_name']); 		
		$path_info 	= pathinfo($data['file_tmp_name']);
		$finfo 		= new finfo(FILEINFO_MIME_TYPE);
		$attachment = new Zend_Mime_Part($content);					
		
		$attachment->type 			= $finfo->buffer($content);
		$attachment->disposition	= Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding 		= Zend_Mime::ENCODING_BASE64;						
		$attachment->filename 		= $this->generateAttachmentName($data);
		return $attachment;
	}
	
	private function generateAttachmentName($data)
	{
		if(empty($data['file_tmp_name'])){
			return false;
		}
		$path_parts = pathinfo($data['file_tmp_name']);
		#$file_name = $path_info['basename'];
		$file_name = $path_parts['filename'] . '_' . $data['fio'] . '.' . $path_parts['extension'];	
		return $file_name;
	}
	
	
	
	
	public function updateType($type_id)
	{
		return HM_Dormitory_Refund_RefundModel::getTypeName($type_id);
	}
	
	
	public function updateDate($date)
    {
		if (!strtotime($date)) return '';		
        return $date;
    }
	
	
	
	
	# - возвращает факультет студента	
	#public function getFacultet($userID = false){		
	#		
	#	$owner = $this->_org->getOne($this->_org->find($userID));
	#	
	#	if($owner->soid_external == 'PR_11' || $owner->soid_external == 'ST_11' || $owner->soid_external == '11'){
	#		return false;
	#	}
	#	
	#	$this->_facultet = $owner->name;
	#	
	#	
	#	if($owner->owner_soid > 0){			
	#		$this->getFacultet($owner->owner_soid);
	#	}
	#	
	#	return;		
	#	
	#}
	

	
}