#!/usr/bin/env php
<?php
/*	
	Пересчитывает итоговую оценку по текущим баллам за занятия и сравнивает ее с итоговой за сессию
	Если не сомпадают, обновляет.
	Все сессии, т.к. если синхронизированны с 1С, значит студент в прошедшем
	только активные студенты и только те, которые назначены на сессию, НЕ в прошедшем.
	Прошедшие сессии не берем
	Если сессия продлена, то только с актуальной датой
.  */
try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/recalculate_total_ball_on_lessons_ball.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Синхронизация балов за занятия с итоговым баллом за сессию.';
	$email_messageText 	= 'Результаты синхронизации балов за занятия с итоговым баллом за сессию в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		= 'sdo';
	$_FROM_PASSWORD 	= 'z931am008';
    $_FROM_EMAIL        = 'sdo@rgsu.net';    // email отправителя
    $_FROM_NAME         = 'СДО РГСУ';        // Имя отправителя


	
	$curDate = date('Y-m-d 23:59:59.000',time());

	
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
	
	echo PHP_EOL.'______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['BEGIN'], $zLog::INFO);
	echo 'Start'.PHP_EOL;
	echo '______________________________________________'.PHP_EOL;
			
	#$serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');
	echo '1: '.(memory_get_usage()/1048576).PHP_EOL;
	$serviceUser		= Zend_Registry::get('serviceContainer')->getService('User');
	#$serviceUserGroup	= Zend_Registry::get('serviceContainer')->getService('StudyGroupUsers');
	$serviceStudent		= Zend_Registry::get('serviceContainer')->getService('Student');
	#$serviceUserProgram	= Zend_Registry::get('serviceContainer')->getService('ProgrammUser');
	$serviceSubject		= Zend_Registry::get('serviceContainer')->getService('Subject');
	$serviceMarkFactory	= Zend_Registry::get('serviceContainer')->getService('MarkStrategyFactory');
	$serviceSubjectMark	= Zend_Registry::get('serviceContainer')->getService('SubjectMark');
	#$serviceJournalResult = Zend_Registry::get('serviceContainer')->getService('LessonJournalResult');
	 

	
	$res_users = $serviceUser->fetchAll(
		$serviceUser->quoteInto('mid_external IS NOT NULL AND role_1c = 1 AND (blocked IS NULL OR blocked != ?)', 1) # студенты активные, созданные из импортом из 1С
		#AND role_1c = 1  AND MID = 5829 AND role_1c = 1
	);
	echo '2: '.(memory_get_usage()/1048576).PHP_EOL;
	$users = array();
	foreach($res_users as $user){
		$users[$user->MID] = array(
			'MID' 			=> $user->MID,
			'mid_external'	=> $user->mid_external,
			'fio' 			=> $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,				
		);			
	}
	echo '3: '.(memory_get_usage()/1048576).PHP_EOL;
	unset($res_users);
	echo '4: '.(memory_get_usage()/1048576).PHP_EOL;
	#var_dump($users[5829]);
	
	
	#die;

	
	
	

	if(!count($users)){ //--IF_1		
		die;
		$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);		
		echo 'Data is empty'.PHP_EOL;
		
	} else { //--IF_1			
		#####
		
		$zLog->log($zLog->msg['RECALCULATE_MARK_ON_LESSONS_DESCRIPTION'], $zLog::INFO);
		foreach($users as $user){
			echo $user['MID'].': '.round(memory_get_usage()/1048576).PHP_EOL;				
			# Берем все итоговые оценки студента, и при этомон долже быть назначен на сессию, не в прошедшем.
			#Для каждой записи находим баллы баллы за сессию и баллы за уроки сессии. Если не совпадают, обновляем.
			
			$user_total_balls = array(); # итоговый балл за сессию
			$select = $serviceStudent->getSelect();
			$select->from(array('s' => 'Students'), array(
				'subject_id' 	=> 'cm.cid',
				'mark' 			=> 'cm.mark',
				'mark_current' 	=> 'cm.mark_current',
				'mark_landmark' => 'cm.mark_landmark',				
			));
			$select->joinInner(array('cm' => 'courses_marks'), 'cm.mid = s.MID AND cm.cid = s.CID', array());
			$select->where(	$serviceStudent->quoteInto('s.MID = ?',  $user['MID'])	);	
			$select->where(	$serviceStudent->quoteInto('cm.mid = ?', $user['MID'])	);	
					
			$res = $select->query()->fetchAll();
			unset($select);
			if(!empty($res)){
				foreach($res as $i){
					$user_total_balls[ $i['subject_id'] ] = array(
						'mark'			=> $i['mark'],
						'mark_current'	=> $i['mark_current'],
						'mark_landmark'	=> $i['mark_landmark'],
					);
				}
			}
			unset($res);
			
			if(empty($user_total_balls)){ continue; }
			foreach($user_total_balls as $subject_id => $row){
				
				$subject = $serviceSubject->find($subject_id)->current();
				
				if( strtotime($subject->end) < time() && empty($subject->time_ended_debt)              && empty($subject->time_ended_debt_2) )				{ continue; } # не продлена				
				if( strtotime($subject->end) < time() && strtotime($subject->time_ended_debt) < time() && empty($subject->time_ended_debt_2) )				{ continue; } # первое продление				
				if( strtotime($subject->end) < time() && strtotime($subject->time_ended_debt) < time() && strtotime($subject->time_ended_debt_2) < time() )	{ continue; } # второе продление
				
				
				$factory = $serviceMarkFactory->getStrategy($subject->getMarkType());
				$total 	 = $factory->calcTotalValue($subject_id, $user['MID'], true);
				
				
				if( method_exists($factory, 'clearLessonAssignCache') ){
					$factory->clearLessonAssignCache();
				}

				
				$total	 = ($total > 100) ? 100 : $total;
				
				
				
				
				if($row['mark'] == strval($total)){ continue; }
				
				
				$serviceSubjectMark->updateWhere(array('mark' => $total), array(
					'cid = ?' => $subject_id,
					'mid = ?' => $user['MID'],
				));
				
				echo "\t-> ".$subject_id.': '.$row['mark'].' => '.$total."\n";
				
				$zLog->log(sprintf($zLog->msg['OK_RECALCULATE_MARK_ON_LESSONS'], $user['MID'], $user['mid_external'], $user['fio'], $subject_id, $subject->external_id, $subject->name, $row['mark'], $total, $row['mark_current'], $row['mark_landmark'], $subject->begin, $subject->end, $subject->time_ended_debt, $subject->time_ended_debt_2  ), 9);	
				#break;
			}
			
			
		}
		echo '6: '.(memory_get_usage()/1048576).PHP_EOL;		
		#####
	} //--IF_1
	echo '______________________________________________'.PHP_EOL;			
	echo 'End. Exit';
	$zLog->log($zLog->msg['END'], $zLog::INFO);
} catch (Exception $e) {    
	echo 'Exception: ',  $e->getMessage(), "\n";
	if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }
}

//-----SEND REPORT TO EMAIL
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



