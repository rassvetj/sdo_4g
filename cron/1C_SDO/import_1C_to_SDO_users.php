#!/usr/bin/env php
<?php
/* Почему после обновления снова считается, что студента надо обновить, хотя у него поля не изменились? */
/* Скрипт загружает пользователей в автоматическом режиме из 1С УОП 1 раз в день */
/*	
	Users.csv				Слушатели	
*/

require_once  '_import_init.php';

//=====Определяем параметры, специфичные для данного скрипта.
if (!defined('IS_EMPTY_IMPORT')) { //-если = true, тогда выгрузка будет без реальной загрузки в БД.
    define('IS_EMPTY_IMPORT', false);
    //define('IS_EMPTY_IMPORT', true);
}

if (!defined('IS_SEND_REPORT')) { //-если = true, тогда отсылаем письмо
    //define('IS_SEND_REPORT', false);
    define('IS_SEND_REPORT', true);
}

if (!defined('IS_UPLOAD_FROM_FTP')) { //-если = true, тогда скачиваем с ftp данные для импорта
    //define('IS_UPLOAD_FROM_FTP', false);
    define('IS_UPLOAD_FROM_FTP', true);
}

$LOG_PATH = $_SERVER['DOCUMENT_ROOT'].'/zlog/import/users/';
$LOG_FILE_NAME = 'log_users_common.csv';

$isError = false; //--есть ошибки. Если да, то выводим сообщение в письме.
$isErrorDo = false; //--есть ошибки. Если да, то выводим сообщение в письме.

$LOG_FILE_NAME_USERS 		= 'log_users.csv';
$LOG_FILE_NAME_USERS_DO 	= 'log_users_do.csv';


$LOG_FILE_LIST_ALL = array( //--пути к логам для всех
	$LOG_PATH.'/'.$LOG_FILE_NAME,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS,	
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,
);

$LOG_FILE_LIST = array( //--пути к логам
	$LOG_PATH.'/'.$LOG_FILE_NAME,	 
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS,	
);

$LOG_FILE_LIST_DO = array( //--пути к логам для ДО
	$LOG_PATH.'/'.$LOG_FILE_NAME,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,
);



//=============Тело скрипта==========================
if(createPath($LOG_PATH)){ 
	$logFile = $LOG_PATH.'/'.$LOG_FILE_NAME;
	foreach($LOG_FILE_LIST as $f){ //--удаляем старые логи
		$f = realpath($f);
		if($f){				
			unlink($f);
		}		
	}
	foreach($LOG_FILE_LIST_DO as $f){ //--удаляем старые логи
		$f = realpath($f);
		if($f){				
			unlink($f);
		}		
	}
} else {
	echo 'Error create path to log: '.$LOG_PATH."\n";
}

$log = initLog($logFile);

$log->log($log->msg['SCRIPT_BEGIN'], $log::INFO);		
echo "\nScript started\n";			

$C = initUserParams();


if(IS_UPLOAD_FROM_FTP){
	$ftp_conn_id = getConnect($FTP_SERVER_NAME, $FTP_LOGIN, $FTP_PASS, false, $log);
	$isGetFtpFiles = getFilesFromFtp($C['FTP_FILE_LIST'], $ftp_conn_id, $log);
	ftp_close($ftp_conn_id);
} else {
	$isGetFtpFiles  = true;	
	echo "FTP files not uploaded, because set in config 'IS_UPLOAD_FROM_FTP' = false\n";
	echo "For import used local files in path '".PATH_TO_FILES."'"."\n";
}

$C = initUserParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.

if($isGetFtpFiles){
	$log->log($log->msg['ALL_FILES_UPLOAD'], $log::INFO);	
	echo "FTP files success upload\n";			
		
	$log->log($log->msg['IMPORT_BEGIN'], $log::INFO);	
	echo "Begin import\n";	
	
	$extLogFiles = array(		
		'USERS' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_USERS,
		'USERS_DO' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,		
	);
	
				
	if(importUserFiles($C, $log, $extLogFiles)){ //--импортируем слушателей			
		$log->log(sprintf($log->msg['ITEM_IMPORT_END'], 'Users'), 9);
		echo "Import complate\n";					
	} else {		
		$isErrorDo = $isError = true;
		echo "Error. In process to import has been an errors. See in details logs\n";	
		$log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], 'Users'), $log::ERR);
		$log->isError = true; //--флаг, по которому определяем, что есть ошибки и надо общий лог отправить на 112
	}
} else {	
	$isErrorDo = $isError = true;
	echo "Error. FTP files not uploaded\n";						
	$log->log(sprintf($log->msg['FILE_UPLOAD_ERROR'], 'Users'), $log::ERR);	
	$log->isError = true;	
}
$log->log(sprintf($log->msg['IMPORT_END'], 'Users'), $log::INFO);	

$C = initUserParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.


foreach($LOG_FILE_LIST_ALL as $f){
	convertLogFile($f);	
}

if(IS_SEND_REPORT){
	if($log->isError){
		if(sendReport($reportCommonToEmail, '', array(), array($LOG_PATH.'/'.$LOG_FILE_NAME))){ //--112
			echo "\nReport sended to $reportCommonToEmail (common)\n";		
		} else {
			echo "\nReport not sended $reportCommonToEmail (common)\n";	
		}	
	} else {
		echo "\nReport not sended to $reportCommonToEmail, because no error (common)\n";	
	}
	
	
	$bodyDo = $body = _('Выгрузка прошла успешно. Дополнительную информацию смотрите во вложении.');
	if($isErrorDo){
		$bodyDo = _('Во время импорта возникли ошибки. Подробную информацию смотрите во вложении.');
	} 
	if($isError){
		$body = _('Во время импорта возникли ошибки. Подробную информацию смотрите во вложении.');	
	} 
	$body = $body.'<hr>';
	$bodyDo = $bodyDo.'<hr>';
	
	foreach($C['FILE_DESCRIPTION_NOT_DO'] as $k => $v){
		$body = $body.'<br>'.$k.' - '.$v.'<br>';
	}
	foreach($C['FILE_DESCRIPTION_DO'] as $k => $v){
		$bodyDo = $bodyDo.'<br>'.$k.' - '.$v.'<br>';
	}
	
	
	$body 	= $body.'<br>'.$LOG_FILE_NAME_USERS.' - содержит информацию о записях, которые были добавлены или обновлены при импорте в СДО.<br>';
	$bodyDo = $bodyDo.'<br>'.$LOG_FILE_NAME_USERS_DO.' - содержит информацию о записях, которые были добавлены или обновлены при импорте в СДО.<br>';
	
		
	if(sendReport($reportToEmail, $body, $C['LOCAL_FILE_LIST_NOT_DO'], array($LOG_PATH.'/'.$LOG_FILE_NAME_USERS))){
		echo "\nReport sended to $reportToEmail\n";	
	} else {
		echo "\nReport not sended to $reportToEmail\n";		
	}

	if(sendReport($reportToEmailDo, $bodyDo, $C['LOCAL_FILE_LIST_DO'], array($LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO))){
		echo "\nReport sended to $reportToEmailDo\n";	
	} else {
		echo "\nReport not sended to $reportToEmailDo\n";		
	}	
} else {
	echo "\nReport not sended, because set in config 'IS_SEND_REPORT' = false\n";	
}

echo "\ndone\n";
//=============Конец работы скрипта==========================



//===============Функции=====================================
/**
 * опрелеляем параметры для импорта Слушателей
*/
function initUserParams(){
	
	$name_users 			= 'Users.csv';	
	$name_1c_log 			= 'log_1c_users.csv';	
	#$name_1c_log_do 		= 'log_1c_users_d.csv';	
	
	
	//--Список файлов для загрузки с FTP.
	$C['FTP_FILE_LIST'] = array(	
		$name_users,	
		$name_1c_log,	
		#$name_1c_log_do,	
	);
	
	$C['LOCAL_FILE_LIST'] = array(		
		realpath(PATH_TO_FILES.'/'.$name_users),		
		realpath(PATH_TO_FILES.'/'.$name_1c_log),		
		#realpath(PATH_TO_FILES.'/'.$name_1c_log_do),		
	);
	
	
	$C['LOCAL_FILE_LIST_DO'] = array( //--для отправки письма ДО
		realpath(PATH_TO_FILES.'/'.$name_users),				
		#realpath(PATH_TO_FILES.'/'.$name_1c_log_do),		
	);
	
	$C['FILE_DESCRIPTION_DO'] = array( //--для чего нужны файлы
		$name_users => _('файл с данными, который был выгружен из 1С и загружен в СДО'),				
		#$name_1c_log_do => _('содержит все ошибки, которые возникли при выгрузке данных из 1С в файл "'.$name_users.'"'),		
		//$name_1c_log_do => _('содержит информацию о записях, которые были добавлены или обновлены при импорте файла "'.$name_users.'".'),	
	);
	
	$C['FILE_DESCRIPTION_NOT_DO'] = array( //--для чего нужны файлы
		$name_users 	=> _('файл с данными, который был выгружен из 1С и загружен в СДО'),					
		$name_1c_log 	=> _('содержит все ошибки, которые возникли при выгрузке данных из 1С в файл "'.$name_users.'"'),	
		//$name_1c_log 	=> _('содержит информацию о записях, которые были добавлены или обновлены при импорте файла "'.$name_users.'".'),	
	);
	
	$C['LOCAL_FILE_LIST_NOT_DO'] = array(	//--для отправки письма не ДО	
		realpath(PATH_TO_FILES.'/'.$name_users),				
		realpath(PATH_TO_FILES.'/'.$name_1c_log),			
	);
	
	$C['USERS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_users),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_users,
		'FILE' 		=> $name_users,
	);
	return $C;
}

?>