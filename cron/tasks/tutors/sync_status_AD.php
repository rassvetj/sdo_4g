#!/usr/bin/env php
<?php
/* Синхронизирует статус пользователя с AD  */

	$CRON_DIR 					= __DIR__.'/../../';
	$init_path 					= $CRON_DIR.'init/_init.php';
	$class_log					= $CRON_DIR.'init/classes/log.php';
	$class_mail					= $CRON_DIR.'init/classes/mail.php';
	$class_ldap					= $CRON_DIR.'init/classes/ldap.php';
	$_PATH_TO_LOG_FILE 			= $CRON_DIR.'logs/log_sync_status_AD.csv';
	$_PATH_TO_ERRORS_LOG_FILE 	= $CRON_DIR.'logs/log_errors_sync_status_AD.csv';
	$_HAS_ERRORS				= false;
	
	if(!file_exists($init_path)){
		echo 'Error: init file not found.';
		die;
	}	
	if(!file_exists($class_log)){
		echo 'Error: log class not found.';
		die;
	}	
	if(!file_exists($class_mail)){
		echo 'Error: mail class not found.';
		die;
	}
	if(!file_exists($class_ldap)){
		echo 'Error: ldap class not found.';
		die;
	}
	
	require_once $init_path;
	require_once $class_log;
	require_once $class_mail;	
	require_once $class_ldap;	
	############################################################################################################
	
	$email_to 			  = '112@rgsu.net';
	$email_to_errors	  = '112@rgsu.net';
	$email_subject 		  = 'СДО. Результаты синхронизации пользователей с AD.';
	$email_subject_errors = 'СДО. Ошибка при синхронизации пользователей с AD.';
	$email_messageText 	  = 'Результаты синхронизации пользователей с AD от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		  = Zend_Registry::get('config')->mailer->params->username; //'sdo';
	$_FROM_PASSWORD 	  = Zend_Registry::get('config')->mailer->params->password; //'z931am008';
	$_FROM_EMAIL 		  = Zend_Registry::get('config')->mailer->default->email;   //'sdo@rgsu.net';	
	$_FROM_NAME 		  = Zend_Registry::get('config')->mailer->default->name;   //'СДО РГСУ';
	$_REASON_GRADUATED	  = 'заблокирован в AD';
	
	$zLog = new Log_cCron();
	$zLog->setPath($_PATH_TO_LOG_FILE);
	$zLog->setDefaultParams();
	
	echo PHP_EOL.'______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['BEGIN'], $zLog::INFO);
	echo 'Start'.PHP_EOL;
	echo '______________________________________________'.PHP_EOL;
	
	
	
	
	########## PREPERE MAIL REPORT
	$mail = new Mail_cCron(Zend_Registry::get('config')->charset);
	
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
	#############################
	
	
	
	
	
	# берем всех активных пользователей с признаком AD
	$serviceUser = Zend_Registry::get('serviceContainer')->getService('User');	
	$select 	 = $serviceUser->getSelect();
	$select->from('People', array(
		'MID',
		'Login',		
		'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' ') , FirstName), ' '), Patronymic)"),
	));
	$select->where('blocked = ?', HM_User_UserModel::STATUS_ACTIVE);
	$select->where('isAD = ?', 1);
	$select->where('role_1c != ?', 1); # исключение студентов
	//$select->where('Login = ?', 'KublanovskajaEM');
	//$select->limit(50);
	$select->group(array('MID','Login','LastName','FirstName','Patronymic'));
	$result = $select->query()->fetchAll();
	
	if(empty($result)){
		$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);					
		$zLog->log($zLog->msg['END'], $zLog::INFO);
		echo 'Data is empty.'.PHP_EOL;
		echo 'Exit.'.PHP_EOL;
		die;
	}
	echo "\n________________\n".'Blocked users:'."\n______________________\n";
	
	
	$configSpecial = array();			
	$config 	   = Zend_Registry::get('config')->ldap->options->toArray();
	$special 	   = Zend_Registry::get('config')->ldap->special->options;	
	$ldap 		   = new Ldap_cCron($config);
	if($special instanceof Zend_Config){		
		$configSpecial = $special->toArray();
	}
	
	foreach($result as $k => $u){
		echo "\n".($k+1).": ";
		$isBlocked = $ldap->isBlocked($u['Login'], $configSpecial);
		if($isBlocked === true){			
			$serviceUser->updateWhere(array('blocked' => HM_User_UserModel::STATUS_BLOCKED, 'block_message' => 'Заблокирован в AD'), array('MID = ?' => $u['MID'], 'Login = ?' => $u['Login']));			
			# добавить логирование
			echo "[BLOCKED]: ".$u['Login']."\n";
			$zLog->log(sprintf($zLog->msg['OK_BLOCKED'], $u['Login'], $u['MID'], $u['fio']), 9);			
		} elseif($isBlocked instanceof Exception && $isBlocked->getCode() != 32) { # 32 - user not found			
			echo "Exception: ".$isBlocked->getMessage()."\n";
			$zLog->log(sprintf($zLog->msg['EXCEPTION'], $isBlocked->getMessage()), 8);
			$zLog->log($zLog->msg['END'], $zLog::INFO);
			
			########## SEND REPORT TO EMAIL
			$mail->addTo($email_to_errors);
			$mail->setSubject($email_subject_errors);
			$mail->createAttachment($_PATH_TO_LOG_FILE);	
			try {				
				$mail->send();			
			} catch (Zend_Mail_Exception $e) {                	
				echo 'Exception: '.$e->getMessage().PHP_EOL;	
				if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }	
			}
			######################			
			die; # Есть ошибка, последующие записи не имеет смысла обрабатывать.
		} else {				
			echo $u['Login']."\n";
		}		
	}
	


	echo '______________________________________________'.PHP_EOL;			
	echo 'End. Exit';
	$zLog->log($zLog->msg['END'], $zLog::INFO);
	
	########## SEND REPORT TO EMAIL
	$mail->addTo($email_to);
	$mail->setSubject($email_subject);
	$mail->createAttachment($_PATH_TO_LOG_FILE);	
	try {				
		$mail->send();			
	} catch (Zend_Mail_Exception $e) {                	
		echo 'Exception: '.$e->getMessage().PHP_EOL;	
		if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }	
	}
	######################
	
	die;
	
	
	
	

