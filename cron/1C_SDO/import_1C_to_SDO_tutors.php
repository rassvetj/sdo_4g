#!/usr/bin/env php
<?php
/* Скрипт загружает преподавателей в автоматическом режиме из 1С УОП 1 раз в день */
/*	
	Tutors.csv				Тьюторы	
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


$LOG_PATH = $_SERVER['DOCUMENT_ROOT'].'/zlog/import/tutors/';
$LOG_FILE_NAME_COMMON	= 'log_common.csv';
$LOG_FILE_NAME_TUTORS	= 'log_tutors.csv';

$LOG_FILE_LIST = array( //--пути к логам
	$LOG_PATH.'/'.$LOG_FILE_NAME_COMMON,	 	
	$LOG_PATH.'/'.$LOG_FILE_NAME_TUTORS,	 	
);


//=============Тело скрипта==========================
if(createPath($LOG_PATH)){ 
	$logFile = $LOG_PATH.'/'.$LOG_FILE_NAME_COMMON;
	foreach($LOG_FILE_LIST as $f){ //--удаляем старые логи
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


$C = initTutorParams();

if(IS_UPLOAD_FROM_FTP){
	$ftp_conn_id = getConnect($FTP_SERVER_NAME, $FTP_LOGIN, $FTP_PASS, false, $log);
	$isGetFtpFiles = getFilesFromFtp($C['FTP_FILE_LIST'], $ftp_conn_id, $log);	
	ftp_close($ftp_conn_id);	
} else {
	$isGetFtpFiles  = true;	
	echo "FTP files not uploaded, because set in config 'IS_UPLOAD_FROM_FTP' = false\n";
	echo "For import used local files in path '".PATH_TO_FILES."'"."\n";
}



$C = initTutorParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.





if($isGetFtpFiles){	
	$log->log($log->msg['ALL_FILES_UPLOAD'], $log::INFO);	
	echo "FTP files success upload\n";			
	
	$log->log($log->msg['IMPORT_BEGIN'], $log::INFO);	
	echo "Begin import\n";
		
	$extLogFiles = array(
		'TUTORS' => $LOG_PATH.'/'.$LOG_FILE_NAME_TUTORS,		
	);
		
	if(importTutorFiles($C, $log, $extLogFiles)){ //--импортируем тьюторов			
		echo "Import complate\n";			
	} else {		
		echo "Error. In process to import has been an errors. See in details logs\n";					
		$log->log(sprintf($log->msg['ITEM_IMPORT_ERROR'], 'Tutors'), $log::ERR);
		$log->isError = true; //--флаг, по которому определяем, что есть ошибки и надо общий лог отправить на 112
	}
} else {	
	echo "Error. FTP files not uploaded\n";						
	$log->log(sprintf($log->msg['FILE_UPLOAD_ERROR'], 'Tutors'), $log::ERR);
	$log->isError = true;
}

$log->log(sprintf($log->msg['IMPORT_END'], 'Tutors'), $log::INFO);			


$C = initTutorParams(); //-повторная инициализация, т.к. пути до файлов, которых еще нет будут пустые. Позже можно разделить эту ф-цию на 2. Одна для общих параметров, вторая для файлов импорта.

foreach($LOG_FILE_LIST as $f){
	convertLogFile($f);
}

if(IS_SEND_REPORT){
	if($log->isError){
		if(sendReport($reportCommonToEmail, '', array(), array($LOG_PATH.'/'.$LOG_FILE_NAME_COMMON))){ //--112
			echo "\nReport sended to $reportCommonToEmail(common)\n";		
		} else {
			echo "\nReport not sended to $reportCommonToEmail(common)\n";	
		}	
	} else {
		echo "\nReport not sended to $reportCommonToEmail, because no error (common)\n";	
	}
		
	$body = '';
	foreach($C['FILE_DESCRIPTION'] as $k => $v){
		$body = $body.'<br>'.$k.' - '.$v.'<br>';
	}
	$body 	= $body.'<br>'.$LOG_FILE_NAME_TUTORS.' - содержит информацию о записях, которые были добавлены или обновлены при импорте в СДО.<br>';
		
	if(sendReport($reportToEmailDo, $body, $C['LOCAL_FILE_LIST'], array($LOG_PATH.'/'.$LOG_FILE_NAME_TUTORS))){ //--do
		echo "\nReport sended\n";		
	} else {
		echo "\nReport not sended\n";	
	}	
} else {
	echo "\nReport not sended, because set in config 'IS_SEND_REPORT' = false\n";	
}

echo "\ndone\n";
//=============Скрипт завершен==========================



//====================Функции======================
/**
 * опрелеляем параметры для импорта Тьюторов
 * переписать
*/
function initTutorParams(){	
	$name_tutors 			= 'Tutors.csv';		
	$name_1c_log 			= 'log_1c_tutors.csv';		
	
	//--Список файлов для загрузки с FTP.
	$C['FTP_FILE_LIST'] = array(	
		$name_tutors,	
		$name_1c_log,	
	);	
	
	$C['LOCAL_FILE_LIST'] = array(		
		realpath(PATH_TO_FILES.'/'.$name_tutors),		
		realpath(PATH_TO_FILES.'/'.$name_1c_log),		
	);
	
	$C['FILE_DESCRIPTION'] = array( //--для чего нужны файлы
		$name_tutors => _('файл с данными, который был выгружен из 1С и загружен в СДО'),				
		$name_1c_log => _('содержит все ошибки, которые возникли при выгрузке данных из 1С в файл "'.$name_tutors.'"'),	
	);
	
	$C['TUTORS'] = array(
		'REAL_PATH'	=> realpath(PATH_TO_FILES.'/'.$name_tutors),
		'PATH' 		=> PATH_TO_FILES.'/'.$name_tutors,
		'FILE' 		=> $name_tutors,
	);	
	
	return $C;
}

?>