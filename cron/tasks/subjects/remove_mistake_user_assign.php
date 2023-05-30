#!/usr/bin/env php
<?php
/*	Удаление наначений студентов на сессии по условиям:
	  1. Дата окончания сессии не прошла
	  2. Сессия не продлена
	  3. Сессия назанчена на студента через программу обучения, т.е. есть рабочая цепочка связи: студент - сессия - программа обучения
	  3. Программа обучения сессии не совпадает с программой обучения группы студента
	  4. Нет оценки в сессии. Если оценка есть, не удалять, а вывести в лог об этом.
	  5. Все студенты, выгруженные из 1С в т.ч. и заблокированные.
	  6. Сессии, назначыенные не через программу не трогаем.
	  
	  * Если не назначен, то назначить? Если да, то только для активных. Заблокированных отсеять надо.

.  */
try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/remove_mistake_user_assign.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты проверки назначений студентов на сессии.';
	$email_messageText 	= 'Результаты проверки назначений студентов на сессии в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
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
	$serviceUserGroup	= Zend_Registry::get('serviceContainer')->getService('StudyGroupUsers');
	$serviceStudent		= Zend_Registry::get('serviceContainer')->getService('Student');
	#$serviceUserProgram	= Zend_Registry::get('serviceContainer')->getService('ProgrammUser');
	

	
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
		
		$zLog->log($zLog->msg['STUDENT_ASSIGN_REMOVE_DESCRIPTION'], $zLog::INFO);
		foreach($users as $user){
			echo $user['MID'].': '.round(memory_get_usage()/1048576).PHP_EOL;				
			# сессии программы 
			# все назначенные сессии, которыен не продлены и находятся в активных
			# программа обучения сессии.
			# если программа обучения в сессии нет среди программ студента - удаляем ее, если нет балла
			# Если балл есть, пишем в лог.
			
			# программы групп  студента. Это эталонный список, по которому будут сравниваться программы сессий студента
			$user_programms = array();
			$select = $serviceUserGroup->getSelect();
			$select->from(array('sgp' => 'study_groups_programms'), array(
				'programm_id' => 'sgp.programm_id',
				'programm_name' => 'p.name',
			));
			$select->joinInner(array('sgu' => 'study_groups_users'), 'sgu.group_id = sgp.group_id', array());
			$select->joinInner(array('p' => 'programm'), 'p.programm_id = sgp.programm_id', array());
			$select->where(	$serviceUserGroup->quoteInto('sgu.user_id = ?', $user['MID'])	);			
			$res = $select->query()->fetchAll();
			unset($select);
			if(!empty($res)){
				foreach($res as $i){
					$user_programms[ $i['programm_id'] ] = $i['programm_name'];
				}
			}
			unset($res);
			
			# получаем все назначенные сесси студента.
			$user_subjects = array();
			$select = $serviceUserGroup->getSelect();
			$select->from(array('s' => 'subjects'), array(
				'subject_id'				=> 's.subid',
				'subject_name'				=> 's.name',
				'subject_external_id'		=> 's.external_id',
				'programm_id'				=> 'pe.programm_id',
				'programm_name'				=> 'p.name',
				'user_mark'					=> 'cm.mark',
				'user_assign'				=> 'st.Registered',
				'user_time_registered'		=> 'st.time_registered',
				'user_time_ended_planned'	=> 'st.time_ended_planned',
			));
			$select->joinInner(array('st' => 'Students'), 'st.CID = s.subid', array());
			$select->joinInner(array('pe' => 'programm_events'), 'pe.item_id = s.subid', array());
			$select->joinInner(array('p' => 'programm'), 'p.programm_id = pe.programm_id', array());
			$select->joinLeft (array('cm' => 'courses_marks'), 'cm.cid = s.subid AND cm.mid = st.MID', array());
			$select->where($serviceUserGroup->quoteInto('st.MID = ?', $user['MID']));
			$select->where($serviceUserGroup->quoteInto('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
			$select->where($serviceUserGroup->quoteInto('s.end > ?', date('Y-m-d H:i:s')));
			$select->where($serviceUserGroup->quoteInto('(s.time_ended_debt IS NULL       OR s.time_ended_debt      = ?)', ''));
			$select->where($serviceUserGroup->quoteInto('(s.time_ended_debt_2 IS NULL     OR s.time_ended_debt_2    = ?)', ''));
			$select->where($serviceUserGroup->quoteInto('(st.time_ended_debtor IS NULL    OR st.time_ended_debtor   = ?)', ''));
			$select->where($serviceUserGroup->quoteInto('(st.time_ended_debtor_2 IS NULL  OR st.time_ended_debtor_2 = ?)', ''));
			#$select->group(array('s.subid', 'pe.programm_id'));
			#var_dump($select->assemble());
			$res = $select->query()->fetchAll();
			
			unset($select);
			if(empty($res)){ continue; }
			
			# сессия может быть в нескольких программах, поэтому так.
			foreach($res as $i){
				$user_subjects[ $i['subject_id'] ][] = array(					
					'programm_id' 				=> $i['programm_id'],
					'programm_name' 			=> $i['programm_name'],
					'user_mark' 				=> $i['user_mark'],
					'user_assign' 				=> $i['user_assign'],
					'user_time_registered' 		=> $i['user_time_registered'],
					'user_time_ended_planned' 	=> $i['user_time_ended_planned'],
					'subject_id' 				=> $i['subject_id'],
					'subject_external_id' 		=> $i['subject_external_id'],
					'subject_name' 				=> $i['subject_name'],
				); 
			}
			
			if(empty($user_subjects)){ continue; }
			
			foreach($user_subjects as $subject_id => $row){
				$isNeedDelete 	= true; # нужно удалить
				$issetMark 		= false; # нет оценки в сессии
				$info 		= array();
				foreach($row as $i){					
					$info = $i;
					if($issetMark === false && !empty($i['user_mark'])){
						$issetMark = true;
					}
					
					# Есть сессия в программе студента. Удалять не надо.
					if(in_array($i['programm_id'], array_keys($user_programms))){
						$isNeedDelete = false;
						break;
					}					
				}
				
				# назначенная сессия не связана с программой обучения группы, на которой обучается студент.
				if($isNeedDelete === true){					
					if($issetMark === true){
						# пишем в лог ошибку о том, что есть уже оценка и сессия не связана 
						$zLog->log(sprintf($zLog->msg['ERR_STUDENT_HAS_BALL'], $user['MID'], $user['mid_external'], $user['fio'], $info['subject_id'], $info['subject_external_id'], $info['subject_name'], $info['user_assign'], $info['user_time_registered'], $info['user_time_ended_planned'], $info['user_mark'], implode(', ',$user_programms), $info['programm_name']), $zLog::ERR);
					} else {
						#удаляем из Students						
						$isDelete = $serviceStudent->deleteBy(
										$serviceStudent->quoteInto(
												array('MID = ?', ' AND CID = ?', ' AND Registered = ?'),
												array($user['MID'], $info['subject_id'], $info['user_assign'])
										)
						);
						$zLog->log(sprintf($zLog->msg['OK_STUDENT_ASSIGN_REMOVE'], $user['MID'], $user['mid_external'], $user['fio'], $info['subject_id'], $info['subject_external_id'], $info['subject_name'], $info['user_assign'], $info['user_time_registered'], $info['user_time_ended_planned'], $info['user_mark'], implode(', ',$user_programms), $info['programm_name']), 9);						
					}
				}
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



