#!/usr/bin/env php
<?php
/* Перевод в прошедшее обучение всех отчисленных студентов.  */
try {	
	$CRON_DIR 					= __DIR__.'/../../';
	$init_path 					= $CRON_DIR.'init/_init.php';
	$log_class					= $CRON_DIR.'init/classes/log.php';
	$mail_class					= $CRON_DIR.'init/classes/mail.php';
	$ftp_class					= $CRON_DIR.'init/classes/ftp.php';
	$csv_class					= $CRON_DIR.'init/classes/csv.php';
	$_PATH_TO_LOG_FILE 			= $CRON_DIR.'logs/log_graduated.csv';
	$_PATH_TO_ERRORS_LOG_FILE 	= $CRON_DIR.'logs/log_errors_graduated.csv';
	$_HAS_ERRORS				= false;
	
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
	if(!file_exists($ftp_class)){
		echo 'Error: ftp class not found.';
		exit();
	}
	if(!file_exists($csv_class)){
		echo 'Error: csv class not found.';
		exit();
	}
	
	require_once $init_path;
	require_once $log_class;
	require_once $mail_class;
	require_once $ftp_class;
	require_once $csv_class;
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_to_errors	= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты перевода отчисленных студентов в прошедшее обучение.';
	$email_messageText 	= 'Результаты перевода отчисленных студентов в прошедшее обучение от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		= 'sdo';
	$_FROM_PASSWORD 	= 'z931am008';
	$_FROM_EMAIL 		= 'sdo@rgsu.net';	// email отправителя
	$_FROM_NAME 		= 'СДО РГСУ';		// Имя отправителя
	
	$_REASON_GRADUATED	= 'отчислен';
	
	$_UPLOAD_DIR		= 'files/';	
	$_CSV_FILE_NAME 	= 'Excluded.csv'; //--файл, который будет считан для обработки
	$_PATH_TO_DATA_FILES= $_UPLOAD_DIR.$_CSV_FILE_NAME;
	$_FTP_FILES			= array( //--список всех файлов, которые надо скачать с FTP
		$_CSV_FILE_NAME,	
	);
	
	$_FIELD_USER_ID 		= 'mid_external';
	$_FIELD_STATUS_NAME	 	= 'reason';
	$_FIELD_STATUS_VALUE 	= 'status';
	$_FIELD_FIO 			= 'fio';
	
	$_CSV_FIELDS		= array(
		$_FIELD_USER_ID,
		$_FIELD_STATUS_NAME,
		$_FIELD_FIO,
	);
	
	$_USER_STATUSES 	= array(		
		'отчислен'					=> HM_User_UserModel::STATUS_BLOCKED,
	);
	
	$zErrLog = new Log_cCron();
	$zErrLog->setPath($_PATH_TO_ERRORS_LOG_FILE);
	$zErrLog->setDefaultParams();
	$zErrLog->addMessageTemplate('EMAIL_PARAM_NOT_SET', 'Параметры отправителя не заданы. Используются параметры по умолчанию из файла config.ini');
	
	$zLog = new Log_cCron();
	$zLog->setPath($_PATH_TO_LOG_FILE);
	$zLog->setDefaultParams();		
	$zLog->addMessageTemplate('DATA_EMPTY', 'Нет данных для изменения');	
	$zLog->addMessageTemplate('ALL_NOT_UPDATE', 'Ни одной записи не было изменено');
	$zLog->addMessageTemplate('SUCCESS_UPDATE', 'Переведен в прошедше обучение;%s;%s;в сессии;%s;%s;по причине;%s');
	$zLog->addMessageTemplate('ERROR_UPDATE', 'Не удалось перевести в прошедше обучение;%s;%s;в сессии;%s;%s');
	
	
	echo PHP_EOL.'______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['BEGIN'], $zLog::INFO);
	echo 'Start'.PHP_EOL;
	echo '______________________________________________'.PHP_EOL;
	

	$zFtp = new Ftp_cCron();
	$isConnect = $zFtp->connect();
	
	
	if($isConnect !== true){ //--ошибка коннекта.
		echo $isConnect.PHP_EOL; 
		if($zErrLog) { $zErrLog->log($isConnect, $zErrLog::ERR); }
		$_HAS_ERRORS = true;
	} else {
		$zFtp->setUploadPath($_UPLOAD_DIR); //-_временно закомаенитровано на время тестов.
		$isUploadFiles = $zFtp->uploadFiles($_FTP_FILES);
		#$isUploadFiles = true;
		
		if($isUploadFiles !== true){ //--ошибка скачивания файлов с FTP
			echo $isUploadFiles.PHP_EOL; 
			if($zErrLog) { $zErrLog->log($isUploadFiles, $zErrLog::ERR); }	
			$_HAS_ERRORS = true;			
		} else {			
			$zCsv = new Csv_cCron();
			$zCsv->setPath($_UPLOAD_DIR);
			$zCsv->setFields($_CSV_FIELDS);
			$zCsv->readCSVContent($_CSV_FILE_NAME);
			$content = $zCsv->getCSVContent();			
			$iter = new ArrayIterator($content); 
			$userIDs = array();
			foreach($iter as $row) {					
				if(
					isset($_USER_STATUSES[	mb_strtolower($row->{$_FIELD_STATUS_NAME})	])
					&&
					$_USER_STATUSES[	mb_strtolower($row->{$_FIELD_STATUS_NAME})	] == HM_User_UserModel::STATUS_BLOCKED
				){
					$data[ $row->{$_FIELD_USER_ID}] = $row;					
				}
			}			
		}		
	}
	$zFtp->closeConnection();
	
	if(!count($data)){
		if($zLog) { $zLog->log($zLog->msg['DATA_EMPTY'], $zLog::INFO); }
	} else {		
		$serviceUser 		= Zend_Registry::get('serviceContainer')->getService('User');
		$serviceSubject 	= Zend_Registry::get('serviceContainer')->getService('Subject');
		$serviceGraduated 	= Zend_Registry::get('serviceContainer')->getService('Graduated');
		/*
		$string_keys = array();
		foreach(array_keys($data) as $i){
			if(!empty($i)){
				$string_keys[] = (string) $i;
			}
		}	
		*/
		//--отбор пары студент-сессия.
		$select = $serviceUser->getSelect();		
        $select->from(array('p' => 'People'), array(
			'MID' 					=> 'p.MID',
			'blocked' 				=> 'p.blocked',
			//'status_1c' 			=> 'p.status_1c',
			'mid_external' 			=> 'p.mid_external',
			'subject_id' 			=> 'st.CID',				
			'subject_name' 			=> 'subj.name',				
			'subject_external_id' 	=> 'subj.external_id',				
		));
		$select->join(array('st' => 'Students'), 'st.MID = p.MID', array());	 
		$select->join(array('subj' => 'subjects'), 'subj.subid = st.CID', array());	 
		$select->where('p.blocked = ?', HM_User_UserModel::STATUS_BLOCKED); //--переводим только заблокированных студентов в прошедшее.
		$users = $select->query()->fetchAll();
		
		if(!count($users)){
			if($zLog) { $zLog->log($zLog->msg['DATA_EMPTY'], $zLog::INFO); }
		} else {
			$isAllRowsNotUpdated = true; //--нет ни одного совпадения для обновления
			foreach($users as $us){				
				if( isset($data[$us['mid_external']]) && count($data[$us['mid_external']]) ) {																
					$isGraduated = $serviceSubject->assignGraduated($us['subject_id'], $us['MID']); //--переводим впрошедшее.
					if (!$isGraduated) {							
						if($zLog) {
							$zLog->log(
								sprintf($zLog->msg['ERROR_UPDATE'],									
										$us['mid_external'], $data[$us['mid_external']]->{$_FIELD_FIO}, $us['subject_external_id'], $us['subject_name']),
							$zLog::ERR); 
						}							
					} else {
						$isAllRowsNotUpdated = false;						
						$serviceGraduated->update( //--обновляем статус перевода в прошедшее.
							array(
								'SID'	=> $isGraduated->SID,
								'reason'=> $_REASON_GRADUATED,
							)
						);							
						if($zLog) {
							$zLog->log(
								sprintf($zLog->msg['SUCCESS_UPDATE'],									
										$us['mid_external'], $data[$us['mid_external']]->{$_FIELD_FIO}, $us['subject_external_id'], $us['subject_name'], $data[$us['mid_external']]->{$_FIELD_STATUS_NAME}),
							9); 
						}									
					}
				}						
			}
			if($isAllRowsNotUpdated){ if($zLog) { $zLog->log($zLog->msg['ALL_NOT_UPDATE'], $zLog::INFO); } 	}
		}		
	}	
	echo '______________________________________________'.PHP_EOL;			
	echo 'End. Exit';
	$zLog->log($zLog->msg['END'], $zLog::INFO);
} catch (Exception $e) {    
	echo 'Exception: ',  $e->getMessage(), "\n";
	if($zErrLog) { $zErrLog->log(sprintf($zErrLog->msg['EXCEPTION'], $e->getMessage()), 8); }
	$_HAS_ERRORS = true;
}

//-----SEND REPORT TO EMAIL
$mail = new Mail_cCron(Zend_Registry::get('config')->charset);
if(empty($_FROM_LOGIN) || empty($_FROM_PASSWORD)){	
	if($zErrLog) { $zErrLog->log($zErrLog->msg['EMAIL_PARAM_NOT_SET'], $zErrLog::INFO); }	
	$_HAS_ERRORS = true;
	$mail->setFromToDefaultFrom();	
} else {
	$mail->setLogin( $_FROM_LOGIN );
	$mail->setPassword( $_FROM_PASSWORD );
	$mail->changeTransport();
	$mail->setFrom($_FROM_EMAIL, $_FROM_NAME);
}
$mail->setSubject($email_subject);     
$mail->addTo($email_to);
$mail->setType(Zend_Mime::MULTIPART_RELATED);
$mail->setBodyHtml($email_messageText, Zend_Registry::get('config')->charset);

if(realpath($_PATH_TO_LOG_FILE)){
	$content = file_get_contents($_PATH_TO_LOG_FILE); 
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $finfo->buffer($content);
	$path_info = pathinfo($_PATH_TO_LOG_FILE);				
	$file_name = $path_info['basename'];

	$attachment = new Zend_Mime_Part($content);					
	$attachment->type = $mime_type;
	$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
	$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
	$attachment->filename = $file_name;
	$mail->addAttachment($attachment);							
}

if(realpath($_PATH_TO_DATA_FILES)){
	$content = file_get_contents($_PATH_TO_DATA_FILES); 
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $finfo->buffer($content);
	$path_info = pathinfo($_PATH_TO_DATA_FILES);				
	$file_name = $path_info['basename'];

	$attachment = new Zend_Mime_Part($content);					
	$attachment->type = $mime_type;
	$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
	$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
	$attachment->filename = $file_name;
	$mail->addAttachment($attachment);							
}

try {				
	$mail->send();			
	
	if($_HAS_ERRORS){	
		$mail->clearAttachment();
		$mail->clearRecipients();
		$mail->addTo($email_to_errors);		
		if(realpath($_PATH_TO_ERRORS_LOG_FILE)){
			$content = file_get_contents($_PATH_TO_ERRORS_LOG_FILE); 
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime_type = $finfo->buffer($content);
			$path_info = pathinfo($_PATH_TO_ERRORS_LOG_FILE);				
			$file_name = $path_info['basename'];

			$attachment = new Zend_Mime_Part($content);					
			$attachment->type = $mime_type;
			$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
			$attachment->filename = $file_name;
			$mail->addAttachment($attachment);							
		}		
		$mail->send();
	}
		
} catch (Zend_Mail_Exception $e) {                	
	echo 'Exception: '.$e->getMessage().PHP_EOL;	
	if($zErrLog) { $zErrLog->log(sprintf($zErrLog->msg['EXCEPTION'], $e->getMessage()), 8); }
	$_HAS_ERRORS = true;	
}