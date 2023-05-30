<?php
//===============================================
//================== Функции ====================
//===============================================

/**
 * импорт всех файлов.
 * @$extLogFiles - массив дополнительных логов. Ключ - имя элемента. Знаение - путь с именем файла. 
*/
function importAllFiles($C, $log = false, $extLogFiles = false){		
	if(!REAL_PATH_TO_FILES){			
		if($log){ $log->log(sprintf($log->msg['PATH_UNAVAILABLE'], PATH_TO_FILES), $log::ERR); }
		return false;
	}	
		
	if(!checkLocalFiles($C['FTP_FILE_LIST'], $log)){				
		if($log){ $log->log($log->msg['FILES_INCORRECTED'], $log::ERR); }
		return false;
	}
	
	if($log){ $log->log($log->msg['ALL_FILES_CORRECT'], 9); }
		
	//------1. Предметы	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['LEARNING_SUBJECTS']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {		
		$isImport = importLearningsubjects($C['LEARNING_SUBJECTS'], $log, $extLogFiles);
	}
	
	if(!$isImport){					
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['LEARNING_SUBJECTS']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['LEARNING_SUBJECTS']['FILE']), 9); }
		
		
	//------2. Программы	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['PROGRAMMS']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importProgramms($C['PROGRAMMS'], $log, $extLogFiles);						
	}	
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['PROGRAMMS']['FILE']), $log::ERR); }
		return false;
	} 	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['PROGRAMMS']['FILE']), 9); }
	
				
	//------3. Группы.	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['GROUPS']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importGroups($C['GROUPS'], $log, $extLogFiles);						
	}	
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['GROUPS']['FILE']), $log::ERR); }
		return false;			
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['GROUPS']['FILE']), 9); }
	
	
	//------4. Слушатели. 	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['USERS']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importUsers($C['USERS'], $log, $extLogFiles);						
	}
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['USERS']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['USERS']['FILE']), 9); }
	
	
	//------5. Оргструктура	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['ORGSTRUCTURE']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importOrgstructure($C['ORGSTRUCTURE'], $log, $extLogFiles);										
	}	
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['ORGSTRUCTURE']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['ORGSTRUCTURE']['FILE']), 9); }
	
	
	//------6. Сессии	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['SESSIONS']['FILE']), 9); }
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importSessions($C['SESSIONS'], $log, $extLogFiles);
	}	
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['SESSIONS']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['SESSIONS']['FILE']), 9); }	
	
	return true;
}


/**
 * импорт только пользователей.
*/
function importUserFiles($C, $log = false, $extLogFiles = false){		
	if(!REAL_PATH_TO_FILES){			
		if($log){ $log->log(sprintf($log->msg['PATH_UNAVAILABLE'], PATH_TO_FILES), $log::ERR); }
		return false;
	}	
		
	if(!checkLocalFiles($C['FTP_FILE_LIST'])){				
		if($log){ $log->log($log->msg['FILES_INCORRECTED'], $log::ERR); }
		return false;
	}	
	
	if($log){ $log->log($log->msg['ALL_FILES_CORRECT'], 9); }
	
	//------1. Слушатели. 	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['USERS']['FILE']), 9); }
	
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importUsers($C['USERS'], $log, $extLogFiles);						
	}	
	
	if(!$isImport){	
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['USERS']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['USERS']['FILE']), 9); }
	
	return true;
}

/**
 * импортируем тьюторов
*/
function importTutorFiles($C, $log = false, $extLogFiles = false){		
			
	if(!REAL_PATH_TO_FILES){			
		if($log){ $log->log(sprintf($log->msg['PATH_UNAVAILABLE'], PATH_TO_FILES), $log::ERR); }		
		return false;
	}	
	
	if(!checkLocalFiles($C['FTP_FILE_LIST'], $log)){				
		if($log){ $log->log($log->msg['FILES_INCORRECTED'], $log::ERR); }
		return false;
	}
		
	if($log){ $log->log($log->msg['ALL_FILES_CORRECT'], 9); }
	
	//------Преподаватели. 	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_BEGIN'], $C['TUTORS']['FILE']), 9); }
	
	
	if(IS_EMPTY_IMPORT){
		$isImport = true;
	} else {
		$isImport = importTutors($C['TUTORS'], $log, $extLogFiles);						
	}	
	
	if(!$isImport){		
		if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $C['TUTORS']['FILE']), $log::ERR); }
		return false;
	}	
	if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_END'], $C['TUTORS']['FILE']), 9); }
	
	return true;
}


/**
 * отсылка отчета на почту.
*/
function sendReport($toEmail, $body = '', $files = array(), $logFiles = array(), $log = false){
	$validator = new Zend_Validate_EmailAddress();
	
	if (!$validator->isValid($toEmail)) {		
		echo "Error. Incorrect email: $toEmail\n";
		if($log){ $log->log(sprintf($log->msg['EMAIL_ERROR'], $toEmail), 9); }		
		return false;
	}
	
	$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
    $mail->setSubject('СДО. Результаты импорта.');     
	$messageText = '';
	$messageText .= 'Результаты импорта данных в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
	$messageText = $messageText.'<br>'.$body;
	$mail->setFromToDefaultFrom();
	$mail->addTo($toEmail);
	
	$mail->setType(Zend_Mime::MULTIPART_RELATED);
			
	$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
	
	
	if(is_array($logFiles) && count($logFiles) > 0){
		foreach($logFiles as $f){			
			if($f  && realpath($f)){
				$content = file_get_contents($f); 
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->buffer($content);
				$path_info = pathinfo($f);				
				$file_name = $path_info['basename'];

				$attachment = new Zend_Mime_Part($content);					
				$attachment->type = $mime_type;
				$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
				$attachment->filename = $file_name;
				$mail->addAttachment($attachment);					
			}
		}
	}
	
	
	if(is_array($files) && count($files) > 0){
		foreach($files as $f){
			if($f  && realpath($f)){
				$content = file_get_contents($f); 
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->buffer($content);
				$path_info = pathinfo($f);
				$file_name = $path_info['basename'];

				$attachment = new Zend_Mime_Part($content);					
				$attachment->type = $mime_type;
				$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
				$attachment->filename = $file_name;
				$mail->addAttachment($attachment);					
			}
		}
	}
	
	
	
	try {				
		if($mail->send()){
			return true;	
		}			
		return false;
	} catch (Zend_Mail_Exception $e) {                
		echo $e->getMessage()."\n";			
		if($log){ $log->log(sprintf($log->msg['SEND_ERROR'], $e->getMessage()), $log::ERR); }	
		return false;
	}
}




//--проверяет на доступность локальные файлы и папки, используемые при импорте
function checkLocalFiles($nameFiles, $log = false){
	
	if(!is_array($nameFiles) || count($nameFiles) < 1){		
		echo "Errors. List local files is empty.\n";
		if($log){ $log->log($log->msg['FILES_NOT_SET'], $log::ERR); }
		return false;
	}
		
	$error = false;
	foreach($nameFiles as $file){				
		if(!file_exists( realpath(PATH_TO_FILES.'/'.$file)  )){
			echo 'Error. File not found: "'.$file.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_UNAVAILABLE'], $file), $log::ERR); }			
			$error = true;
		} else {
			$curDate = date('Y.m.d',time());		
			$lastUpd = date ("Y.m.d", filemtime(realpath(PATH_TO_FILES.'/'.$file)));		
			if($lastUpd != $curDate){			
				echo 'Error. File is to old: "'.$file.'". Last update: '.$lastUpd."\n";				
				if($log){ $log->log(sprintf($log->msg['FILE_IS_OLD'], $file, $lastUpd), $log::ERR); }
				$error = true;
			} 
		}	
	}	
	return !$error;
}





function filterInt(&$string){
	$string = preg_replace("/[^0-9]/", '', $string);
}


/**
 * подключение к папке с файлами на ftp
*/

function getConnect($ftp_server, $ftp_user, $ftp_pass, $ftp_dir = false, $log = false){
	
	$conn_id = ftp_connect($ftp_server); 
	
	if(!$conn_id) {
		echo 'Error. Can not connect to "'.$ftp_server.'"'."\n";
		if($log){ $log->log(sprintf($log->msg['FTP_CONNECT_ERROR'], $ftp_server), $log::ERR); }
		return false;
	}
	
	if($log){ $log->log(sprintf($log->msg['FTP_CONNECT_SUCCESS'], $ftp_server), 9); }	
	
	if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {		
		echo 'Connect to: "'.$ftp_server.'"'."\n";	
		if($log){ $log->log(sprintf($log->msg['FTP_LOGIN_SUCCESS'], $ftp_server), 9); }		
		
		if($ftp_dir){
			$ftp_dir = trim($ftp_dir, '/');			
			if (!ftp_chdir($conn_id, $ftp_dir)) {
				echo 'Error. Can not change dir to "'.$ftp_dir.'"'."\n";			
				if($log){ $log->log(sprintf($log->msg['FTP_CHDIR_ERROR'], $ftp_dir), $log::ERR); }
				return false;
			}
		}	
		ftp_pasv($conn_id, true);
		return $conn_id;
	} 
	echo 'Error. Can not login to "'.$ftp_server.'"'."\n";			
	if($log){ $log->log(sprintf($log->msg['FTP_LOGIN_ERROR'], $ftp_server), $log::ERR); }
	return false;
}


/**
 * загружает в локальную папку файлы с FTP
 * $ftp_names = array();
 * ftp_conn_id = ftp open connect resource
*/

function getFilesFromFtp($ftp_names, $ftp_conn_id = false, $log = false){
	
	
	if(!is_array($ftp_names) || count($ftp_names) < 0) {
		echo 'Error. Not set name of files.'."\n";
		if($log){ $log->log($log->msg['FTP_FILES_NOT_SET'], $log::ERR); }		
		return false;
	}
	
	if(!$ftp_conn_id){
		echo 'Error. FTP connection  not open.'."\n";
		if($log){ $log->log($log->msg['FTP_CONNECT_CLOSED'], $log::ERR); }		
		return false;
	}
	
	$curDate = date('Y-m-d', time());
	
	
	echo 'Path to upload: "'.REAL_PATH_TO_FILES.'"'."\n";
	//if($log){ $log->log(sprintf($log->msg['PATH_TO_UPLOAD'], REAL_PATH_TO_FILES), 9); } 
	foreach($ftp_names as $server_file){
		$local_file = REAL_PATH_TO_FILES.'/'.$server_file;
		
		$modDateFtpFile = ftp_mdtm($ftp_conn_id, $server_file);
		if ($modDateFtpFile != -1) {
			if(date("Y-m-d", $modDateFtpFile) != $curDate){ //--если файла не сегодняшний
				echo 'Error. Remoute file "'.$server_file.'" is to old'."\n";
				if($log){ $log->log(sprintf($log->msg['FILE_IS_OLD'], $server_file, date("Y-m-d", $modDateFtpFile)), $log::ERR); }
				return false;
			}						
		} else {
			echo 'Error. Info about file "'.$server_file.'" is not obtained'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_INFO_ERROR'], $server_file), $log::ERR); }
			return false;
		}
		
		$local_file = REAL_PATH_TO_FILES.'/'.$server_file;
		
		if (ftp_get($ftp_conn_id, $local_file, $server_file, FTP_BINARY)) {
			echo 'File "'.$server_file.'" is uploaded.'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_UPLOAD_SUCCESS'], $server_file), 9); }			
		} else {
			echo 'Error. File "'.$server_file.'" is not uploaded'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_UPLOAD_ERROR'], $local_file), $log::ERR); }
			return false;
		}	
	}
	return true;
}



function getCsvContent($file, $keys = array(), $log = false){
		
	if(!$file || !$keys || !is_array($keys) || count($keys) < 1){		
		echo 'Error CSV. File or keys is miss';
		if($log){ $log->log($log->msg['CSV_PARAMS_ERROR'], $log::ERR); }
		return false;
	}
	
	$content = array();
	$row = 1;
	if (($handle = fopen($file, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {						
			if($row > 1){
				/* Ex:
					$keys = array(
						'id_external',
						'name',						
					);
				*/	
				if(count($data) > 0){
					if (!(count($data) == 1 && trim($data[0]) == '')){ //--Исключаем пустую строку						
						foreach($keys as $numCol => $Key){ //--$numCol - порядковый номер названия поля, оно же индекс в CSV файле.
							if(isset($data[$numCol])){
								$tt[$Key] = trim($data[$numCol]);
							} else {
								$tt[$Key] = '';
							}					
						}				
						$content[] = (object)$tt;
					}
				}
			}
			$row++;
		}
		fclose($handle);
		return $content;
	}
	echo 'Error. Can not open file "'.$file.'"'."\n";
	if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $file), $log::ERR); }
	return false;
}


/**
 *  Пишет лог в файл.
 * param1 - массив строк или одна строка
 * param2 - имя файла для записи 
*/
/*
function seveToLog($data = false, $fileName = 'common.csv'){
	$fileName = COMMON_LOG_PREFIX.$fileName;
	if(!$data){
		return false;
	}
	
	if(!is_array($data)){
		$data = array($data);
	}
	
	
	$errorsMsg = array(
		'EXCEPTION' 			=> '%s',
		'CREATE_SUCCESS' 		=> '(%s) %s',
		'UPDATE_SUCCESS' 		=> '(%s) %s',		
		'IMPORT_CSV_SUCCESS' 	=> 'Файл "%s" успешно импортирован.',
		'IMPORT_CSV_ERROR' 		=> 'Ошибка импорта Файла "%s". См. подробный отчет.',
		'PATH_ERROR' 			=> 'Ошибка доступа к папке "%s".',
		'PATH_CREATE_ERROR' 	=> 'Ошибка создания папки "%s".',
		'FILE_ERROR' 			=> 'Ошибка доступа к файлу "%s".',
		'FILE_CREATE_ERROR' 	=> 'Ошибка создания файла "%s".',
	);
	
	$path = REAL_PATH_TO_LOGS.'/'.date('Y-m-d', time());	
	if(!is_writable($path)){ //--проверка на запись в папку.				
		if(!mkdir($path, 0664)){ //--пробуем создать папку.			
			echo 'Path "'.$path.'" can not create';
			return false;
		}		
	}	
	
	
	$file = $path.'/'.$fileName; //--файл для записи		

	if (!$handle = fopen($file, 'a')) {
		echo "File can`t open: ($file)";
		exit;
	}
	
	foreach($data as $line){					
		if (fwrite($handle, date('Y-m-d H:i:s',time()).';'.$line."\r\n") === FALSE) {
			echo "Can`t write into file: ($file)";
			fclose($handle);
			return false;
		}
	}
	fclose($handle);
		
	return true;		
}
*/


function isFileCorrect($params = false){
	if(!$params){
		if($log){ $log->log($log->msg['PARAMS_ERROR'], $log::ERR); }
		return 'Error. Empty params.';
	}
	
	if(!file_exists($params['REAL_PATH'])){
		if($log){ $log->log(sprintf($log->msg['FILE_NOT_FOUND'], $params['REAL_PATH']), $log::ERR); }		
		return "\n".'Error. File not found: "'.$params['PATH'].'"'."\n";			
	} else {
		$curDate = date('Y.m.d',time());		
		$lastUpd = date ("Y.m.d", filemtime($params['REAL_PATH']));		
		if($lastUpd != $curDate){						
			if($log){ $log->log(sprintf($log->msg['FILE_IS_OLD'], $params['REAL_PATH'], $lastUpd), $log::ERR); }
			return "\n".'Error. File is to old: "'.$params['FILE'].'". Last update: '.$lastUpd.'. Exit script.'."\n";
		} else {
			return true;
		}
	}	
}

		
function importLearningsubjects($params = false, $log = false, $extLogFiles = false){
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'id_external',
			'name',
			'direction',
			'specialisation',
			'hours',
			'control',
			'year',			
			'semester',
		);
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
		
		//--filters		
		//--удаляем пустые атрибуты
		//--преобразуем к int
		foreach($data as $i){
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}
			filterInt($i->id_external);
			filterInt($i->year);
		}
		
		if(isset($extLogFiles['LEARNING_SUBJECTS'])){
			$log_LS = initLog($extLogFiles['LEARNING_SUBJECTS']);
		} else{
			$log_LS = $log; //--Если иной лог не определен, пишем в общий
		}
		
		
		
		$t = new HM_Learningsubjects_Import_Manager();
		
		$tt = $t->init($data);
		$result = $t->import($log_LS);
		
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}				
		
		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {			
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";		
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_LS){ $log_LS->log(sprintf($log_LS->msg['END_IMPORT_EMPTY'], $fileName), $log_LS::INFO); }
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";					
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log::INFO); }
			if($log_LS){ $log_LS->log(sprintf($log_LS->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log_LS::INFO); }
		}
		
		return true;
		 
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}
}

/**
 * Оргструктура
*/
function importOrgstructure($params = false, $log = false, $extLogFiles = false){
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'soid_external',
            'name',
            'owner_soid_external',
            'mid_external',			
		);
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){	
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";		
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
		
		//--filters		
		//--удаляем пустые атрибуты
		//--НЕ преобразуем к int, т.к. id у преподов символьно-числовой.id разделов с префиксом.
		foreach($data as $i){
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}			
		}
		
		if(isset($extLogFiles['ORGSTRUCTURE'])){
			$log_ORG = initLog($extLogFiles['ORGSTRUCTURE']);
		} else{
			$log_ORG = $log; //--Если иной лог не определен, пишем в общий
		}
		
		$t = new HM_Orgstructure_Import_Manager();
		
		
		$tt = $t->init($data);		
		$result = $t->import($log_ORG);
		
		if($result['error'] === true){	
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";		
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
				
		if($result['allInsert'] == 0 && $result['allUpdate'] == 0 && $result['allUsers'] == 0) {		
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";		
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_ORG){ $log_ORG->log(sprintf($log_ORG->msg['END_IMPORT_EMPTY'], $fileName), $log_ORG::INFO); }
		} else {	
			echo 'Export completed. File: "'.$fileName.'"'."\n";							
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), '-', $result['allInsert'], $result['update'], $result['allUpdate'], $result['users'], $result['allUsers']), $log::INFO); }
			if($log_ORG){ $log_ORG->log(sprintf($log_ORG->msg['END_IMPORT_INFO'], $fileName, count($data), '-', $result['allInsert'], $result['update'], $result['allUpdate'], $result['users'], $result['allUsers']), $log_ORG::INFO); }
		}		
		return true;
		 
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}
}

/**
 * Импорт сессий
*/
function importSessions($params = false, $log = false, $extLogFiles = false){
	try {
		
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'external_id',
            'learning_subject_id_external',
            'programm_id_external',
            'name',
            'chair',
            'teacher_id_external',
            'exam_type',
            'learn',
            'hours_total',
            'classroom',
            'self_study',
            'lection',
            'lab',
            'practice',
            'exam',
            'begin',
            'end',		
		);
		
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";					
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		} 
						
		//--filters		
		//--НЕ удаляем пустые атрибуты? почему?		
		foreach($data as $i){
			filterInt($i->external_id);
			filterInt($i->learning_subject_id_external);
			filterInt($i->programm_id_external);
			
			$i->exam_type = trim($i->exam_type);
			if($i->exam_type == 'Приём экзаменов'){
				$i->exam_type = HM_Subject_SubjectModel::EXAM_TYPE_EXAM;
			} else if($i->exam_type == 'Приём зачётов'){
				$i->exam_type = HM_Subject_SubjectModel::EXAM_TYPE_TEST;
			} else {
				$i->exam_type = HM_Subject_SubjectModel::EXAM_TYPE_NONE;
			}
			
			$format = 'Y-m-d';
			$i->begin = date($format, strtotime($i->begin));
			$i->end = date($format, strtotime($i->end));
			$i->period = HM_Subject_SubjectModel::PERIOD_DATES;
			$i->period_restriction_type = HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT;                
        }        
				
		if(isset($extLogFiles['SESSIONS'])){
			$log_S = initLog($extLogFiles['SESSIONS']);
		} else{
			$log_S = $log; //--Если иной лог не определен, пишем в общий
		}
		
		$t = new HM_Subject_Import_Manager();		
		$t->init($data);			
		$result = $t->import($log_S);
		
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
		
		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_S){ $log_S->log(sprintf($log_S->msg['END_IMPORT_EMPTY'], $fileName), $log_S::INFO); }
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log::INFO); }
			if($log_S){ $log_S->log(sprintf($log_S->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log_S::INFO); }
		}		
		return true;
		
		
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}
}




/**
 * Импорт программ
*/
function importProgramms($params = false, $log = false, $extLogFiles = false){
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'id_external',
            'name',          		
		);
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
		
		//--filters		
		//--удаляем пустые атрибуты
		//--преобразуем к int
		foreach($data as $i){			
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}			
			filterInt($i->id_external);
		}
		
		if(isset($extLogFiles['PROGRAMMS'])){
			$log_P = initLog($extLogFiles['PROGRAMMS']);
		} else{
			$log_P = $log; //--Если иной лог не определен, пишем в общий
		}
		
		
		$t = new HM_Programm_Import_Manager();
		$t->init($data);
		$result = $t->import($log_P);
		
		
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
				
				
		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_P){ $log_P->log(sprintf($log_P->msg['END_IMPORT_EMPTY'], $fileName), $log_P::INFO); }
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log::INFO); }
			if($log_P){ $log_P->log(sprintf($log_P->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log_P::INFO); }
		}	
			
		return true;
				
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}	
}


/**
 * Импорт групп.
*/
function importGroups($params = false, $log = false, $extLogFiles = false){
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'id_external',
            'name',
            'faculty',
            'year',
            'education_type',
            'speciality',
            'course',
            'duration',
            'foundation_year',
            'programm_id_external',
            'programm_id_name',         		
		);
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
							
		//--filters		
		//--удаляем пустые атрибуты
		//--преобразуем к int
		$type = HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM;
		foreach($data as $i){			
			$i->type = $type;
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}			
			filterInt($i->id_external);
            filterInt($i->foundation_year);
            filterInt($i->programm_id_external);	
		}
		
		if(isset($extLogFiles['GROUPS'])){
			$log_G = initLog($extLogFiles['GROUPS']);
		} else{
			$log_G = $log; //--Если иной лог не определен, пишем в общий
		}
		
		$t = new HM_StudyGroup_Import_Manager();
		$t->init($data);
		$result = $t->import($log_G);
		
		
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
		
		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_G){ $log_G->log(sprintf($log_G->msg['END_IMPORT_EMPTY'], $fileName), $log_G::INFO); }
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log::INFO); }
			if($log_G){ $log_G->log(sprintf($log_G->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], '-', '-'), $log_G::INFO); }
		}
		
		return true;
		
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}	
}


/**
 * Импорт слушателей.
*/
function importUsers($params = false, $log = false, $extLogFiles = false){
	
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'mid_external',
            'LastName',
            'FirstName',
            'Patronymic',
            'Gender',
            'BirthDate',
            'group_id_external',
            'status_1c',
            'Login',
            'Phone',
            'EMail',
            'CellularNumber', 
			'isDO', //--для разделения по логам при мипорте.
			'organization',
			'Password',
            'isAD',            
            'begin_learning',
            'Skype', 
		);
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
		
		
		if(isset($extLogFiles['USERS'])){
			$log_U = initLog($extLogFiles['USERS']);			
		} else{			
			$log_U = $log; 
		}
		
		if(isset($extLogFiles['USERS_DO'])){			
			$log_Udo = initLog($extLogFiles['USERS_DO']);
		} else{			
			$log_Udo = $log; //--лог только для ДО
		}
		
		
		$log_U->log('Тип ошибки;Текст ошибки;Номер строки в исходном файле;СтудентКод;Фамилия;Имя;Отчество;Пол;ДатаРождения;ГруппаКод;Состояние;Логин;ТелефонКонтактный;ПочтаФизлица;ТелефонСотовый;ДО;Организация', $log_U::INFO);
		$log_Udo->log('Тип ошибки;Текст ошибки;Номер строки в исходном файле;СтудентКод;Фамилия;Имя;Отчество;Пол;ДатаРождения;ГруппаКод;Состояние;Логин;ТелефонКонтактный;ПочтаФизлица;ТелефонСотовый;ДО;Организация', $log_U::INFO);
		
		
		
		//--filters		
		//--удаляем пустые атрибуты
		//--преобразуем к int		
		// HM_User_Csv_Student_CsvMapper modifyModel
		$role_1c = HM_User_UserModel::ROLE_1C_STUDENT;
		$validator = new Zend_Validate_EmailAddress(); 
		foreach($data as $k => $i){
			$logName = ($i->isDO == 1) ? ('log_Udo') : ('log_U');
			
			$raw_i = clone $i;
			
			$i->role_1c = $role_1c;	
			$i->blocked = 0;
			$i->isAD = 1;
			$i->need_edit = 0;
			
			filterInt($i->mid_external);
			filterInt($i->BirthDate);
			filterInt($i->group_id_external);
			
			$i->organization = trim($i->organization);
			
			if(empty($i->LastName) && empty($i->FirstName) && empty($i->Patronymic)){
				
				$error_description = getErrorDescription($k, $raw_i);
				if(${$logName}){ ${$logName}->log(sprintf(${$logName}->msg['INCORRECT_PARAM_ITEM_NOT_CREATE'], 'ФИО', $i->mid_external, '', $error_description), ${$logName}::ERR); }				
				unset($data[$k]);
				continue;
			}
			
			if(empty($i->Login)){
				
				$error_description = getErrorDescription($k, $raw_i);
				if(${$logName}){ ${$logName}->log(sprintf(${$logName}->msg['INCORRECT_PARAM_ITEM_NOT_CREATE'], 'Логин', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, $error_description), ${$logName}::ERR); }				
				unset($data[$k]);
				continue;
			}
			
			
			if(trim($i->Gender) == 'Мужской'){
				$i->Gender = 1;
			} else if(trim($i->Gender) == 'Женский'){
				$i->Gender = 2;
			} else {

				$error_description = getErrorDescription($k, $raw_i);
				if(${$logName}){ ${$logName}->log(sprintf(${$logName}->msg['INCORRECT_PARAM'], 'Пол', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, $error_description), ${$logName}::ERR); }				
			}
			
			
			
			if(!empty($i->BirthDate) && $i->BirthDate < 1900){
				
				$error_description = getErrorDescription($k, $raw_i);
				if(${$logName}){ ${$logName}->log(sprintf(${$logName}->msg['INCORRECT_PARAM'], 'ДатаРождения', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, $error_description), ${$logName}::ERR); }				
			}
						
			
			if(!empty($i->EMail)){				
				if (!$validator->isValid($i->EMail)) {
					
					$error_description = getErrorDescription($k, $raw_i);
					if(${$logName}){ ${$logName}->log(sprintf(${$logName}->msg['INCORRECT_PARAM'], 'ПочтаФизлица', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, $error_description), ${$logName}::ERR); }				
				}
			}
			
			
			$ts = strtotime($i->begin_learning);
			if($ts <= 0){
				$i->begin_learning = '';
			} else {
				$i->begin_learning = date('Y-m-d', $ts);
			}
			
			$i->Skype = trim($i->Skype);
			
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}			
		}
		
		
		
		$t = new HM_User_Import_Manager();
		$role1c = HM_User_UserModel::ROLE_1C_STUDENT;
		$t->init($data, $role1c);
		
		$result = $t->import($log_U, $log_Udo);
		//$result = false;
		
	
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
			


		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_U){ $log_U->log(sprintf($log_U->msg['END_IMPORT_EMPTY'], $fileName), $log_U::INFO); }		
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], 0, 0), $log::INFO); }
			if($log_U){ $log_U->log(sprintf($log_U->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], 0, 0), $log_U::INFO); }
		}
		
		if($result['allInsertDO'] == 0 && $result['allUpdateDO'] == 0) {
			echo 'Export completed. (DO) But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY_DO'], $fileName), $log::INFO); }
			if($log_Udo){ $log_Udo->log(sprintf($log_Udo->msg['END_IMPORT_EMPTY'], $fileName), $log_Udo::INFO); }
		} else {
			echo 'Export completed. (DO) File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO_DO'], $fileName, count($data), $result['insertDO'], $result['allInsertDO'], $result['updateDO'], $result['allUpdateDO'], 0, 0), $log::INFO); }
			if($log_Udo){ $log_Udo->log(sprintf($log_Udo->msg['END_IMPORT_INFO_DO'], $fileName, count($data), $result['insertDO'], $result['allInsertDO'], $result['updateDO'], $result['allUpdateDO'], 0, 0), $log_Udo::INFO); }
		}
		
		if(!empty($result['doubleRows'])){
			echo 'Double MID row count: "'.$result['doubleRows'].'"'."\n";
			if($log){ $log->log('Задвоений по коду студента: '.$result['doubleRows'], $log::INFO); }
			if($log_U){ $log_U->log('Задвоений по коду студента: '.$result['doubleRows'], $log_U::INFO); }
		}
		
		
		return true;
		
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}
	
}


/**
 * Импорт тьюторов.
*/
function importTutors($params = false, $log = false, $extLogFiles = false){
	try {
		$path = $params['REAL_PATH'];
		$fileName = $params['FILE'];
		
		$keys = array(
			'mid_external',
            'LastName',
            'FirstName',
            'Patronymic',
            'Login',
            'EMail',
            'Password',
            'isTutor',
            'tags', 
			'organization',			
		);
		
		$data = getCsvContent($path, $keys, $log);
		
		if($data === false){			
			echo 'Error. File not fount or error read: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['FILE_READ_ERROR'], $fileName), $log::ERR); }
			return false;
		}
		
		if(isset($extLogFiles['TUTORS'])){
			$log_T = initLog($extLogFiles['TUTORS']);			
		} else{			
			$log_T = $log; 
		}
		
		//--filters		
		//--удаляем пустые атрибуты
		//--преобразуем к int		
		// HM_User_Csv_Student_CsvMapper modifyModel
		$role_1c = HM_User_UserModel::ROLE_1C_TUTOR;		
		$validator = new Zend_Validate_EmailAddress(); 
		foreach($data as $k => $i){
			$i->role_1c = $role_1c;	
			$i->blocked = 0;
			$i->isAD = 1;
			$i->need_edit = 0;
			
			$i->organization = trim($i->organization);

			if(empty($i->LastName) && empty($i->FirstName) && empty($i->Patronymic)){
				
				if($log_T){ $log_T->log(sprintf($log_T->msg['INCORRECT_PARAM_ITEM_NOT_CREATE'], 'ФИО', $i->mid_external, '', ''), $log_T::ERR); }	
				unset($data[$k]);
				continue;				
			}
			
			if(empty($i->Login)){
				if($log_T){ $log_T->log(sprintf($log_T->msg['INCORRECT_PARAM_ITEM_NOT_CREATE'], 'Логин', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, ''), $log_T::ERR); }				
				unset($data[$k]);
				continue;
			}

			if(!empty($i->EMail)){				
				if (!$validator->isValid($i->EMail)) {
					if($log_T){ $log_T->log(sprintf($log_T->msg['INCORRECT_PARAM'], 'ПочтаФизлица', $i->mid_external, $i->LastName.' '.$i->FirstName.' '.$i->Patronymic, ''), $log_T::ERR); }				
				}
			}
						
			foreach($i as $key => $val){
				if(trim($val) == "" ){
					unset($i->{$key});
				}
			}			
		}
		
		
		
		$t = new HM_User_Import_Manager();
		$role1c = HM_User_UserModel::ROLE_1C_TUTOR;
		$t->init($data, $role1c);
		$result = $t->import($log_T);
	
		
		if($result['error'] === true){			
			echo 'Error. Export completed with errors: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], $fileName), $log::ERR); }
			return false;
		}	
			


		if($result['allInsert'] == 0 && $result['allUpdate'] == 0) {
			echo 'Export completed. But new data not found. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_EMPTY'], $fileName), $log::INFO); }
			if($log_T){ $log_T->log(sprintf($log_T->msg['END_IMPORT_EMPTY'], $fileName), $log_T::INFO); }
		} else {
			echo 'Export completed. File: "'.$fileName.'"'."\n";
			if($log){ $log->log(sprintf($log->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], 0, 0), $log::INFO); }
			if($log_T){ $log_T->log(sprintf($log_T->msg['END_IMPORT_INFO'], $fileName, count($data), $result['insert'], $result['allInsert'], $result['update'], $result['allUpdate'], 0, 0), $log_T::INFO); }
		}
		
		return true;
		
	
	} catch (Exception $e) {				
		echo 'Exception: ',  $e->getMessage(), "\n";
		if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), 8); }
		return false;
	}
}


/**
 * инициализируем экземпляр класса Zend Log переопределив некоторые параметры.
 * return Zend Log class
 * EMERG = 0
 * ALERT = 1
 * CRIT = 2
 * ERR = 3
 * WARN = 4
 * NOTICE = 5
 * INFO = 6
 * DEBUG = 7
 * EXCEPTION = 8
 * SUCCESS = 9
*/
function initLog($path){
	
	if(!$path){
		return false;
	}	
		
	$msg = array(
		'EXCEPTION' 			=> '%s',
		
		'FTP_CONNECT_SUCCESS'	=> _('Подключен к "%s"'),
		'FTP_CONNECT_ERROR'		=> _('Не удается подключиться к "%s"'),
		'FTP_LOGIN_SUCCESS'		=> _('Авторизация на "%s"'),
		'FTP_LOGIN_ERROR'		=> _('Не удается авторизоваться на "%s"'),
		'FTP_CHDIR_SUCCESS'		=> _('Каталог изменен на "%s"'),
		'FTP_CHDIR_ERROR'		=> _('Не удается изменить каталог на "%s"'),
		'FTP_FILES_NOT_SET' 	=> _('Не установлены названия файлов для загрузки в настройках скрипта'),
		'FTP_CONNECT_CLOSED'	=> _('Подключение к ftp не установлено.'),
		
		'PATH_UNAVAILABLE' 		=> _('Папка "%s" недоступна.'),
		
		'FILE_UNAVAILABLE' 		=> _('Файл "%s" недоступен.'),
		'FILE_UPLOAD_SUCCESS' 	=> _('Файл "%s" успешно загружен.'),
		'FILE_UPLOAD_ERROR' 	=> _('Не удалось загрузить файл "%s".'),
		'FILE_READ_ERROR' 		=> _('Ошибка чтения файла "%s".'),
		'FILE_WRITE_ERROR' 		=> _('Ошибка записи в файл "%s".'),
		
		'FILE_NOT_FOUND' 		=> _('Файл не найден: "%s".'),
		
		'PATH_TO_UPLOAD' 		=> _('Папка загрузок: "%s".'),
		
		
		'FILE_INCORRECTED' 		=> _('Некорректный файл "%s".'),
		'FILES_INCORRECTED' 	=> _('Некорректные файлы для импорта.'),
		'FILES_NOT_SET' 		=> _('Не установлен список файлов для импорта.'),
		
		'FILE_IS_OLD'			=> _('Файл "%s" устарел. Дата обновления: "%s"'),	
		'FILE_INFO_ERROR'		=> _('Не удалось получить информацию о "%s"'),
		
		
		'ALL_FILES_UPLOAD'		=> _('Все файлы загружены.'),
		'ALL_FILES_CORRECT'		=> _('Все файлы корректны.'),
		
		
		'ITEM_CREATE_SUCCESS'	=> _('Создан (%s) "%s".'),
		'ITEM_CREATE_ERROR'		=> _('Не удалось создать запись: (%s) "%s".'),
		'ITEM_UPDATE_SUCCESS'	=> _('Обновлен (%s) "%s".'),
		'ITEM_UPDATE_ERROR'		=> _('Не удалось обновить запись (%s) "%s".'),
		'ITEM_POSITION_CREATE_SUCCESS'	=> _('Создано назначение (%s).'),
		'ITEM_POSITION_CREATE_ERROR'	=> _('Не удалось создать назначение: (%s).'),
		
		'ITEM_LINK_TUTOR_SUCCESS'		=> _('Назначен тьютор "%s" на сессию (%s).'),
		'ITEM_LINK_TUTOR_ERROR'			=> _('Ошибка назначения тьютора "%s" на сессию (%s).'),
		'ITEM_SESSION_NOT_FOUND'		=> _('Отсутствует соотвествующий учебный предмет или привязка к курсу. Сессия (%s) "%s"'),
		
		'END_IMPORT_INFO'		=> _('Импорт  файла "%s" завершен. Всего:(%s). Новых:(%s/%s). Изменено:(%s/%s). Пользователей:(%s/%s)'),
		'END_IMPORT_INFO_DO'	=> _('Импорт  файла "%s" завершен. Всего:(%s). Новых:(%s/%s). Изменено:(%s/%s). Пользователей:(%s/%s) (для ДО)'),
		'END_IMPORT_EMPTY'		=> _('Во время импорта  файла "%s" ни одной записи не было изменено или создано.'),
		'END_IMPORT_EMPTY_DO'	=> _('Во время импорта  файла "%s" ни одной записи не было изменено или создано. (для ДО)'),
		'ITEM_IMPORT_BEGIN'		=> _('Импорт файла "%s" начался.'),
		'ITEM_IMPORT_END'		=> _('Импорт файла "%s" завершен.'),
		'ITEM_IMPORT_ERROR'		=> _('Ошибка при импорте файла "%s".'),
		
		'IMPORT_BEGIN'			=> _('Импорт начался.'),
		'IMPORT_END'			=> _('Импорт завершен.'),
		'IMPORT_END_ERROR'		=> _('Импорт завершен с ошибкой.'),
		
		'SEND_SUCCESS'			=> _('Отчет успешно отправлен'),
		'SEND_ERROR'			=> _('Ошибка отправки отчета. Причина: "%s"'),
		'EMAIL_ERROR'			=> _('Некорректный email: "%s".'),
		
		
		'CSV_PARAMS_ERROR'		=> _('Не указаны параметры для разбора CSV.'),		
		'PARAMS_ERROR'			=> _('Параметры не установлены.'),		
		
		
		
		
		'SCRIPT_BEGIN'			=> _('Скрипт запущен.'),
		'SCRIPT_END'			=> _('Скрипт завершен. Выход.'),
		
		
		'INCORRECT_PARAM'					=> _('Некорректный параметр: "%s"; в записи (%s) "%s" %s'),
		'INCORRECT_PARAM_ITEM_NOT_CREATE'	=> _('Запись не создана/обновлена. Некорректный параметр: "%s"; в записи (%s) "%s" %s'),
		'DOUBLE_MID_ROW'					=> _('Задвоение по коду студента; (%s) "%s" %s'),
	);
	
	/*
	foreach($msg as $k => $v){
		$msg[$k] = mb_convert_encoding($v, 'windows-1251', 'utf-8');	
	}
	*/

	try {
		
		$format = '%timestamp%;%priorityName% (%priority%);%message%'.PHP_EOL;
		$formatter = new Zend_Log_Formatter_Simple($format);
		if(!$formatter){
			return false;
		}
		$mode = 'w'; //--перезаписываем файл.
		$writer = new Zend_Log_Writer_Stream($path, $mode);		
		if(!$writer){
			return false;
		}
		
		$writer->setFormatter($formatter);	
		
		$log = new Zend_Log($writer); 	
		if(!$log){
			return false;
		}
		
		$log->msg = $msg;
		$log->addPriority('EXCEPTION', 8);
		$log->addPriority('SUCCESS', 9);			
		
		return $log;
	} catch (Exception $e) {
		echo 'Exception: '.$e->getMessage()."\n";
	}
}

/**
 * Првоеряем наличие данного пути.
 * Ечсли его нет, то пробуем создать
*/
function createPath($path){
	if(realpath($path)){
		return true;
	}
	
	if (!mkdir($path, 0770, true)) {
		return false;
	}
	return true;		
}


/**
 * меняет кодировку лог файла
*/
function convertLogFile($file, $fromCharset = 'utf-8', $toCharset = 'windows-1251'){
	if(!$file){
		return false; 
	}
	
	$contents = false;
	
	if(!$handle = fopen($file, "rb")){
		return false;
	}	
	$contents = fread($handle, filesize($file));
	
	if(!$contents){
		return false;
	}
	
	if (!is_writable($file)){
		return false;
	}
	
	if (!$handle = fopen($file, 'wb')){
		return false;
	}
			
	$contents = mb_convert_encoding($contents, $toCharset, $fromCharset);
	
	if(empty($contents)){
		return false;
	}
		
	if (fwrite($handle, $contents) === FALSE) {
		return false;
	}	
	
	fclose($handle);				
	
	return true;
}


# данные для лога по проверяемой строке в случае некорректных данных
function getErrorDescription($row_num, $row)
{
	$description = array(
		'Строка' 			=> $row_num + 1 + 1, # Номер строки в excel. +1 - первая строка - имена столбцов. +1 - нумерация массива идет с 0, в excel с 1.
		'СтудентКод'		=> $row->mid_external,
		'Фамилия'			=> $row->LastName,
		'Имя'				=> $row->FirstName,
		'Отчество'			=> $row->Patronymic,
		'Пол'				=> $row->Gender,
		'ДатаРождения'		=> $row->BirthDate,
		'ГруппаКод'			=> $row->group_id_external,
		'Состояние'			=> $row->status_1c,
		'Логин'				=> $row->Login,
		'ТелефонКонтактный'	=> $row->Phone,
		'ПочтаФизлица'		=> $row->EMail,
		'ТелефонСотовый'	=> $row->CellularNumber,
		'ДО'				=> $row->isDO,
		'Организация'		=> $row->organization,
	);
	return ';'.implode(';', $description);
}

?>
