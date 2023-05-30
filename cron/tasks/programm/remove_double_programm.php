#!/usr/bin/env php
<?php
/* Удаление программы обучения студента, котора не связана с его группой.  */
try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/remove_double_programm.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты проверки программ обучения студентов.';
	$email_messageText 	= 'Результаты проверки программ обучения студентов в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
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
	$serviceUserProgram	= Zend_Registry::get('serviceContainer')->getService('ProgrammUser');
	

	
	$res_users = $serviceUser->fetchAll(
		$serviceUser->quoteInto('mid_external IS NOT NULL AND role_1c = 1 AND (blocked IS NULL OR blocked != ?)', 1) # студенты активные
		#AND role_1c = 1  AND MID = 5829
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
		
		$zLog->log($zLog->msg['OK_PROGRAMM_REMOVE_DESCRIPTION'], $zLog::INFO);
		foreach($users as $user){
			echo $user['MID'].': '.round(memory_get_usage()/1048576).PHP_EOL;	
			
			# программы студента
			$user_programms = array();
			$select = $serviceUserProgram->getSelect();
			$select->from(array('p' => 'programm'), array(
				'programm_id'			=> 'pu.programm_id',
				'programm_id_external'	=> 'p.id_external',
				'programm_name'			=> 'p.name',
				'user_id'				=> 'pu.user_id',
				'assign_date'			=> 'pu.assign_date',
			));
			$select->joinInner(array('pu' => 'programm_users'), 'p.programm_id = pu.programm_id', array());
			$select->where('pu.user_id = ?', $user['MID']);
			$res = $select->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $i){					
					$user_programms[$i['programm_id']] = array(
						'programm_id'			=> $i['programm_id'],
						'programm_id_external'	=> $i['programm_id_external'],
						'programm_name'			=> $i['programm_name'],
						'user_id'				=> $i['user_id'],
						'assign_date'			=> $i['assign_date'],
					);
				}				
			}
			unset($res);
			if(empty($user_programms)){ continue; }
			
			
			# группы програм, на которые назначен студент
			$programm_groups = array();
			$select = $serviceUserProgram->getSelect();
			$select->from(array('sgp' => 'study_groups_programms'), array(
				'programm_id' 	=> 'sgp.programm_id',
				'group_id' 		=> 'sgp.group_id',
				'group_name'	=> 'g.name',
			));
			$select->joinInner(array('g' => 'study_groups'), 'g.group_id = sgp.group_id', array());
			$select->where(
				$serviceUserProgram->quoteInto('programm_id IN (?)', array_keys($user_programms)) 
			);
			$res = $select->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $i){
					$programm_groups[$i['programm_id']][$i['group_id']] = array(
						'programm_id' 	=> $i['programm_id'],
						'group_id' 		=> $i['group_id'],
						'group_name'	=> $i['group_name'],
					);
				}
			}
			unset($res);
			if(empty($programm_groups)){ continue; }
			
			
			# группы студента
			$res_groups_user 	= $serviceUserGroup->getUserGroups($user['MID']);
			$user_groups 		= array();
			foreach($res_groups_user as $g){				
				$user_groups[$g['group_id']] = $g['name'];
			}			
			
			$toDelete = array();
			foreach($programm_groups as $programm_id => $val){
				$notFountGroup = true; # студент назначена на программу, но не назначен на группу. 
				foreach($val as $group_id => $group_name){
					if(isset($user_groups[$group_id])){ # есть связь: студент-группа-программа-студент. Удалять не надо. 
						$notFountGroup = false;
						break;
					}					
				}
				if($notFountGroup === true){ # студент назначен на программу, но не назначен на любую группу этой программы. Такую запись надо удалить
					$toDelete[$programm_id] = $user_programms[$programm_id];
				}												
			}
			
			if(empty($toDelete)){ continue; }
			
			foreach($toDelete as $programm_id => $val){				
				/*
				$isDeleted =	$serviceUserProgram->deleteBy(
										$serviceUserProgram->quoteInto(
												array('programm_id = ?', ' AND user_id = ?', ' AND assign_date = ?'),
												array($val['programm_id'], $val['user_id'], $val['assign_date'])
										)
								);
				*/								
				$zLog->log(sprintf($zLog->msg['OK_PROGRAMM_REMOVE'], $user['MID'], $user['mid_external'], $user['fio'], $val['programm_id'], $val['programm_id_external'], $val['programm_name'], $val['assign_date']), 9);
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



