<?php
class HM_Workload_WorkloadService extends HM_Service_Abstract
{    
	const CACHE_NAME = 'HM_Workload_WorkloadService';	
	//protected $_students = array(); //-_хранит id студентов. Используется в запросах в виде ограничения. Задается в reportController.
	
	/**	 
	 * получаем кол-во секунд, прощедшее с момента назначения тьютора и отправки приветственного письма
	 * @return int - seconds
	*/
	public function getTimeWelcomeMessage($tutor_id, $subject_id){		
		if(!$tutor_id || !$subject_id){
			return false;
		}
		$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;			
		$vi = $this->getViolations($tutor_id, $type, $subject_id);
		$viTime = false;		
		if($vi){
			$viTime = $vi->current()->violation_time;						
		} else {							
			$viTime = $this->setCurrentWelcomeViolation($tutor_id, $subject_id, $type);			
		}			
		return $viTime;		
	}
	
	
	/**
	 * добавляет или обновляет запись в БД
	 * суммирует те значения, что поступили в функцию. Не перезаписывает, а именно суммирует с теми, что уже есть в БД
	 * @params: array(	'MID' => '',
						'subid' => '',					
						'type' => '',
						'violation_time' => ''
						'lesson_id' => '',				
						'intervals' => '',	)
	*/
	public function addViolations($data){
				
		if(!is_array($data) || empty($data)){
			return false;
		}
		
		if(empty($data['subid'])){
			return false;
		}
		
		$data['MID'] = trim($data['MID']);
		if(empty($data['MID'])){
			return false;
		}
		
		$types = HM_Workload_WorkloadModel::getTypes();		
		if(!array_key_exists ($data['type'], $types)){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$criteriaFields = array( //--список полей, ко которым будет поиск записи.
			'MID', 'subid', 'type', 'lesson_id',
		);
		
		$criteria = array();
		$criteriaValues = array();
		foreach($data as $k => $v){
			if(in_array($k, $criteriaFields)){
				$criteria[] = $k.' = ? AND ';
				$criteriaValues[] = $v; 
			}
		}
		$criteria[] = '1=1';
				
		try {			
			$exsistRow = $this->_serviceWorkload->getOne($this->_serviceWorkload->fetchAll($this->_serviceWorkload->quoteInto(
				$criteria,
				$criteriaValues
			)));				
		} catch (Exception $e) {}				
		
		$data['date_update'] = date('Y-m-d H:i:s',time());

		if($exsistRow){ //-обновляем. 
			if($data['type'] != HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE && $data['type'] != HM_Workload_WorkloadModel::TYPE_SHEET_PASSED){ //--Для типа прив сообщение и ведомость передана не обновляем.
				$data['violation_id'] = $exsistRow->violation_id;
				if(isset($data['violation_time'])){
					$data['violation_time'] = (int)$data['violation_time'] + (int)$exsistRow->violation_time;
				}
				if(isset($data['intervals'])){
					$data['intervals'] = (int)$data['intervals'] + (int)$exsistRow->intervals;
				}				
				$isUpdate = $this->_serviceWorkload->update($data);
				if(!$isUpdate){
					return false;
				}
			}
		} else { //--создаем новую запись			
			$isInsert = $this->_serviceWorkload->insert($data);			
			if(!$isInsert){
				return false;
			}
		}		
		return true;
	}
	
	/**
	 * возвращает записи из таблицы штрафов.
	 * @return array objects
	*/
	public function getViolations($MID, $type, $subjectID, $lessonID = false){
		if(!$type || !$subjectID){
			return false;
		}

		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$criteria = array(
			//'MID = ?' => $MID,
			'type = ?' => $type,
			'subid = ?' => $subjectID,
		);	
		if($MID){			
			$criteria['MID = ?'] = $MID;
		}
		
		if($lessonID){			
			$criteria['lesson_id = ?'] = $lessonID;
		}
		$vi = $this->_serviceWorkload->fetchAll($criteria);
		
		if(!count($vi)){
			return false;
		}		
		return $vi;		
	}
	
	/**
	 * дата, с которой считать тьютора назначенным на сессию. Максимальная дата среди даты начала сессии и даты назначения на сессию.
	 * @return timestamp
	*/
	public function getDateBegin($mid, $subjectID){
	
		if(!$mid || !$subjectID){
			return false;
		}		
		$beginDate = 0;
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$subject = $this->_serviceSubject->getOne(
			$this->_serviceSubject->find($subjectID)
		);
		
		if($subject->begin){			
			$beginDate = (int) strtotime($subject->begin);			
		}			
		$beginDate = ($beginDate < 0) ? (0) : ($beginDate);
				
		$select = $this->_serviceWorkload->getSelect();
        $select->from(            
			'Tutors',
            array(
				'date_assign',				
            )
        );		
		$select->where('MID = ?', $mid); 
		$select->where('CID= ?', $subjectID);
		$select->where('date_assign IS NOT NULL');
		$select->limit(1);		
		$tutor = $select->query()->fetchAll();
		
		if(!$tutor){
			return $beginDate;
		}		
		if(isset($tutor[0]['date_assign'])){
			$assignDate = (int) strtotime($tutor[0]['date_assign']);
			if($beginDate < $assignDate){
				$beginDate = $assignDate;
			}			
		} 		
		return $beginDate;
	}
	
	
	/**
	 * возвращаем дату первого сообщения (уведомление в рамках личного кабинета.) студентам курса .
	 * @return timestamp
	*/
	public function getTimeFirstMessage($mid, $subjectID, $date_end = false, $date_begin = false){		
		if(!$mid || !$subjectID){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();
        $select->from(            
			array('m' => 'messages'),
            array('created' => 'MIN(m.created)',)
        );
		$select->join(
			array('s' => 'Students'),
            's.CID = m.subject_id',
            array()
		);
		/*		
		if(count($this->_students) < 1)	{	$this->restoreFromCache();	 }
		if(count($this->_students))		{	$select->where($this->quoteInto('s.MID IN (?)',$this->_students)); }
		*/
				
		$select->where('m.from = ?', $mid);
		$select->where('m.subject_id = ?', $subjectID);
		if($date_end){
			$select->where('m.created <= ?', $date_end); //--если указана дата, то берем только то, что было отправлено до этой даты. нАпример, до даты окончания сессии.
		}
		
		if($date_begin){
			$select->where('m.created >= ?', $date_begin); //--если указана дата, то берем только то, что было отправлено после этой даты.
		}
		
		$res = $select->query()->fetchAll();		
		if(!$res){	return false;	}
		if(isset($res[0]['created'])){
			return strtotime($res[0]['created']);
		}		
		return false;
	}
	
	
	/**
	 * выборка всех последних сообщений преподавателей и тьюторов + прикрепление задания к уроку.
	 * @return array: [lesson_id][to_whom][date]- нет так.
	*/
	public function getFirstTeacherMessages($userID = false, $lessonID = false){
		
		$types = array(
			HM_Interview_InterviewModel::MESSAGE_TYPE_TASK,
			HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER,
			HM_Interview_InterviewModel::MESSAGE_TYPE_CONDITION,
			HM_Interview_InterviewModel::MESSAGE_TYPE_BALL,
			HM_Interview_InterviewModel::MESSAGE_TYPE_EMPTY,
		);
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('i' => 'interview'),
            array(
				'interview_id' => 'MAX(i.interview_id)',		
				'lesson_id' => 'i.lesson_id',		
				'to_whom' => 'i.to_whom',		
				'date' => 'MAX(i.date)',		
				'interview_hash' => 'i.interview_hash',	
            )
        );		
		$select->where( $this->quoteInto('i.type IN (?)', $types) );
		$select->group(array('lesson_id', 'interview_hash', 'to_whom'));
		
		if($userID){			
			$select->where($this->quoteInto(array('i.user_id = ? ', ' OR user_id = 0'), array($userID) ));
		}
		
		if($lessonID){
			$select->where('i.lesson_id = ?', $lessonID);
		}		
		$res = $select->query()->fetchAll();		
		if(!$res){	return false;	}
		return $res;	
	}
	
	/**
	 * выборка всех сообщений студентов.
	 * @return array: [lesson_id][user_id][][date]
	*/
	public function getAllStudentMessages($studentID = false, $lessonID = false){
		$types = array(
			HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION,
			HM_Interview_InterviewModel::MESSAGE_TYPE_TEST,			
		);
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('i' => 'interview'),
            array(
				'interview_id' => 'i.interview_id',		
				'lesson_id' => 'i.lesson_id',		
				'user_id' => 'i.user_id',		
				'date' => 'i.date',		
				'interview_hash' => 'i.interview_hash',						  
            )
        );
		$select->where( $this->quoteInto('i.type IN (?)', $types) );
		$select->group(array('lesson_id', 'interview_hash', 'user_id', 'date', 'interview_id'));
		
		if($userID){			
			$select->where($this->quoteInto(array('i.user_id = ? ', ' OR user_id = 0'), array($userID) ));
		}		
		if($lessonID){
			$select->where('i.lesson_id = ?', $lessonID);
		}		
		$res = $select->query()->fetchAll();		
		if(!$res){	return false;	}
		
		$data = array();
		foreach($res as $i){
			$data[$i['lesson_id']][$i['user_id']][] = $i['date'];
		}
		return $data;		
	}
	
	
	/**
	 * даты начала отсчета просрочки.
	 * сравниваем дату последнего сообщения преопа с датами сообщений студента. Берем наименьшую дату студента, которая больше даты препода.
	 * @return array: [lesson_id][to_whom][date]
	 * проверить факт выбора даты назначения или даты начала сессии.
	*/
	public function getDateBeginLessonViolation($teacherID = false, $lessonID = false, $studentID = false){ 
		
		$msgT = $this->getFirstTeacherMessages($teacherID, $lessonID);
		$msgS = $this->getAllStudentMessages($studentID, $lessonID);
		
		if(!$msgT || !$msgS){
			return false;
		}
	
		$data = array();
		foreach($msgT as $t){
			$timestampT = strtotime($t['date']);			
			if( isset($msgS[$t['lesson_id']][$t['to_whom']])){
				$dates = $msgS[$t['lesson_id']][$t['to_whom']];
				if(!empty($dates)){									
					foreach($dates as $d){
						$timestampS = strtotime($d);
						if($timestampT < $timestampS){							
							if(!isset($data[$t['lesson_id']][$t['to_whom']])){								
								$data[$t['lesson_id']][$t['to_whom']] = $timestampS;
							} else {								
								if($data[$t['lesson_id']][$t['to_whom']] > $timestampS){									
									$data[$t['lesson_id']][$t['to_whom']] = $timestampS;
								}
							}
						}
					}
				}				
			}		
		}		
		return $data;		
	}
	
	/*
	 * получаем время просрочки по уроку
	 * @return array
	 * [tutorID] => [lessonID] => [studentID] = [violation time in seconds]
	*/
	public function getTimeLessonViolation($teacherID, $lessonID, $studentID, $subject_id = false){		
		$t = is_array($teacherID) ? (false) : ($teacherID);
		$l = is_array($lessonID) ? (false) : ($lessonID);
		$s = is_array($subject_id) ? (false) : ($subject_id);		
		$msgBeginDates = $this->getDateBeginLessonViolation($t, $l, $s);	//--это корректно работает без параметров????
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('t' => 'Tutors'),
            array(
				'tutorID' => 't.MID',											  
				'lessonID' => 'l.SHEID',											  
				'studentID' => 'ls.MID',											  
            )
        );
		$select->join(
			array('subj' => 'subjects'),
            'subj.subid = t.CID',
            array()
		);
		$select->join(
			array('l' => 'schedule'),
            'l.CID = t.CID',
            array()
		);
		$select->join(
			array('ls' => 'Students'),
            'ls.CID = subj.subid',
            array()
		);			
		$select->where('l.typeID = ?',HM_Event_EventModel::TYPE_TASK); //--только задание на проверку		
		
		if($teacherID){
			$teacherID = (array)$teacherID;
			if(count($teacherID)){
				$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $teacherID));				
			}
		}
		if($lessonID){
			$lessonID = (array)$lessonID;
			if(count($lessonID)){
				$select->where($this->_serviceWorkload->quoteInto('l.SHEID IN (?)', $lessonID));				
			}
		}
		if($studentID){
			$studentID = (array)$studentID;
			if(count($studentID)){
				$select->where($this->_serviceWorkload->quoteInto('ls.MID IN (?)', $studentID));				
			}
		}
		if($subject_id){
			$subject_id = (array)$subject_id;
			if(count($subject_id)){
				$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subject_id));				
			}
		}			
		$res = $select->query()->fetchAll();		
		
		if(!$res){
			return false;
		}
		
		$data = array();		
		foreach($res as $k=>$i){			
			if(isset($msgBeginDates[$i['lessonID']][$i['studentID']])){
				if(!isset($res[$k]['violationTime'])){					
					$violationTime = time() - ((int)$msgBeginDates[$i['lessonID']][$i['studentID']] + (int) HM_Workload_WorkloadModel::SAFE_TIME);//--вычисляем время просрочки.
					if($violationTime > 0){
						$data[$i['tutorID']][$i['lessonID']][$i['studentID']] = $violationTime;
					} else {
						$data[$i['tutorID']][$i['lessonID']][$i['studentID']] = 0;
					}
				}
			}
		}					
		return $data;		
	}
	
	/**
	 * возвращает кол-во секунд просрочки по одному уроку и студенту.
	 * @return integer
	*/
	public function getOneTimeLessonViolation($teacherID, $lessonID, $studentID){
		if(!$teacherID || !$lessonID || !$studentID){
			return false;
		}
		$violations = $this->getTimeLessonViolation($teacherID, $lessonID, $studentID);		
		if(isset($violations[$teacherID][$lessonID][$studentID])){
			return $violations[$teacherID][$lessonID][$studentID];
		}
		return false;
	}
	
	/**
	 * устанавливает просрочку преподавателю при проверке задания.
	 * Если $violationSeconds = false, значит нет интервала, и фиксировать факт просрочки не надо. Т.е. последний был препод, а не студент
	 * Если $violationSeconds = 0 или более, значит надо фиксировать факт просрочки и кол-во интервалов.
	*/
	public function setLessonViolation($teacherID, $studentID, $lessonID, $subjectID){
		
		if(!$teacherID  || !$studentID || !$lessonID || !$subjectID){
			return false;
		}		
		$violationSeconds = $this->getOneTimeLessonViolation($teacherID, $lessonID, $studentID); //--просрочка на конкретног остудента и предмет.			
		
		//--нужно ли тут это условие? Если нет просрочки, то надо ли добавлять +1 интервал?
		//--если 0, то по факту надо в БД занести.
		//-_протестировать!!!
		if($violationSeconds !== false){ //--заносим просрочку в БД при добавлении сообщения преподом.
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			$type = HM_Workload_WorkloadModel::TYPE_SUBJECT_ASSESSMENT;											
			$data = array(
				'MID' => $teacherID,
				'subid' => $subjectID,					
				'type' => $type,
				'violation_time' => $violationSeconds,				
				'lesson_id' => $lessonID,				
				'intervals' => 1,	
			);				
			$this->_serviceWorkload->addViolations($data);	
		}
		return true;
	}
	
	
	/**
	 * расчитывает разницу (просрочку).
	 * @return int seconds
	 * @params timestamp
	*/
	
	public function getViolationSeconds($begin, $end){
		if(empty($end)){
			$end = time();
		}
		$violationTime = (int)$end - ( (int)$begin + (int) HM_Workload_WorkloadModel::SAFE_TIME);//--вычисляем время просрочки.
		if($violationTime > 0){
			return $violationTime;
		}
		return 0;		
	}
	
	
	/**
	 * возвращает пользователей, которые находятся в подчинении у наблюдателя в оргструктуре в виде списка. А также на одном уровне с ним.
	 * @return array([MID] => [fio])
	*/
	public function getListOrgstructurePersons($MID, $isEnd = false)
	{	
		$special_users = array(5161, 5829, 72882);
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$selectC = $this->_serviceOrgstructure->getSelect();
		$selectC->from(
			array('so' => 'structure_of_organ'),
			array(				
				'so.soid',
				'so.lft',
				'so.rgt',
			)
		);
		$selectC->join(
			array('so2' => 'structure_of_organ'),
            'so2.owner_soid = so.soid',
            array()
        );
		$selectC->where('so2.mid = ?', $MID);
		$current = $selectC->query()->fetchAll();
		
		if(!$current){
			return array(''=>_('Нет'));
		}
		
		$criteria = array();
		$criteriaValues = array();
		foreach($current as $i){
			$criteria[] = '(so.lft >= ? ';
			$criteriaValues[] = $i['lft'];
			$criteria[] = ' AND so.rgt <= ?) OR ';
			$criteriaValues[] = $i['rgt'];
		}
		$criteria[] = ' 1 != 1';
		
		$select = $this->_serviceOrgstructure->getSelect();
		$select->from(
			array('so' => 'structure_of_organ'),
			array(				
				'MID' => 'p.MID',
				'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
			)
		);
		$select->join(
			array('p' => 'People'),
            'p.MID = so.MID',
            array()
        );
		$select->join(
			array('t' => 'Tutors'),
            'p.MID = t.mid',
            array()
        );
		$select->join(
			array('subj' => 'subjects'),
			't.CID = subj.subid',
			array()
		);
		
		$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
		$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
		
		
		#$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
		
		
		if($isEnd){
			$select->where('subj.isSheetPassed IS NOT NULL');
		} else {
			$select->where('subj.isSheetPassed IS NULL');
		}
		
		
		$select->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
		$select->where(
			$this->_serviceOrgstructure->quoteInto(
				$criteria,
				$criteriaValues
			)
		);		
		$select->order('p.LastName');
		$select->where('so.mid != ?', $MID);
		$select->where('so.mid > ?', 0);
		$res = $select->query()->fetchAll();
		
		$list = array('-1'=>_('Все'));
		
		if(!$res){
			return array(''=>_('Нет'));
		}
		
		foreach($res as $i){
			$list[$i['MID']] = $i['fio'];
		}
		
		
		
		if(in_array($this->getService('User')->getCurrentUserId(), $special_users)){ # расширенный список тьюторов
			####
			$selectWithOrg = $this->_serviceOrgstructure->getSelect();
			$selectWithOrg->from(
				array('p' => 'People '),
				array(				
					'MID' => 'p.MID',
					'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
				)
			);
			$selectWithOrg->joinLeft(
				array('so' => 'structure_of_organ'),
				'p.MID = so.MID',
				array()
			);
			$selectWithOrg->join(
				array('t' => 'Tutors'),
				'p.MID = t.mid',
				array()
			);
			$selectWithOrg->join(
				array('subj' => 'subjects'),
				't.CID = subj.subid',
				array()
			);			
			$selectWithOrg->where('so.soid IS NULL');
			$selectWithOrg->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			$selectWithOrg->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			#$selectWithOrg->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
			$selectWithOrg->order('p.LastName');
			$selectWithOrg->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
			$res = $selectWithOrg->query()->fetchAll();
			if($res){
				foreach($res as $i){
					if(!isset($list[$i['MID']])){
						$list[$i['MID']] = $i['fio'];	
					}
				}
			}
			###
		}
		
		return $list;		
	}
	
	/**
	 * Выборка кол-во слушателей, назначенных на сесси.
	 * @return array([subject_id] => [count_student])
	 *
	*/
	public function getSubjectStudentCount($subject_id = false){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select_students = $this->_serviceWorkload->getSelect();
		$select_students->from(
			array('s' => 'Students'),
			array(				
				'subject_id' => 's.CID',					
				'count_student' => 'COUNT(s.SID)',
			)
		);
		if($subject_id > 0){ //--выбрана сессия				
			$select_students->where('s.CID = ?', $subject_id);
		} 		
		$select_students->group('s.CID');	
		$res2 = $select_students->query()->fetchAll();

		$data = array();
		if($res2){
			foreach($res2 as $s){
				$data[$s['subject_id']] = $s['count_student'];
			}
		}
		return $data;
	}
	
	
	/**
	 * Выборка зафиксированных в БД просрочек		
	 * @return array(all fields)
	 *
	*/
	public function getDBViolations($type = false, $subjectIDs = false, $tutorIDs = false){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		//--выборка зафиксированных в БД просрочек			
		$select_violations = $this->_serviceWorkload->getSelect();
		$select_violations->from(
			array('w' => 'workload_violations')			
		);
		if($type){
			$select_violations->where('w.type = ?', $type);
		}
		
		if($subjectIDs){
			$subjectIDs = (array)$subjectIDs;
			if(count($subjectIDs)){
				$select_violations->where($this->_serviceWorkload->quoteInto('w.subid IN (?)', $subjectIDs));				
			}
		}	
		
		if($tutorIDs){
			$tutorIDs = (array)$tutorIDs;
			if(count($tutorIDs)){
				$select_violations->where($this->_serviceWorkload->quoteInto('w.MID IN (?)', $tutorIDs));				
			}
		}

		$res_violations = $select_violations->query()->fetchAll();			
		if($res_violations){
			return $res_violations;
		}
		return false;
	}
	
	/**
	 * Выборка активных студентов сессиии. Активные - те, которые хотя бы 1 раз написали в занятии.
	 * @return array('student_id', 'lesson_id', 'tutor_id', 'subject_id)
	*/
	public function getSubjectActiveStudents($tutorIDs = false, $subjectIDs = false, $getCloseSubject = false){		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select_active = $this->_serviceWorkload->getSelect();			
		$select_active->from(
			array('s' => 'Students'),
			array(				
				'student_id' => 's.MID',										
				'lesson_id' => 'l.SHEID',										
				'tutor_id' => 't.MID',	
				'subject_id' => 'subj.subid',	
			)
		);
		$select_active->join(
			array('subj' => 'subjects'),
			's.CID = subj.subid',
			array()
		);
		$select_active->join(
			array('l' => 'schedule'),
			'l.CID = subj.subid',
			array()
		);
		$select_active->join(
			array('i' => 'interview'),
			'i.user_id = s.MID AND i.lesson_id = l.SHEID',
			array()
		);
		$select_active->join(
			array('t' => 'Tutors'),
			'subj.subid = t.CID',
			array()
		);
		$select_active->where('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
		
		if($getCloseSubject === true){
			$select_active->where('subj.isSheetPassed IS NOT NULL');	
		} else {
			$select_active->where('subj.isSheetPassed IS NULL');	
		}		
		
		$select_active->where('l.typeID = ?', HM_Event_EventModel::TYPE_TASK);
		
		if($subjectIDs){
			$subjectIDs = (array)$subjectIDs;
			if(count($subjectIDs)){
				$select_active->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));				
			}
		}
		if($tutorIDs){
			$tutorIDs = (array)$tutorIDs;
			if(count($tutorIDs)){
				$select_active->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));				
			}
		}		
		$select_active->group(array('s.MID', 'l.SHEID', 't.MID','subj.subid'));			
		$res_students = $select_active->query()->fetchAll();
		
		if($res_students){
			return $res_students;			
		}
		return false;
	}
	
	
	/**
	 * Расчет нагрузки (H) для тьютора по одной сессии.
	 * @return float
	*/
	public function getWorkload($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){
			return false;
		}
		$_F0 = $_F4 = $_F1 = $_F2 = $_F3 = 0;
		
		$_F0 = $this->getWorkload_F0($tutor_id, $subject_id); //-приветственное письмо
		$_F1 = $this->getWorkload_F1($tutor_id, $subject_id); //-проверка заданий
		$_F2 = $this->getWorkload_F2($tutor_id, $subject_id); //-форум
		$_F3 = $this->getWorkload_F3($subject_id); //-вебираны
		$_F4 = $this->getWorkload_F4($subject_id); //-ведомость передана
		return $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);		
	}
	
	
	/**
	 * Расчет нагрузки (H) для тьютора по одной сессии на указанную дату.
	 * Если сессия закончена, то расчитываем до даты окончания все сообщения на форуме и т.д.
	 * Данные, что появились после даты окнчания не учитываем.
	 * нужно ли указывать дату окончания?
	 * @return float
	*/
	public function getWorkloadFix($tutor_id, $subject_id, $date_end, $date_begin = false){
		if(!$tutor_id || !$subject_id){
			return false;
		}
		$_F0 = $_F4 = $_F1 = $_F2 = $_F3 = 0;
		
		$_F0 = $this->getWorkloadFix_F0($tutor_id, $subject_id, $date_end, $date_begin); //-приветственное письмо		
		$_F1 = $this->getWorkloadFix_F1($tutor_id, $subject_id, $date_end, $date_begin); //-проверка заданий		
		$_F2 = $this->getWorkloadFix_F2($tutor_id, $subject_id, $date_end, $date_begin); //-форум		
		$_F3 = $this->getWorkload_F3($subject_id); //-вебираны не влияют на дату.
		$_F4 = $this->getWorkload_F4($subject_id, $date_end, $date_begin); //-ведомость передана. Также не зависит от даты
		//pr($_F0.' * '.$_F4.' * ( '.$_F1.' + '.$_F2.' + '.$_F3.')');		
		return $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);		
	}
	
	
	/**
	 * Расчет нагрузки (H) для тьютора по одной сессии, по которой ПЕРЕДАЛИ ведомость.
	 * @return float
	*/
	public function getEndWorkload($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){
			return false;
		}
		$H_from_DB = false; //--берем H из БД. сделать выборку. Но сейчас даже таблицы нет.
		if($H_from_DB === false){
			$H_calculate = $this->getWorkload($tutor_id, $subject_id);
			//--сохраняем в БД расчитанную нагрузку. Реализовать, если надо.
			return $H_calculate;
		}		
		return $H_from_DB;		
	}
	
	/**
	 * Нагрузка по приветственнопу письму.
	 * @return 1 | 0
	 */
	public function getWorkload_F0($tutor_id, $subject_id){
		$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;
		$vi = $this->getViolations($tutor_id, $type, $subject_id);
		if($vi){
			return 1;
		}		
		
		$dateFirstMsg = $this->getTimeFirstMessage($tutor_id, $subject_id);		
		if(!$dateFirstMsg){
			return 0;
		}		
		
		$this->setCurrentWelcomeViolation($tutor_id, $subject_id, $type, $dateFirstMsg);
		return 1;
	}
	
	
	/**
	 * Нагрузка по приветственнопу письму.
	 * $date_end - дата, до которой бедет производиться расчет и выборка сообщений
	 * @return 1 | 0
	 */
	public function getWorkloadFix_F0($tutor_id, $subject_id, $date_end, $date_begin = false){
		if(HM_Workload_WorkloadModel::DISABLE_WORKLOAD_WELCOME_MESSAGE === true){ //--не учитываем нагрузку по прив. письму.
			return 1;
		}
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }
		//Если есть письмо в просрочках, или находим первое сообщение по периоду.
		/*
		$dateBegin = $this->getDateBegin($tutor_id, $subject_id); //=-дата назначения тьютора или дата начала сессии
		
		if($date_begin_period){
			if($dateBegin < strtotime($date_begin_period) ){ //--если период расчета не включает дату начала сессии или назначения тьютора.
				$dateBegin = strtotime($date_begin_period); 
			}
		}		
		$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;			
		$vi = $this->getViolations($tutor_id, $type, $subject_id); //--находим время фиксации письма.				
		if($vi){						
			$viTimeEnd = strtotime($vi->current()->date_update);															
			if($dateBegin > $viTimeEnd){ //-_если дата фиксации была до начала периода, то засчитываем как 1
				return 1;
			}
		} else {													
			$this->setCurrentWelcomeViolation($tutor_id, $subject_id, $type);						
			$viTimeEnd = ($date_end_period) ? (strtotime($date_end_period)) : (time()); //--дата до которой бедет проихводится расчет просрочки.
		}		
		$delta = $this->getViolationSeconds($dateBegin, $viTimeEnd);		
		if($delta > 0){
			return 0;
		}
		return 1;
		*/		
		$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;
		$vi = $this->getViolations($tutor_id, $type, $subject_id);		
		if($vi){ //--зафиксированно прив письмо.			
			return 1;			
		}
		
		$subject = $this->_serviceSubject->getById($subject_id);     
        if($subject && strtotime($subject->end) > strtotime($date_end)){ //--если сессия еще не закончилась на период формирования отчета.			
			return 1;
        }
		
		if($subject && strtotime($subject->begin) > 0){
			$date_begin = $subject->begin;//--дата начала сессии, т.к. если прив. письмо и не попало в период, то оно может быть перед ним.
		}
		
        $dateFirstMsg = $this->getTimeFirstMessage($tutor_id, $subject_id, $date_end, $date_begin);                		
        if(!$dateFirstMsg){            
			return 0;
		}   		
		//$this->setCurrentWelcomeViolation($tutor_id, $subject_id, $type, $dateFirstMsg); //--фиксируем прив. письмо.
        return 1;
	}
	
	
	
	/**
	 * Нагрузка по проверки заданий
	 * @return float
	 */
	public function getWorkload_F1($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){
			return 0;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();			
		$select->from(
			array('s' => 'Students'),
			array(				
				'student_id' => 'p.MID',
				'lesson_id' => 'l.SHEID',												
			)
		);
		$select->joinLeft(
			array('l' => 'schedule'),
			's.CID = l.CID',
			array()
		);
		$select->join(
			array('p' => 'People'),
			'p.MID = s.MID',
			array()
		);
		$select->joinLeft(
			array('m' => 'interview'),
			'm.lesson_id = l.SHEID AND m.to_whom = s.MID',
			array()
		);		
		$select->where('l.CID = ?', $subject_id);
		$select->where('l.typeID = ?', HM_Event_EventModel::TYPE_TASK); //--задание на проверку		
		$select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
		$select->where('m.user_id = ?', $tutor_id);		
		$select->where('m.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		$select->group(array('p.MID','l.SHEID'));		
		$res = $select->query()->fetchAll();
		$count = count($res); //-кол-во фактов выставления оценок во всех заданиях на проверку.
		return ($count * 0.35);		
	}
	
	
	/**
	 * Нагрузка по проверки заданий c учетом даты
	 * @return float
	 */
	public function getWorkloadFix_F1($tutor_id, $subject_id, $date_end = false, $date_begin = false){
		
		if(!$tutor_id || !$subject_id){
			return 0;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$subUsers = $this->_serviceWorkload->getSelect();
		$subUsers->from(array('Students'),	array('MID','CID'));
		
		$subGrad = $this->_serviceWorkload->getSelect();
		$subGrad->from(array('graduated'),	array('MID','CID'));
		
		$subUSelect = $this->_serviceWorkload->getSelect();
		$subUSelect->union(array($subUsers, $subGrad));
		
		
		$select = $this->_serviceWorkload->getSelect();			
		$select->from(
			array('s' => $subUSelect),			
			array(				
				'student_id' => 'p.MID',
				'lesson_id' => 'l.SHEID',												
			)
		);
		$select->joinLeft(
			array('l' => 'schedule'),
			's.CID = l.CID',
			array()
		);
		$select->join(
			array('p' => 'People'),
			'p.MID = s.MID',
			array()
		);
		$select->joinLeft(
			array('m' => 'interview'),
			'm.lesson_id = l.SHEID AND m.to_whom = s.MID',
			array()
		);
		/*
		if(count($this->_students) < 1)	{	$this->restoreFromCache();	 }
		if(count($this->_students))		{	$select->where($this->quoteInto('s.MID IN (?)',$this->_students)); }
		*/
		$select->where('l.CID = ?', $subject_id);
		$select->where('l.typeID = ?', HM_Event_EventModel::TYPE_TASK); //--задание на проверку		
		$select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
		$select->where('m.user_id = ?', $tutor_id);		
		$select->where('m.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		if($date_end){
			$date_end = $date_end.' 23:59:59';
			$select->where('m.date <= ?', $date_end);		
		}
		
		if($date_begin){
			$date_begin = $date_begin.' 00:00:00';
			$select->where('m.date >= ?', $date_begin);		
		}
		
		$select->group(array('p.MID','l.SHEID'));

		$res = $select->query()->fetchAll();		
		$count = count($res); //-кол-во фактов выставления оценок во всех заданиях на проверку.
		return ($count * 0.35);	
	}
	
	
	/**
	 * Нагрузка по форуму
	 * @return float
	 */
	public function getWorkload_F2($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){
			return 0;
		}
		if(!$this->_serviceLesson){ $this->_serviceLesson = $this->getService('Lesson');	}
		$lessons = $this->_serviceLesson->fetchAll(array(
            'CID = ?' => $subject_id,  
			'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
			'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        ));			
		$countLessons = count($lessons);
	
		$countStudents = $this->getCountStudents($subject_id);
		
		$maxParts = 3 * $countLessons * $countStudents;
		
		$curParts = 0;
		$vi = $this->getViolations($tutor_id, HM_Workload_WorkloadModel::TYPE_FORUM_ANSWER, $subject_id);		
		if($vi){		
			foreach($vi as $i){				
				$curParts = $curParts +  (int)$i->intervals;
			}
		}
		$min = min($maxParts, $curParts);		
		return ($min * 0.05);
	}
	
	/**
	 * Нагрузка по форуму c с учетом даты.
	 * @return float
	 */
	public function getWorkloadFix_F2($tutor_id, $subject_id, $date_end, $date_begin = false){
		return 0; //--временно не учитываем нагрузку по этому пункту.
		
		if(!$tutor_id || !$subject_id){
			return 0;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		if(!$this->_serviceLesson){ $this->_serviceLesson = $this->getService('Lesson');	}
		$lessons = $this->_serviceLesson->fetchAll(array(
            'CID = ?' => $subject_id,  
			'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
			'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        ));			
		$countLessons = count($lessons);
	
		$countStudents = $this->getCountStudents($subject_id) + $this->getCountGraduat($subject_id);
		
		
		$maxParts = 3 * $countLessons * $countStudents;
		
		$curParts = 0;
		//--берем форум.
		//--в нем все сообщения до опр даты
		//--находим интервалы.
		#################################################
		$forum = $this->getService('ForumForum')->getForumBySubjectId($subject_id);			
		if($forum){
			
			
			$subUsers = $this->_serviceWorkload->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
			
			$subGrad = $this->_serviceWorkload->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
			
			$subUSelect = $this->_serviceWorkload->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			
			$select = $this->_serviceWorkload->getSelect();
			$select->from(
				array('m' => 'forums_messages'),
				array(									
					'student_id' => 's.MID', //--если пусто, то это не студент
					'message_id' => 'm.message_id',
					'author_id' => 'm.user_id',													
					'date' => 'm.created',				
				)
			);
			
			$criteria = 's.MID = m.user_id'; 
			/*
			if(count($this->_students) < 1)	{	$this->restoreFromCache();	 }				
			if(count($this->_students))		{	
				$criteria .= ' AND '.$this->quoteInto('s.MID IN (?)',$this->_students); //--если делать обычным where, то не попадут сообщения тьюторов.
			}	
			*/
			$select->joinLeft(
				array('s' => $subUSelect),
				$criteria,
				array()
			);						
			$select->where('m.forum_id = ?',$forum->forum_id);		
			if($date_end){					
				$select->where('m.created <= ?',$date_end);		
			}
			
			if($date_begin){				
				$select->where('m.created >= ?',$date_begin);		
			}
			$select->group(array('s.MID', 'm.message_id', 'm.user_id', 'm.created'));								
			$select->order('m.created');	

			$res = $select->query()->fetchAll();
			
			$data = array();			
			if($res){				
				foreach($res as $i){					
					if((strtotime($i['date']) <=  strtotime($date_end.' 23:59:59')) || !$date_end){ //если есть ограничение по вермени или ограничения нет вовсе.
						
						if(!isset($data['last_msg_user_id'])){							
							$data['last_msg_user_id'] = $i['author_id'];
							$data['date_begin'] = $i['date'];												
						} else {							
							if($i['student_id'] > 0){ //--сообщение от студента																
								if($data['last_msg_user_id'] == $tutor_id){ //--предыдущее сообщение от тьютора									
									$data['last_msg_user_id'] = $i['author_id'];
									$data['date_begin'] = $i['date'];					
								}
							} else { //--если от тьютора текущее сообщение 																									
								if($data['last_msg_user_id'] != $tutor_id){ //--предыдущее сообщение НЕ от тьютора. Конец интервала																		
									if($i['author_id'] == $tutor_id){ //--сообщение написал этот тьютор, а не кто-то иной. 
										$data['intervals']++;
										$data['last_msg_user_id'] = $i['author_id']; //--начинаем новый интервал сообщением от тьютора.
										$data['date_begin'] = $i['date'];
									}									
								}
							}
						}													
					}					
				}						
				/* //--актуально для просрочки, но для нагрузки открытый интервал не учитывается.
				if($data['last_msg_user_id'] != $tutor_id){ //--Если последний не тьютор отвечал. Закрываем период.
					$dateEnd = ($date_end) ? (strtotime($date_end.' 23:59:59')) : (time());							
					$dateBegin = strtotime($data['date_begin']);
					if($dateBegin > 0){						
						$data['intervals']++;						
					}
				}
				*/
			}
			$curParts = $data['intervals'];
		}	
		
		#################################################		
		$min = min($maxParts, $curParts);		
		return ($min * 0.05);
	}
	
	/**
	 * Нагрузка по вебинарам
	 * @return int
	 */
	public function getWorkload_F3($subject_id){
		return 0; //--временно не учитываем нагрузку по этому пункту.
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		if(!$this->_serviceLesson){ $this->_serviceLesson = $this->getService('Lesson');	}
		
		$s = $this->_serviceSubject->getById($subject_id);
		if(!$s){
			return 0;
		}				
		$webinars = $this->_serviceLesson->fetchAll(array(
            'CID = ?' => $subject_id,
            'typeID = ?' => HM_Event_EventModel::TYPE_WEBINAR,			
			'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        ));		
		return (2 * count($webinars) );		
	}
	
	/**
	 * Нагрузка по предоставлению ведеомсти.
	 * @return 1 | 0
	 */
	public function getWorkload_F4($subject_id, $date_end = false, $date_begin = false){
		if(HM_Workload_WorkloadModel::DISABLE_WORKLOAD_SHEET_PASSED === true){ //--не учитываем нагрузку по ведомости
			return 1;
		}
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		$s = $this->_serviceSubject->getById($subject_id);
		if(!$s){		
			return 0;
		}		
		$endTimestamp = false; //--дата окончания сессии		
		if($s->end){
			$endTimestamp = strtotime($s->end);
			if(!$endTimestamp){
				$endTimestamp = strtotime($s->end_planned);
			}
		}
		
		if(!$endTimestamp){ //--нет даты окочания сессии.		
			return 0;
		}
		
		if(!$date_end){
			$date_end = date('Y-m-d',time());
		}
		
		if($date_end && strtotime($date_end) <= $endTimestamp){ //-на период формировани отчета сессия еще не закочилась.			
			return 1;
		}
		
		if($s->isSheetPassed){
			$vi = $this->getViolations(false, HM_Workload_WorkloadModel::TYPE_SHEET_PASSED, $subject_id);		
			if($vi){
				if(strtotime($vi->current()->date_update) <= strtotime($date_end)){	//--дата предоставления ведомости меньше, чем окончание периода формирования отчета.			
					return 1;
				} else {				
					return 0;
				}
			}		
		}
		return 0;
	}
	

	
	
	/**
	 * Находим и фиксируем последнюю просрочку по приветственному письму
	*/
	public function setCurrentWelcomeViolation($tutor_id, $subject_id, $type, $dateEndViolation = false){
		$dateBegin = $this->getDateBegin($tutor_id, $subject_id);		
		$dateBeginViolation = (int) $dateBegin + (int) HM_Workload_WorkloadModel::SAFE_TIME; //--дата отсчета штрафа о приветственном письме.			
		
		$dateEndViolation = $this->getTimeFirstMessage($tutor_id, $subject_id);
		
		$isNeedInsert = true;
		if(!$dateEndViolation){	//--нет пиьсма. Берем текущую дату.			
			$dateEndViolation = time();
			$isNeedInsert = false;
		}			
		$delta = $dateEndViolation - $dateBeginViolation;
		
		if($delta < 0){ //--не просрочил.
			$delta = 0;
		} 		
		$viTime = $delta;		
		if($isNeedInsert){ //--есть письмо. Сохраняем в бд	
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			$data = array(
				'MID' => $tutor_id,
				'subid' => $subject_id,					
				'type' => $type,
				'violation_time' => $viTime,				
			);						
			$this->_serviceWorkload->addViolations($data);
		}			
		return $viTime;
	}
	
	
	public function getCountStudents($subject_id){
		if(!$subject_id){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();			
		$select->from(
			array('p' => 'People'),
			array(				
				'count_students' => 'COUNT(p.MID)',				
			)
		);
		$select->join(
			array('s' => 'Students'),
			's.MID = p.MID',
			array()
		);
		/*
		if(count($this->_students) < 1)	{	$this->restoreFromCache();	 }
		if(count($this->_students))		{	$select->where($this->quoteInto('s.MID IN (?)',$this->_students)); }
		*/
		$select->where('s.CID = ?', $subject_id);
		$res = $select->query()->fetchObject();		
		$countStudents = ($res) ? ($res->count_students) : (0);
		
		return $countStudents;
	}
	
	/**
	 * кол-во завершивших обучение
	*/
	public function getCountGraduat($subject_id){
		if(!$subject_id){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();			
		$select->from(
			array('p' => 'People'),
			array(				
				'count_students' => 'COUNT(p.MID)',				
			)
		);
		$select->join(
			array('s' => 'graduated'),
			's.MID = p.MID',
			array()
		);
		/*
		if(count($this->_students) < 1)	{	$this->restoreFromCache();	 }
		if(count($this->_students))		{	$select->where($this->quoteInto('s.MID IN (?)',$this->_students)); }
		*/
		$select->where('s.CID = ?', $subject_id);
		$res = $select->query()->fetchObject();		
		$countStudents = ($res) ? ($res->count_students) : (0);
		
		return $countStudents;
	}
		
	
	/*
	 * получаем просрочки по проверке заданий для несколькоих записей
	 * @return array
	 * Замена ф-ции getTimeSubjectViolation 
	*/	
	public function getAllTimeSubjectViolation($tutorIDs, $subjectIDs, $getCloseSubject = false){			
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$res_violations = $this->_serviceWorkload->getDBViolations(HM_Workload_WorkloadModel::TYPE_SUBJECT_ASSESSMENT, $subjectIDs, $tutorIDs); //--выборка зафиксированных в БД просрочек

		
		$allViolationDB = array();			
		if($res_violations){
			foreach($res_violations as $v){
				$allViolationDB[$v['MID']][$v['lesson_id']] = $v['violation_time'];
			}
		}
		
		$res_students = $this->_serviceWorkload->getSubjectActiveStudents($tutorIDs, $subjectIDs, $getCloseSubject); //--активные студенты			
		$allViolation = $this->_serviceWorkload->getTimeLessonViolation($tutorIDs, false, false, $subjectIDs); //--просрочки по сессиям, которые не зафиксированы в таблице просрочек.		
		$countParts = $this->_serviceWorkload->getSubjectParts($subjectIDs);
		
		$total_violations = array();			
		if($res_students){
			foreach($res_students as $s){
				$total_seconds = 0;
				$active_students = 0;
				if(!isset($total_violations[$s['tutor_id']][$s['subject_id']][$s['lesson_id']])){ //--если еще не обрабатывали этот урок
					if(isset($allViolation[$s['tutor_id']][$s['lesson_id']]) && count($allViolation[$s['tutor_id']][$s['lesson_id']])){					
						foreach($allViolation[$s['tutor_id']][$s['lesson_id']] as $seconds){
							$total_seconds = $total_seconds + $seconds;								
						}					
					}
					if(isset($allViolationDB[$s['tutor_id']][$s['lesson_id']])){ //--если есть просрочка в БД
						$total_seconds = $total_seconds + $allViolationDB[$s['tutor_id']][$s['lesson_id']];
					}
					$total_violations[$s['tutor_id']][$s['subject_id']][$s['lesson_id']]['seconds'] = $total_seconds;
				}										
				$total_violations[$s['tutor_id']][$s['subject_id']][$s['lesson_id']]['active_students']++;					
			}
		}
		
		$avgTime = array(); //--средне время просрочки на каждую сессию. Формат [tutor_id] => [subject_id] => T		
		foreach($total_violations as $tutor_id => $subject){ //--расчитываем среднее время вросрочки.				
			foreach($subject as $subj_id => $lessons){				
				$count_parts = $countParts[$subj_id];								
				if(count($lessons)) {
					$sum = 0;											
					foreach($lessons as $v){							
						$d = 0;
						if($v['active_students'] >= 0){
							$d = ($v['seconds'] / $v['active_students']);
						}
						$sum = $sum + $d;
					}						
					$avg = $sum/$count_parts;	
				} else {
					$avg = false;
				}
				$avgTime[$tutor_id][$subj_id] = $avg;										
			}
		}
		
		return array(
			'avgTime' => $avgTime,
			'countParts' => $countParts,
		);
	}
	
	/*
	 * получаем просрочки по форуму.
	 * @return array	 
	*/	
	public function getAllTimeForumViolation($tutorIDs, $subjectIDs, $getCloseSubject = false){		
	
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		if(!$this->_serviceWorkloadForum){ $this->_serviceWorkloadForum = $this->getService('WorkloadForum');	}
		
		$res_violations = $this->_serviceWorkload->getDBViolations(HM_Workload_WorkloadModel::TYPE_FORUM_ANSWER, $subjectIDs, $tutorIDs); //--выборка зафиксированных в БД просрочек	
		
		$allViolationDB = array();				
		if($res_violations){
			foreach($res_violations as $v){
				$allViolationDB[$v['MID']][$v['subid']] = array(
					'violation_time' => $v['violation_time'],
					'intervals' => $v['intervals'],
				);					
			}
		}						
		$res_students = $this->_serviceWorkloadForum->getForumActiveStudents($tutorIDs, $subjectIDs); //--активные студенты			
		$allViolation = $this->_serviceWorkloadForum->getTimeForumViolation($tutorIDs); //--все просрочки по форуму, которые не зафиксированы в таблице просрочек.
		//pr($res_students);
		//pr($allViolation);
		$total_violations = array();			
		if($res_students){
			foreach($res_students as $s){
				$total_seconds = 0;
				$active_students = 0;
				if(!isset($total_violations[$s['tutor_id']][$s['subject_id']][$s['forum_id']])){ //--если еще не обрабатывали этот форум
					
					if(isset($allViolation[$s['tutor_id']][$s['forum_id']])){												
						$total_seconds = $allViolation[$s['tutor_id']][$s['forum_id']];												
					}						
					if(isset($allViolationDB[$s['tutor_id']][$s['subject_id']]['violation_time'])){ //--если есть просрочка в БД
						$total_seconds = $total_seconds + $allViolationDB[$s['tutor_id']][$s['subject_id']]['violation_time'];
					}	
					//pr($total_seconds);
					$total_violations[$s['tutor_id']][$s['subject_id']][$s['forum_id']]['seconds'] = $total_seconds;
					$total_violations[$s['tutor_id']][$s['subject_id']][$s['forum_id']]['intervals'] = $allViolationDB[$s['tutor_id']][$s['subject_id']]['intervals'];
				}										
				$total_violations[$s['tutor_id']][$s['subject_id']][$s['forum_id']]['active_students']++;					
			}
		}			
		
		$avgTime = array(); //--средне время просрочки на каждую сессию. Формат [tutor_id] => [subject_id] => T
		$intervalsSubject = array(); //--кол-во интервалов в сессиях
		foreach($total_violations as $tutor_id => $subject){ //--расчитываем среднее время вросрочки.				
			foreach($subject as $subj_id => $forum){															
				$sum = 0;
				$intervals = false;
				foreach($forum as $v){							
					$d = 0;
					$intervals = ($intervals) ? ($intervals) : ($v['intervals']);
					if($v['active_students'] >= 0){
						$d = ($v['seconds'] / $v['active_students']);
					}
					$sum = $sum + $d;
				}				
				$intervalsSubject[$subj_id] = $intervals;
				if($intervals > 0){	
					$avg = $sum/$intervals;	
					
				} else {
					//$avg = false;
					$avg = $sum; //--Если интервалы не зафиксированы в БД, то берем как (время просрочки / 1), 
					$intervalsSubject[$subj_id] = 1; //--т.к. если есть просрочка, то значит есть 1 не зафиксированный интервал.
					
				}				
				$avgTime[$tutor_id][$subj_id] = $avg;														
			}
		}	

		return array(
			'avgTime' => $avgTime,
			'intervals' => $intervalsSubject,
		);
	}
	
	
	/**
	 * массив сессий с кол-м уроков с типом "задание на проверку"
	*/
	public function getSubjectParts($subjectIDs){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();			
		$select->from(
			array('subj' => 'Subjects'),
			array(				
				'subject_id' => 'subj.subid',				
				'count_lesson' => 'COUNT(l.SHEID)',				
			)
		);
		$select->join(
			array('l' => 'schedule'),
			'l.CID = subj.subid',
			array()
		);
		$select->group(array('subj.subid'));
		$select->where('l.typeID = ?', HM_Event_EventModel::TYPE_TASK); //--задание на проверку		
		$select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
		
		if($subjectIDs){
			$subjectIDs = (array)$subjectIDs;
			if(count($subjectIDs)){
				$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));				
			}
		}		
		$res = $select->query()->fetchAll();		
		if(!$res){
			return false;
		}
		$data = array();
		foreach($res as $r){
			$data[$r['subject_id']] = $r['count_lesson'];
		}
		return $data;		
	}
	/*
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'students'   => $this->_students,                
            ),
            self::CACHE_NAME
        );
    }
	*/
	/*
	public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_students = $actions['students'];            
            return true;
        }
        return false;
    }
	*/
	/*
	public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }
	*/
	
	
	
}