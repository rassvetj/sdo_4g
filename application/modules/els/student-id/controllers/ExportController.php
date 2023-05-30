<?php
class StudentId_ExportController extends HM_Controller_Action
{
	private $_pdf  			= false;
	private $_current_user 	= false;
	private $_first_doc		= false;
	private $_disciplins	= false;
	
	public function init()
	{
		parent::init();
		
		$current_user		= $this->getService('User')->getCurrentUser();	
		$recordbook_number	= $this->getService('RecordCard')->getRecordbookNumber($current_user->mid_external);
		$isStudent			= empty($recordbook_number) ? false : true;		
		if(!$isStudent){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Студенческий билет сейчас не доступен. Попробуйте завтра'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');
			die;	
		}
		
	}
	
	
	
	public function pdfAction()
    {
        $this->_helper->layout()->disableLayout();
		
		$this->_current_user = $this->getService('User')->getCurrentUser();
		
		$this->_recService	= $this->getService('RecordCard');
		$this->_first_doc	= $this->_recService->getFirstActualOrder($this->_current_user->mid_external);
		
		$this->_delay_days	= $this->_recService->getDelayDays($this->_current_user->mid_external); # дней отсрочки. Например, академ. отпуск.
		$this->_studyCardService 	= $this->getService('StudyCard'); 
		$this->_disciplins 			= $this->_studyCardService->getDisciplins($this->_current_user->mid_external); //-_получаем список всех зачетов и экзаменов, что сдавал студент
		$this->_recordbook_info 	= $this->_recService->getRecordbookInfo($this->_current_user->mid_external);
		
		$this->initPdf();
		$this->addPageMain();
		
		echo $this->outputPdf();
		die;
    }
	
	
	private function initPdf()
	{
		require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';
		
		$this->_pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
		$this->_pdf->SetProtection(array('copy','modify','annot-forms','fill-forms','extract','assemble'));
		
		$this->_pdf->SetFont('times', 'BI', 20, '', 'false');
		
		$this->_pdf->SetCreator(PDF_CREATOR);
		$this->_pdf->SetAuthor('RGSU');
		$this->_pdf->SetTitle('Студенческий билет');
		$this->_pdf->SetSubject('Студенческий билет');
		$this->_pdf->SetKeywords('Студенческий билет');

		$this->_pdf->setPrintHeader(false);
		$this->_pdf->setPrintFooter(false);
		$this->_pdf->SetMargins(5, 0, 2);
		$this->_pdf->SetFooterMargin(5);
		$this->_pdf->SetAutoPageBreak(false);
		return true;
	}
	
	
	private function addPageMain()
	{	
		
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
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
		
		
		$this->_recService = $this->getService('RecordCard');
		
		$this->view->number 	= $this->_recService->getRecordbookNumber($this->_current_user->mid_external);
		$this->view->last_name	= $this->_current_user->LastName;
		
		$this->view->first_name = $this->_current_user->FirstName;
		$this->view->patronymic = $this->_current_user->Patronymic;
		
		# форма обучения
		$this->view->form_study = $this->_recordbook_info->study_form;
		
		# Зачислен приказом от
		$this->view->date_from	= '';
		if(strtotime($this->_first_doc->DateFrom) > 0){
			$date_take 				= new Zend_Date($this->_first_doc->DateFrom);			
			$this->view->date_from	= $date_take->get(Zend_Date::DATE_LONG);
		}
		
		# дата выдачи
		$this->view->date_take	= '';
		if(strtotime($this->_first_doc->DateTake) > 0){
			$date_take 				= new Zend_Date($this->_first_doc->DateTake);			
			$this->view->date_take	= $date_take->get(Zend_Date::DATE_LONG);
		}
		# Зачислен приказом №
		$this->view->code 	= trim($this->_first_doc->Code);
		
		# Руководитель организации, осуществляющей ообразовательную деятельность, или иное уполномоченное должностное лицо
		$this->view->director_organization_fio = 'Самойленко Д.Н.';
		
		
		# Руководитель структурного подразделения
		$this->view->director_subdivision_fio = $this->getDirectorSubdivisionName();
		
		
		$this->view->years = array();
			
			
		# Через _disciplins - не надежный способ определения года.
		$years = ceil(intval($this->_recordbook_info->semester) / 2);			
		for ($i=1; $i<=$years; $i++) {
			$this->view->years[$i] = array(
				'year' 						=> $i,
				'director_subdivision_fio'	=> $this->view->director_subdivision_fio,
			);
		}
			
		for ($i=6; $i>$years; $i--) {
			$this->view->years[$i] = false;
		}
			
		
		
		if($this->_disciplins){
			foreach($this->_disciplins as $j){
				$i = (object)$j;				
				$i->Date = date('d.m.Y', strtotime($i->Date));
				/*
				$year = intval(floor(($i->Semester + 1)/2));
				$this->view->years[$year] = array(
					'year' 						=> $year,
					'director_subdivision_fio'	=> $this->view->director_subdivision_fio,
				);
				*/
			}
		}
		
		/*
		if(empty($this->view->years)){
			$this->view->years[1] = array(
										'year' 						=> 1,
										'director_subdivision_fio'	=> $this->view->director_subdivision_fio,
									);
		}
		*/
		
		# в каком году поступил
		$year_start = false;
		
		# было: с даты первого приказа Стало с даты начала обучения студента, если задано.		
		$ts 		= strtotime($this->_current_user->begin_learning);		
		$ts 		= $ts > 0 ? $ts : strtotime($this->_first_doc->DateTake); 
		
		if($ts > 0){ $year_start = date('Y', $ts); }
		
		# Действителен по 
		$this->view->date_valid = array();
		
		foreach($this->view->years as $year => $i){
			if(empty($i)){ continue; }
			$this->view->date_valid[$year] = $this->getDateValidToFormatted($year, $years); # зависит от даты начала обучения			
			
			$this->view->years[$year]['director_subdivision_fio'] = $this->getDirectorSubdivisionName($year, $years);			
		}
		
		# это должно быть ПОСЛЕ вывода печатей		
		#$this->view->years	= array_pad($this->view->years, 6, false);
		
		
		$content = $this->view->render('export/parts/main.tpl');
		
		$image_cover              = $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\cover\all.png'; # обложка	
		$this->_pdf->Image($image_cover, 0, 3, 295, 0, '', '', '', false, 100);
		
		# Аватар
		// The '@' character is used to indicate that follows an image data stream and not an image file name
		$user_photo_content = $this->getPhoto();
		if(!empty($user_photo_content)){
			$this->_pdf->Image('@'.$user_photo_content, 10, 111, 32, 0, '', '', '', false, 100);
		}
		
		
		$director_organization_sign		= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Samoylenko.png'; # подпись руководителя организации
		$this->_pdf->Image($director_organization_sign,  44, 176, 20, 0, '', '', '', false, 300); # Подпись
		
		
		# Печать и подпись
		$dean_office_stamp				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\stamp\dean_office.png'; # печать деканата
		
		# Печать гербовая
		$main_stamp				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\stamp\rgsu.png'; # печать деканата
		$this->_pdf->Image($main_stamp, 16, 139, 35, 0, '', '', '', false, 300);
		
		
		if(!empty($this->view->years[1])){
			$this->_pdf->Image($dean_office_stamp, 151, 108, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(1, $years), 210, 110, 12, 0, '', '', '', false, 300);				
		}
		
		if(!empty($this->view->years[2])){
			$this->_pdf->Image($dean_office_stamp, 155, 115, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(2, $years), 210, 123, 12, 0, '', '', '', false, 300);
		}
			
		if(!empty($this->view->years[3])){
			$this->_pdf->Image($dean_office_stamp, 152, 130, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(3, $years), 210, 136, 12, 0, '', '', '', false, 300);				
		}
			
		if(!empty($this->view->years[4])){
			$this->_pdf->Image($dean_office_stamp, 157, 140, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(4, $years), 210, 148, 12, 0, '', '', '', false, 300);
		}
			
		if(!empty($this->view->years[5])){
			$this->_pdf->Image($dean_office_stamp, 151, 156, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(5, $years), 210, 160, 12, 0, '', '', '', false, 300);
		}
			
		if(!empty($this->view->years[6])){
			$this->_pdf->Image($dean_office_stamp, 157, 160, 35, 0, '', '', '', false, 300);
			$this->_pdf->Image($this->getDirectorSubdivisionSignPath(6, $years), 210, 174, 12, 0, '', '', '', false, 300);
		}
		
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	
	
	
	private function outputPdf()
	{
		return $this->_pdf->Output('student_id.pdf', 'I');		
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
	
	# @return object DateTime
	public function getDateValidTo($cource, $years_total)
	{
		$timestamp_begin_learning = strtotime($this->_current_user->begin_learning);
		if($timestamp_begin_learning <= 0){ return false; }
		
		$dt = new DateTime();
		$dt->setTimestamp($timestamp_begin_learning);
		$dt->add(new DateInterval('P'.$cource.'Y')); # +N лет. Если первый курс, то +1 год, для второго курса - +2 года
		$dt->sub(new DateInterval('P1D')); # -1 день
		
		# Если есть отсрочка и дата последнего продления меньше текущей, то накидываем еще год.
		# иначе оставляем как есть.
		if($cource == $years_total && !empty($this->_delay_days)){
			$dt_current = new DateTime('now');
			if($dt_current > $dt){
				$dt->add(new DateInterval('P1Y'));
				
				# второй год отсрочки? Оо.
				if($this->_delay_days > 365){
					$dt->add(new DateInterval('P1Y'));
				}
				# третий год отсрочки
				if($this->_delay_days > 730){
					$dt->add(new DateInterval('P1Y'));
				}
			}
		}
		return $dt;
	}
	
	public function getDateValidToFormatted($cource, $years_total)
	{
		$dt = $this->getDateValidTo($cource, $years_total);
		if(!$dt){ return false; }
		$zd = new Zend_Date($dt->format('d.m.Y'));			
		return $zd->get(Zend_Date::DATE_LONG);
	}
	
	# получаем контент фото студента с ftp по его гуиду
	public function getPhoto()
	{
		#$user_photo 			= $_SERVER["DOCUMENT_ROOT"].'\\..\\'.$this->_current_user->getPhoto();
		#$user_photo 			= explode('?',$user_photo);
		#$user_photo 			= $user_photo[0];		
		#$content = file_get_contents ($user_photo);
		
		if(!$this->_recService){ $this->_recService = $this->getService('RecordCard'); }
		$content = $this->_recService->getPhoto($this->_current_user->mid_external);
		
		$finfo 		= new finfo(FILEINFO_MIME_TYPE);
		$mime_type	= $finfo->buffer($content);
		
		$allow_mimes = array('image/jpeg', 'image/png', 'image/x-ms-bmp');
		
		if(!in_array($mime_type, $allow_mimes)){
			
			# for test 
			if($this->_current_user->MID == 93943){
				$user_photo 			= $_SERVER["DOCUMENT_ROOT"].'\\..\\'.$this->_current_user->getPhoto();
				$user_photo 			= explode('?',$user_photo);
				$user_photo 			= $user_photo[0];		
				$content = file_get_contents ($user_photo);
				if($content){
					return $content;
				}
			}
			return false;
		}		
		return $content;
	}
	
	public function getDirectorSubdivisionName($cource = false, $years_total = false)
	{
		if(empty($cource) || empty($years_total)){
			return _('Елагина П.В.');
		}
				
		$dt = $this->getDateValidTo($cource, $years_total);

		if(($dt->getTimestamp() >= strtotime('05.09.2020')) and ($dt->getTimestamp() < strtotime('05.09.2022'))){
			return 'Гыбина Т.Г.';
		}

		if(!($dt instanceof DateTime)){
			return _('Елагина П.В.');
		}
		
		if(($dt->getTimestamp() < strtotime('05.09.2020')) or ($dt->getTimestamp() >= strtotime('05.09.2022'))){
			return _('Елагина П.В.');
		}
		
		return 'Елагина П.В.';
	}
	
	# подпись руководителя структурного подразделения
	public function getDirectorSubdivisionSignPath($cource = false, $years_total = false)
	{
		if(empty($cource) || empty($years_total)){
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Elagina.png';
		}
		
		$dt = $this->getDateValidTo($cource, $years_total);

		if(($dt->getTimestamp() >= strtotime('05.09.2020')) and ($dt->getTimestamp() < strtotime('05.09.2022'))){
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Gybina.png';
		}
		
		if(!($dt instanceof DateTime)){
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Elagina.png';
		}
		
		if(($dt->getTimestamp() < strtotime('05.09.2020')) or ($dt->getTimestamp() >= strtotime('05.09.2022'))){
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Elagina.png';
		}
		
		return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-id\views\files\sign\Elagina.png';
	}
	
	
	 
}