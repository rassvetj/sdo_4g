#!/usr/bin/env php
<?php
/* Перерасчет итоговой оценки за занятие  */
try {	
	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/recalculate_score_for_subject.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты перерасчета итоговой оценоки.';
	$email_messageText 	= 'Результаты перерасчета итоговой оценоки от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		= 'sdo';
	$_FROM_PASSWORD 	= 'z931am008';
    $_FROM_EMAIL        = 'sdo@rgsu.net';    // email отправителя
    $_FROM_NAME         = 'СДО РГСУ';        // Имя отправителя
	
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
	
	$zLog = new Log_cCron();
	$zLog->setPath($_PATH_TO_LOG_FILE);
	$zLog->setDefaultParams();
	
	//$log->setPath($_PATH_TO_LOG_FILE);	
	//$zLog = $log->init();
	
	echo PHP_EOL.'______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['BEGIN'], $zLog::INFO);
	echo 'Start'.PHP_EOL;
	echo '______________________________________________'.PHP_EOL;
	//для каждой сессии, дата окончания или дата продления которой еще не закончилась,
		//для каждого назначенного студента,
			//выполнять перерасчет оценки за курс (аналогичный функционал есть у тьютора в ведомости успеваемости: "выставить оценку за курс" )
			
	$serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');

	$curDate = date('Y-m-d 23:59:59.000',time());

	$select = $serviceSubject->getSelect();
	$select->from(
		array('s' => 'subjects'),
		array(
			'subject_id' 			=> 's.subid',
			'subject_external_id'	=> 's.external_id',			
			'subject_name' 			=> 's.name',
			'user_id' 				=> 'st.MID',
			'user_external_id'		=> 'p.mid_external',
			'LastName' 				=> 'p.LastName',
			'FirstName' 			=> 'p.FirstName',
			'Patronymic' 			=> 'p.Patronymic',
			'mark' 					=> 'm.mark',
		)
	);
	$select->join(
		array('st' => 'Students'),
		'st.CID = s.subid AND st.MID > 0',
		array()
	);
	$select->join(
		array('p' => 'People'),
		'p.MID = st.MID',
		array()
	);
	$select->joinLeft(
		array('m' => 'courses_marks'),
		'm.mid = st.MID AND m.cid = s.subid',
		array()
	);
	$select->where($serviceSubject->quoteInto(
						array('s.time_ended_debt >= ?', ' OR (s.end >= ? AND s.time_ended_debt IS NULL)'),
						array($curDate, $curDate)
					)
	);
	$select->where('m.mark IS NOT NULL'); //--нужно ли обновлять, если нет ошенки?
	$select->where('(m.sync_1c IS NULL OR m.sync_1c = 0)'); // если оценка с 1С синхронизирована повторно не надо пересчитывать балл
	#$select->where('st.MID = ?', 31454); //--временное ограничение.
	#$select->limit(5000);
	echo 'MEM_USE: '.convert(memory_get_usage(true)) . "\n";
	$res = $select->query()->fetchAll();
	unset($select);
	
	if(!count($res)){		
		$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);
		$zLog->log($zLog->msg['END'], $zLog::INFO);
		echo 'Data is empty'.PHP_EOL;		
		exit();
	}
	
	$lessonAssignService = Zend_Registry::get('serviceContainer')->getService('LessonAssign');	
	$serviceMarkBrsStrategy = Zend_Registry::get('serviceContainer')->getService('MarkBrsStrategy');	
	echo 'MEM_USE: '.convert(memory_get_usage(true)) . "\n";
	foreach($res as $key=>$i){		
		$fio = $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'];		
		$total = $serviceMarkBrsStrategy->calcTotalValue($i['subject_id'], $i['user_id'], true);				
        $total = ($total > 100) ? 100 : $total;		
		$serviceMarkBrsStrategy->clearLessonAssignCache();
		
		if(strval($total) == strval($i['mark'])) {
			unset($res[$key]);
			continue;
		} # совпадения не обрабатываем.
		
		$totalScore = $lessonAssignService->onLessonScoreChanged($i['subject_id'], $i['user_id']); # изменение оценки
		#$totalScore = false;
		
		if($totalScore === false){								
			$zLog->log(sprintf($zLog->msg['ERR_SET_SCORE'], $i['user_external_id'], $fio, $i['subject_external_id'], $i['subject_name'], $i['mark'], $total), 3);		
		} else {					
			$zLog->log(sprintf($zLog->msg['OK_SET_SCORE'], $i['user_external_id'], $fio, $i['subject_external_id'], $i['subject_name'], $i['mark'], $totalScore), 9);
		}
		unset($res[$key]);		
	}
	echo 'MEM_USE: '.convert(memory_get_usage(true)) . "\n";
	echo '______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['END'], $zLog::INFO);
	echo 'End. Exit';
} catch (Exception $e) {    
	echo 'Exception: ',  $e->getMessage(), "\n";
	if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }
}



########## SEND REPORT TO EMAIL
$mail = new Mail_cCron(Zend_Registry::get('config')->charset);
$mail->setSubject($email_subject);     
$mail->addTo($email_to);

if(empty($_FROM_LOGIN) || empty($_FROM_PASSWORD)){
	echo 'NOTICE: Login and Password not change. Used from "config.ini"'.PHP_EOL;
	$mail->setFromToDefaultFrom();	
} else {
	$mail->setLogin( $_FROM_LOGIN );
	$mail->setPassword( $_FROM_PASSWORD );
	$mail->changeTransport();
	$mail->setFrom($_FROM_EMAIL, $_FROM_NAME);
}

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

try {				
	$mail->send();			
} catch (Zend_Mail_Exception $e) {                	
	echo 'Exception: '.$e->getMessage().PHP_EOL;	
	if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }	
}
######################



### other functions
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}