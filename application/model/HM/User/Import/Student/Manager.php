<?php
# Импорт идет черз web-сервис 1C
class HM_User_Import_Student_Manager      
{
	private $_codes    = array();
	private $_hasError = false;
	
	public function import()
	{
		$queue_size         = intval(Zend_Registry::get('config')->import->students->queue_size);
		$queue_mid_external = array();
		
		$this->log('Start', Zend_Log::INFO, true);
		
		if(!$this->loadAllCodes()){
			$this->log('Не удалось получить список актуальных студентов');
			$this->log('End');
			return false;
		}
		
		if(empty($this->_codes)){
			$this->log('Список актуальных студентов пуст');
			$this->log('End');
			return false;
		}
		
		foreach($this->_codes as $mid_external){
			$queue_mid_external[$mid_external] = $mid_external;
			
			if(!empty($queue_size)){
				if(count($queue_mid_external) == $queue_size){
					$this->log('Импорт пакета по ' . $queue_size);
					if(!$this->importQueue($queue_mid_external)){
						return false;
					}
					$queue_mid_external = array();
				}
			}
		}
		
		if(!empty($queue_mid_external)){
			$this->log('Импорт всех сразу или остаток ' . count($queue_mid_external));
			if($this->importQueue($queue_mid_external)){
				return false;
			}
			$queue_mid_external = array();
		}
		
		$this->log('End');
		return true;
	}
	
	
	private function importQueue($mid_externals = array())
	{
		$students = $this->getStudents($mid_externals);
		if(!$students){
			$this->log('Не удалось получить информацию по студентам: ' . implode(',', $mid_externals));
			return false;
		}
		
		foreach($students as $student){
			$this->log('Импорт студента ' . $student->mid_external);	
			# TODO Реализовать обновление отдельного студента.
		}
		return true;
	}
	
	private function getStudents($mid_externals = array())
	{
		$data       = array();
		$config     = Zend_Registry::get('config')->soap->students;
		$soapClient = new Zend_Soap_Client();
		$params     = array('kodes' => $mid_externals);
		
		$soapClient->setWsdl($config->wsdl);
		$soapClient->setHttpLogin($config->login);
		$soapClient->setHttpPassword($config->password);
		
		try {
			$response = $soapClient->GetDataStudents($params);
		} catch (Exception $e) {			
			$this->_hasError = true;
			$this->log($e->getMessage(), Zend_Log::ERR);
			error_log($e);
			return false;
		} 
		
		if(empty($response->return)){
			return false;
		}
		
		# TODO обработка $response и возврат методом. Возможно, каждый элемент нужно реализовать в виде модели.
		foreach($response->return as $user){
			$data[] = $user;
		}
		
		if(empty($data)){
			return false;
		}
		return $data;
	}
	
	private function loadAllCodes()
	{
		$data       = array();
		$config     = Zend_Registry::get('config')->soap->students;
		$soapClient = new Zend_Soap_Client();
		$params     = array();
		
		$soapClient->setWsdl($config->wsdl);
		$soapClient->setHttpLogin($config->login);
		$soapClient->setHttpPassword($config->password);
		
		try {
			$response = $soapClient->GetCodes($params);
		} catch (Exception $e) {
			$this->_hasError = true;
			$this->log($e->getMessage(), Zend_Log::ERR);
			error_log($e);
			return false;
		}
		
		foreach($response->return->kod as $item){
			$data[$item->kod] = $item->kod;
		}
		$this->_codes = $data;
		return true;
	}
	
	private function log($message, $priority = Zend_Log::INFO, $rewrite = false)
	{
		$logConfig     = Zend_Registry::get('config')->import->log;		
		$log_file_name = Zend_Registry::get('config')->import->students->log_file_name;
		$folder        = $logConfig->folder;
		$format        = $logConfig->format;
		$mode_rewrite  = $logConfig->mode->rewrite;
		$path          = $folder . '/' . $log_file_name;
		
		if(!is_dir($folder)){
			error_log('Не найден путь import.log.folder in ' . __FILE__ . ':' . __LINE__);
			return false;
		}
		
		$log = new Zend_Log();
		
		if($rewrite){
			$writer = new Zend_Log_Writer_Stream($path, $mode_rewrite);
		} else {
			$writer = new Zend_Log_Writer_Stream($path);
		}
		
		$writer->setFormatter(new Zend_Log_Formatter_Simple($format));		
		$log->addWriter($writer);
		$log->log($message . PHP_EOL, $priority);
		return true;
	}
	
	public function sendReport()
	{
		$reportConfig = Zend_Registry::get('config')->import->students->report;
		$mailerConfig = Zend_Registry::get('config')->mailer;
		$bodyText 	  = 'Результаты синхронизации статусов пользователей с 1С в СДО от ' . date('Y-m-d H:i:s', time()) . '</br>';
		
		if(!$reportConfig->email->send){
			$this->log('Отправка отчета на почту отключена');
			$this->toArchiveLog();
			return false;
		}
		
		try {
			$transport = new Zend_Mail_Transport_Smtp(array(
				'host' => $mailerConfig->params->host, 'auth' => $mailerConfig->params->auth, 'username' => $mailerConfig->params->username, 'password' => $mailerConfig->params->password, 'port' => $mailerConfig->params->port,
			));
			$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
			$mail->setDefaultTransport($transport);	
			$mail->setFrom($mailerConfig->default->email, $mailerConfig->default->name);
			$mail->setSubject($reportConfig->email->subject);
			
			if($this->_hasError){
				$mail->addTo($reportConfig->email->with_error_to);
				$bodyText .= 'Во время импорта возникли ошибки';
			} else {
				$mail->addTo($reportConfig->email->to);
			}
			
			
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setBodyHtml($bodyText, Zend_Registry::get('config')->charset);
			
			
			$logFile = Zend_Registry::get('config')->import->log->folder . '/' . Zend_Registry::get('config')->import->students->log_file_name;
			
			if(realpath($logFile)){
				$content   = file_get_contents($logFile); 
				$finfo     = new finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->buffer($content);
				$path_info = pathinfo($logFile);				
				$file_name = $path_info['basename'];

				$attachment = new Zend_Mime_Part($content);					
				$attachment->type = $mime_type;
				$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
				$attachment->filename = $file_name;
				$mail->addAttachment($attachment);							
			}
			
			$mail->send();
		} catch (Exception $e) {
			$this->log($e->getMessage(), Zend_Log::ERR);
			error_log($e);
			$this->toArchiveLog();
			return false;
		}
		return true;
	}
	
	# Сохранение файла логов отдельным файлом
	# TODO сделать архивирование
	private function toArchiveLog()
	{
		$folder      = Zend_Registry::get('config')->import->log->archive_folder;
		$logFile     = Zend_Registry::get('config')->import->log->folder . '/' . Zend_Registry::get('config')->import->students->log_file_name;
		$archiveFile = $folder . '/' . pathinfo($logFile, PATHINFO_FILENAME) . '_' . date('Y-m-d_His') . '.' . pathinfo($logFile, PATHINFO_EXTENSION);
		
		if(!is_dir($folder)){
			error_log('Не найден путь import.log.archive_folder in ' . __FILE__ . ':' . __LINE__);
			return false;
		}
		
		
		if(!file_exists ($logFile)){
			error_log('Не найден log-файл in ' . __FILE__ . ':' . __LINE__);
			return false;
		}
		
		if (!copy($logFile, $archiveFile)) {
			error_log('Не удалось скопировать файл ' . $logFile . ' в ' . $archiveFile . ' in ' . __FILE__ . ':' . __LINE__);
			return false;
		}
		return true;
	}
	
	
	
}