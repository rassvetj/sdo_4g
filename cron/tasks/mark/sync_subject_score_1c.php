#!/usr/bin/env php
<?php
/* Синхронизация оценок за сессию с оценками из зачетной книжки.  */
/* # DEPRECATED. */
/* Заменен на sync_subject_score_1c_v2.php */
try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/sync_subject_score_1c.txt';
	
	
	$email_to 			= 'HramovSV@rgsu.net';
	$email_subject 		= 'СДО. Результаты синхронизации оценок.';
	$email_messageText 	= 'Результаты синхронизации оценок из 1С в СДО от '.date('Y-m-d H:i:s',time()).'<br>';
	$_FROM_LOGIN 		= 'sdo';
	$_FROM_PASSWORD 	= 'z931am008';
    $_FROM_EMAIL        = 'sdo@rgsu.net';    // email отправителя
    $_FROM_NAME         = 'СДО РГСУ';        // Имя отправителя


	
	$_PASSED_TYPE = 'зачтено';
	$_PASSED_MIN = 65;
	$_PASSED_MAX = 100;
	
	$score_scale = array(		
		$_PASSED_TYPE 			=> $_PASSED_MAX,
		'отлично' 				=> 100,
		'хорошо' 				=> 84,
		'удовлетворительно' 	=> 74,
		'неудовлетворительно'	=> 64,						
	);	
	
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
			
	$serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');

	

	
	$select = $serviceSubject->getSelect();
	$select->from(
		array('s' => 'subjects'),
		array(
			'subject_id' 			=> 's.subid',
			'subject_external_id' 	=> 's.external_id',
			'subject_name' 			=> 's.name',
			'user_id' 				=> 'st.MID',
			'mark' 					=> 'm.mark',
			'semester' 				=> 'ls.semester',
			'exam_type'				=> 's.exam_type',
		)
	);
	$select->join(
		array('st' => 'Students'),
		'st.CID = s.subid AND st.MID > 0',
		array()
	);
	$select->joinLeft(
		array('ls' => 'learning_subjects'),
		'ls.id_external = s.learning_subject_id_external',
		array()
	);	
	$select->joinLeft(
		array('m' => 'courses_marks'),
		'm.mid = st.MID AND m.cid = s.subid',
		array()
	);
	
	
	$select->where('s.learning_subject_id_external IS NOT NULL'); 
	$select->where('m.sync_1c IS NULL OR m.sync_1c != ?', HM_Subject_Mark_MarkModel::MARK_SYNC_1C); //--записи, которые не синхронизированы ранее.
	$select->group(array('s.subid', 's.external_id', 's.name', 'st.MID', 'm.mark', 'ls.semester', 's.exam_type'));

	$resSubjects = $select->query()->fetchAll();	
	
	if(!count($resSubjects)){ //--IF_1		
		$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);		
		echo 'Data is empty'.PHP_EOL;				
	} else { //--IF_1			
		$cardData = array();
		$userExternalIDs = array();
		# 2 запроса оценок сделано для экономии памяти. Иначе 1.5 милиона записей за раз слишком много кушают.
		$selectCard = $serviceSubject->getSelect(); # отбираем только экзамены
		$selectCard->from(array('study_cards'),
			array(						
				'StudyCode',			
				'Semester',			
				'Disciplina',			
				'Mark',
				'Type',
			)
		);			
		$selectCard->where('StudyCode > 0');		
		$selectCard->where('Type = ?', 'Экзамен');	
		
		$cards = $selectCard->query()->fetchAll();			
		unset($selectCard);
		
		if(count($cards)){
			$iter = new ArrayIterator($cards);
			foreach($iter as $i){
				$cardData[$i['StudyCode']][$i['Semester']][$i['Disciplina']]['mark'] = $i['Mark'];
				$cardData[$i['StudyCode']][$i['Semester']][$i['Disciplina']]['type'] = $i['Type'];
				$userExternalIDs[$i['StudyCode']] = $i['StudyCode'];
			}			
		}		
		unset($cards);			
		unset($iter);
				
		$selectCard = $serviceSubject->getSelect(); # отбираем все оставлееся (кроме экзамена)
		$selectCard->from(
			array('study_cards'),
			array(						
				'StudyCode',			
				'Semester',			
				'Disciplina',			
				'Mark',
				'Type',				
			)
		);			
		$selectCard->where('StudyCode > 0');		
		$selectCard->where('Type != ?', 'Экзамен');		
		$cards = $selectCard->query()->fetchAll();		
		unset($selectCard);		
		if(count($cards)){
			$iter = new ArrayIterator($cards);
			foreach($iter as $i){
				$cardData[$i['StudyCode']][$i['Semester']][$i['Disciplina']]['mark'] = $i['Mark'];
				$cardData[$i['StudyCode']][$i['Semester']][$i['Disciplina']]['type'] = $i['Type'];
				$userExternalIDs[$i['StudyCode']] = $i['StudyCode'];
			}			
		}		
		unset($cards);
		unset($iter);
		
		$selectUsers = $serviceSubject->getSelect(); //------берем студентов
		$selectUsers->from(array('People'), array('MID', 'mid_external', 'LastName', 'FirstName', 'Patronymic' ) );			
		$selectUsers->where($serviceSubject->quoteInto('mid_external IN (?)',$userExternalIDs));	
		$res_users = $selectUsers->query()->fetchAll();	
		
		$usersInfo = array();
		if(count($res_users)){
			$iter = new ArrayIterator($res_users);
			foreach($iter as $i){
				$usersInfo[$i['MID']] = array(
					'mid_external'	=> $i['mid_external'],
					'LastName' 		=> $i['LastName'],
					'FirstName' 	=> $i['FirstName'],
					'Patronymic'	=> $i['Patronymic'],
				);
			}
		}
		
		unset($res_users);
		unset($selectUsers);
		
		if(!count($cardData)){	//--IF_2	
			$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);			
			echo 'Data is empty'.PHP_EOL;					
		} else {			//--IF_2
			
			$subjectMarkService = Zend_Registry::get('serviceContainer')->getService('SubjectMark');
			$subjectService = Zend_Registry::get('serviceContainer')->getService('Subject');
			$count_match = 0;
			$iter = new ArrayIterator($resSubjects);
			foreach($iter as $i){		        		
				$mid_external = $usersInfo[$i['user_id']]['mid_external'];
				
				if(empty($mid_external)){ continue; }
				
				$mark_1c = $cardData[$mid_external][$i['semester']][$i['subject_name']]['mark'];
				$type_1c = $cardData[$mid_external][$i['semester']][$i['subject_name']]['type'];
				
				if(!isset($mark_1c) || empty($type_1c)) { continue; }
					
				if(isExamTypeSame($i['exam_type'], $type_1c, $mark_1c)){
					if($i['mark'] === NULL){
						$mark_sdo = false;					
					} else {
						$mark_sdo = getFiveScaleMark(round($i['mark'])); //--100 бальную шкалу преобразуем в 5 бальную.	Перед этим округляем по правилам математики
					}
					
					
					$fio = $usersInfo[$i['user_id']]['LastName'].' '.$usersInfo[$i['user_id']]['FirstName'].' '.$usersInfo[$i['user_id']]['Patronymic'];		
					$mark_1c = mb_strtolower($mark_1c);					
					$score_1c = $score_scale[ $mark_1c ]; //--100 бальная шкала.						
					if($score_1c > 0){						
						$data = array(						
							'cid' 		=> $i['subject_id'],
							'mid' 		=> $i['user_id'],					
							'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
							'sync_1c' 	=> HM_Subject_Mark_MarkModel::MARK_SYNC_1C,
						);	
						
						$score_total = $i['mark']; //--итоговая оценка, которая будет указана в лог-файле в виде конечной
						$score_total_from_sdo = 'СДО'; # приняли оценку из СДО главной.
						if(
							($mark_1c != $_PASSED_TYPE && getFiveScaleMark(round($score_1c)) != $mark_sdo)//--обновляем в случае несовпадения оценок по 5 бальной шкале.
							||
							($mark_1c == $_PASSED_TYPE && $i['mark'] < $_PASSED_MIN) //--или для типа "зачет", если меньше предельной границы
							){ 						
								$data['mark'] = $score_1c;
								$score_total = $score_1c;
								$score_total_from_sdo = '';# приняли оценку из уч. карточки главной.
						} 
												
						if($i['mark'] === NULL){ //--надо делать INSERT, 							
							$isSetMark = $subjectMarkService->insert($data);										
							#$isSetMark = true;
						} else { //--надо делать UPDATE																	
							#$isSetMark = true;														
							$isSetMark = $subjectMarkService->updateWhere($data, array(
								'cid = ?' => $i['subject_id'],
								'mid = ?' => $i['user_id']
							));														
						}				
						if($isSetMark === false){											
							if($zLog) { $zLog->log(sprintf($zLog->msg['ERR_SCORE_UPDATE'], $mid_external, $fio, $i['subject_external_id'], $i['subject_name'].' ('.$i['semester'].')', $i['mark'], $score_total, $mark_1c, $score_total_from_sdo), $zLog::ERR); } //--не удалось обновить оценку.
						} else {							
							#$isUserPast = true;
							$isUserPast = $subjectService->assignGraduated($i['subject_id'], $i['user_id']); //--переводим в прошедшее.																					
							if($isUserPast === false){						
								if($zLog) { $zLog->log(sprintf($zLog->msg['ERR_SEND_USER_TO_PAST'], $mid_external, $fio, $i['subject_external_id'], $i['subject_name'].' ('.$i['semester'].')'), $zLog::ERR); } //--не удалось перевести	
							}
							if($zLog) { $zLog->log(sprintf($zLog->msg['OK_SCORE_UPDATE'], $mid_external, $fio, $i['subject_external_id'], $i['subject_name'].' ('.$i['semester'].')', $i['mark'], $score_total, $mark_1c, $score_total_from_sdo), 9); } //--успешно обновили оценку.
						}
						$count_match++;				
					}			
				} 
			}
			if($count_match == 0){
				if($zLog) { $zLog->log($zLog->msg['NOT_MATCHES'], $zLog::INFO); } //--нет совпадений. Ничего не изменено.
			}						
		} //--IF_2
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


/**
 *  преобразует из 100 бальной в 5 бальную щкалу
 * @ param int
 * @ return int/boolean
 */
function getFiveScaleMark($mark){
	$mark = intval($mark);
	if($mark > 84){
		return 5;
	} elseif( 84 >= $mark && $mark >= 75 ){
		return 4;
	} elseif( 74 >= $mark && $mark >= 65 ){
		return 3;
	} elseif( 65 > $mark && $mark >= 0 ){
		return 2;
	} 
	return false;
}

/**
 * Определяет форму контроля в зачетке по отношению к форме контроля в сессии и сопоставляет их 
 * @return bool
*/
function isExamTypeSame($examTypeId, $studyCardType, $studyCardMark){	
	if(empty($examTypeId) || empty($studyCardType) || empty($studyCardMark)){ return false; }
	
	if(in_array($studyCardMark, array('Отлично','Удовлетворительно', 'Хорошо'))){
		if($studyCardType == 'Экзамен' && $examTypeId == HM_Subject_SubjectModel::EXAM_TYPE_EXAM){ # экзамен
			return true;
		} elseif($studyCardType == 'Зачет' && $examTypeId == HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK){ # диф. зачет
			return true;
		}
	} elseif($studyCardMark == 'Зачтено' && $studyCardType == 'Зачет' && $examTypeId == HM_Subject_SubjectModel::EXAM_TYPE_TEST) { # зачет
		return true;
	}	
	return false;	
}