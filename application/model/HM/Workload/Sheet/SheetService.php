<?php
/**
 * содержит методы по работе с блоком "Предоставление ведомости и мотивированное заключение"
*/
class HM_Workload_Sheet_SheetService extends HM_Workload_WorkloadService implements Es_Entity_Trigger
{	
	protected $_senderUsers = array(); //--храним id студентов, которым надо отослать сообщение и которым уже отправили.
	
	
	/**
	 * возвращает пользователей, которые находятся в подчинении у наблюдателя в оргструктуре. А также на одном уровне с ним.
	 * @return array(MIDs)
	*/
	public function getOrgstructurePersons($MID){		
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
			return false;
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
				'so.mid'
			)
		);
		$select->where(
			$this->_serviceOrgstructure->quoteInto(
				$criteria,
				$criteriaValues
			)
		);		
		//$select->where('so.mid != ?', $MID);
		$select->where('so.mid > ?', 0);
		$res = $select->query()->fetchAll();
		if(!$res){
			return false;
		}
		return $res;		
	}

	/**
	 * Делает выборку сессий по заданным id тьюторов.
	 * @return array
	*/
	public function getSubjectList($userIDs){
		$userIDs = (array) $userIDs;		
		if(empty($userIDs)){
			return false;
		}
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		
		$subUsers = $this->_serviceSubject->getSelect();
		$subUsers->from(array('Students'),	array('MID','CID'));
		
		$subGrad = $this->_serviceSubject->getSelect();
		$subGrad->from(array('graduated'),	array('MID','CID'));
		
		$subUSelect = $this->_serviceSubject->getSelect();
		$subUSelect->union(array($subUsers, $subGrad));
		
		$sub_select = $this->_serviceSubject->getSelect(); //--последний закрепленный тьютор
		$sub_select->from(
			array('t' => 'Tutors'),
			array(				
				'CID' => 't.CID',								
				'max_tid' => 'MAX(t.TID)',
			)
		);
		$sub_select->group(array('t.CID'));
		$sub_select->where('t.CID > 0');		
		
		$select = $this->_serviceSubject->getSelect();
		$select->from(
			array('subj' => 'subjects'),
			array(				
				'subid' => 'subj.subid',				
				'name' => 'subj.name',				
				'begin' => 'subj.begin',				
				'end' => 'subj.end',				
				'time_ended_debt' => 'subj.time_ended_debt',				
				'isSheetPassed' => 'subj.isSheetPassed',				
				'tutors' => new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic) )"),
				'last_tutor_id' => 't.MID',
			)
		);
		$select->join(
			array('t' => 'Tutors'),
            't.CID = subj.subid',
            array()
        );
		$select->join(
			array('subt' => $sub_select),
            'subt.max_tid = t.TID',
            array()
        );
		$select->join(
			array('p' => 'People'),
            'p.MID = t.MID',
            array()
        );

		$select->join( //--берем только те сессии, на которые назначены студенты или в прошедшем
			array('s' => $subUSelect),
			's.CID = subj.subid AND s.MID > 0',
			array()
		);
				
		$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);		
		$select->where('subj.time_ended_debt IS NULL'); //--не долг
		$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
		//$select->where('subj.end >= ?', date('Y-m-d 00:00:00',time())); //--не закончившиеся сессии
		$select->where(
			$this->_serviceSubject->quoteInto(
				't.MID IN (?)',
				$userIDs
			)
		);
		$select->group(array('subj.subid', 'subj.name', 'subj.begin', 'subj.end', 'subj.time_ended_debt', 'subj.isSheetPassed', 't.MID'));
		$select->order('subj.name ASC');
		$res = $select->query()->fetchAll();
		
		if(!$res){
			return false;
		}
		return $res;
	}
	
	/**
	 * Делает выборку сессий, по которым ведомость не передана. По заданным id тьюторов.
	 * @return array
	*/
	public function getOpenSubjectList($userIDs){
		try {
			$userIDs = (array) $userIDs;		
			if(empty($userIDs)){
				return false;
			}
			if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
			
			#$year = date('Y',time());
			#$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
			
			
			$subUsers = $this->_serviceSubject->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
			
			$subGrad = $this->_serviceSubject->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
			
			$subUSelect = $this->_serviceSubject->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			
			$select = $this->_serviceSubject->getSelect();
			$select->from(
				array('subj' => 'subjects'),
				array(				
					'subid' => 'subj.subid',				
					'name' => 'subj.name',				
					'begin' => 'subj.begin',				
					'end' => 'subj.end',				
					'time_ended_debt' => 'subj.time_ended_debt',				
					'isSheetPassed' => 'subj.isSheetPassed',				
					'tutors' => new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic) )"),
				)
			);
			$select->join(
				array('t' => 'Tutors'),
				't.CID = subj.subid',
				array()
			);
			$select->join(
				array('p' => 'People'),
				'p.MID = t.MID',
				array()
			);

			$select->join( //--берем только те сессии, на которые назначены студенты или в прошедшем.
				array('s' => $subUSelect),
				's.CID = subj.subid AND s.MID > 0',
				array()
			);
					
			$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			
			
			#$select->where($this->_serviceSubject->quoteInto(array('(subj.begin >= ? AND subj.time_ended_debt IS NOT NULL) OR (subj.time_ended_debt IS NULL) '), array($year.'-09-01'))); #условие для нагрузки
			#$select->where($this->_serviceSubject->quoteInto('subj.end <= ?', date('Y-m-d',time()))); # сессии, которые на дату окончания периода завершились. Открытые не берем. Условие для нарушения сроков реагирования
			
			#$select->where('subj.time_ended_debt IS NULL'); //--не долг
			
			
			#$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			//$select->where('subj.end >= ?', date('Y-m-d 00:00:00',time())); //--не закончившиеся сессии		
			$select->where(
				$this->_serviceSubject->quoteInto(
					't.MID IN (?)',
					$userIDs
				)
			);
			$select->where('subj.isSheetPassed IS NULL');
			$select->group(array('subj.subid', 'subj.name', 'subj.begin', 'subj.end', 'subj.time_ended_debt', 'subj.isSheetPassed'));
			$select->order('subj.name ASC');
			$res = $select->query()->fetchAll();
			
			if(!$res){
				return false;
			}
			return $res;
			
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
	}
	
	
	
	/**
	 * Делает выборку сессий, по которым ведомость не передана. По заданным id тьюторов.
	 * @return array
	*/
	public function getCloseSubjectList($userIDs){

		$userIDs = (array) $userIDs;		
		if(empty($userIDs)){
			return false;
		}
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }
		
		#$year = date('Y',time());
		#$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
		
		$subUsers = $this->_serviceSubject->getSelect();
		$subUsers->from(array('Students'),	array('MID','CID'));
		
		$subGrad = $this->_serviceSubject->getSelect();
		$subGrad->from(array('graduated'),	array('MID','CID'));
		
		$subUSelect = $this->_serviceSubject->getSelect();
		$subUSelect->union(array($subUsers, $subGrad));
		
		$select = $this->_serviceSubject->getSelect();
		$select->from(
			array('subj' => 'subjects'),
			array(				
				'subid' => 'subj.subid',				
				'name' => 'subj.name',				
				'begin' => 'subj.begin',				
				'end' => 'subj.end',				
				'time_ended_debt' => 'subj.time_ended_debt',				
				'isSheetPassed' => 'subj.isSheetPassed',				
				'tutors' => new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic) )"),
			)
		);
		$select->join(
			array('t' => 'Tutors'),
            't.CID = subj.subid',
            array()
        );
		$select->join(
			array('p' => 'People'),
            'p.MID = t.MID',
            array()
        );	
		$select->join( //--берем только те сессии, на которые назначены студенты или в прошедшем
			array('s' => $subUSelect),
			's.CID = subj.subid AND s.MID > 0',
			array()
		);
		$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
		
		/*
		$select->join( //--берем только те сессии, на которые назначены студенты
			array('s' => 'Students'),
            's.CID = subj.subid AND MID > 0',
            array()
        );	
		*/
		
		

		
		$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);	
		
		#$select->where($this->_serviceSubject->quoteInto(array('(subj.begin >= ? AND subj.time_ended_debt IS NOT NULL) OR (subj.time_ended_debt IS NULL) '), array($year.'-09-01')));
		#$select->where($this->_serviceSubject->quoteInto('subj.end <= ?', date('Y-m-d',time()))); # сессии, которые на дату окончания периода завершились. Открытые не берем. Условие для нарушения сроков реагирования
		#$select->where('subj.time_ended_debt IS NULL'); //--не долг
		
		
		//$select->where('subj.end >= ?', date('Y-m-d 00:00:00',time())); //--не закончившиеся сессии		
		$select->where(
			$this->_serviceSubject->quoteInto(
				't.MID IN (?)',
				$userIDs
			)
		);
		$select->where('subj.isSheetPassed IS NOT NULL');
		$select->group(array('subj.subid', 'subj.name', 'subj.begin', 'subj.end', 'subj.time_ended_debt', 'subj.isSheetPassed'));
		$select->order('subj.name ASC');
		$res = $select->query()->fetchAll();
		
		if(!$res){
			return false;
		}		
		return $res;
	}
	
	/**
	 * Сохраняет время просрочки по предоставлению ведомости
	 * @return timestamp | false
	*/
	public function setSheetViolation($subjectID) {		
		
		if(!$subjectID){
			return false;
		}		
		$tutorIDs = $this->getTutors($subjectID);
		if(!$tutorIDs){
			return false;
		}		
		$type = HM_Workload_WorkloadModel::TYPE_SHEET_PASSED;
		
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		
		foreach($tutorIDs as $mid){						
			$subject = $this->_serviceSubject->getOne(
				$this->_serviceSubject->find($subjectID)
			);
			$endSubject = strtotime($subject->end);						
			if($endSubject){				
				$issetViolations = $this->getViolations($mid, $type, $subjectID);				
				if(!$issetViolations){									
					$seconds = $this->getViolationSeconds($endSubject);
					$data = array(
						'MID' => $mid,
						'subid' => $subjectID,					
						'type' => $type,
						'violation_time' => $seconds,													
					);
					$this->addViolations($data);					
				}				
			}			
		}		
	}
	
	/**
	 * Получаем id тьюторов, назначенных на курс
	 * @return array of MIDs
	*/
	public function getTutors($subjectID) {
		if(!$subjectID){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$select = $this->_serviceWorkload->getSelect();
		$select->from(
			array('u' => 'People'),
			array(				
				'MID' => 'u.MID',								
			)
		);
		$select->join(
			array('t' => 'Tutors'),
            't.MID = u.MID',
            array()
        );		
		$select->where('t.CID = ?',$subjectID);
		$res = $select->query()->fetchAll();
		if(!$res){
			return false;
		}
		
		$IDs = array();
		foreach($res as $i){
			$IDs[$i['MID']] = $i['MID'];
		}
		if(empty($IDs)){
			return false;
		}
		return $IDs;
	}
	
	/**
	 * Получаем слушателей курса
	 * @return array, key - MID, value - ball
	*/
	public function getListeners($subjectID) {
		if(!$subjectID){
			return false;
		}
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$select = $this->_serviceWorkload->getSelect();
		$select->from(
			array('u' => 'People'),
			array(				
				'MID' => 'u.MID',																			
				'mark' => 'cm.mark', //--если пусто, значит счяитать, что оценка неуд.							
			)
		);
		$select->join(
			array('s' => 'Students'),
            's.MID = u.MID',
            array()
        );
		$select->joinLeft(
			array('cm' => 'courses_marks'),
            'cm.mid = s.MID AND cm.cid = s.CID',
            array()
        );		
		$select->where('s.CID = ?',$subjectID);		
		$res = $select->query()->fetchAll();
		if(!$res){
			return false;
		}
		$data = array();
		foreach($res as $i){
			$data[$i['MID']] = ($i['mark']) ? ($i['mark']) : (0);
		}
		return $data;		
	}
	
	/**
	 * Отсылаем мотивированное заключение всем слушателям курса внутри личного кабинета.
	 * @return boolean
	 * не работает отправка от пользователя = 0, т.е. как системное уведомление.
	*/
	public function sendMotivationMessages($subjectId){
		if(!$subjectId){
			return false;
		}	
		$usersIDs = $this->getListeners($subjectId);
		if(!$usersIDs || empty($usersIDs)){
			return false;
		}	

		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}		
		$item = $this->_serviceSubject->getOne($this->_serviceSubject->find($subjectId));
		
		foreach($usersIDs as $MID => $ball){
			$this->_senderUsers = array($MID);
			//$ball = $MID - 21500 + 35;				
			
			$template = HM_Workload_WorkloadModel::getMessageTemplate($ball);
			$item->template = $template;
			$item->ball = $ball;
			
			try { //--иначе не работает для тьюторов/преподавателей. Нет шаблона .tpl для сообщений на почту?
				$this->getService('EventDispatcher')->notify(
					new sfEvent($this, __CLASS__.'::esPushTrigger', array('item' => $item))
				);			
			} catch (Exception $e) { 
				return false;
			}											
		}		
		return true;		
	}
	
	
	public function createEvent(HM_model_Abstract $model) {
		
        $event = $this->getService('ESFactory')->newEvent($model, array(
           // 'date', 'message' //--данные полей, которые дополнительно будет созранены в таблице событий es_events
        ), $this);		
        return $event; 
    }
	
	/**
	 * Кому будет отсылаться уведомление
	 * Переопределяем ф-цию из класса Event в es
	 * $id - id PK записи  модели из переменной $item	 
	*/
	public function getRelatedUserList($id) {				
		return $this->_senderUsers;			
    }
	
	
	/**
	 * Создаем событие.
	 * Переопределяем ф-цию из класса Event или Trigger.
	*/
	public function triggerPushCallback() {	
	
        return function($ev) {			
            $params = $ev->getParameters();
            $subject = $params['item'];
			
            $service = $ev->getSubject();
            $event = $service->createEvent($subject);
          
			$event->setParam('template', $subject->template);
			$event->setParam('ball', $subject->ball);
			
			$event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_MOTIVATION_MESSAGE);
			
            //--поиск курса с таким же типом, чтобы сгруппировать. Если курса нет, тогда создает новую запиьс в es_event_groups и название курса берет из $eventGroup->setData 'course_name' => 'Название курса',
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$subject->subid
            );
			
            $eventGroup->setData(json_encode(
                array(                    
					'course_name' => $subject->name,
					'course_id' => $subject->subid,
                )
            ));
			
            $event->setGroup($eventGroup);           
			
            $esService = $service->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }
	
	/**
	 * Получаем текст мотивированного заключения. 
	 * Тип шаблона и оценка хранится в уведомлении.
	*/
	public function getMotivationMessage($subjectId){
		if(!$subjectId){
			return false;
		}
		$user = $this->getService('User')->getCurrentUser();
		
		$select = $this->getService('Workload')->getSelect();
		$select->from(
			array('ev' => 'es_events'),
			array(				
				'description' => 'ev.description',																							
			)
		);
		$select->join(
			array('evu' => 'es_event_users'),
            'evu.event_id = ev.event_id',
            array()
        );		
		$select->where('evu.user_id = ?',$user->MID);		
		$select->where('ev.event_type_id = ?', Es_Entity_AbstractEvent::EVENT_TYPE_MOTIVATION_MESSAGE);				
		$res = $select->query()->fetchAll();
		
		$data = false;
		foreach($res as $i){
			$obj = json_decode($i['description']);			
			if($obj->subjectId == $subjectId && $obj->template && !$data){
				$msg = HM_Workload_WorkloadModel::getMessageText($obj->template);
				if($msg){
					$msg = str_replace('[USER_NAME]', $user->FirstName.' '.$user->Patronymic,$msg);					
					$data = $msg;
					break;
				} 
			}			
		}
		if(!$data){
			return false;
		}		
		return $data;
	}
	
	
	/*
	 * находит T минуя таблицу просрочек. Данные берет все из БД из разных таблиц.
	 * $date_fix time, format: Y-m-d
	*/
	
	public function getT($tutor_id, $subject_id, $date_fix){			
		$T_message = $this->getAvgT_Message($tutor_id, $subject_id, $date_fix); //--просрочка по письму - берем дату из таблицы просрочек, 
		$T_subject = $this->getAvgT_Subject($tutor_id, $subject_id, $date_fix); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
		$T_forum = 	 $this->getAvgT_Forum($tutor_id, $subject_id, $date_fix); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
		$T_vedomost =$this->getAvgT_Vedomost($tutor_id, $subject_id, $date_fix); //--просрочка по предоставлению ведомости
		//pr($T_message);
		//pr($T_subject);
		//pr($T_forum);
		//pr($T_vedomost);
		return ( ($T_message + $T_subject + $T_forum + $T_vedomost) / 4 );		
	}
	
	/**
	 * Находим среднее вермя просрочки от момента отправки письма до указанной даты.
	 *$date_end format Y-m-d
	 *
	*/

	public function getAvgT_Message($tutor_id, $subject_id, $date_end_period, $date_begin_period = false){
		if(HM_Workload_WorkloadModel::DISABLE_VIOLATION_WELCOME_MESSAGE === true){ //--не учитываем просрочку по прив. письму.
			return 0;
		}

		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		
		$dateBegin = $this->_serviceWorkload->getDateBegin($tutor_id, $subject_id); //=-дата назначения тьютора или дата начала сессии
		if($date_begin_period){
			if($dateBegin < strtotime($date_begin_period) ){ //--если период расчета не включает дату начала сессии или назначения тьютора.
				$dateBegin = strtotime($date_begin_period); 
			}
		}
		
		$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;			
		$vi = $this->_serviceWorkload->getViolations($tutor_id, $type, $subject_id); //--находим время фиксации письма.
		if($vi){			
			if(strtotime($vi->current()->date_update) < strtotime($date_end_period)){ //--если мы формируем отчет на период до даты фиксации прочрочки в БД
				$viTimeEnd = strtotime($vi->current()->date_update);				
			} else {
				$viTimeEnd = ($date_end_period) ? (strtotime($date_end_period)) : (time()); //--дата до которой будет производиться расчет просрочки.
			}			
		} else {										
			$this->_serviceWorkload->setCurrentWelcomeViolation($tutor_id, $subject_id, $type);						
			$viTimeEnd = ($date_end_period) ? (strtotime($date_end_period)) : (time()); //--дата до которой будет производиться расчет просрочки.
		}				
		$delta = $this->_serviceWorkload->getViolationSeconds($dateBegin, $viTimeEnd);
		$students = $this->_serviceWorkload->getCountStudents($subject_id);
		
		$avg = 0;
		if($students > 0){
			$avg = $delta / $students;
		}				
		return $avg;	
	}
	
	
	/**
	 * Находим среднее вермя просрочки в сессии от момента назначения на сессию до указанной даты.
	 * $getAvtiveStudents - если true, то ф-ция вернет массив вида array(
																			'avg' => '' //--среднее время просрочки
																			'activeStudents' => '' //--активыне студенты
																			'count_violations' => '' //--кол-во нарушений.
	 * иначе строку, значение переменной avg
	 * этот флаг для совместимости со старыми отчетами. Потом они будут не нужны и можно бутет это тфлаг удалить
	*/
	public function getAvgT_Subject($tutor_id, $subject_id, $date_end, $date_begin = false, $getAvtiveStudents = false){
		
		#if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		try {
		
		$lessonIDs = $this->getService('Lesson')->getActiveLessonsOnSubjectId($subject_id);
		if(!count($lessonIDs)){ # нет уроков.
			return false; # а точно это возвращать?
		} 
		
		$graduatedIDs 	= $this->getService('Graduated')->fetchAll($this->getService('Graduated')->quoteInto('CID = ?', $subject_id))->getList('MID');
		$studentIDs  	= $this->getService('Student')->fetchAll($this->getService('Student')->quoteInto('CID = ?', $subject_id))->getList('MID');
		$studentIDs 	= $graduatedIDs + $studentIDs; # при учете, что ключи массива - это id студента. Иначе надо использовать array_merge
		
		
		$endStr = '';
		$endValue = '';
		if($date_end){	
			$endStr = ' AND date <= ?';
			$endValue = $date_end.' 23:59:59';						
		}
		
		$beginStr = '';
		$beginValue = '';
		if($date_begin){			
			$beginStr = ' AND date >= ?';
			$beginValue = $date_begin.' 00:00:00';			
		}
		
		$messaages = $this->getService('Interview')->fetchAll($this->getService('Interview')->quoteInto(
						array('( user_id IN (?)', ' OR user_id IN (?) )', ' AND lesson_id IN (?) ', $beginStr, $endStr),
						array($studentIDs, 		array($tutor_id, 0),	$lessonIDs,  $beginValue, $endValue)			
					), array('interview_id'));
		
		if(!count($messaages)){ # нет сообщений.
			return false; # а точно это возвращать?
		}
		
		$newData = array();
		foreach($messaages as $m){
			$newData[] = array(
				'lesson_id' 	=> $m->lesson_id,
				'student_id' 	=> (in_array($m->user_id, $studentIDs)) ? ($m->user_id) : ($m->to_whom),
				'message_id' 	=> $m->interview_id,
				'author_id' 	=> $m->user_id,
				'to_whom' 		=> $m->to_whom,				
				'message' 		=> $m->message,				
				'date' 			=> $m->date,				
			);
		}			

		$result = $this->calculatePeriods($subject_id, $tutor_id, $newData, $date_end);
		
		if($getAvtiveStudents){
			return $result;
		}
		return $result['avg'];
		
		} catch (Exception $e) {
			#echo 'Выброшено исключение: ',  $e->getMessage(), "\n";			
		}
	}
	
	
	/**
	 * расчитывает просрочки среди указанных сообщений
	 * return array
	*/
	public function calculatePeriods($subject_id, $tutor_id, $messages, $date_end){
		$data = array();
		$avg = 0;
		if($messages){	
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			foreach($messages as $i){				
				if($i['student_id'] == $i['author_id'] || $i['student_id'] == $i['to_whom']) { //--отбираем только сообщения, которые от студента или для студента. 
					if((strtotime($i['date']) <=  strtotime($date_end.' 23:59:59')) || !$date_end){ //если есть ограничение по вермени или ограничения нет вовсе.
						if(!isset($data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'])){							
							//--присваиваем данные начала нового интервала
							$data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'] = 	$i['author_id'];
							$data[$i['lesson_id']][$i['student_id']]['date_begin'] = $i['date'];								
						} else {
							if($i['student_id'] == $i['author_id']){												
								if($data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'] != $i['author_id']){ //--начало нового оинтервала.														
							
									//--присваиваем данные начала нового интервала
									$data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'] = $i['author_id'];
									$data[$i['lesson_id']][$i['student_id']]['date_begin'] = $i['date']; //--начало интервала
								}
							} else { //--это не студент. Это конец интервала. Надо расчитать интервал																				
								if( $data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'] == $i['student_id'] ) {//--Если пред. сообщение было от студента								
									$delta = $this->_serviceWorkload->getViolationSeconds(strtotime($data[$i['lesson_id']][$i['student_id']]['date_begin']), strtotime($i['date']));								
									if($delta > 0){
										$data[$i['lesson_id']][$i['student_id']]['intervals']++;
										$data[$i['lesson_id']][$i['student_id']]['seconds'] = $data[$i['lesson_id']][$i['student_id']]['seconds'] + $delta;
									}
								}
								
								//--присваиваем данные начала нового интервала
								$data[$i['lesson_id']][$i['student_id']]['last_msg_user_id'] = $i['author_id'];
								$data[$i['lesson_id']][$i['student_id']]['date_begin'] = $i['date'];
							}				
						}		
					}	
				}	
			}	

			//--Закрываем не закрытые периоды.
			if(count($data) > 0){ //--кол во разделов в сессии
				foreach($data as $k => $lessons){
					if(count($lessons)){
						foreach($lessons as  $k2 => $student){					
							if($student['last_msg_user_id'] != $tutor_id && $student['last_msg_user_id'] != 0){ //--если интервал открыт, т.е. последний ответ от студента. тьютор не ответил, значит берем дату fix						
								$dateEnd = ($date_end) ? (strtotime($date_end.' 23:59:59')) : (time());									
								$delta = $this->_serviceWorkload->getViolationSeconds(strtotime($student['date_begin']), $dateEnd);
								if($delta > 0){
									$data[$k][$k2]['seconds'] = $data[$k][$k2]['seconds'] + $delta;
									$data[$k][$k2]['intervals']++;						
								}
							}
						}
					}
				}
			
				$avg = 0;				
				$links = array(); //--данные для формирования ссылок на просрочку в урок								
				$count_violations = 0; //-кол-во просрочек. или же это интервалы расчета
				$totalSeconds = 0;
				foreach($data as $k => $lessons){
					$sumSeconds = 0;
					
					//--суммируем просрочку по всем урокам и делим на активных студентов.
					if(count($lessons) > 0){//--активные студенты в занятии
						$activeStudents = 0;						
						foreach($lessons as  $k2 => $student){							
							$sumSeconds = $sumSeconds + $student['seconds'];
							if(isset($student['intervals']) && $student['intervals'] > 0){ 
								$activeStudents++;
								$count_violations = $count_violations + $student['intervals'];
								$links[] = array(
									'subject_id' 	=> $subject_id,
									'lesson_id' 	=> $k,
									'student_id' 	=> $k2,
									'author_id' 	=> $k2,
								);
							}														
						}						
						if($activeStudents > 0){													
							$totalSeconds = $totalSeconds + $sumSeconds / $activeStudents;															
						}						
					}
				}	
				$avg = $totalSeconds / count($data);							
			}
		}
		return array(
			'avg' => $avg,
			'activeStudents' 	=> $activeStudents,
			'count_violations' 	=> $count_violations,
			'links' 			=> $links,
		);
	}
	
	
	
	
	/**
	 * Находим среднее вермя просрочки на форуме от момента назначения на сессию до указанной даты.
	 *
	*/
	public function getAvgT_Forum($tutor_id, $subject_id, $date_end, $date_begin = false, $getAvtiveStudents = false){				
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		try {			
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
						//'message' => 'MAX(m.text)',				
						'date' => 'm.created',				
						'section_id' => 'm.section_id',				
					)
				);
				
				$criteria = $this->_serviceWorkload->quoteInto('s.MID = m.user_id AND s.CID = ?', $subject_id); 
				/*
				if(count($this->_serviceWorkload->_students) < 1)	{	$this->_serviceWorkload->restoreFromCache();	 }				
				if(count($this->_serviceWorkload->_students))		{	
					$criteria .= ' AND '.$this->_serviceWorkload->quoteInto('s.MID IN (?)',$this->_serviceWorkload->_students); //--если делать обычным where, то не попадут сообщения тьюторов.
				}
				*/
				
				$select->joinLeft(
					array('s' => $subUSelect),
					$criteria,
					array()
				);						
				$select->where('m.forum_id = ?',$forum->forum_id);	
					
				
				if($date_end){			
					$select->where('m.created <= ?', $date_end.' 23:59:59'); 
				}
				if($date_begin){			
					$select->where('m.created >= ?', $date_begin.' 00:00:00'); 
				}
				
				
				
				$select->group(array('s.MID', 'm.message_id', 'm.user_id', 'm.created', 'm.section_id'));								
				$select->order('m.created');								
				$res = $select->query()->fetchAll();				
				$data = array();				
				if($res){
					$activeStudents = array(); //--активные студенты					
					foreach($res as $i){
						if($i['student_id'] > 0){
							$activeStudents[$i['student_id']] = $i['student_id'];
						}						
						if(!isset($data['date_begin'])){							
							$data['date_begin'] 		= $i['date'];
							$data['student_id'] 		= $i['student_id'];
							$data['author_id'] 			= $i['author_id'];							
							$data['section_id'] 		= $i['section_id'];							
							$data['message_id'] 		= $i['message_id'];							
							continue;
						}
						
						//-_пред не студент и текущий не студент						
						if(!( 							
							$data['student_id'] == $data['author_id'] && $data['student_id'] != $tutor_id //--пред студент
							&&
							$i['student_id'] == $i['author_id'] && $i['student_id'] != $tutor_id //--текущий студент
							)
						){							
							if($i['student_id'] != $i['author_id'] && $data['student_id'] == $data['author_id']) { //--текущий не студент, т.е. тьютор, а пред. студент.								
								$delta = $this->_serviceWorkload->getViolationSeconds(strtotime($data['date_begin']), strtotime($i['date']));
								if($delta > 0){								
									$data['seconds'] = $data['seconds'] + $delta;
									$data['intervals']++;																		
									$links[] = array('subject_id' => $subject_id, 'section_id' => $data['section_id'], 'message_id' => $data['message_id'], 'author_id' => $data['author_id']); 									
								}
							}												
							$data['date_begin'] 		= $i['date'];
							$data['student_id'] 		= $i['student_id'];
							$data['author_id'] 			= $i['author_id'];
							$data['section_id'] 		= $i['section_id'];							
							$data['message_id'] 		= $i['message_id'];							
						}
					}						
					
					if($data['student_id'] == $data['author_id'] && $data['student_id'] != $tutor_id){  //--Если последний не тьютор отвечал. Закрываем период.
						$dateEnd = ($date_end) ? (strtotime($date_end.' 23:59:59')) : (time());	
						$delta = $this->_serviceWorkload->getViolationSeconds(strtotime($data['date_begin']), $dateEnd);
						if($delta > 0){								
							$data['seconds'] = $data['seconds'] + $delta;
							$data['intervals']++;
							$links[] = array('subject_id' => $subject_id, 'section_id' => $data['section_id'], 'message_id' => $data['message_id'], 'author_id' => $data['author_id']); 									
						}
					}
					
					$avg = 0; //-среднее время просрочки
					$count_violations = 0; //--кол интервалов
					if(count($activeStudents) > 0 && $data['intervals'] > 0) {
						$avg = ( $data['seconds'] / count($activeStudents) ) / $data['intervals'];
						$count_violations = $data['intervals'];
					}					
					if($getAvtiveStudents){						
						return array(
							'avg' 				=> $avg,
							'activeStudents' 	=> count($activeStudents),
							'count_violations' 	=> $count_violations,
							'links' 			=> $links,
						);
					}					
					return $avg;
				}
			}			
		} catch (Exception $e) {			
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";			
		}
		return 0;
	}
	
	
	/**
	 * Находим среднее вермя просрочки по предоставлению ведомости. 
	 *
	*/
	public function getAvgT_Vedomost($tutor_id, $subject_id, $date_end, $date_begin = false){
		if(HM_Workload_WorkloadModel::DISABLE_VIOLATION_SHEET_PASSED === true){ //--не учитываем просрочку по ведомости.
			return 0;
		}
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload'); }
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }

		$type = HM_Workload_WorkloadModel::TYPE_SHEET_PASSED;
		$vi = $this->_serviceWorkload->getViolations($tutor_id, $type, $subject_id);
		$date_vedomost = false;
		if($vi){
			foreach($vi as $i){				
				$date_vedomost = $i->date_update;
				//--получаем дату предоставления ведомости.
				//return $i->violation_time;
			}
		}			
		$subject = $this->_serviceSubject->getById($subject_id);
		
		if($subject){
			if($date_vedomost === false){ //--если нет записи о предоставлении ведомости, то берем дату окончания сессии.
				$date_vedomost = $subject->end;
			}
			if(strtotime($date_begin) < strtotime($date_vedomost)){ //--если дата ведомости больше, то берем ее, т.к. если иначе, то ведомости еще не было на тот момент. И пока идет сессия, то и просрочки нет.
				$date_begin = $date_vedomost;
			}
			$delta = $this->_serviceWorkload->getViolationSeconds(strtotime($date_begin), strtotime($date_end));
			
			if($delta < 0){
				$delta = 0;
			}
			
			$countStudents = $this->_serviceWorkload->getCountStudents($subject_id);				
			if($countStudents > 0){
				return ($delta / $countStudents);
			}
		}		
		return 0;
	}
	
	
	
	
	
}