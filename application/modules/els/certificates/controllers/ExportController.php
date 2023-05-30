<?php
class Certificates_ExportController extends HM_Controller_Action
{
	private $_pdf  			= false;
	private $_current_user 	= false;
	private $_first_doc		= false;
	private $_disciplins	= false;
	
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
	
	
	
	public function confirmingStudentPdfAction()
    {
        $this->_helper->layout()->disableLayout();
		
		$this->_current_user 	= $this->getService('User')->getCurrentUser();
		
		$service_certificates 	= $this->getService('CertificatesStudent');
		
		$request				= $this->getRequest();
		$item_id				= (int)$request->getParam('item', false);
		
		$this->view->fio_dative		 = false;
		$this->view->order_numbr 	 = false;
		$this->view->order_date  	 = false;
		$this->view->date_birth	 	 = false;
		$this->view->direction 	 	 = false;   # направление подготовки.
		$this->view->direction_code  = false;   # направление подготовки - код
		$this->view->orders			 = array(); # все пирказы студента
		$this->view->date_graduation = false;   # дата окончания обучения
		$this->this->current_course = false;    # текущий курс
		$this->view->study_form 	= false;	# Форма обучения
		$this->view->based 			= false;	# Основа обучения
		
		
		$this->geterate_new_order_numbr = false;
		
		
		if(empty($item_id)){
			$isCanCreateNewItem   = $service_certificates->isCanCreateNewConfirmingStudent($this->_current_user->MID);
			
			if(!$isCanCreateNewItem){
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Вы недавно формировали справку и не можете создать новую'))
				);			
				$this->_redirector->gotoSimple('confirming-student', 'index', 'certificates');		
				die;
			}
			$this->geterate_new_order_numbr = true;
			$this->path_to_sign 			= $this->getPathToSign();
			
		} else {
			$item = $service_certificates->getById($item_id);
			
			if(empty($item) || $item->MID != $this->_current_user->MID){
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('У Вас нет такой справки'))
				);			
				$this->_redirector->gotoSimple('confirming-student', 'index', 'certificates');		
				die;
			}
			
			$this->path_to_sign 			= $this->getPathToSign($item->date_created);
			
			$this->view->order_numbr 		= $item->number;
			$this->view->fio_dative 		= $item->fio_dative;
            $this->view->date_birth 		= date('d.m.Y', strtotime($item->date_birth));            
            $this->view->current_course 	= $item->current_course;
            $this->view->study_form 		= $item->study_form;
            $this->view->based 				= $item->based;
            $this->view->direction 			= $item->direction;
            $this->view->direction_code 	= $item->direction_code;
			
			if(strtotime($item->date_graduation) > 0){
				$date_graduation				= new Zend_Date($item->date_graduation);			
				$this->view->date_graduation	= $date_graduation->get(Zend_Date::DATE_LONG);				
			}
			
			
			if(strtotime($item->date_created) > 0){
				$date_created		= new Zend_Date($item->date_created);			
				$this->view->order_date	= $date_created->get(Zend_Date::DATE_LONG);				
			}
			
		}
		
		
		$this->user_info = $this->getService('UserInfo')->getByCode($this->_current_user->mid_external);
		#$this->_recService	= $this->getService('RecordCard');
		#$this->_first_doc	= $this->_recService->getFirstActualOrder($this->_current_user->mid_external);
		
		#$this->_studyCardService 	= $this->getService('StudyCard'); 
		#$this->_disciplins 			= $this->_studyCardService->getDisciplins($this->_current_user->mid_external); //-_получаем список всех зачетов и экзаменов, что сдавал студент
		#$this->_recordbook_info 	= $this->_recService->getRecordbookInfo($this->_current_user->mid_external);
		
		$this->initPdf();
		$this->addPageMain();
		
		echo $this->outputPdf();
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
		$this->_pdf->SetTitle('Справка, подтверждающая статус студента');
		$this->_pdf->SetSubject('Справка, подтверждающая статус студента');
		$this->_pdf->SetKeywords('Справка, подтверждающая статус студента');

		$this->_pdf->setPrintHeader(false);
		$this->_pdf->setPrintFooter(false);
		$this->_pdf->SetMargins(5, 0, 2);
		#$this->_pdf->SetMargins(10, 10, 10, true);
		$this->_pdf->SetFooterMargin(5);
		$this->_pdf->SetAutoPageBreak(false);
		return true;
	}
	
	
	private function addPageMain()
	{	
		
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(15, 5, 15);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		
		#$recordbook_info = $this->getService('RecordCard')->getRecordbookInfo($this->_current_user->mid_external);
		
		# дата рождения
		$birth_date_timestamp = strtotime($this->user_info->date_birth);
		# Считаем валидной дату с 1930 года.
		if($birth_date_timestamp > -1262304000){
			$this->view->date_birth	= date('d.m.Y', $birth_date_timestamp);	
		}
		
		# текущий курс обучения		
		$this->view->current_course = (int)$this->user_info->course; # $this->getCourse($this->user_info->semester);
		$this->view->study_form 	= $this->getStudyForm($this->user_info->study_form); 
		
		
		
		
		if($this->geterate_new_order_numbr){
			
			# нужно в дательном падеже
			$this->view->fio_dative = $this->user_info->fio_dative;
		
			# Дата окончания обучения		
			$date_graduation_timestamp = strtotime($this->user_info->date_graduation);			
			if(strtotime($this->user_info->date_graduation) > 0){
				$date_graduation				= new Zend_Date($this->user_info->date_graduation);			
				$this->view->date_graduation	= $date_graduation->get(Zend_Date::DATE_MEDIUM);				
			}
		
			$this->_recService	= $this->getService('RecordCard');
			$first_doc	= $this->_recService->getFirstActualOrder($this->_current_user->mid_external);
			if(!empty($first_doc)){
				# направление подготовки.
				$this->view->direction 		= $this->user_info->specialty;
				$this->view->direction_code = $this->user_info->specialty_code;
			}
			
			# Пока не выводим
			$orders 			= $this->_recService->getUserOrders($this->_current_user->mid_external);					
			#$this->view->orders = $this->filterOders($orders);
			
			# Основа обучения
			$this->view->based = $this->getBased($orders);
		}
		
		
		
		
		
		
		
		
		# шрифты должны быть уже сгенерированы и лежать в папке tcpdf/fonts
		# Если их нет, то нужно запихать ttf в online-tcpdf-генератор или же выполнить:
		#$fontname = TCPDF_FONTS::addTTFfont('/path-to-font/FreeSerifItalic.ttf', 'TrueTypeUnicode', '', 96);
		#$pdf->SetFont($fontname, '', 14, '', false);

		$this->_pdf->addTTFfont('museosanscyrl100', 'TrueTypeUnicode', 'museosanscyrl100.php');
		$this->_pdf->addTTFfont('museosanscyrl300', 'TrueTypeUnicode', 'museosanscyrl300.php');
		$this->_pdf->addTTFfont('museosanscyrl500', 'TrueTypeUnicode', 'museosanscyrl500.php');
		$this->_pdf->addTTFfont('museosanscyrl700', 'TrueTypeUnicode', 'museosanscyrl700.php');
		$this->_pdf->addTTFfont('museosanscyrl900', 'TrueTypeUnicode', 'museosanscyrl900.php');
		
		$this->_pdf->SetFont('museosanscyrl100', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl300', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl500', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl700', '', 14, '', false);
		$this->_pdf->SetFont('museosanscyrl900', '', 14, '', false);
		
		
		$main_logo				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\certificates\views\files\logo\main.png'; # логотип
		$this->_pdf->Image($main_logo, 15, 10, 33, 0, '', '', '', false, 300);
		
		
		# Если есть пустые поля, то гербовую печать и подпись НЕ выводим. выводим сообщение с предложением написать в деканат.
		$has_empty_fields = $this->hasEmptyFields();		
		# Теперь если не хватает данные, справку не создаем, а переходим в раздел 
		if($has_empty_fields){
			$this->_redirector->gotoSimple('confirming-student', 'index', 'certificates');		
			die;			
		}
		
		
		$this->view->stamp_and_sign_url = false;
		if($has_empty_fields){
			#$stamp_and_sign				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\certificates\views\files\type\confirming-student\stamp_and_sign_empty.png'; # подпись и печать
			$stamp_and_sign				= $this->path_to_sign . '/stamp_and_sign_empty.png'; # подпись и печать
			$this->_pdf->Image($stamp_and_sign, 107, 256, 88, 0, '', '', '', false, 300);			
		} else {
			#$stamp_and_sign				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\certificates\views\files\type\confirming-student\stamp_and_sign.png'; # подпись и печать					
			$stamp_and_sign				= $this->path_to_sign . '/stamp_and_sign.png'; # подпись и печать					
			$this->view->stamp_and_sign_url = $stamp_and_sign;
		}		
		
		
		
		
		# присваиваем номер только в том случае, если есть все поля.
		if(!$has_empty_fields && $this->geterate_new_order_numbr){
			# добавляем в БД новый номер справки с датой
			$service_certificates 		= $this->getService('CertificatesStudent');
			
			$data = array(
				'fio_dative' 		=> $this->view->fio_dative, # фио в дательном падаже
				'date_birth' 		=> $this->view->date_birth, # рождения
				'current_course' 	=> (int)$this->view->current_course, # текущий курс
				'study_form' 		=> $this->view->study_form, # форма обучения
				'based' 			=> $this->view->based, # основа обучения 
				'direction' 		=> $this->view->direction, # направление 
				'direction_code' 	=> $this->view->direction_code, # код направления		
				'date_graduation' 	=> $this->user_info->date_graduation, # дата окончания обучения
			);
			
			$this->view->order_numbr 	= $service_certificates->addNumber($this->_current_user->MID, HM_CertificatesStudent_CertificatesStudentModel::TYPE_CONFIRMING_STUDENT, $data);
			$date_created				= new Zend_Date();			
			$this->view->order_date		= $date_created->get(Zend_Date::DATE_LONG);
		}
		
		if(empty($this->view->order_date)){
			$date_created				= new Zend_Date();			
			$this->view->order_date		= $date_created->get(Zend_Date::DATE_LONG);
		}
		
		
		
		
		
		$content = $this->view->render('export/confirming-student/parts/main.tpl');
		
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		
		return true;
	}
	
	
	
	
	private function outputPdf()
	{
		return $this->_pdf->Output('confirming_student.pdf', 'I');		
	}
	
	
	# обрезка строки до нужной длинны до пробела
	public function smartCutting($source, $limit) 
	{ 
		$words_array = explode(' ', $source);	 
		$short = '';
	 
		for ($i=0; $i < count($words_array); $i++) { 
			if ( mb_strlen($short, 'utf-8') <= $limit ) {
				$short .= $words_array[$i] . ' ';
			}
		}
	 
		$short = trim($short);
	 
		if ( mb_strlen($short, 'utf-8') <= $limit - 10 ) {
			$return = $source;
		} else {
			$return = $short;
		}
	 
		return $return;
	}
	
	/*
	public function getCourse($semester)
	{
		$semester = (int)$semester;
		return ceil($semester / 2);
	}
	*/
		
	public function getStudyForm($raw_study_form)
	{
		$list = array(
			'Очная (дневная)' 			=> 'очной',
			'Очно-заочная (вечерняя)' 	=> 'очно-заочной',
			'Заочная' 					=> 'заочной',
		);
		return $list[$raw_study_form];
	}
	
	
	private function hasEmptyFields()
	{
		$list_errors = array();
		
		if(empty($this->view->fio_dative)){
			$list_errors[] = _('Имя в дательном падеже');
		}
		
		if(empty($this->view->date_birth)){
			$list_errors[] = _('Дата рождения');
		}
		
		if(empty($this->view->direction)){
			$list_errors[] = _('Направление подготовки');
		}
		
		if(empty($this->view->direction_code)){
			$list_errors[] = _('Код направления подготовки');
		}
		
		if(empty($this->view->date_graduation)){
			$list_errors[] = _('Дата окончания обучения');
		}
		
		if(empty($this->view->current_course)){
			$list_errors[] = _('Текущий курс');
		}
		
		if(empty($this->view->study_form)){
			$list_errors[] = _('Форма обучения');
		}
		
		if(empty($this->view->based)){
			$list_errors[] = _('Основа обучения');
		}
		
		
		
		if(!empty($list_errors)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
					array(	'type' => HM_Notification_NotificationModel::TYPE_ERROR,
							#'message' => _('Ваша справка сформирована без печати, т.к. не заполнены поля').': '.implode(', ', $list_errors))
							'message' => _('Ваша справка не сформирована, т.к. не заполнены поля').': '.implode(', ', $list_errors))
			);
			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
					array(	'type' => HM_Notification_NotificationModel::TYPE_ERROR,
							'message' => _('Обратитесь в деканат').': dekanat@rgsu.net')
			);
			
			return true;
		}
		return false;
	}
	
	
	/**
	 * @return string
	 * Приводим к читабельному виду описание приказа.
	 * А именно удаляем время в дате приказа.
	*/
	private function normalizOrderNote($description)
	{
		$tmp = explode(' ', $description);
		array_pop($tmp);
		return implode(' ', $tmp);
	}
	
	/**
	 * @return array
	 * фильтруем приказы.
	*/
	private function filterOders($orders)
	{
		if(empty($orders)){ return false; }
			
		$allow_order_types = HM_CertificatesStudent_CertificatesStudentModel::getAllowOrderTypes();
		$filtered_orders   = array();
		
		# сначала находим последний приказ о переводе на след. курс.
		$last_order_transfer = array();
		foreach($orders as $i){
			if($i->TypeOrder == 'Перевод' && $i->Reason == 'На следующий курс'){
				if(empty($last_order_transfer)){
					$last_order_transfer = $i;
					continue;
				}
				
				if(strtotime($last_order_transfer->DateTake) < strtotime($i->DateTake)){
					$last_order_transfer = $i;
					continue;
				}
			}
		}
			
		foreach($orders as $i){
			$order_type = mb_strtolower (trim($i->TypeOrder), 'UTF-8');
			if(!in_array($order_type, $allow_order_types)){ continue; }
			
			if($i->TypeOrder == 'Перевод' && $i->Reason == 'На следующий курс'){
				if( $last_order_transfer->record_card_id != $i->record_card_id ){
					continue;
				}
			}
			$filtered_orders[] = $this->normalizOrderNote($i->OrderNote);
		}
		return $filtered_orders;
	}
	
	
	/**
	 * @return string
	 * основу обучения получаем в том виде, в котором нужно вывести на странице
    */
	private function getBased($orders)
	{
		$last_order 		= array();		
		$allow_order_types	= HM_CertificatesStudent_CertificatesStudentModel::getAllowOrderTypes();
		
		# получаем последний актуальный приказ
		foreach($orders as $i){
			$order_type = mb_strtolower (trim($i->TypeOrder), 'UTF-8');
			if(!in_array($order_type, $allow_order_types)){ continue; }
			
			
			if(empty($last_order)){
				$last_order = $i;
				continue;
			}
				
			if(strtotime($last_order->DateTake) < strtotime($i->DateTake)){
				$last_order = $i;
				continue;
			}
		}
		return HM_CertificatesStudent_CertificatesStudentModel::getBasedFormatted($last_order->Based);
	}
	
	
	public function getPathToSign($date_created = false)
	{
		$path 			= __DIR__.'/../views/files/type/confirming-student/';
		if(!$date_created){
			$date_created	= date('Y-m-d');			
		} else {
			$date_created	= date('Y-m-d', strtotime($date_created));	
		}
		
		if($date_created < HM_Certificates_CertificatesModel::SIGN_KRAPOTKINA_DATE_BEFORE){
			return $path.'/old/krapotkina/';
		}
		
		if(($date_created >= HM_Certificates_CertificatesModel::SIGN_GYBINA_DATE_FROM) and ($date_created < HM_Certificates_CertificatesModel::SING_ELAGINA_DATE_FROM)){
			return $path.'/Gybina/';
		}	
		
		if($date_created >= HM_Certificates_CertificatesModel::SING_ELAGINA_DATE_FROM){
			return $path;
		}	
		
		return $path;
	}
	/*
	public function getDateValidTo($year_start, $cource)
	{
		$timestamp_begin_learning = strtotime($this->_current_user->begin_learning);
		if($timestamp_begin_learning <= 0){ return false; }
		
		$dt = new DateTime();
		$dt->setTimestamp($timestamp_begin_learning);
		$dt->add(new DateInterval('P'.$cource.'Y')); # +N лет. Если первый курс, то +1 год, для второго курса - +2 года
		$dt->sub(new DateInterval('P1D')); # -1 день
		
		$zd = new Zend_Date($dt->format('d.m.Y'));			
		return $zd->get(Zend_Date::DATE_LONG);
	}
	*/
	
	# получаем контент фото студента с ftp по его гуиду
	/*
	public function getPhoto()
	{
		if(!$this->_recService){ $this->_recService = $this->getService('RecordCard'); }
		$content = $this->_recService->getPhoto($this->_current_user->mid_external);
		
		return $content;
	}
	*/
		
	
}