#!/usr/bin/env php
<?php
/* Изменяет статус пользователя в зависимости от состояния в 1С  */
try {	
	$CRON_DIR 					= __DIR__.'/../../';
	$init_path 					= $CRON_DIR.'init/_init.php';
	$log_class					= $CRON_DIR.'init/classes/log.php';
	$mail_class					= $CRON_DIR.'init/classes/mail.php';
	$ftp_class					= $CRON_DIR.'init/classes/ftp.php';
	$csv_class					= $CRON_DIR.'init/classes/csv.php';
	$_PATH_TO_LOG_FILE 			= $CRON_DIR.'logs/log_change_status.csv';
	$_PATH_TO_ERRORS_LOG_FILE 	= $CRON_DIR.'logs/log_errors_change_status.csv';
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
	
	$email_to 			= 'KrivoshlykovaES@rgsu.net'; #'BykovJEJU@rgsu.net';
	$email_to_errors	= '112@rgsu.net';
	$email_subject 		= 'СДО. Результаты синхронизации статусов пользователей.';
	$email_messageText 	= 'Результаты синхронизации статусов пользователей с 1С в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		= 'sdo';
	$_FROM_PASSWORD 	= 'z931am008';
	$_FROM_EMAIL 		= 'sdo@rgsu.net';	// email отправителя
	$_FROM_NAME 		= 'СДО РГСУ';		// Имя отправителя
	
	
	
	$_UPLOAD_DIR		= __DIR__.'/files/';
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
		'в академическом отпуске' 	=> HM_User_UserModel::STATUS_BLOCKED,
		'отчислен'					=> HM_User_UserModel::STATUS_BLOCKED,
		'увольнение'				=> HM_User_UserModel::STATUS_BLOCKED,
		'подан на отчисление'		=> HM_User_UserModel::STATUS_BLOCKED,		
		
		'поступил'					=> HM_User_UserModel::STATUS_ACTIVE,
		'учится'					=> HM_User_UserModel::STATUS_ACTIVE, 
		'на повторном обучении'		=> HM_User_UserModel::STATUS_ACTIVE, 
		'наканикулах'				=> HM_User_UserModel::STATUS_ACTIVE, 
	);
	
	$zErrLog = new Log_cCron();
	$zErrLog->setPath($_PATH_TO_ERRORS_LOG_FILE);
	$zErrLog->setDefaultParams();
	$zErrLog->addMessageTemplate('EMAIL_PARAM_NOT_SET', 'Параметры отправителя не заданы. Используются параметры по умолчанию из файла config.ini');
	
	$zLog = new Log_cCron();
	$zLog->setPath($_PATH_TO_LOG_FILE);
	$zLog->setDefaultParams();	
	$zLog->addMessageTemplate('STATUS_UNRECOGNIZE', 'Статус "%s" не распознан у пользователя (%s) %s');
	$zLog->addMessageTemplate('DATA_EMPTY', 'Нет данных для изменения');	
	$zLog->addMessageTemplate('ALL_NOT_UPDATE', 'Ни одной записи не было изменено');
	$zLog->addMessageTemplate('SUCCESS_UPDATE', 'Статус изменен;%s;%s;с;"%s";%s;на;"%s";%s');
	
	
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
		$zFtp->setUploadPath($_UPLOAD_DIR);
		$isUploadFiles = $zFtp->uploadFiles($_FTP_FILES);
		
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
				$status_numeric = $_USER_STATUSES[	mb_strtolower($row->{$_FIELD_STATUS_NAME})	]	; //--выставляем статус в числовом виде, который будет записан в БД								
				if(!is_int($status_numeric)){
					if($zLog) { $zLog->log(sprintf($zLog->msg['STATUS_UNRECOGNIZE'], $row->{$_FIELD_STATUS_NAME},$row->{$_FIELD_USER_ID}, $row->{$_FIELD_FIO}) , $zLog::ERR); } //-_в лог об ошибке
					continue;
				}
				$data[ $row->{$_FIELD_USER_ID}] = $row;
				$data[ $row->{$_FIELD_USER_ID}]->{$_FIELD_STATUS_VALUE} = $status_numeric;
			}			
		}		
	}
	$zFtp->closeConnection();
	
	
	if(!count($data)){
		if($zLog) { $zLog->log($zLog->msg['DATA_EMPTY'], $zLog::INFO); }
	} else {		
		$serviceUser = Zend_Registry::get('serviceContainer')->getService('User');
		$string_keys = array();
		foreach(array_keys($data) as $i){
			if(!empty($i)){
				$string_keys[] = (string) $i;
			}
		}	
		$users = $serviceUser->fetchAll($serviceUser->quoteInto('mid_external IN (?)', $string_keys));		
		if(!count($users)){
			if($zLog) { $zLog->log($zLog->msg['DATA_EMPTY'], $zLog::INFO); }
		} else {
			$isAllRowsNotUpdated = true; //--нет ни одного совпадения для обновления
			foreach($users as $us){
				if(
					isset($data[$us->mid_external])
					&&
					(
					$data[$us->mid_external]->{$_FIELD_STATUS_VALUE}!= $us->blocked
					||
					$data[$us->mid_external]->{$_FIELD_STATUS_NAME} != $us->status_1c
					)
				){	
					$isAllRowsNotUpdated = false;
					$serviceUser->update(
						array(
							'MID' 		=> $us->MID,
							'blocked' 	=> $data[$us->mid_external]->{$_FIELD_STATUS_VALUE},
							'status_1c'	=> $data[$us->mid_external]->{$_FIELD_STATUS_NAME},
						)
					);
									
					if($zLog) {
						$zLog->log(
							sprintf($zLog->msg['SUCCESS_UPDATE'],									
									$us->mid_external, $data[$us->mid_external]->{$_FIELD_FIO}, $us->status_1c, $us->blocked, $data[$us->mid_external]->{$_FIELD_STATUS_NAME}, $data[$us->mid_external]->{$_FIELD_STATUS_VALUE}),
								9); }
								
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