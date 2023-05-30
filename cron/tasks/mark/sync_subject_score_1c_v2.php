#!/usr/bin/env php
<?php
/*
	* Пересчет оценок по урокам делать не надо, т.к. оценка уже есть в 1С и в случае чего мы обновляем на нее.
	* И что уже в СДО - не выжно.
	
	Синхронизация оценок за сессию с оценками из зачетной книжки. Исправленный вариант с учетом модульных дисциплин
	Алгоритм работы:
	1. Если сессия не модульная, то сопоставление выполняется по названию, семестру и форме контроля сессии.
	2. Если сессия модульная, тогда поиск сессий происходит по вхождению "кода предмета" из зачетной книжки в коде модулльной дисциплины (с 6 соимолв 6 символов).
	3. Если сессия модульная, тогда анализ оценки происходит только для главного модуля. Все остальные дисциплины этого модуля переводятся в прошедшее "как есть", без изменения балла.
	4. Анализ оценки и необходимость изменения выполняются как и раньше: берется бал из зачетной книжки и балл из СДО переводятся в 5-шкалу. Если оценки разные, обновляется балл в СДО на максимальный балл оценки из зачетной книжки соотв. диапазона по 5-шкале.
	5. Т.к. данные в 1С могут попасть вручную, минуя стандартный алгоритм обратной синхронизации, необходимо игнорировать признак "Данные обработаны 1С".
*/

class syncRecordBook {
	
	const CREDIT_PASSED_MIN = 65; # минимальный проходной балл для зачета
	
	private $_log 		= NULL;	# Экземпляр лога
	private $_userInfo 	= array(); # накопительный массив по данным пользователя
	private $_recordBookInfo	= array(); # кэш данных зачетной книжки одного студента. Если нет совпадения по ключу, обнуляем и получаем новые данные другого студента
	private $_moduleFlags		= array(); # кэш модульных сессий: key => subject_id, value => bool, true if main module
	
	private $_studentModules	= array(); # кэш модульных сессий студента вида user_id => module_code => array of subjects params (subject_id => values)
	
	private $_serviceSubject 	=  NULL; 
	private $_serviceUser		=  NULL;
	private $_serviceMark		=  NULL;
	
	private $_isFakeRun         = false; # выполнение скрипта без внесения изменений в БД
	
	public function setFakeRun()
	{
		$this->_isFakeRun = true;
	}

	
	public static function getScoreScale(){
		return array(		
			'зачтено'	 			=> 100,
			'отлично' 				=> 100,
			'хорошо' 				=> 84,
			'удовлетворительно' 	=> 74,
			'неудовлетворительно'	=> 64,						
		);	
	}
	
	public function getScoreByMarkName($mark_name){
		$score_list = self::getScoreScale();
		return $score_list[mb_strtolower($mark_name)];
	}
	
	
	/**
	 * Сам процесс синхронизации с обновлением данных
	*/
	public function sync(){
		$zLog = $this->_log;
		
		$data = $this->getUserProgress();
		
		if(empty($data)){
			$zLog->log($zLog->msg['DATA_IS_EMPTY'], $zLog::INFO);
			echo 'Data is empty'.PHP_EOL;
			return false;
		}
		#var_dump($data);
		foreach($data as $i){
			
			$mid_external = $this->getMidExternal($i['user_id']);			
			if(empty($mid_external)){ continue; }
			
			$record_book = $this->getUserRecordBookInfo($mid_external);
			
			if(empty($record_book)){ continue; }
			
				
			if($i['module_code']){
				# Второстепенные модули не обрабатываем
				
				
				if(!$this->isMainModule($i['subject_id'])){
					continue;
				}
				
				#echo 'main - '.$i['subject_id'].PHP_EOL;
				$subject_discipline_code 	= $this->getDisciplineCodeFromModuleCode($i['module_code']);
				
				
				if(empty($subject_discipline_code)){ continue; }
				$i['discipline_code'] 		= $subject_discipline_code;
				
				$finded_record_book 		= $this->findSubjectBase($i, $record_book, true);
				
				#var_dump($i['subject_id'], $subject_discipline_code, $finded_record_book);
				
				if(empty($finded_record_book)){ continue; }
				
				$new_ball 	= $this->getNewBall($i['mark'], $finded_record_book['Mark']);
				
				$additional = array(
					'subject_external_id' 	=> $i['subject_external_id'],
					'subject_name' 			=> $i['subject_name'],
					'semester' 				=> $i['semester'],
					'mark_text'				=> $finded_record_book['Mark'],
					
					'module_isMain'			=> true,
					'module_code'			=> $i['module_code'],
					'discipline_code'		=> $subject_discipline_code,
					'recordbook_subject_name'=> $finded_record_book['Disciplina'],
				);
				
				# Перевод в завершенные обучение
				$this->graduatedSubject($i['user_id'], $i['subject_id'], $i['mark'], $new_ball, $additional);
				
				# Среди сессий студента находим все модульные по коду главного модуля, кроме самого главного модуля. Но вроде и главный попадает
				$user_modules = $this->findModuleSubjects($i['user_id'], $i['subject_id'], $i['module_code'], $data);
				
				if(empty($user_modules)){ continue; }
				
				$student = $this->getUserInfo($i['user_id']);
				
				foreach($user_modules as $subject_id => $subject){
					if($i['subject_external_id'] == $subject['subject_external_id']){ continue; }
					
					# Если нашелся второй главный модуль и семестр не совпал - завершать его не надо.
					if($this->isMainModule($subject_id) && $subject['semester'] != $i['semester']){
						$msg = 'Дублирование главного модуля;'.$i['subject_external_id'].';'.$i['subject_name'].';'.$i['semester'].';и главного модуля;'.$subject['subject_external_id'].';'.$subject['subject_name'].';'.$subject['semester'].';Студент;'.$student['mid_external'].';'.$student['fio'].';';
						$zLog->log($msg, $zLog::ERR);
						continue;
					} else {
						# Второй главный модуль завершаем в том случае, если у него совпал семестр первого главного модуля. Если это произошло, значит неверно были назвыны занятия в СДО второго главного модуля и он должен быть доп. модулем.
						
						# Переводим в прошедшее без изменения балла ( = false)						
						$additional = array(
							'subject_external_id' 	=> $subject['subject_external_id'],
							'subject_name' 			=> $subject['subject_name'],
							'semester' 				=> $subject['semester'],
							'mark_text'				=> '',
							
							'module_isSlave'		=> true,
							'module_code'			=> $subject['module_code'],
							'discipline_code'		=> $subject_discipline_code,							
						);						
						$this->graduatedSubject($subject['user_id'], $subject['subject_id'], $subject['mark'], false, $additional);
					}					
				}
				
			} else {
				
				$finded_record_book = $this->findSubjectBase($i, $record_book);
				
				if(empty($finded_record_book)){ continue; }
				
				$new_ball 	= $this->getNewBall($i['mark'], $finded_record_book['Mark']);
				
				$additional = array(
					'subject_external_id' 	=> $i['subject_external_id'],
					'subject_name' 			=> $i['subject_name'],
					'semester' 				=> $i['semester'],
					'mark_text'				=> $finded_record_book['Mark'],
				);
				
				# Перевод в завершенные обучение
				$this->graduatedSubject($i['user_id'], $i['subject_id'], $i['mark'], $new_ball, $additional);				
			}			
		}		
		return true;
	}
	
	/**
	 * Не оптимальное решение, т.к. постоянно прогоняет весь набор данных по всем студентам.
	 * Находит среди всей коллекции даннных сессии поеределнного стдуента определенного модуля
	*/
	public function findModuleSubjects($user_id, $subject_id, $module_code, $all_data){
		if(empty($this->_studentModules[$user_id])){
			$data = array();
			foreach($all_data as $i){
				if($i['subject_id'] 	== $subject_id)	{ continue; } # сессию родителя (если все ок, то должна быть главным модулем) не учитываем
				if($i['user_id'] 		!= $user_id)	{ continue; } # не нужный студент
				if(empty($i['module_code']))			{ continue; } # не модуль
				
				$data[$i['module_code']][$i['subject_id']] = $i;
			}
			$this->_studentModules[$user_id] = $data;
		}
		return $this->_studentModules[$user_id][$module_code];		
	}
	
	/**
	 * Из поля кода модуля получает код дисциплины с 6 символа 6 символов
	 * Нужно для поиска модульных сессий в зачетной книжке.
	*/
	public function getDisciplineCodeFromModuleCode($module_code){
		if(empty($module_code)){ return false; }
		return substr($module_code, 5, 6);
	}
	
	
	
	/**
	 * Обновлене итоговой оценки за сессию + перевод в прошедшее
	 * $new_mark = false - изменение балла не требуется.
	 * 
	*/
	protected function graduatedSubject($student_id, $subject_id, $old_mark, $new_mark, $additional = array()){
		$data = array(						
			'cid' 		=> $subject_id,
			'mid' 		=> $student_id,					
			'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
			'sync_1c' 	=> HM_Subject_Mark_MarkModel::MARK_SYNC_1C,
			'mark'		=> $new_mark,
		);
		$isSetMark 	= false;
		$zLog 		= $this->_log;
		
		# Что-то пошло не так.
		if($new_mark === NULL){
			$msg  = 'Некорректный новый балл;'.$student['mid_external'].';'.$student['fio'].';'.$additional['subject_external_id'].';'.$additional['subject_name'].';'.$additional['semester'].';'.$old_mark.';'.$new_mark.';'.$additional['mark_text'];			
			$zLog->log($msg, $zLog::ERR);
			return false;
		}
		
		
		if(!$this->_serviceMark)	{ $this->_serviceMark 	 = Zend_Registry::get('serviceContainer')->getService('SubjectMark'); }
		if(!$this->_serviceSubject) { $this->_serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject'); }
		
		# 1. Обновление оценки за сессию		
		if($old_mark === NULL && $new_mark !== false){
			# создаем новую запись с оценкой из 1С.
			if($this->_isFakeRun){
				$isSetMark = true;	
			} else{
				$isSetMark = $this->_serviceMark->insert($data);	
			}
		} elseif($new_mark === false) {		
			# не надо менять оценку. Остается та, что в СДО как есть.
		
		} elseif($old_mark == $new_mark){
			# не надо менять оценку. Остается та, что в СДО
			
		} else {
			# обновляем оценку на из 1С			
			if($this->_isFakeRun){
				$isSetMark = true;
			} else{
				$isSetMark = $this->_serviceMark->updateWhere($data, array(
					'cid = ?' => $subject_id,
					'mid = ?' => $student_id,
				));	
			}
						
		}
		
		# 2. Перевод в прошедшее штатным сопобом.
		if($this->_isFakeRun){
			$isUserPast = true;
		} else{
			$isUserPast = $this->_serviceSubject->assignGraduated($subject_id, $student_id);		
		}
		
		$student = $this->getUserInfo($student_id);
		$msg  = $student['mid_external'].';'.$student['fio'].';'.$additional['subject_external_id'].';'.$additional['subject_name'].';'.$additional['semester'].';'.$old_mark.';'.$new_mark.';'.$additional['mark_text'];
		$msg .= $isSetMark  ? ';1С (изменена)' : ';СДО (не изменилась)';
		$msg .= $isUserPast ? ';курс завершен' : ';не удалось завершить курс';
		
		if($additional['module_isMain'])			{ $msg .= ';Главный модуль'; 							}
		if($additional['module_isSlave'])			{ $msg .= ';Доп. модуль'; 								}		
		if($additional['module_code'])				{ $msg .= ';'.$additional['module_code']; 				}
		if($additional['discipline_code'])			{ $msg .= ';'.$additional['discipline_code']; 			}
		if($additional['recordbook_subject_name'])	{ $msg .= ';'.$additional['recordbook_subject_name'];	}
		
		$msg_type = $isUserPast ? $zLog::INFO : $zLog::ERR;
		$zLog->log($msg, $msg_type);

		return true;
	}
	
	
	/**
	 * определяем новый балл для обновления в СДО.	 
	 * @return float/false
	 * false - изменение балла не требуется.
	*/
	public function getNewBall($sdo_ball, $record_book_mark){
		$record_book_mark = mb_strtolower($record_book_mark);
		$record_book_ball = $this->getScoreByMarkName($record_book_mark);
		
		# Зачет. Не пройден минимальный порог по баллам в СДО
		if($record_book_mark == 'зачтено' && $sdo_ball < self::CREDIT_PASSED_MIN){
			return 	$record_book_ball;		
		}
		
		# Экзамен. Несовпадение по 5-шкале.
		$sdo_5_mark 			= $this->getFiveScaleMark($sdo_ball); # 100-шкала => 5-шкала
		$record_book_5_mark 	= $this->getFiveScaleMark($record_book_ball); # 100-шкала => 5-шкала
		
		if($record_book_mark != 'зачтено' &&  $sdo_5_mark != $record_book_5_mark){
			return $record_book_ball;
		}
		return false;
	}
	
	#round($sdo_ball)
	
	/**
	 * Среди данных зачетной книжки ищет нужную сессию для перевода в прошедшее.
	 * Поиск по совпадению по семестру, форму контроля, и названию
	 * Если модульнаф дисциплина, то вместо сопоставления по имени сопоставляем по коду дисциплины
	 * @return array, record_book row
	*/
	public function findSubjectBase($data_subject, $data_record_book, $isModule = false)
	{
		if(empty($data_subject) || empty($data_record_book)){ return false; }
		#$zLog = $this->_log;
		
		$data_subject_fotm_control = $this->getFormControlName($data_subject['exam_type']);
		
		if(empty($data_subject_fotm_control)){
			#$zLog->log('Не определена форма контроля в сессии #'.$data_subject['subject_id'], $zLog::ERR);
			return false;
		}
		
		foreach($data_record_book as $i){
			
			if($i['Vid'] == 'Курсовая'){ continue; } # ситуация с курсовыми не определена. Пока их игнорируем.
		
			if($isModule){
				if($data_subject['discipline_code']	!= $i['DisciplineCode']){ continue; }			
			} else {			
				if($data_subject['subject_name']	!= $i['Disciplina']){ continue; }
			}
			if($data_subject['semester'] 		!= $i['Semester'])	{ continue; }
			
			# инфа по невалидным данным уходит в лог.
			if(!$this->isCorrectData($i)){ continue; }
			
			if($data_subject_fotm_control != $i['Type'])	{ continue; }
			
			return $i;
		}				
		return false;
	}
	
	/**
	 * Преобразует числовое представление формы контроля в тесстовое, как в зачетной книжке.
	 * Диф. зачет в СДО приравнивается к зачету в зачетке из 1С.
	*/
	public function getFormControlName($form_id){
		$forms = array(			
            HM_Subject_SubjectModel::EXAM_TYPE_EXAM   		=> _('Экзамен'),
            HM_Subject_SubjectModel::EXAM_TYPE_TEST   		=> _('Зачет'),            
            HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK   	=> _('Диф. зачет'), # диф. зачет
		);
		return $forms[$form_id];
	}
	
	/**
	 * Проверка корректности данных.
	 * В ч.с. соответствие формы контроля и оценки (5-шкала)
	*/
	public function isCorrectData($row_record_book){
		
		$zLog = $this->_log;
		
		if($row_record_book['Type'] == 'Экзамен' && $row_record_book['Mark'] == 'Зачтено'){
			$zLog->log('Некорректные данные в документе:;#'.$row_record_book['DocNum'].';'.$row_record_book['Disciplina'].';тип "'.$row_record_book['Type'].'", оценка "'.$row_record_book['Mark'].'"', $zLog::ERR);
			return false;
		}
		
		return true;
	}
	
	public function getMidExternal($user_id){
		$user_info = $this->getUserInfo($user_id);
		return $user_info['mid_external'];
	}
	
	public function getUserInfo($user_id){
		if(isset($this->_userInfo[$user_id])){ return $this->_userInfo[$user_id]; }
		
		if(!$this->_serviceUser){ $this->_serviceUser = Zend_Registry::get('serviceContainer')->getService('User'); }
		
		$select = $this->_serviceUser->getSelect(); //------берем студентов
		$select->from(array('People'), array('mid_external', 'LastName', 'FirstName', 'Patronymic' ) );			
		$select->where($this->_serviceUser->quoteInto('MID = ?', $user_id));	
		$res = $select->query()->fetchObject();
		$this->_userInfo[$user_id] = array(
			'mid_external'	=> $res->mid_external,
			'fio'			=> $res->LastName.' '.$res->FirstName.' '.$res->Patronymic,
		);
		return $this->_userInfo[$user_id];
	}
	
	
	/**
	 * получаем зачетную книжку студента
	*/
	private function getUserRecordBookInfo($mid_external){
		if(empty($mid_external)){ return false; }
		
		if(isset($this->_recordBookInfo[$mid_external])){
			#echo 'c-'.$mid_external.PHP_EOL;
			return $this->_recordBookInfo[$mid_external];
		} else {			
			$this->_recordBookInfo = array();
		}
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject'); }
		
		$select = $this->_serviceSubject->getSelect(); # отбираем только экзамены
		$select->from(array('study_cards'),
			array(
				'DocNum',
				'StudyCode',			
				'Semester',			
				'Disciplina',			
				'Mark',
				'Type' => new Zend_Db_Expr("CASE WHEN Type='Зачет' AND Mark != 'Зачтено' THEN 'Диф. зачет' ELSE Type END"),
				'DisciplineCode',
				'Vid',
			)
		);
		#echo 'db-'.$mid_external.PHP_EOL;
		$select->where($this->_serviceSubject->quoteInto('StudyCode = ?', $mid_external));
		
		$this->_recordBookInfo[$mid_external] = $select->query()->fetchAll();		
		return $this->_recordBookInfo[$mid_external];
	}
	
	public function initLog($path_to_file){
		$this->_log = new Log_cCron();
		$this->_log->setPath($path_to_file);
		$this->_log->setDefaultParams();
		return $this->_log;
	}
	
	
	/**
	 * Получаем набор данных по оценкам, сессиям студентов.
	 * Сортировка по user_id для лучшей  работы кэша данных зачетной книжки.
	*/
	public function getUserProgress(){		
		if(!$this->_serviceSubject){ $this->_serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject'); }
	
		$select = $this->_serviceSubject->getSelect();
		$select->from(
			array('s' => 'subjects'),
			array(
				'subject_id' 					=> 's.subid',
				'subject_external_id' 			=> 's.external_id',
				'subject_name' 					=> 's.name',
				'user_id' 						=> 'st.MID',
				'mark' 							=> 'm.mark',
				'semester' 						=> 's.semester', # теперь семестр берется не из цч. предмета, а из сессии.
				'exam_type'						=> 's.exam_type',
				'module_code'					=> 's.module_code',				
			)
		);
		$select->join(
			array('st' => 'Students'),
			'st.CID = s.subid AND st.MID > 0',
			array()
		);
		/*
		$select->joinLeft(
			array('ls' => 'learning_subjects'),
			'ls.id_external = s.learning_subject_id_external',
			array()
		);	
		*/
		$select->joinLeft(
			array('m' => 'courses_marks'),
			'm.mid = st.MID AND m.cid = s.subid',
			array()
		);
		$select->order('st.MID');
		
		$select->where('s.learning_subject_id_external IS NOT NULL');
		#$select->where('st.MID = ?', 24181); # на время теста
		#$select->where('st.MID = ?', 29295); # на время теста
		#$select->where('st.MID = ?', 5829); # на время теста
				
		#$select->where('m.sync_1c IS NULL OR m.sync_1c != ?', HM_Subject_Mark_MarkModel::MARK_SYNC_1C); //--записи, которые не синхронизированы ранее.
		$select->group(array('s.subid', 's.external_id', 's.name', 'st.MID', 'm.mark', 's.semester', 's.exam_type', 's.module_code', 's.learning_subject_id_external'));

		return $select->query()->fetchAll();	
	}
	
	/**
	 * Определяет, главный ли модуль или нет
	*/
	public function isMainModule($subject_id){
		if(empty($subject_id)){ return false; }
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject'); }
		
		if(isset($this->_moduleFlags[$subject_id])){
			return (bool)$this->_moduleFlags[$subject_id];
		}
		
		if($this->_serviceSubject->isMainModule($subject_id)){
			$this->_moduleFlags[$subject_id] = true;
		} else {
			$this->_moduleFlags[$subject_id] = false;
		}
		return $this->_moduleFlags[$subject_id];
	}
	
	
	/**
	 *  преобразует из 100 бальной в 5 бальную щкалу
	 * @ param int
	 * @ return int/boolean
	 */
	public function getFiveScaleMark($mark){
		$mark = round($mark);
		$mark = intval($mark);
		if($mark > 84)						{ return 5; }
		elseif( 84 >= $mark && $mark >= 75 ){ return 4; }
		elseif( 74 >= $mark && $mark >= 65 ){ return 3; }
		elseif( 65 >  $mark && $mark >= 0  ){ return 2; } 
		return false;
	}
	
}


try {	
	ini_set('memory_limit', '2548M');

	$CRON_DIR 			= __DIR__.'/../../';
	$init_path 			= $CRON_DIR.'init/_init.php';
	$log_class			= $CRON_DIR.'init/classes/log.php';
	$mail_class			= $CRON_DIR.'init/classes/mail.php';
	$_PATH_TO_LOG_FILE 	= $CRON_DIR.'logs/sync_subject_score_1c_v2.txt';
	
	
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
		
	$rb		= new syncRecordBook();
	#$rb->setFakeRun();
	$zLog	= $rb->initLog($_PATH_TO_LOG_FILE);
	
	echo PHP_EOL.'______________________________________________'.PHP_EOL;
	$zLog->log($zLog->msg['BEGIN'], $zLog::INFO);
	echo 'Start'.PHP_EOL;
	echo '______________________________________________'.PHP_EOL;
			
	$rb->sync();
	
	$zLog->log($zLog->msg['END'], $zLog::INFO);
	
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
		$content 	= file_get_contents($_PATH_TO_LOG_FILE); 
		$finfo 		= new finfo(FILEINFO_MIME_TYPE);
		$mime_type 	= $finfo->buffer($content);
		$path_info 	= pathinfo($_PATH_TO_LOG_FILE);				
		$file_name 	= $path_info['basename'];

		$attachment 				= new Zend_Mime_Part($content);					
		$attachment->type 			= $mime_type;
		$attachment->disposition 	= Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding 		= Zend_Mime::ENCODING_BASE64;						
		$attachment->filename 		= $file_name;
		$mail->addAttachment($attachment);	
	}
	$mail->send();
	
	
} catch (Exception $e) {    
	echo 'Exception: ',  $e->getMessage(), "\n";
	if($zLog) { $zLog->log(sprintf($zLog->msg['EXCEPTION'], $e->getMessage()), 8); }
}

echo 'exit';
die;



