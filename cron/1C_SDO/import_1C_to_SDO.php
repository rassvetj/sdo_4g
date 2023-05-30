#!/usr/bin/env php
<?php
/* Скрипт загружает в автоматическом режиме данные из 1С УОП по алгоритму Алтуховой. */
/* Порядок загрузки файлов: */
/*
	1. Learningsubjects.csv		Предметы
	2. Programms.csv			Программы
	3. Groups.csv				Группы
	4. Users.csv				Слушатели
	5. Orgstructure.csv			Оргструктура
	6. Sessions.csv				Сессии
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

$LOG_PATH = $_SERVER['DOCUMENT_ROOT'].'/zlog/import/all/';
$LOG_FILE_NAME_COMMON = 'log_common.csv';

$LOG_FILE_NAME_LEARNING 	= 'log_learningsubjects.csv';
$LOG_FILE_NAME_PROGRAMMS 	= 'log_programms.csv';
$LOG_FILE_NAME_GROUPS 		= 'log_groups.csv';
$LOG_FILE_NAME_USERS 		= 'log_users.csv';
$LOG_FILE_NAME_USERS_DO 	= 'log_users_do.csv';
$LOG_FILE_NAME_ORG 			= 'log_orgstructure.csv';
$LOG_FILE_NAME_SESSIONS 	= 'log_sessions.csv';


$LOG_FILE_LIST = array( //--пути к логам
	$LOG_PATH.'/'.$LOG_FILE_NAME_COMMON,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_LEARNING,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_PROGRAMMS,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_GROUPS,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_ORG,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_SESSIONS,	 	
);

//=============Тело скрипта==========================


if(createPath($LOG_PATH)){ 
	$logFileCom 	= $LOG_PATH.'/'.$LOG_FILE_NAME_COMMON;	
	foreach($LOG_FILE_LIST as $f){ //--удаляем старые логи
		$f = realpath($f);
		if($f){				
			unlink($f);
		}		
	}	
} else {
	echo 'Error create path to log: '.$LOG_PATH."\n";
}

$log = initLog($logFileCom);



$log->log($log->msg['SCRIPT_BEGIN'], $log::INFO);		
echo "\nScript started\n";			

$C = initAllParams();

if(IS_UPLOAD_FROM_FTP){
	$ftp_conn_id = getConnect($FTP_SERVER_NAME, $FTP_LOGIN, $FTP_PASS, false, $log);
	$isGetFtpFiles = getFilesFromFtp($C['FTP_FILE_LIST'], $ftp_conn_id, $log);	
	ftp_close($ftp_conn_id);	
} else {
	$isGetFtpFiles  = true;	
	echo "FTP files not uploaded, because set in config 'IS_UPLOAD_FROM_FTP' = false\n";
	echo "For import used local files in path '".PATH_TO_FILES."'"."\n";
}

$C = initAllParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.

if($isGetFtpFiles){	
	$log->log($log->msg['ALL_FILES_UPLOAD'], $log::INFO);	
	echo "FTP files success upload\n";			
	
	$log->log($log->msg['IMPORT_BEGIN'], $log::INFO);	
	echo "Begin import\n";
	
	$extLogFiles = array(
		'LEARNING_SUBJECTS' => $LOG_PATH.'/'.$LOG_FILE_NAME_LEARNING,
		'PROGRAMMS' 		=> $LOG_PATH.'/'.$LOG_FILE_NAME_PROGRAMMS,		
		'GROUPS' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_GROUPS,		
		'USERS' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_USERS,
		'USERS_DO' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,
		'ORGSTRUCTURE' 		=> $LOG_PATH.'/'.$LOG_FILE_NAME_ORG,		
		'SESSIONS' 			=> $LOG_PATH.'/'.$LOG_FILE_NAME_SESSIONS,
	);
	
	if(importAllFiles($C, $log, $extLogFiles)){ //--импортируем все файлы.		
		$log->log($log->msg['IMPORT_END'], $log::INFO);		
		echo "Import complate\n";					
	} else {		
		$log->log($log->msg['IMPORT_END_ERROR'], $log::ERR);		
		echo "Error. In process to import has been an errors. See in details logs\n";	
		$log->isError = true; //--флаг, по которому определяем, что есть ошибки и надо общий лог отправить на 112		
	}
} else {	
	echo "Error. FTP files not uploaded\n";	
	$log->isError = true;	
}

$C = initAllParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.

foreach($LOG_FILE_LIST as $f){
	convertLogFile($f);
}

if(IS_SEND_REPORT){
	if($log->isError){
		if(sendReport($reportCommonToEmail, '', array(), array($LOG_PATH.'/'.$LOG_FILE_NAME_COMMON))){
			echo "\nReport sended to $reportCommonToEmail (common)\n";		
		} else {
			echo "\nReport not sended $reportCommonToEmail (common)\n";	
		}	
	} else {
		echo "\nReport not sended to $reportCommonToEmail, because no error (common)\n";	
	}
	
	
	if(sendReport($reportToEmail, '', $C['LOCAL_FILE_LIST'], array(
		$LOG_PATH.'/'.$LOG_FILE_NAME_LEARNING,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_PROGRAMMS,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_GROUPS,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_USERS,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_USERS_DO,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_ORG,	 	
		$LOG_PATH.'/'.$LOG_FILE_NAME_SESSIONS,
	))){
		echo "\nReport sended to $reportToEmail\n";	
	} else {	
		echo "\nReport not sended $reportToEmail\n";	
	}	
} else {
	echo "\nReport not sended, because set in config 'IS_SEND_REPORT' = false\n";	
}

echo "\ndone\n";

//=============Скрипт завершен==========================



//====================Функции======================
/**
 * опрелеляем параметры для импорта всех файлов.
*/
function initAllParams(){
	
	$name_learningsubjects 	= 'Learningsubjects.csv';	
	$name_programms			= 'Programms.csv';
	$name_groups 			= 'Groups.csv';
	$name_users 			= 'Users.csv';	
	$name_orgstructure 		= 'Orgstructure.csv';
	$name_sessions 			= 'Sessions.csv';
	$name_1c_log 			= 'log_errors.csv';
	$name_1c_user 			= 'log_1c_users.csv';	
	$name_1c_user_do 		= 'log_1c_users_d.csv';
	
	//--Список файлов для загрузки с FTP.
	$C['FTP_FILE_LIST'] = array(
		$name_learningsubjects,
		$name_programms,
		$name_groups,
		$name_users,
		$name_orgstructure,
		$name_sessions,
		$name_1c_log,
		$name_1c_user,
		$name_1c_user_do,
	);

	
	$C['LOCAL_FILE_LIST'] = array(		
		realpath(PATH_TO_FILES.'/'.$name_learningsubjects),
		realpath(PATH_TO_FILES.'/'.$name_programms),
		realpath(PATH_TO_FILES.'/'.$name_groups),
		realpath(PATH_TO_FILES.'/'.$name_users),
		realpath(PATH_TO_FILES.'/'.$name_orgstructure),
		realpath(PATH_TO_FILES.'/'.$name_sessions),		
		realpath(PATH_TO_FILES.'/'.$name_1c_log),		
		realpath(PATH_TO_FILES.'/'.$name_1c_user),		
		realpath(PATH_TO_FILES.'/'.$name_1c_user_do),		
	);
	
	
	$C['LEARNING_SUBJECTS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_learningsubjects),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_learningsubjects,
		'FILE' 		=> $name_learningsubjects,
	);
	
	$C['PROGRAMMS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_programms),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_programms,
		'FILE' 		=> $name_programms,
	);
	
	$C['GROUPS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_groups),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_groups,
		'FILE' 		=> $name_groups,
	);
	
	$C['USERS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_users),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_users,
		'FILE' 		=> $name_users,
	);
	
	$C['ORGSTRUCTURE'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_orgstructure),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_orgstructure,
		'FILE' 		=> $name_orgstructure,
	);
	
	$C['SESSIONS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_sessions),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_sessions,
		'FILE' 		=> $name_sessions,
	);
	
	
	return $C;
}


?>