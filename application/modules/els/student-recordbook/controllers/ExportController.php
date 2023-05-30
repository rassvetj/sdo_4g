<?php
class StudentRecordbook_ExportController extends HM_Controller_Action
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
					'message' => _('Зачетная книжка сейчас не доступна. Попробуйте завтра'))
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
		
		
		$this->_studyCardService 	= $this->getService('StudyCard'); 
		$this->_disciplins 			= $this->_studyCardService->getDisciplinsFrom1C($this->_current_user->mid_external); //-_получаем список всех зачетов и экзаменов, что сдавал студент
		
		
		$recordbook_info = $this->_recService->getRecordbookInfo($this->_current_user->mid_external);
		$this->_current_semester = (int)$recordbook_info->semester;
		
		
		$this->initPdf();
		$this->addPageMain();
		
		for($year=1; $year<=6; $year++){
			$this->addPageBase($year);
		}
		
		$this->addPageFacultativeCourse();		
		$this->addPagePracticScientificWork();
		
		$this->addPageGosGiaWork();
		
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
		$this->_pdf->SetTitle('Зачетная книжка');
		$this->_pdf->SetSubject('Зачетная книжка');
		$this->_pdf->SetKeywords('Зачетная книжка');

		$this->_pdf->setPrintHeader(false);
		$this->_pdf->setPrintFooter(false);
		$this->_pdf->SetMargins(5, 0, 2);
		$this->_pdf->SetFooterMargin(5);
		$this->_pdf->SetAutoPageBreak(false);
		return true;
	}
	
	
	private function addPageMain()
	{	
		$this->_recService = $this->getService('RecordCard');
		
		$this->view->cover_main 		= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\cover\main.png'; # обложка		
		$dean_office_stamp				= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\stamp\dean_office.png'; # печать деканата
		
		$director_organization_stamp	= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\stamp\rgsu.png'; # печать руководителя организации
		$director_organization_sign		= $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\sign\Samoylenko.png'; # подпись руководителя организации
		$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath(); # подпись руководителя структурного подразделения
		
		#$user_photo 			= $_SERVER["DOCUMENT_ROOT"].'\\..\\'.$this->_current_user->getPhoto();
		#$user_photo 			= explode('?',$user_photo);
		#$user_photo 			= $user_photo[0];
		
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		# 0 = auto
		# Аватар
		#$this->_pdf->Image($user_photo, 3, 111, 40, 0, '', '', '', false, 300);
		
		$user_photo_content = $this->getPhoto();
		if(!empty($user_photo_content)){
			$this->_pdf->Image('@'.$user_photo_content, 3, 111, 40, 0, '', '', '', false, 300);
		}
		
		
		# Печать РГСУ
		$this->_pdf->Image($dean_office_stamp, 25, 135, 40, 0, '', '', '', false, 300);
		
		# Руководитель организации, осуществляющей ообразовательную деятельность, или иное уполномоченное должностное лицо
		$this->_pdf->Image($director_organization_stamp, 200, 155, 35, 0, '', '', '', false, 300); # Печать
		$this->_pdf->Image($director_organization_sign,  180, 157, 0, 10, '', '', '', false, 300); # Подпись
		#$this->_pdf->Image($director_organization_fio, 	 235, 160, 35, 0, '', '', '', false, 300); # ФИО
		
		$this->view->director_organization_fio = 'Самойленко Д.Н.';
		$this->view->director_organization_fio = str_pad($this->view->director_organization_fio, 68, ' ', STR_PAD_BOTH);
		$this->view->director_organization_fio = str_replace(' ', '&nbsp;', $this->view->director_organization_fio);
		
		
		# Руководитель структурного подразделения
		$this->_pdf->Image($director_subdivision_sign,  200, 190, 0, 10, '', '', '', false, 300); # Подпись
		#$this->_pdf->Image($director_subdivision_fio, 	235, 189, 35, 0, '', '', '', false, 300); # ФИО	
		$this->view->director_subdivision_fio = $this->getDirectorSubdivisionName();
		$this->view->director_subdivision_fio = str_pad($this->view->director_subdivision_fio, 100, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio);		
		
		# переменные для вывода в шаблоне
		$this->view->has_user_photo 		= !empty($user_photo_content) 		? true : false;
		$this->view->has_stamp_dean_office 	= (file_exists($dean_office_stamp) 	? true : false);
		
		$this->view->number = $this->_recService->getRecordbookNumber($this->_current_user->mid_external);
		$this->view->number = str_pad($this->view->number, 10, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->number);
		
		$this->view->fio 	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		$this->view->fio 	= str_pad($this->view->fio, 145, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->fio);
		
		
		
		# дата выдачи зачетной книжки
		if(empty($this->_first_doc->DateTake)){
			$this->view->date_take = '20      г.';	
		} else {
			$other_date 			= new Zend_Date($this->_first_doc->DateTake);
			$this->view->date_take	= $other_date->get(Zend_Date::DATE_LONG);
		}		
		$this->view->date_take 	= str_pad($this->view->date_take, 35, ' ', STR_PAD_LEFT);
		$this->view->date_take = str_replace(' ', '&nbsp;', $this->view->date_take);
		
		# направление подготовки. Если не влезает на одну строку, обрезаем и переносим в specialization
		$this->view->speciality 	= $this->_first_doc->Speciality;
		$speciality_line_2			= '';
		if(mb_strlen($this->view->speciality) > 70){
			$speciality_line_2		= $this->view->speciality;
			$this->view->speciality = $this->smartCutting($this->view->speciality, 28);
			$speciality_line_2		= str_replace($this->view->speciality, '', $speciality_line_2);
		}		
		$this->view->speciality 	= str_pad($this->view->speciality, 65, ' ', STR_PAD_BOTH);
		$this->view->speciality = str_replace(' ', '&nbsp;', $this->view->speciality);
		
		# специальность
		$this->view->specialization = $speciality_line_2;		
		if(!empty($this->_first_doc->Specialization)){
			$this->view->specialization .= ' ('.$this->_first_doc->Specialization.')';
		}
		
		$this->view->specialization 	= str_pad($this->view->specialization, 145, ' ', STR_PAD_BOTH);
		$this->view->specialization = str_replace(' ', '&nbsp;', $this->view->specialization);
		
		# Структурное подразделение
		$this->view->faculty 		= trim($this->_first_doc->Faculty.' / '.$this->_current_user->organization, '/ ');
		
		
		$this->view->faculty 	= str_pad($this->view->faculty, 95, ' ', STR_PAD_BOTH);
		$this->view->faculty = str_replace(' ', '&nbsp;', $this->view->faculty);
		
		$this->view->order_name		= HM_RecordCard_RecordCardModel::getOrderName($this->_first_doc->TypeOrder);
		
		$this->view->order_number	= $this->_first_doc->Code;
		
		$content = $this->view->render('export/parts/main.tpl');
		
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	
	# базовые страницы.
	private function addPageBase($year = 1)
	{
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		$this->view->semestr_first  = ($year*2)-1;
		$this->view->semestr_second = $year*2;
		
		$this->view->year_begin 	= ($this->_first_doc->year_begin + $year) - 1;
		$this->view->year_end   	= $this->_first_doc->year_begin + $year;		
		
		if($this->_first_doc->Course > 1){
			$this->view->year_begin	= $this->view->year_begin - ($this->_first_doc->Course - 1);
			$this->view->year_end	= $this->view->year_end   - ($this->_first_doc->Course - 1);
		}
		
		$this->view->cource_name	= HM_RecordCard_RecordCardModel::getCourceName($year);
		
		$year_cur = date('Y');
		
		$this->view->fio 	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		#$this->view->fio = 'Константинопольский Константин Константинович';
		
		$this->view->fio 	= str_pad($this->view->fio, 90, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->fio);
		
		
		
		$this->view->credits_first  = array();
		$this->view->credits_second = array();		
		$this->view->exams_first    = array();
		$this->view->exams_second   = array();
		
		
		$this->view->next_course = false;
		
		if($this->_disciplins){
			foreach($this->_disciplins as $j){
				
				$i = (object)$j;
				
				if(
					$this->_current_user->MID == 17094
					&&
					in_array($i->DocNum, array('778', '779'))
				){
					continue;
				}
				
				
				
				$i->Date = date('d.m.Y', strtotime($i->Date));
				
				
				# оставляем фотмат Фамилия И.О.				
				#$i->Teacher = 'Константинопольский Константин Константинович';
				$parts_fio	= explode(' ', $i->Teacher);
				$parts_fio	= array_filter($parts_fio);
				$i->Teacher = $parts_fio[0];				
				$i->Teacher = $parts_fio[0].' '.mb_substr($parts_fio[1], 0, 1, 'utf-8').' '.mb_substr($parts_fio[2], 0, 1, 'utf-8').'';
				
				
				# длинна дисциплины должна быть не больше 2 строк
				$i->Disciplina = $this->smartCutting($i->Disciplina, 45);
				
				
				# скипаем практику и курсовую
				if($i->Vid == HM_StudyCard_StudyCardModel::VID_PRACTIC || $i->Vid == HM_StudyCard_StudyCardModel::VID_COURSE_WORK){ continue; }
				
				
				# Есть дисциплины следующего курса, значяит переведен на след. курс.
				if($i->Semester == ($this->view->semestr_second+1)){
					$this->view->next_course = $year+1;
				}
				
				
				if($this->view->semestr_first == $i->Semester && in_array($i->Type, array(HM_StudyCard_StudyCardModel::TIPE_ZACHET, HM_StudyCard_StudyCardModel::TIPE_TEST))){
					if(count($this->view->credits_first) < 10){
						$this->view->credits_first[] = $i;						
					} else {					
						$this->view->exams_first[] = $i;
					}
					continue;
				}
				
				if($this->view->semestr_second == $i->Semester && in_array($i->Type, array(HM_StudyCard_StudyCardModel::TIPE_ZACHET, HM_StudyCard_StudyCardModel::TIPE_TEST))){
					if(count($this->view->credits_second) < 10){
						$this->view->credits_second[] = $i;
					} else {
						$this->view->exams_second[] = $i;
					}
					continue;
				}
				
				if($this->view->semestr_first == $i->Semester && HM_StudyCard_StudyCardModel::TIPE_EXAM == $i->Type){
					if(count($this->view->exams_first) < 10){
						$this->view->exams_first[] = $i;
					} else {
						$this->view->credits_first[] = $i;
					}
					continue;
				}
				
				if($this->view->semestr_second == $i->Semester && HM_StudyCard_StudyCardModel::TIPE_EXAM == $i->Type){
					if(count($this->view->exams_second) < 10){
						$this->view->exams_second[] = $i;
					} else {
						$this->view->credits_second[] = $i;
					}
					continue;
				}
			}
		}
		
		# на странице экзаменов первыми идут экзамены, а уже после зачеты, что не вошли на страницу зачетов
		usort($this->view->exams_first, array($this, 'sortByExam'));
		usort($this->view->exams_second, array($this, 'sortByExam'));
		
		# на странице зачетов первыми идут зачеты, а уже после экзамены, что не вошли на страницу экзаменов
		usort($this->view->credits_first, array($this, 'sortByCredit'));
		usort($this->view->credits_second, array($this, 'sortByCredit'));
		
		
		$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath($this->view->year_begin, $this->view->year_end); # подпись руководителя структурного подразделения
		
		
		
		$this->view->director_subdivision_fio_first		= '';
		$this->view->director_subdivision_fio_second	= '';
		
		# подпись только в прошедших семестрах. В текущих не ставим!!
		if(!empty($this->view->credits_second) || !empty($this->view->exams_second) ||  ($this->view->semestr_first < $this->_current_semester)  ){
			if(!empty($this->view->credits_first) || !empty($this->view->exams_first)){
				# Руководитель структурного подразделения - 1 страница
				$this->_pdf->Image($director_subdivision_sign,  280, 98, 10, 0, '', '', '', false, 300); # Подпись
				$this->view->director_subdivision_fio_first = $this->getDirectorSubdivisionName($this->view->year_begin, $this->view->year_end);
			}
		}
		
		if($this->view->year_end < $year_cur ){
			if(!empty($this->view->credits_second) || !empty($this->view->exams_second)){
				# Руководитель структурного подразделения - 2 страница
				$this->_pdf->Image($director_subdivision_sign,  280, 198, 10, 0, '', '', '', false, 300); # Подпись
				$this->view->director_subdivision_fio_second = $this->getDirectorSubdivisionName($this->view->year_begin, $this->view->year_end);
			}
		}
		
		$this->view->director_subdivision_fio_first = str_pad($this->view->director_subdivision_fio_first, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_first = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_first);
		
		$this->view->director_subdivision_fio_second = str_pad($this->view->director_subdivision_fio_second, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_second = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_second);
		
		
		$this->view->credits_first 	= array_pad($this->view->credits_first, 10, false);
		$this->view->credits_second = array_pad($this->view->credits_second, 10, false);
		$this->view->exams_first	= array_pad($this->view->exams_first, 10, false);
		$this->view->exams_second 	= array_pad($this->view->exams_second, 10, false);
		
		$content = $this->view->render('export/parts/base.tpl');
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;		
	}
	
	# факультативы и курсовые работы
	private function addPageFacultativeCourse()
	{
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		
		$this->view->fio 	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		$this->view->fio 	= str_pad($this->view->fio, 90, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->fio);
		
		# пока у нас нет факультативов
		$this->view->facultative_credits 	= array();
		$this->view->facultative_exams 		= array();
		$this->view->course_work			= array();
		
		//--курсовые
		if($this->_disciplins){
			foreach($this->_disciplins as $j){
				$i = (object)$j;
				
				$i->Date = date('d.m.Y', strtotime($i->Date));
				
				$i->Semester = (int)$i->Semester;
				
				# оставляем фотмат Фамилия И.О.				
				#$i->Teacher = 'Константинопольский Константин Константинович';
				$parts_fio	= explode(' ', $i->Teacher);
				$parts_fio	= array_filter($parts_fio);
				$i->Teacher = $parts_fio[0];				
				$i->Teacher = $parts_fio[0].' '.mb_substr($parts_fio[1], 0, 1, 'utf-8').' '.mb_substr($parts_fio[2], 0, 1, 'utf-8').'';
				
				# длинна дисциплины должна быть не больше 2 строк
				$i->Disciplina = $this->smartCutting($i->Disciplina, 45);
				
				
				if($i->Vid != HM_StudyCard_StudyCardModel::VID_COURSE_WORK){ continue; }
				
				$this->view->course_work[] = $i;
			}
		}
		
		
		$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath(); # подпись руководителя структурного подразделения
		
		$this->view->director_subdivision_fio_facultative = '';
		$this->view->director_subdivision_fio_course_work = '';
		
		if(!empty($this->view->facultative_credits) || !empty($this->view->facultative_exams)){
			# Руководитель структурного подразделения - факультатив
			#$this->_pdf->Image($director_subdivision_fio, 	230, 98, 25, 0, '', '', '', false, 300); # ФИО
			$this->_pdf->Image($director_subdivision_sign,  280, 98, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_fio_facultative = $this->getDirectorSubdivisionName();
		}
		
		if(!empty($this->view->course_work)){
			# Руководитель структурного подразделения - курсовые
			#$this->_pdf->Image($director_subdivision_fio, 	230, 198, 25, 0, '', '', '', false, 300); # ФИО
			$this->_pdf->Image($director_subdivision_sign,  280, 198, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_fio_course_work = $this->getDirectorSubdivisionName();
		}
		
		
		$this->view->director_subdivision_fio_facultative = str_pad($this->view->director_subdivision_fio_facultative, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_facultative = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_facultative);
		
		$this->view->director_subdivision_fio_course_work = str_pad($this->view->director_subdivision_fio_course_work, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_course_work = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_course_work);
		
		
		$this->view->facultative_credits 	= array_pad($this->view->facultative_credits, 10, false);
		$this->view->facultative_exams 		= array_pad($this->view->facultative_exams, 10, false);
		$this->view->course_work 			= array_pad($this->view->course_work, 10, false);
		
		$content = $this->view->render('export/parts/facultative-course-work.tpl');
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	
	
	# практика и научно-исследовательская работа
	private function addPagePracticScientificWork()
	{
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		$this->view->fio 	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		$this->view->fio 	= str_pad($this->view->fio, 90, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->fio);
		
	
		$this->view->practic 			= array();
		$this->view->scientific_work	= array();
		
		
		if($this->_disciplins){
			foreach($this->_disciplins as $j){
				$i = (object)$j;
				
				$i->Date = date('d.m.Y', strtotime($i->Date));
				
				$i->Semester = (int)$i->Semester;
				
				# оставляем фотмат Фамилия И.О.				
				#$i->Teacher = 'Константинопольский Константин Константинович';
				$parts_fio	= explode(' ', $i->Teacher);
				$parts_fio	= array_filter($parts_fio);
				$i->Teacher = $parts_fio[0];				
				$i->Teacher = $parts_fio[0].' '.mb_substr($parts_fio[1], 0, 1, 'utf-8').' '.mb_substr($parts_fio[2], 0, 1, 'utf-8').'';
				
				# длинна дисциплины должна быть не больше 2 строк
				$i->Disciplina = $this->smartCutting($i->Disciplina, 45);
				
				
				if($i->Vid == HM_StudyCard_StudyCardModel::VID_PRACTIC){
					# СТранная pdf библиотека плохо реагирует нарусскую букву "Р" (эр). Меняем ее на английскую "P" (пи)
					if($i->Hours == 'ПР'){
						$i->Hours = 'ПP';
					}
						
					$this->view->practic[] = $i;
					continue;
				}
				
				#if($i->Vid == HM_StudyCard_StudyCardModel::VID_PRACTIC){
					#$this->view->scientific_work[] = $i;
					#continue;
				#}
			}
		}
		
		
		$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath(); # подпись руководителя структурного подразделения
		
		
		$this->view->director_subdivision_fio_practic = '';
		$this->view->director_subdivision_fio_scientific_work = '';
		
		
		if(!empty($this->view->practic)){
			# Руководитель структурного подразделения - практика
			#$this->_pdf->Image($director_subdivision_fio, 	230, 98, 25, 0, '', '', '', false, 300); # ФИО
			$this->_pdf->Image($director_subdivision_sign,  280, 98, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_fio_practic = $this->getDirectorSubdivisionName();
		}
		
		if(!empty($this->view->scientific_work)){
			# Руководитель структурного подразделения - научная работа
			#$this->_pdf->Image($director_subdivision_fio, 	230, 198, 25, 0, '', '', '', false, 300); # ФИО
			$this->_pdf->Image($director_subdivision_sign,  280, 198, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_fio_scientific_work = $this->getDirectorSubdivisionName();
		}
		
		$this->view->director_subdivision_fio_practic = str_pad($this->view->director_subdivision_fio_practic, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_practic = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_practic);
		
		$this->view->director_subdivision_fio_scientific_work = str_pad($this->view->director_subdivision_fio_scientific_work, 65, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_fio_scientific_work = str_replace(' ', '&nbsp;', $this->view->director_subdivision_fio_scientific_work);
		
		
		
		$this->view->practic 			= array_pad($this->view->practic, 10, false);
		$this->view->scientific_work 	= array_pad($this->view->scientific_work, 20, false);
		
		
		$content = $this->view->render('export/parts/practic-scientific-work.tpl');
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	
	# практика и научно-исследовательская работа
	private function addPageGosGiaWork()
	{
		$this->_pdf->AddPage(); 
		$this->_pdf->SetXY(2, 5, 5);
		$this->_pdf->SetDrawColor(100, 100, 0);
		
		$this->view->fio 	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		$this->view->fio 	= str_pad($this->view->fio, 90, ' ', STR_PAD_BOTH);
		$this->view->fio = str_replace(' ', '&nbsp;', $this->view->fio);
		
		
		
		$this->view->gia	= array();
		
		
		##
		$this->_recService	= $this->getService('RecordCard');		
		$this->view->gos = $this->_recService->getGOSFrom1C($this->_current_user->mid_external);
		
		if(empty($this->view->gos)){
			$this->view->gos 	= array();	
		}
		
		$this->view->gia = $this->_recService->getGIA($this->_current_user->mid_external);
		
		# допущен к государственной итоговой аттестации, если есть дата приказа гиа
		$this->view->gia_is_passed = (strtotime($this->view->gia->DateTake) > 0 ? true : false);
		#$this->view->gia_is_passed = true;
		
		$this->view->gia_date_take = '        г.';
		
		# ГОС данные
		if($this->view->gia_is_passed){
			$this->view->gia_is_passed_fio	= $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
			$gia_date_take 					= new Zend_Date($this->view->gia->DateTake);
			
			$this->view->gia_date_take 		= $gia_date_take->get(Zend_Date::DATE_LONG);
			$this->view->gia_date_take 	= str_pad($this->view->gia_date_take, 30, ' ', STR_PAD_LEFT);
			$this->view->gia_date_take = str_replace(' ', '&nbsp;', $this->view->gia_date_take);
			
			$this->view->gia_order_number 	= $this->view->gia->Code;			
			$this->view->gia_order_number 	= str_pad($this->view->gia_order_number, 15, ' ', STR_PAD_BOTH);
			$this->view->gia_order_number = str_replace(' ', '&nbsp;', $this->view->gia_order_number);			
		}
		
		$this->view->gia_date_take 	= str_pad($this->view->gia_date_take, 30, ' ', STR_PAD_LEFT);
		$this->view->gia_date_take = str_replace(' ', '&nbsp;', $this->view->gia_date_take);
			
		$this->view->gia_order_number 	= str_pad($this->view->gia_order_number, 15, ' ', STR_PAD_BOTH);
		$this->view->gia_order_number = str_replace(' ', '&nbsp;', $this->view->gia_order_number);
		
		$this->view->gia_is_passed_fio 	= str_pad($this->view->gia_is_passed_fio, 60, ' ', STR_PAD_BOTH);
		$this->view->gia_is_passed_fio = str_replace(' ', '&nbsp;', $this->view->gia_is_passed_fio);
		
		
		
		# ГИА данные и решение комиссии		
		$this->view->grad = $this->_recService->getGraduationWorkFrom1C($this->_current_user->mid_external);
		
		
		if(!empty($this->view->grad)){
			# Руководитель структурного подразделения - Решением государственной экзаменационной комиссии
			$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath(); # подпись руководителя структурного подразделения
			#$this->_pdf->Image($director_subdivision_fio, 	260, 173, 25, 0, '', '', '', false, 300); # ФИО
			$this->_pdf->Image($director_subdivision_sign,  240, 173, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_sign_grad = $this->getDirectorSubdivisionName();
		}
		
		# тему делим на 4 строки.
		$theme_remains	= $this->view->grad['theme'];
		$theme			= array();
		
		$theme[1] 		= $this->smartCutting($theme_remains, 50);
		$theme_remains 	= str_replace($theme[1], '', $theme_remains);
		
		$theme[2] 		= $this->smartCutting($theme_remains, 65);
		$theme_remains	= str_replace($theme[2], '', $theme_remains);
		
		$theme[3] 		= $this->smartCutting($theme_remains, 65);
		$theme_remains = str_replace($theme[3], '', $theme_remains);
		
		$theme[4] 		= $this->smartCutting($theme_remains, 65);
		$theme_remains 	= str_replace($theme[4], '', $theme_remains);
		
		$this->view->grad['theme_separated'] = $theme;
		
		$this->view->grad['manager'] = str_pad($this->view->grad['manager'], 80, ' ', STR_PAD_BOTH);
		$this->view->grad['manager'] = str_replace(' ', '&nbsp;', $this->view->grad['manager']);
		
		if(strtotime($this->view->grad['date_graduation_work']) > 0){
			$date_graduation_work 					= new Zend_Date($this->view->grad['date_graduation_work']);			
			$this->view->grad['date_graduation_work'] = $date_graduation_work->get(Zend_Date::DATE_LONG);
		} else {
			$this->view->grad['date_graduation_work'] = '          г.';
		}
		$this->view->grad['date_graduation_work'] 	= str_pad($this->view->grad['date_graduation_work'], 30, ' ', STR_PAD_LEFT);
		$this->view->grad['date_graduation_work'] 	= str_replace(' ', '&nbsp;', $this->view->grad['date_graduation_work']);
		
		$this->view->grad['ball'] 	= str_pad($this->view->grad['ball'], 46, ' ', STR_PAD_BOTH);
		$this->view->grad['ball'] 	= str_replace(' ', '&nbsp;', $this->view->grad['ball']);
		
		$this->view->grad['type_work'] 	= str_pad($this->view->grad['type_work'], 40, ' ', STR_PAD_BOTH);
		$this->view->grad['type_work'] 	= str_replace(' ', '&nbsp;', $this->view->grad['type_work']);
		
		$this->view->grad['chair'] 	= str_pad($this->view->grad['chair'], 70, ' ', STR_PAD_BOTH);
		$this->view->grad['chair'] 	= str_replace(' ', '&nbsp;', $this->view->grad['chair']);
		
		
		$this->view->grad['protocol_number'] 	= str_pad($this->view->grad['protocol_number'], 20, ' ', STR_PAD_BOTH);
		$this->view->grad['protocol_number'] 	= str_replace(' ', '&nbsp;', $this->view->grad['protocol_number']);
		
		
		if(strtotime($this->view->grad['date_commission']) > 0){
			$date_commission 					= new Zend_Date($this->view->grad['date_commission']);			
			$this->view->grad['date_commission'] = $date_commission->get(Zend_Date::DATE_LONG);
		} else {
			$this->view->grad['date_commission'] = '          г.';
		}
		$this->view->grad['date_commission'] 	= str_pad($this->view->grad['date_commission'], 20, ' ', STR_PAD_LEFT);
		$this->view->grad['date_commission'] 	= str_replace(' ', '&nbsp;', $this->view->grad['date_commission']);
		
		$this->view->grad['student_fio'] = $this->_current_user->LastName.' '.$this->_current_user->FirstName.' '.$this->_current_user->Patronymic;
		$this->view->grad['student_fio'] 	= str_pad($this->view->grad['student_fio'], 105, ' ', STR_PAD_BOTH);
		$this->view->grad['student_fio'] = str_replace(' ', '&nbsp;', $this->view->grad['student_fio']);
		
		
		$this->view->grad['members_commission'] = str_replace('~', ', ', $this->view->grad['members_commission']);
		
		#if($this->view->gia){		
		#	$this->view->giaName = $gia->Reason;			
		#	$other_date = new Zend_Date($gia->DateTake);
		#	$giaDateTake = $other_date->get(Zend_Date::DATE_LONG);
		#	$this->view->giaDateTake = $giaDateTake;
		#	$this->view->giaCode = $gia->Code;
		#}	
		
		$director_subdivision_sign		= $this->getDirectorSubdivisionSignPath(); # подпись руководителя структурного подразделения
		
		$this->view->director_subdivision_sign_gos = '';
		$this->view->director_subdivision_sign_grad = '';
		
		if(!empty($this->view->gos)){
			# Руководитель структурного подразделения - ГОСЫ
			$this->_pdf->Image($director_subdivision_sign,  280, 96, 10, 0, '', '', '', false, 300); # Подпись
			$this->view->director_subdivision_sign_gos = $this->getDirectorSubdivisionName();
		}
		
		
		
		
		$this->view->director_subdivision_sign_gos = str_pad($this->view->director_subdivision_sign_gos, 80, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_sign_gos = str_replace(' ', '&nbsp;', $this->view->director_subdivision_sign_gos);
		
		$this->view->director_subdivision_sign_grad = str_pad($this->view->director_subdivision_sign_grad, 45, ' ', STR_PAD_BOTH);
		$this->view->director_subdivision_sign_grad = str_replace(' ', '&nbsp;', $this->view->director_subdivision_sign_grad);
		
		
		
		$this->view->gos 			 	= array_pad($this->view->gos, 6, false);
		
		
		$content = $this->view->render('export/parts/gos-gia.tpl');
		$this->_pdf->writeHTML($content, true, false, false, false, ''); 
		return true;
	}
	
	
	
	private function outputPdf()
	{
		return $this->_pdf->Output('recordbook.pdf', 'I');		
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
			return false;
		}		
		return $content;
	}
	
	public function getDirectorSubdivisionName($year_begin = false, $year_end = false)
	{
		if(empty($year_begin) || empty($year_end)){
			return _('Елагина П.В.');
		}
		
		$year_krapotkina = date('Y', strtotime(HM_Certificates_CertificatesModel::SIGN_KRAPOTKINA_DATE_BEFORE));
		if( $year_end < $year_krapotkina ){
			return _('Крапоткина С.А.');
		}

		$year_gybina1 = date('Y', strtotime(HM_Certificates_CertificatesModel::SING_ELAGINA_DATE_FROM));
		$year_gybina2 = date ('Y', strtotime(HM_Certificates_CertificatesModel::SIGN_GYBINA_DATE_FROM));
		if( ($year_end <= $year_gybina1) and ($year_end >= $year_gybina2) ){
			return _('Гыбина Т.Г.');
		}
		
		return 'Елагина П.В.';
	}
	
	# подпись руководителя структурного подразделения
	public function getDirectorSubdivisionSignPath($year_begin = false, $year_end = false)
	{
		if(empty($year_begin) || empty($year_end)){
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\sign\Elagina.png';
		}
		
		$year_gybina1 = date('Y', strtotime(HM_Certificates_CertificatesModel::SING_ELAGINA_DATE_FROM));
		$year_gybina2 = date ('Y', strtotime(HM_Certificates_CertificatesModel::SIGN_GYBINA_DATE_FROM));
		if( ($year_end <= $year_gybina1) and ($year_end >= $year_gybina2) ){			
			return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\sign\Gybina.png';
		}		
		return $_SERVER["DOCUMENT_ROOT"].'\..\..\application\modules\els\student-recordbook\views\files\sign\Elagina.png';
	}
	
	public function sortByExam($a, $b)
	{
		if ($a->Type == $b->Type) {
			return 0;
		}
		return ($a->Type == 'Экзамен') ? -1 : 1;
	}
	
	public function sortByCredit()
	{
		if ($a->Type == $b->Type) {
			return 0;
		}
		return ($a->Type == 'Зачет') ? -1 : 1;
	}
		
	
}