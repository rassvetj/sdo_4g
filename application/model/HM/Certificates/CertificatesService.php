<?php
class HM_Certificates_CertificatesService extends HM_Service_Abstract
{
	/**
	 * Добавление пользователю курса сертификата
	 * @param INT $MID id пользователя
	 * @param INT $CID id курса
	 * @return bool | HM_Certificates_CertificatesModel
	 */
	public function addCertificate($user_id, $subject_id)
	{
		if ( !$user_id || !$subject_id ) {
			return false;
		}
		
		 $certificate = $this->getOne($this->fetchAll($this->getService('Lesson')->quoteInto(array('subject_id = ?','AND user_id = ?'),
		                                                                       array($subject_id,$user_id))));
        // если сертификата нет - он создается
        if ( !$certificate ) {
        	$certificate = $this->insert(array('user_id' => (int) $user_id,
        									   'subject_id' => (int) $subject_id, 
        									   'created' => date("Y-m-d H:i:s")));
        } 
		
		if ( !$certificate ) {
			return false;
		}
	
		// Генерация файла
		$this->createFile($certificate->certificate_id);
		
		return $certificate;
	}

	/**
	 * Генерация PDF файла сертификата 
	 * @param INT $certificateId
	 * @return boolean
	 */
	public function createFile($certificateId, $returnAsString = null, $certificateText = null)
	{
	    //подключаем библиотеку dompdf
		require_once("dompdf/dompdf_config.inc.php");
		if ( !$certificateId || $certificateId != intval($certificateId)) {
// 			return false; // для preview нам нужно генерить сертификат без реального номера
		}
		
		// получение инормации о курсе и получателе сертификата
		$certificate = $this->getOne($this->findDependence(array('User','Subject'), $certificateId));
	
		if($certificate && count($certificate->student) && count($certificate->courses)) {
		
    		$student = $certificate->student[0];
    		$cource  = $certificate->courses[0];
    		//вызываем модель - таблица оценок
    		$marks = new HM_Subject_Mark_MarkTable();
    		//извлекаем методом оценку за конкретный курс
    		$studentMarks = $marks->getMarks($student->MID, $cource->subid);
    
    		$provmid = $student->MID;
    		$provcid = $cource->subid;
    		
    		
    		$studentName = ($student->FirstName || $student->LastName) ?
    						$student->FirstName . " " . $student->Patronymic . " " . $student->LastName :
    						_("Пользователь")." #".$student->MID;
    						
    		$subjectName = iconv('cp1251',
    							 'utf-8',
    							 wordwrap(iconv('utf-8',
    							 				'cp1251',
    							 				$certificate->courses[0]->name), 
    							 		  85, 
    							 		  "\n"));
		}
						
		$oldEncoding = mb_internal_encoding();
		//mb_internal_encoding("Windows-1251");
		//mb_internal_encoding("UTF-8");
		
		$template = $this->getService('Option')->getOption('template_certificate_text');
		$template = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$template.'</html>';
		
		//$template = str_replace("/upload/files/","../public/upload/files/", $template); // зачем это здесь? в любом случае нужно рефакторить
		$template = str_replace("&nbsp;"," ", $template);
		$template = str_replace("<em>","<em> ", $template);
		$template = str_replace("<strong>","<strong> ", $template);	
		
		if ($studentName) $template = str_replace("[NAME]", " ".$studentName." ", $template);									
		if ($subjectName) $template = str_replace("[COURSE]", " ".$subjectName." ", $template);
		if ($certificateId) $template = str_replace("[CERTIFICATE]", " ".$this->getFormatNubmer($certificateId)." ", $template);
		if ($studentMarks) $template = str_replace("[GRADE]", " ".$studentMarks." ", $template);
			
		$dompdf = new DOMPDF();
		//загружаем поток-строку тегов
		$dompdf->load_html($template);
		$dompdf->render();
		//записываем его в переменную
		$output = $dompdf->output();
		//$dompdf->stream('1986.pdf'); // Выводим результат (скачивание)
		//file_put_contents("100001.pdf",$output);
		
		//создание файла сертификата
		//$pdf = new Zend_Pdf();
		
		//$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN);
		 
		//$font = Zend_Pdf_Font::fontWithPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 
		//		   							".." . DIRECTORY_SEPARATOR . 
		//		   							"public" . DIRECTORY_SEPARATOR . 
		//									"fonts" . DIRECTORY_SEPARATOR .
		//									"arial.ttf");
		
				
		//$text_lines = explode("\n",$template);
		
        
		//$cur_page = 0;
		# создаем первую страницу
		//$pdf->pages[$cur_page] = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        //$pdf->pages[$cur_page]->setFont($font, 10);
        //$padding = $pdf->pages[$cur_page]->getHeight() - 30;
                 
		//foreach ( $text_lines as $line) {
            # перенос на др страницу
		//    if ( $padding < 30) {
        //         $cur_page ++;
        //         $pdf->pages[$cur_page] = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        //         $pdf->pages[$cur_page]->setFont($font, 10);
        //         $padding = $pdf->pages[$cur_page]->getHeight() - 30;
        //    }
            
		//	$pdf->pages[$cur_page]->drawText($line,70,$padding,'UTF-8');
		//	$padding -= 20;
		//}
		
		if (!$returnAsString) {
    		$oldUmask = umask(0);
    		$fileName = Zend_Registry::get('config')->path->upload->cetrificates . "{$certificateId}.pdf";
    		file_put_contents($fileName, $output);
    		//$pdf->save($fileName);
    		chmod($fileName, 0777);
    		umask($oldUmask);
    		//mb_internal_encoding($oldEncoding);									
    		return true;
		} 
	    return $output;
	}
	
	/**
	 * Форматирует ИД сертификата для печати или отображения на экране
	 * @param int|string $certificateId
	 * @return string
	 */
	public function getFormatNubmer($certificateId = null)
	{
	    if ( !$certificateId ) return '';
	    
	    return str_pad($certificateId, 10, "0", STR_PAD_LEFT);
	}

}