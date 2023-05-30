<?php

class HM_Interview_InterviewService extends HM_Service_Abstract implements Es_Entity_Trigger
{
    /**
     * Добавлены методы сервиса.
     *
     * @author Artem Smirnov
     * @date 19.02.2013
     */
	 
	private $_lastMessageList = array();


    public function insert($data, $unsetNull = null) {
		
        $item = parent::insert($data, $unsetNull);
	
        if (intval($item->type) !== HM_Interview_InterviewModel::MESSAGE_TYPE_TASK &&
            intval($item->type) !== HM_Interview_InterviewModel::MESSAGE_TYPE_BALL
        ) {	
            try { //--иначе не работает для тьюторов/преподавателей.								
					$this->getService('EventDispatcher')->notify(
						new sfEvent($this, __CLASS__.'::esPushTrigger', array('item' => $item))
					);						
			} catch (Exception $e) { 
			}
        }		
        return $item;
    }

    public function createEvent(HM_model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'date', 'message'
        ), $this);
        return $event; 
    }

    public function getRelatedUserList($id) {		
        $isUser  = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER);
		$message = $this->getById($id);
		
		
		
		if($isUser){ # отправка всем тюторам, которым доступно это занятие.
					 #+ только тем тьюторам, которым доступен данный студент
			# Если тьютор выполнил какое-то действие, то другим тьюторам не шлем.
			$lesson = $this->getService('Lesson')->getLesson($message->lesson_id);
			if(!empty($lesson->CID)){				
				$tutors = $this->getService('User')->fetchAllJoinInner('Tutor', $this->quoteInto(array('Tutor.CID = ?'), array($lesson->CID)));
				if(!empty($tutors)){
					$serviceLesson = $this->getService('Lesson');
					
					# оставляем тьюторов, которые доступны данному студенту.
					$availableTutors = $this->getService('Subject')->filterAvailableTutors($tutors, $message->user_id, $lesson->CID);
					
					foreach($availableTutors as $tutor_id => $name){
						# фильтрация по занятию.
						if($serviceLesson->isAvailable($tutor_id, $message->lesson_id, $lesson->CID)){
							$result[$tutor_id] = $tutor_id;
						}						
					}					
				}				
			}
		} else { # иначе только студенту из поля to_whom
			$result[$message->to_whom] = $message->to_whom;			
		}
		
		/*
		$select= Zend_Db_Table_Abstract::getDefaultAdapter()->select()            
            ->from(array('t' => 'Tutors'), array('tid' => 't.MID','to_whom'=>'i.to_whom', 'user_id' => 'i.user_id'))
            ->join(array('s' => 'subjects'), 's.subid = t.CID')
            ->join(array('l' => 'schedule'), 'l.CID = s.subid')
            ->join(array('i' => 'interview'), 'i.lesson_id = l.SHEID')
            ->where('i.interview_id = ?', $id, 'INTEGER');
        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $result = array();
		$isUser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER);
		$serviceSubject = $this->getService('Subject');
        # шлем автору и всем доступных тьюторам.
		foreach ($rows as $item) {			            
			if (!in_array($item['to_whom'], $result) && $item['to_whom'] ) {
                $result[] = $item['to_whom'];
            }			
		
			$studentIDs = $serviceSubject->getAvailableStudents(intval($item['tid']), $item['subid']);
			$studentID = ($isUser)?($item['user_id']):($item['to_whom']); # отправитель или получаетль - студент
			if(!$studentIDs || in_array($studentID, $studentIDs)){ # нет ограничений в назначении или назначение есть и текущий студент доступен тьютору
				$result[] = intval($item['tid']);
			}							
		}
		*/

        return $result;
    }

    public function triggerPushCallback() {		
        return function($ev) {
            $params = $ev->getParameters();
            $interMessage = $params['item'];
            $service = $ev->getSubject();
            $event = $service->createEvent($interMessage);
            
            $user = $service->getService('User')->getById(intval($interMessage->user_id)); 
            
            $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
            $select->from(array('i' => 'interview'),array())
                ->join(array('sh' => 'schedule'), 'sh.SHEID = i.lesson_id', array('lesson_name' => 'sh.title', 'lesson_id' => 'sh.SHEID'))
                ->join(array('s' => 'subjects'), 's.subid = sh.CID', array('course_name' => 's.name', 'cid' => 's.subid'))
                ->where('i.interview_id = ?', $interMessage->getPrimaryKey(), 'INTEGER');
            $stmt = $select->query();
            $stmt->execute();
            $row = $stmt->fetch();
            $event->setParam('lesson_name', $row['lesson_name']);
            $event->setParam('course_name', $row['course_name']);
            $event->setParam('user_name', $user->getName());
            $event->setParam('user_id', $user->getPrimaryKey());
            
            $userAvatar = '/'.ltrim($user->getPhoto(), '/');
            $event->setParam('user_avatar', $userAvatar);
            
            $event->setParam('course_id', (int)$row['cid']);
            $event->setParam('lesson_id', (int)$row['lesson_id']);
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_TASK_ACTION);
            
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$row['cid']
            );
            $eventGroup->setData(json_encode(
                array(
                    'course_name' => $event->getParam('course_name'),
                    'course_id' => $event->getParam('course_id')
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
     * Выбирает все задания для текущего занятия.
     *
     * @param $schedule_id
     * @return array
     */
    public function getAllBySchedule($schedule_id)
    {
        $select = $this->getSelect();
        $select->from(
            array('i' => 'interview'),
            array(
                'i.interview_hash',
                'i.to_whom',
                'i.user_id',
                'to_whom_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'user_id_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic)"),
                'i.date',
                'i.type'
            )
        );
        $select->joinLeft(
            array('p' => 'People'),
            'p.MID = i.to_whom',
            array()
        );
        $select->joinLeft(
            array('p2' => 'People'),
            'p2.MID = i.user_id',
            array()
        );
        $select->where('i.lesson_id = ?', $schedule_id);
        return $select->getAdapter()->fetchAll($select);
    }

    public function getAssignedUsersByInterview($interview_id)
    {
        $select = $this->getSelect();
        $select->from(
            array('scid' => 'scheduleID'),
            array(
                'scid.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")
            )
        );
        $select->joinLeft(
            array('p' => 'People'),
            'p.MID = scid.MID',
            array()
        );
        $select->where('scid.SHEID = ?', $interview_id);
        $select->where('scid.MID != 0');
        return $select->getAdapter()->fetchAll($select);
    }

    public function getCompletedInterviews($user_id)
    {

    }
    
    public function onDeleteQuestion($questionId)
    {
        $interviews = $this->fetchAll(array(
            'question_id = ?' => $questionId,
            'type = ?' => HM_Interview_InterviewModel::MESSAGE_TYPE_TASK,
        ));
        foreach ($interviews as $interview) {
            $this->getService('LessonAssign')->deleteBy($this->getService('LessonAssign')->quoteInto(array(
                'SHEID = ? AND ',        
                'MID = ?',        
            ), array(
                $interview->lesson_id,
                $interview->to_whom,
            )));
        }
        $this->deleteBy(array('question_id = ?' => $questionId)); // удаляем сообщения всех типов, не только постановку задачи
    }
	
	
	/**
	 * return boolean
	 * для студента:
	 * 	true - показывать ли форму прикрепления ответа за задание.
	 * 	false - ничего
	 * для преподавателя и тьютора:
	 * 	true - показывать ли форму выставления оценки	 
	 * 	false - форма изменения оценки.	 
	*/
	public function isShowBaseForm($userID, $lessonID, $subjectID){
		
		if(!$userID || !$lessonID || !$subjectID){
			return false;
		}		
		$select = $this->getSelect();
        $select->from(
            array('scid' => 'scheduleID'),
            array(                
				'scid.attempts',                                
            )
        );        
        $select->where('scid.SHEID = ?', $lessonID);
        $select->where('scid.MID = ?', $userID);
        $select->where('scid.attempts IS NOT NULL');
        $select->where('scid.attempts > 0');		
        $attempts = $select->getAdapter()->fetchRow($select);		
		
		if(!$attempts){		
			$attemptsCount = 0;			
		} else {
			$attemptsCount = (int)$attempts['attempts'];			
		}		
		
		$countMesTypeBall = $this->getSelect();
        $countMesTypeBall->from(
            array('i' => 'interview'),
            array(
                'count' => 'COUNT(i.interview_id)',                
            )
        ); 						
		$countMesTypeBall->where('i.lesson_id = ?', $lessonID);
		$countMesTypeBall->where('i.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		$countMesTypeBall->where('i.to_whom = ?', $userID);		
		$countMesTypeBall = $countMesTypeBall->getAdapter()->fetchRow($countMesTypeBall);
		
		if($countMesTypeBall) {
			$mesTypeBall = (int)$countMesTypeBall['count'];
		} else {
			$mesTypeBall = 0;
		}
		
		$countAttempts = ($attemptsCount + 1) - $mesTypeBall;
				
		if($countAttempts > 0){ //--есть еще попытки. Отображаем форму в уроке.
			return true;
		}	
		return false;		
	}
	
	
	
	public function getLastMessage($lession_id, $user_id){
		if(!$lession_id || !$user_id) { return false; }
		
		if(!array_key_exists($lession_id, $this->_lastMessageList)){
			$subSelect = $this->getSelect();
			$subSelect->from('interview', array(																	
				'lesson_id',
				'max_date' => 'MAX(date)',
			));
			$subSelect->where($this->quoteInto(array('(user_id = ?', ' OR to_whom = ?)'), array($user_id, $user_id)));
			#$subSelect->where('interview_hash > 0');
			$subSelect->group(array('lesson_id'));
			
			$select = $this->getSelect();
			$select->from(array('i' => 'interview'), array(
				'type'      => 'i.type',
				'lesson_id' => 'i.lesson_id',
			));
			$select->join(array('sub' => $subSelect), 'sub.max_date = i.date AND sub.lesson_id = i.lesson_id', array());	
			$select->where($this->quoteInto(array('(i.user_id = ?', ' OR i.to_whom = ?)'), array($user_id, $user_id )));
			$res = $select->query()->fetchAll();
			if($res){
				foreach($res as $item){
					$this->_lastMessageList[$item['lesson_id']] = $item['type'];
				}
			}			
		} 
		
		if(!array_key_exists($lession_id, $this->_lastMessageList)){
			return false;
		}
		
		# чтобы не ранушать логику. Т.к. по названию метода, он вазвращает сущность "сообщение", а не одно значение.
		return array('type' => $this->_lastMessageList[$lession_id]);
	}
	
	/**	 
	 * Есть ли сообщения от студента с для проверки.
	 * @return bool
	 *
	*/
	public function issetUserAnswer($user_id, $lesson_id){
		return $this->issetUserFile($user_id, $lesson_id);
		#return (boolean) $this->getOne($this->fetchAll($this->quoteInto(array('user_id = ?', ' AND lesson_id = ? ', ' AND type = ?') , array($user_id, $lesson_id, HM_Interview_InterviewModel::MESSAGE_TYPE_TEST)))); 
	}
	
	public function issetUserFile($user_id, $lesson_id){
		$select = $this->getSelect();
		$select->from(array('i' => 'interview'), array('file_count' => 'COUNT(f.file_id)') );	
		$select->join(array('f' => 'interview_files'), 'f.interview_id = i.interview_id', array());
		$select->where($this->quoteInto(array('i.lesson_id = ?', ' AND i.user_id = ?'), array($lesson_id, $user_id)));
		$select->limit(1);
		$obj = $select->query()->fetchObject();
		return (boolean) $obj->file_count;
	}
	
	/**
	 * Может ли тьютор выставить оценку без ответа студента.
	 * @user_id int
	 * @lesson_id int
	 * @return bool 	 
	*/
	public function isCanSetMark($user_id, $lesson_id){
		if(!$user_id || !$lesson_id){ return false; }
		$lesson = $this->getService('Lesson')->getLesson($lesson_id);		
		if(!$lesson) { return false; }				
		if($lesson->isCanMarkAlways == 1){ return true; }
		return $this->issetUserAnswer($user_id, $lesson_id);
	}
	
	
	
	public function getCountTutorMarks($tutor_id, $lesson_id, $subject_id = NULL){ # кол-во выставленных оценок тьютором
		$select = $this->getSelect();
		$select->from(array('i' => 'interview'), array('total' => 'MAX(interview_id)') );
		if(!empty($subject_id)){
			$select->join(array('s' => 'Students'), 's.MID = i.to_whom AND s.CID='.intval($subject_id), array());
		}
		$select->where('type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		$select->where('to_whom > ?', 0);
		$select->where('user_id = ?', (int)$tutor_id);
		$select->where('lesson_id = ?', (int)$lesson_id);
		$select->group(array('user_id', 'lesson_id', 'to_whom'));				
		$res = $select->query()->fetchAll();
		if(empty($res)){ return 0; }
		return count($res);
	}

	public function getById($interview_id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('interview_id = ?', $interview_id)));
    }	
	
	
	
	/**
	 * Занятия, в которых студент не может прикреплять задания
	 * @ $params - array(	lesson_id => count_attempts	)
	*/
	public function  getUnavailableAttachLessons($user_id, $params){
		
		if(empty($params)){ return false; }
		
		$select = $this->getSelect();
		$select->from(array('i' => 'interview'), array(
			'lesson_id' 	=> 'i.lesson_id',
			'count_balls'	=> 'COUNT(interview_id)',
			
		));
		$select->where('type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		$select->where('lesson_id IN (?)', array_keys($params));
		$select->where('to_whom = ?', $user_id);
		$select->group(array('lesson_id'));
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		
		# занятия, в которых студент не может прикрепить работу. 
		$data = array();
		
		foreach($res as $i){
			if(!key_exists($i['lesson_id'], $params)){ continue; }
			
			if(	intval($params[$i['lesson_id']]) < $i['count_balls']	){
				$data[$i['lesson_id']] = $i['lesson_id'];
			}
		}
		return $data;
	}
	
	
	/**
	 * получаем список студенто-занятий, в которых нет реакции преподавателя или студента (последнее сообщение не решение на проверку и не выставлена оценка.)
	 * Также исключаем занятия "Итоговый контроль". 
	*/
	public function getWithoutReactionLessons($subject_id, $student_ids = null){
		
		$lesson_ids = $this->getService('Lesson')->fetchAll($this->getService('Lesson')->quoteInto(array('CID = ? AND ', ' typeID = ? AND ', ' title NOT LIKE ? '),array($subject_id, HM_Event_EventModel::TYPE_TASK, '%Итоговый контроль%')))->getList('SHEID');	
		
		if(empty($lesson_ids)){ return false; }
		
		$fields[] = 'lesson_id IN (?)';
		$values[] = $lesson_ids;
		
		if(!empty($student_ids)){
			$fields[] = ' AND ( user_id IN (?)';
			$values[] = $student_ids;
			
			$fields[] = ' OR to_whom IN (?) )';
			$values[] = $student_ids;
		}
		
		$res = $this->fetchAll($this->quoteInto($fields, $values));
		if(empty($res)){ return false; }
		$last_messages = array();
		$exsist_mark   = array(); # в цепочке сообщений ранее уже была выставлена оценка. Такую цепочку не обрабатываем.
		foreach($res as $i){
			
			if($i->type == HM_Interview_InterviewModel::MESSAGE_TYPE_BALL){
				$exsist_mark[$i->lesson_id.'~'.$i->interview_hash] = 1;
			}
			
			if(isset($last_messages[$i->lesson_id.'~'.$i->interview_hash])){
				$date_old = strtotime($last_messages[$i->lesson_id.'~'.$i->interview_hash]->date);
				$date_new = strtotime($i->date);
				
				if($date_old < $date_new){
					$last_messages[$i->lesson_id.'~'.$i->interview_hash] = $i;
					continue;
				}
			}
			$last_messages[$i->lesson_id.'~'.$i->interview_hash] = $i;			
		}
		
		if(empty($last_messages)){ return false; }
		
		$data = array();
		foreach($last_messages as $key => $i){
			if(isset($exsist_mark[$key])){ continue; }
			if($i->type == HM_Interview_InterviewModel::MESSAGE_TYPE_BALL || $i->type == HM_Interview_InterviewModel::MESSAGE_TYPE_TEST){ continue; }
			
			if(
				HM_Interview_InterviewModel::MESSAGE_TYPE_TASK 		== $i->type ||
				HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER 	== $i->type ||
				HM_Interview_InterviewModel::MESSAGE_TYPE_CONDITION == $i->type ||
				HM_Interview_InterviewModel::MESSAGE_TYPE_BALL 		== $i->type ||
				HM_Interview_InterviewModel::MESSAGE_TYPE_EMPTY 	== $i->type
			){
				$student_id = $i->to_whom;				
			} else {				
				$student_id = $i->user_id;
			}
			
			$data[$student_id][$i->lesson_id] = $i->lesson_id;
		}
		return $data;		
	}
	
	
}
