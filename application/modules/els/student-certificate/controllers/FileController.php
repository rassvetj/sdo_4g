<?php
class StudentCertificate_FileController extends HM_Controller_Action {
	
	private $_pdf           = false;
	private $_orderCaption  = false;
	private $_orderFileName = false;
	private $_type          = false;
	
	public function init(){
		parent::init();
	}
	
	public function getStatementAction()
	{
		$request        = $this->getRequest();
		$validator      = new Zend_Validate_EmailAddress();
		$data           = array();
		$type           = (int)$request->getParam('type');
		$flashMessenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');		
		
		$requiredFields = $this->getRequiredFields($type);
		$values         = $request->getParams();		
		$error          = $this->getError($type, $values);
		
		$this->_type          = $type;
		$this->_orderCaption  = $this->getCaption($type);
		$this->_orderFileName = $this->getFileName($type);
		
		if($error){			
			$flashMessenger->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => $error,
			));
			$this->_redirect('/');
			die;
		}
		
		$data = $this->prepareData($values);
		
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
		$user         = $this->getService('User')->getCurrentUser();
		$userInfo     = $this->getService('UserInfo')->getByCode($user->mid_external);
		$templateName = $this->getTemplateName();
		
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(15, 5, 15);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
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
		
		
		$this->view->fio_genitive        = $userInfo->fio_genitive;
		$this->view->fio                 = $user->getName();
		$this->view->direction           = $data['direction'];
		$this->view->study_form          = $this->prepareStudyForm($data['study_form_name']);
		$this->view->based               = $this->prepareBased($data['basis_learning_name']);
		$this->view->education_level     = $this->getEducationLevel($userInfo->education_type); # уровень образования
		$this->view->education_type      = $this->prepareEducationType($userInfo->education_type);
		$this->view->course              = intval($data['course']) ? intval($data['course']) : intval($userInfo->course);
		$this->view->phone               = $data['phone'];
		$this->view->email               = $data['email'];
		$this->view->academic_leave_type = $this->prepareAcademicLeaveType($data['academic_leave_type'], $data['academic_leave_type_name']);
		$this->view->duration            = $this->getDuration($data['academic_leave_type']);
		
		$this->view->transfer_type       = $this->prepareTransferType($data['transfer_type']);
		
		$this->view->direction_desired   = $data['direction_desired_name'];
		
		if($this->_type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER){
			$this->view->educationTypes      = HM_StudentCertificate_StudentCertificateModel::getEducationTypes();
			$this->view->education_type      = $data['education_type'];
			$this->view->education_type_name = $data['education_type_name'];
			
			$this->view->programs      = HM_StudentCertificate_StudentCertificateModel::getPrograms();
			$this->view->program      = $data['program'];
			$this->view->program_name = $data['program_name'];
			
			$this->view->studyForms      = HM_StudentCertificate_StudentCertificateModel::getStudyForms();
			$this->view->study_form      = $data['study_form'];
			$this->view->study_form_name = $data['study_form_name'];
			
			$this->view->basis_learning = $data['basis_learning'];
		}
		
		if($this->_type == HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION){			
			$this->view->expulsion_type = $data['expulsion_type'];
			$this->view->university     = $data['university'];		
		}
		
		
		
		
		
		$this->view->day                 = date('d');
		$this->view->month               = $this->getHumanMonth(date('n'));
		$this->view->year                = date('Y');
		
		$this->view->logo = $_SERVER["DOCUMENT_ROOT"] . '/../../public/images/icons/logo_rgsu.png';
		$this->view->line = $_SERVER["DOCUMENT_ROOT"] . '/../../public/images/icons/line_orange.jpg';
		
		$this->view->url_checkbox_empty   = $_SERVER["DOCUMENT_ROOT"] . '/../../public/images/icons/checkbox_empty.png';
		$this->view->url_checkbox_checked = $_SERVER["DOCUMENT_ROOT"] . '/../../public/images/icons/checkbox_checked.png';		
		
		#$this->view->documents           = 'Фото.jpg, Паспорт.pdf';
		#$this->view->transfer_type  = $data['transfer_type'];
		#$this->view->organization   = $data['organization'];		
		#$this->view->program        = $data['program'];		
		#$this->view->basis_learning = $data['basis_learning'];
		
		
		#$this->view->direction_training = ''; # НАправление подготовки
		#$this->view->course         = $data['course_c'];
				
		
		
		
		try {
			$content = $this->view->render('certificate/export/pdf/' . $templateName . '.tpl');
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			die;
		}
		
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	private function prepareTransferType($transfer_type_id)
	{
		$types = array(
			HM_StudentCertificate_StudentCertificateModel::TRANSFER_TYPE_CHANGE_STUDY_FORM => _('на другую форму обучения'),
			HM_StudentCertificate_StudentCertificateModel::TRANSFER_TYPE_CHANGE_SPECIALITY => _('на другую специальность'),
			HM_StudentCertificate_StudentCertificateModel::TRANSFER_TYPE_CHANGE_FILIAL     => _('в/из филиала РГСУ'),
		);
		return $types[$transfer_type_id];
	}
	
	private function prepareAcademicLeaveType($academic_leave_type_id, $academic_leave_type_name)
	{
		if($academic_leave_type_id == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_BABY_CARE){
			return _('по уходу за ребенком до достижения им возраста 3-х лет');
		}
		return $academic_leave_type_name;
	}
	
	# по уходу за ребенком до достижения им возраста 3-х лет
	
	private function getDuration($academic_leave_type)
	{	
		if(
			$academic_leave_type == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_FAMILY
			||
			$academic_leave_type == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_MEDICAL
		){
			$dt = new DateTime();
			$dateFrom = $dt->format('d.m.Y');
			$dt->modify('+1 year');
			$dateTo   = $dt->format('d.m.Y');
			return _('с') . ' ' .  $dateFrom . ' ' . _('по') . ' ' . $dateTo;
		}
		
		if(
			$academic_leave_type == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_ARMY			
		){
			return _('1 год с даты убытия по повестке');
		}
		
		if(
			$academic_leave_type == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_PREGNANCY	
		){
			return     _('с') 
					. '&nbsp;«<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>»'
					. '<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'
					. '20<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>г.&nbsp;'
					.  _('по') 
					. '&nbsp;«<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>»'
					. '<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'
					. '20<span style="text-decoration: underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>г';
		}
		
		if(
			$academic_leave_type == HM_StudentCertificate_StudentCertificateModel::ACADEMIC_LEAVE_TYPE_BABY_CARE			
		){
			$dt = new DateTime();
			$dateFrom = $dt->format('d.m.Y');
			$dt->modify('+3 years');
			$dateTo   = $dt->format('d.m.Y');
			return _('с') . ' ' .  $dateFrom . ' ' . _('по') . ' ' . $dateTo;
		}
		return false;
	}
	
	private function getEducationLevel($education_type)
	{
		if(mb_stripos($education_type, '(специалист)') !== false){
			return _('специалитета');
		}
		
		if(mb_stripos($education_type, '(магистр)') !== false){
			return _('магистратуры');
		}
		
		if(mb_stripos($education_type, '(бакалавр)') !== false){
			return _('бакалавриата');
		}
		
		if(mb_stripos($education_type, '(базовый уровень)') !== false){
			return _('базового уровеня');
		}
		
		if(mb_stripos($education_type, '(повышенный уровень)') !== false){
			return _('повышенного уровня');
		}
		
		return false;
	}
	
	private function prepareEducationType($education_type)
	{
		$educationTypes = array(
			'Высшее образование (бакалавр)'                          => _('высшего образования'),
			'Высшее образование (магистр)'                           => _('высшего образования'),
			'Среднее профессиональное образование (базовый уровень)' => _('среднего профессионального образования'),
			'Высшее образование (специалист)'                        => _('высшего образования'),
			'Среднее профессиональное (повышенный уровень)'          => _('среднего профессионального образования'),
			'Аспирантура, ординатура, адъюнктура'                    => _('аспирантуры, ординатуры, адъюнктуры'),
		);
		return $educationTypes[$education_type];
	}
	
	private function prepareStudyForm($study_form)
	{
		$list = array(
			'очная'                                          => 'очной',
			'очно-заочная'                                   => 'очно-заочной',
			'заочная'                                        => 'заочной',
			'заочная с применением дистанционных технологий' => 'заочной с применением дистанционных технологий',
		);
		return $list[$study_form];
	}	
	
	private function prepareBased($based)
	{
		$list = array(
			'контрактная' => _('контрактной'),
			'бюджетная'   => _('бюджетной'),
		);
		return $list[$based];
	}	
	
	
	
	private function getTemplateName()
	{
		if($this->_type == HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE){
			return 'academic_leave';
		}
		
		if($this->_type == HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER){
			return 'transfer';
		}
		
		if($this->_type == HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION){
			return 'expulsion';
		}
	}
	
	private function outputOrderPdf()
	{
		return $this->_pdf->Output($this->_orderFileName . '_' . date('Y.m.d_H.i') . '_.pdf', 'I');
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
	
	private function getCaption($type)
	{
		$list = array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE => _('Заявление на академический отпуск'),
			HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER       => _('Заявление на перевод'),
			HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION      => _('Заявление на отчисление'),
		);
		return $list[$type];
	}
	
	private function getFileName($type)
	{
		$list = array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE => 'statement_academic_leave',
			HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER       => 'statement_transfer',
			HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION      => 'statement_expulsion',
		);
		return $list[$type];
	}
	
	
	
	private function getRequiredFields($type)
	{
		$list = array(
			HM_StudentCertificate_StudentCertificateModel::TYPE_ACADEMIC_LEAVE => array(
				'email'               => _('Email'),
				'phone'               => _('Номер телефона'),
				'direction'           => _('Направление подготовки'),
				'study_form'          => _('Форма обучения'),
				'basis_learning'      => _('Основа обучения'),
				'academic_leave_type' => _('Вид академического отпуска'),
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_TRANSFER       => array(
				'fio'               => _('Ф.И.О.'),
				'email'             => _('E-Mail'),
				'phone'             => _('Номер телефона'),
				'course'            => _('Курс'),
				'transfer_type'     => _('Желаемый перевод'),
				'direction_desired' => _('Желаемое направление подготовки'),
				'education_type'    => _('На базе'),
				'program'           => _('Программа обучения'),
				'study_form'        => _('Форма обучения'),
				'basis_learning'    => _('Основа обучения'),
			),
			HM_StudentCertificate_StudentCertificateModel::TYPE_EXPULSION      => array(
				'fio'               => _('Ф.И.О.'),
				'email'             => _('E-Mail'),
				'phone'             => _('Номер телефона'),
				'course'            => _('Курс'),
				'direction'         => _('Направление подготовки'),
				'study_form'        => _('Форма обучения'),
				'basis_learning'    => _('Основа обучения'),				
				'expulsion_type'    => _('Тип отчисления'),
			),
		);
		return $list[$type];
	}
	
	private function getError($type, $values)
	{
		$requiredFields = $this->getRequiredFields($type);
		
		foreach($requiredFields as $name => $caption){			
			$value = trim($values[$name]);
			
			if(empty($value)){
				return _('Заполните поле "' . $caption . '"');
			}
		}
		return false;
	}
	
	private function prepareData($raw)
	{
		$user     = $this->getService('User')->getCurrentUser();
		$userInfo = $this->getService('UserInfo')->getCurrentUserInfo();
		
		$data = array(
			'fio_genitive'             => $userInfo->fio_genitive,
			'fio'                      => $user->getName(),
			'phone'                    => $raw['phone'],
			'email'                    => $raw['email'],
			'direction'                => $raw['direction'],
			
			'study_form'               => $raw['study_form'],
			'study_form_name'          => HM_StudentCertificate_StudentCertificateModel::getStudyFormById($raw['study_form']),
			
			'basis_learning'           => $raw['basis_learning'],
			'basis_learning_name'      => HM_StudentCertificate_StudentCertificateModel::getBasisLearningById($raw['basis_learning']),
			
			'academic_leave_type'      => $raw['academic_leave_type'],
			'academic_leave_type_name' => HM_StudentCertificate_StudentCertificateModel::getAcademicLeaveTypeName($raw['academic_leave_type']),
			
			'day'                      => date('d'),
			'month'                    => $this->getHumanMonth(date('n')),
			'year'                     => date('Y'),
			
			'course'                   => (int)$raw['course'],
			
			'transfer_type'            => $raw['transfer_type'],
			'transfer_type_name'       => HM_StudentCertificate_StudentCertificateModel::getTransferTypeById($raw['transfer_type']),
			
			'education_type'           => $raw['education_type'],
			'education_type_name'      => HM_StudentCertificate_StudentCertificateModel::getEducationTypeName($raw['education_type']),
			
			'program'           => $raw['program'],
			'program_name'      => HM_StudentCertificate_StudentCertificateModel::getProgramById($raw['program']),
			
			'expulsion_type'      => $raw['expulsion_type'],
			'expulsion_type_name' => HM_StudentCertificate_StudentCertificateModel::getExpulsionTypeName($raw['expulsion_type']),
			
			'university'          => $raw['university'],
		);
		
		if(!empty($raw['direction_desired'])){
			$direction                      = $this->getService('StudentCertificate')->getDirectionById($raw['direction_desired']);
			$data['direction_desired']      = $raw['direction_desired'];
			$data['direction_desired_name'] = $direction->name;
		}
		return $data;
	}

	
}



