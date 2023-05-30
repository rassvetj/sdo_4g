<?php
/* Рассылка  */
	
	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	
	if(!file_exists($init_path)){
		echo 'Error: init file not found.';
		exit();
	}	
	if(!file_exists($log_class)){
		echo 'Error: log class not found.';
		exit();
	}	
	if(!file_exists($mail_class)){
		echo 'Error: mail class not found.';
		exit();
	}
	
	require_once $init_path;
	require_once $log_class;
	require_once $mail_class;
	
	$config = array(
		'subject'			=> 'Оплата общежития РГСУ',
		'report' 			=> array(
					'to' 		=> 'HramovSV@rgsu.net',
					'subject'	=> 'Отчет СУ00-18114 НЕ УЧАТСЯ. Оплата общежития РГСУ', 
		),
		'rows_limit'		=> 1, # кол-во строк, которые будут обработаны за 1 запуск скрипта.
		'delay_mail' 		=> 3, # интервал между каждой отправкой письма, сек.
		'delay_disconnect'	=> 60, # интервал между рарзывом соединения с почтовым сервером и очередным подключением, сек.
		'try_count'			=> 5, # колличество попытокподключения к почтовому серверу.
		'source' 			=> 'default.csv',
		'template' 			=> 'default.txt',
		'log' 				=> 'default.csv',
		'delimiter' 		=> ';',
		'fields'			=> array(
						'email'	=> 0,
						'fio' 	=> 1,
						#'summ' 	=> 2,
		),
		'tpl_var_begin' 	=> '[#',
		'tpl_var_end' 		=> '#]',
		'from'				=> array(
					'login'		=> 'dekanatuser',
					'password'	=> 'Rest+140',
					'name'		=> 'РГСУ',
					'email'		=> 'dekanat@rgsu.net',
					
					#'login'		=> 'do',
					#'password'	=> 'Aw2o8W',
					#'name'		=> 'РГСУ',
					#'email'		=> 'do@rgsu.net',
					
					#'login'		=> 'sdo',
					#'password'	=> 'z931am008',
					#'name'		=> 'РГСУ',
					#'email'		=> 'sdo@rgsu.net',
		),
		
	);
	
	$el = new cronMailRun();
	$el->setConfig($config);
	$el->run();
	$el->sendReport();
	die;
	
	class cronMailRun
	{	
		private $config = null;
		private $log 	= null;
		
		public function setConfig($raw = array())
		{
			foreach($raw as $name => $value){
				if($name == 'source'){
					$value = dirname(__FILE__) . '/source/' . $value;
				}
				
				if($name == 'template'){
					$value = dirname(__FILE__) . '/template/' . $value;
				}
				
				if($name == 'log'){
					$value = dirname(__FILE__) . '/logs/' . $value;
				}
				
				if($name == 'delimiter'){
					$value = trim($value);
				}
				$this->config->{$name} = $value;
			}
		}
		
		public function getConfig($name = false)
		{
			if($name == 'login'){
				$from = $this->config->from;
				return $from['login'];
			}
			
			if($name == 'password'){
				$from = $this->config->from;
				return $from['password'];
			}
			
			if($name == 'from_name'){
				$from = $this->config->from;
				return $from['name'];
			}
			
			if($name == 'from_email'){
				$from = $this->config->from;
				return $from['email'];
			}
			
			if($name == 'report_subject'){
				$report = $this->config->report;
				return $report['subject'];
			}
			
			if($name == 'report_email_to'){
				$report = $this->config->report;
				return $report['to'];
			}
			
			
			
			return $this->config->{$name};
		}
		
		private function initLog()
		{
			if(!file_exists($this->getConfig('log'))){
				return false;
			}
			$this->log = new Log_cCron();
			$this->log->setPath($this->getConfig('log'));
			$this->log->setDefaultParams();
			return true;
		}
		
		public function run()
		{
			if(!$this->initLog()){
				echo 'ERR. Init log error' . PHP_EOL;
				return false;
			}
			
			if(!file_exists($this->getConfig('source'))){
				$this->pushMessage('ERR; Source file not fund');
				return false;
			}
			
			if(!file_exists($this->getConfig('template'))){
				$this->pushMessage('ERR; Template file not fund');
				return false;
			}
			
			$source_rows 	= $this->getSourceRow();
			$rows_passed 	= 0;
			$try_count		= 0;
			foreach($source_rows as $row_num => $raw){
				$rows_passed++;	
				if($rows_passed > $this->getConfig('rows_limit')){
					$this->pushMessage('INFO; Limit rows passed');
					break;
				}
				
				if($try_count > $this->getConfig('try_count')){
					$this->pushMessage('INFO; Try limit reached');
					break;
				}
				
				$data = $this->prepareRow($raw);
				if(empty($data)){ continue; }
				
				$this->delay();
				$send_result = $this->send($data);
				if($send_result === false){
					$this->pushMessage('ERR; Not send');
					continue;
				}
				
				if($send_result instanceof Zend_Mail_Exception){
					$this->pushMessage('ERR; Exception: '.$send_result->getMessage());
					
					if($this->lostConnection($send_result)){
						$try_count++;
						$this->delayDisconnect();
					} 
					continue;
				}
				
				unset($source_rows[intval($row_num)]);
				
				$this->removeRow($source_rows, $row_num);
				#$this->pushMessage('OK; Sending');
			}
			$this->pushMessage('INFO; End');
		}
		
		private function lostConnection($e)
		{
			$message = $e->getMessage();
			if(strpos($message, '5.7.3') !== false){
				return true;
			}
			return false;
		}
		
		private function removeRow($source_rows, $row_num)
		{
			
			$f = fopen($this->getConfig('source'), 'w');
			if(!$f){
				$this->pushMessage('ERR; Source not found');
				return false;
			}
			
			fputs($f, implode('', $source_rows)); 
			fclose($f);
			return true;
		}
		
		private function delay()
		{
			sleep(intval($this->getConfig('delay_mail')));
		}
		
		private function delayDisconnect()
		{
			$delay = (int)$this->getConfig('delay_disconnect');
			$this->pushMessage('INFO. Sleep ' . $delay);
			sleep($delay);
		}
		
		private function getSourceRow()
		{
			return file($this->getConfig('source'));
		}
		
		private function prepareRow($raw)
		{
			return str_getcsv($raw, $this->getConfig('delimiter'));			
		}
		
		private function getEmail($raw)
		{
			$fields 		= $this->getConfig('fields');
			$email_position = (int)$fields['email'];
			return $raw[$email_position];
		}
		
		private function loadTemplate()
		{
			return file_get_contents($this->getConfig('template'));
		}
		
		private function send($data)
		{
			$email = $this->getEmail($data);
			
			$validator = new Zend_Validate_EmailAddress();
			if (!$validator->isValid($email)) {
				$this->pushMessage('ERR; Invalid email;' . $email . ';' . implode(';', $data));
				return false;
			}
			$template = $this->renderTemplate($data);
			if(empty($template)){ 
				$this->pushMessage('ERR; Template is empty');
				return false;
			}
			
			$mail = $this->getMailDefault();
			$mail->addTo($email);
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setBodyHtml($template, Zend_Registry::get('config')->charset);
			
			try {				
				$mail->send();
			} catch (Zend_Mail_Exception $e) {                	
				return $e;
			}
			
			$this->pushMessage('OK; Send;' . $email);
			return true;			
		}
		
		private function getMailDefault()
		{
			$mail = new Mail_cCron(Zend_Registry::get('config')->charset);
			$mail->setSubject($this->getConfig('subject'));     
			$mail->setLogin($this->getConfig('login'));
			$mail->setPassword($this->getConfig('password'));
			$mail->changeTransport();
			$mail->setFrom($this->getConfig('from_email'), $this->getConfig('from_name'));
			return $mail;
		}
		
		private function renderTemplate($data = false)
		{
			$template = $this->loadTemplate();
			if(empty($template)){
				$this->pushMessage('ERR. Template is empty');
				return false;
			}
			$vars = $this->getTemplateVars($data);
			if(empty($vars)){ return $template; }
			foreach($vars as $name => $value){
				$pattern	= $this->preparePattern($name);
				$template	= str_replace($pattern, $value, $template);
			}
			return $template;
		}
		
		private function getTemplateVars($raw)
		{
			$data = array();
			$fields = $this->getConfig('fields');
			if(empty($fields)){ return false; }
			foreach($fields as $name => $position){
				$data[$name] = trim($raw[$position]);
			}
			return $data;
		}
		
		private function preparePattern($name)
		{
			return $this->getConfig('tpl_var_begin') . trim($name) . $this->getConfig('tpl_var_end');
		}
		
		public function pushMessage($message, $type = 6)
		{
			echo $message . PHP_EOL;
			
			if($this->log instanceof Log_cCron){
				$this->log->log($message, $type);
			}
		}
		
		public function sendReport()
		{
			$mail = $this->getMailDefault();
			
			$mail->clearSubject();
			$mail->setSubject($this->getConfig('report_subject'));
			
			$mail->addTo($this->getConfig('report_email_to')); 
			
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setBodyHtml('Результат рассылки во вложении', Zend_Registry::get('config')->charset);
			
			$log_path 	= $this->getConfig('log');
			$content 	= file_get_contents($log_path); 
			$finfo 		= new finfo(FILEINFO_MIME_TYPE);
			$mime_type 	= $finfo->buffer($content);
			$path_info 	= pathinfo($log_path);				
			$file_name 	= $path_info['basename'];

			$attachment = new Zend_Mime_Part($content);					
			$attachment->type = $mime_type;
			$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
			$attachment->filename = $file_name;
			$mail->addAttachment($attachment);
			
			try {				
				$mail->send();
			} catch (Zend_Mail_Exception $e) {                	
				$this->pushMessage('ERR; Report not send');
				$this->pushMessage('ERR; Exception; ' . $e->getMessage());
			}
		}
		
	}	
	
	
	
	
	
	

	


