#!/usr/bin/env php
<?php
/*	Проверяет и назначает назначения студентов на сессии через уч. план
	Условия:
	1. Студент создан путем выгрзки из 1С
	2. Студент не заблокирован
	3. Цепочка проверки такая:
			Студент ->  Группы -> учебный план группы ==> учебный план сессии -> сессия -> нет назначения, значит надо назначить. 
			Если это дисциплина подгруппы, то проверяем, должен ли быть назначен на нее. Если нет, пишем в лог.
.  

	# нужно ли делать огруничение на дату окончания сесси?
	*/
try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/need_subject_user_assign.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты проверки необходимости назначить студента на сессию.';
	$email_messageText 	= 'Результаты проверки необходимости назначить студента на сессию в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
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
			
	
	echo '1: '.(memory_get_usage()/1048576).PHP_EOL;
	$serviceUser		= Zend_Registry::get('serviceContainer')->getService('User');
	$serviceUserGroup	= Zend_Registry::get('serviceContainer')->getService('StudyGroupUsers');
	$serviceStudent		= Zend_Registry::get('serviceContainer')->getService('Student');
	$serviceSubject		= Zend_Registry::get('serviceContainer')->getService('Subject');
	

	
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
		
		$zLog->log($zLog->msg['STUDENT_ASSIGN_ON_SUBJECT_DESCRIPTION'], $zLog::INFO);
		
		$subject_list 	= array(); # информация по сессиям: название, код подгруппы, дата начала, окончани\ , продления, код, код из 1С программы.
		$subgroups_list = array(); # все подгруппы; вида id -> name
				
		$select = $serviceStudent->getSelect();
		$select->from(array('Students_language_list'),	array('code', 'name'));
		$res = $select->query()->fetchAll();
		foreach($res as $i){
			$subgroups_list[$i['code']] = str_replace(array("\r","\n"),"",$i['name']);
		}
		
		foreach($users as $user){
			echo $user['MID'].': '.round(memory_get_usage()/1048576).PHP_EOL;				
			
			#сессии программ групп студента, на которые он должен быть назначен. А ограниение по дате сессии?
			$programm_subjects = array();
			$select = $serviceStudent->getSelect();
			$select->from(array('pe' => 'programm_events'), array(
				'programm_id' 			=> 'pe.programm_id',				
				'subject_id'  			=> 'pe.item_id',				
				'programm_id_external'  => 'p.id_external',				
				'programm_name'  		=> 'p.name',				
			));
			$select->joinInner(array('sgp' => 'study_groups_programms'), 'sgp.programm_id = pe.programm_id', array());
			$select->joinInner(array('sgu' => 'study_groups_users'), 'sgu.group_id = sgp.group_id', array());
			$select->joinInner(array('p'   => 'programm'), 'p.programm_id = pe.programm_id', array());
			
			$select->where(	$serviceStudent->quoteInto('sgu.user_id = ?', $user['MID'])	);
			$select->where(	$serviceStudent->quoteInto('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
			
			$res = $select->query()->fetchAll();			
			unset($select);
			if(empty($res)){ continue; }
			
			foreach($res as $row){
				
				# не трогаем студентов, обучающихся на втором высшем 
				if(
					   ($user['MID']==73778 && $row['programm_id']==3071)
					|| ($user['MID']==74922 && $row['programm_id']==2934)
					|| ($user['MID']==74451 && $row['programm_id']==3082)
					|| ($user['MID']==75832 && $row['programm_id']==3106)
					|| ($user['MID']==74910 && $row['programm_id']==3068)
					|| ($user['MID']==73794 && $row['programm_id']==2994)
					|| ($user['MID']==74658 && $row['programm_id']==2994)
					|| ($user['MID']==74417 && $row['programm_id']==2880)
					|| ($user['MID']==74422 && $row['programm_id']==2880)
					|| ($user['MID']==73797 && $row['programm_id']==2978)
					|| ($user['MID']==74425 && $row['programm_id']==2957)
					|| ($user['MID']==74414 && $row['programm_id']==3005)
					|| ($user['MID']==74256 && $row['programm_id']==2880)					
					|| ($user['MID']==74747 && $row['programm_id']==2880)
					|| ($user['MID']==62608 && $row['programm_id']==2880)
					|| ($user['MID']==74414 && $row['programm_id']==2961)
				){
					continue;
				}
				
				# сессия может быть в нескольких программах
				$programm_subjects[$row['subject_id']][$row['programm_id']] = array(
					'programm_id'	=> $row['programm_id'],
					'id_external' 	=> $row['programm_id_external'],
					'name' 			=> $row['programm_name'],
				); 
			}
			
			# нет сессия на программах студента
			if(empty($programm_subjects)){ continue; }
			
			
			$student_subgroups = array(); # подгруппы студента;			
			
			
			# находим все назначения студента + прошедшие обучение.
			$student_subjects = array();
			
			
			$subUsers = $serviceStudent->getSelect();
			$subUsers->from(array('Students'),	array('CID'));
			$subUsers->where($serviceStudent->quoteInto('MID = ?', $user['MID']));
			$subUsers->where($serviceStudent->quoteInto('CID > ?', 0));
		
			$subGrad = $serviceStudent->getSelect();
			$subGrad->from(array('graduated'),	array('CID'));
			$subGrad->where($serviceStudent->quoteInto('MID = ?', $user['MID']));
			$subGrad->where($serviceStudent->quoteInto('CID > ?', 0));
		
			$select = $serviceStudent->getSelect();
			$select->union(array($subUsers, $subGrad));
			
			$res = $select->query()->fetchAll();
			
			unset($subUsers);
			unset($subGrad);
			unset($subUSelect);
			
			if(!empty($res)){
				foreach($res as $row){
					$student_subjects[$row['CID']] = $row['CID'];
				}				
			}
			
			
			
			# проходимся по сессиям программы и если ее нет с сессиях студента, проверяем, должен ли он быть назначен 
			foreach($programm_subjects as $subject_id => $programms){
				# назначен на сессию, все хорошо.
				if(isset($student_subjects[$subject_id])){ continue; }
				
				$programm_ids 	= array();
				$programm_names = array();
				$programm_codes = array();
				foreach($programms as $programm_id => $row){
					$programm_ids[$row['programm_id']] 		= $row['programm_id'];
					$programm_codes[$row['id_external']] 	= $row['id_external'];
					$programm_names[$row['name']] 			= $row['name'];
				}
				
				
				
				# не назначен, проверяем, может ли быть назначен с учетом подгрупп (ин.яза)				
				if(!isset($subject_list[$subject_id])){
					$subject = $serviceSubject->getById($subject_id);
					$subject_list[$subject_id] = array(
						'id' 				=> $subject_id,
						'external_id' 		=> $subject->external_id,
						'name' 				=> $subject->name,
						'language_code' 	=> $subject->language_code,
						'language_code_name'=> $subgroups_list[$subject->language_code],
						'begin' 			=> $subject->begin,
						'end' 				=> $subject->end,
						'time_ended_debt'	=> $subject->time_ended_debt,
						'time_ended_debt_2'	=> $subject->time_ended_debt_2,
						#'module_code'		=> $subject->module_code,
					);					
				}
				$subject 		= $subject_list[$subject_id];
				$language_code 	= $subject['language_code'];
				$student_subgroups_str = '';
				
				if(!empty($language_code)){
					if(empty($student_subgroups)){
						
						$select = $serviceStudent->getSelect();
						$select->from(array('Students_language'),	array('language_id'));
						$select->where($serviceStudent->quoteInto('mid_external = ?', $user['mid_external']));
						
						$res = $select->query()->fetchAll();
						
						foreach($res as $i){
							$student_subgroups[$i['language_id']] = $subgroups_list[	$i['language_id']	];
						}
					}
					$student_subgroups_str = implode(',', $student_subgroups);					
				}
				
				# программа же общая, поэтому не разделяем на программу студента и программу сессии.				
				if(
					!empty($language_code)
					&&
					!$serviceSubject->isNeedAssign($user['MID'], $language_code)
				){
					# должен быть назначен по программе, но не назначится из-за подгруппы (ин.яз)					
					$zLog->log(sprintf($zLog->msg['NOTICE_STUDENT_ASSIGN_ON_SUBJECT_SUBGROUP'], $user['MID'], $user['mid_external'], $user['fio'],
				
							$subject_id,
							$subject['external_id'],
							$subject['name'],
							implode(',', $programm_ids),
							implode(',', $programm_codes),
							implode(',', $programm_names),
							$subject['begin'],
							$subject['end'],
							$subject['time_ended_debt'],
							$subject['time_ended_debt_2'],
							$subject['language_code_name'],
							$student_subgroups_str
				
					
					), $zLog::INFO);
					
					echo 'Not because subgroup: '.$subject_id.PHP_EOL;
					
				} else { # нужно назначить студента на сессию программы обучения, и указать программу студента
					# assignStudent ничего не возвращает при успехе, поэтому толку от $isAssign нет.
					$serviceSubject->assignStudent($subject_id, $user['MID']);
					$isAssign = true; 
					
					if($isAssign){
						$zLog->log(sprintf($zLog->msg['OK_STUDENT_ASSIGN_ON_SUBJECT'], $user['MID'], $user['mid_external'], $user['fio'],
						
							$subject_id,
							$subject['external_id'],
							$subject['name'],
							implode(',', $programm_ids),
							implode(',', $programm_codes),
							implode(',', $programm_names),
							$subject['begin'],
							$subject['end'],
							$subject['time_ended_debt'],
							$subject['time_ended_debt_2'],
							$subject['language_code_name'],
							$student_subgroups_str
						
						
						), 9);
						
						echo 'Assign on: '.$subject_id.PHP_EOL;
					} else {
						$zLog->log(sprintf($zLog->msg['ERR_STUDENT_ASSIGN_ON_SUBJECT'], $user['MID'], $user['mid_external'], $user['fio'],
							
							$subject_id,
							$subject['external_id'],
							$subject['name'],
							implode(',', $programm_ids),
							implode(',', $programm_codes),
							implode(',', $programm_names),
							$subject['begin'],
							$subject['end'],
							$subject['time_ended_debt'],
							$subject['time_ended_debt_2'],
							$subject['language_code_name'],
							$student_subgroups_str
						
						
						), $zLog::ERR);

						echo 'ERR Assign on: '.$subject_id.PHP_EOL;						
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



