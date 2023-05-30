<?php
class HM_Lesson_Assign_AssignService extends HM_Service_Abstract implements Es_Entity_Trigger
{

    protected $userLessonScoreSet = false;

    public function setUserScore($userId, $scheduleId, $score, $courseId = 0, $automatic = false){
        $lessonService = $this->getService('Lesson');
        if($scheduleId != 'total'){
            $subject = $lessonService->getSubjectByLesson($scheduleId);
            $factory = $this->getService('MarkStrategyFactory')->getStrategy($subject->getMarkType());
            $factory->setUserScore($userId, $scheduleId, $score, $courseId, true);
        } else {
            $this->getService('MarkWeightStrategy')->setUserScore($userId, $scheduleId, $score, $courseId, true);
        }
    }

    public function updateUserLessonScore($values) {
        $this->userLessonScoreSet = true;
        $result = $this->update($values);
        $this->getService('EventDispatcher')->notify(
            new sfEvent($this, __CLASS__."::esPushTrigger", array('lesson' => $result))
        );
        $this->userLessonScoreSet = false;
        return $result;
    }

    public function setUserComments($userId, $scheduleId, $comment, $courseId = 0){
        if ($scheduleId !='total') {
            $res = $this->updateWhere(array('comments' => $comment),array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
        } else {
            $one = $this->getOne($this->getService('SubjectMark')->fetchAll(array('mid = ?' => $userId, 'cid = ?' => $courseId)));
            if( $one ) {
                $one->comments = $comment;
                $this->getService('SubjectMark')->update($one->getValues());
            }
        }
      /*
        $one = $this->fetchAll(array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
        if(count($one) > 0){
            $this->update(array('SSID' => $one[0]->SSID, 'comments' => $comment));
      }*/
    }

    public function insert($data)
    {
        $data['V_STATUS'] = ($mark = HM_Subject_Mark_MarkModel::filterMark($data['V_STATUS'])) ? $mark : -1;
        $data['created'] = $data['updated'] = $this->getDateTime();
		$data['last_user_id'] = (int)$this->getService('User')->getCurrentUserId();
        return parent::insert($data);
    }

    public function update($data)
    {
        $data['V_STATUS'] = HM_Subject_Mark_MarkModel::filterMark($data['V_STATUS']);
        $data['updated'] = $this->getDateTime();
		$data['last_user_id'] = (int)$this->getService('User')->getCurrentUserId();		
        $result = parent::update($data);
		
		$subjectId = (int)$this->getService('Lesson')->getSubjectId($data['SHEID']);
		if($subjectId){
			$score = $this->getService('Subject')->setScore($subjectId, $data['MID']);			
		}
		
		return $result;
    }

    public function onLessonStart($lesson)
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_STUDENT)) {

            $userId = $this->getService('User')->getCurrentUserId();
            if (
                ($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) &&
//                $lesson->getFormulaId() && 
                $lesson->vedomost
            ) {
                $score = $lesson->onStart();

                /* по аналогии с onFinish() перенес в модели
                 * switch ($lesson->getType()) {
                case HM_Event_EventModel::TYPE_RESOURCE:
                    if ($lesson->getScale() == HM_Scale_ScaleModel::TYPE_BINARY) {
                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                }*/

                if ($score !== false) {
                    $this->setUserScore($userId, $lesson->SHEID, $score, 0, true);
                }

            } elseif ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {

                // это для страницы "статистика изучения свободных материалов"
                $this->updateWhere(array(
                    'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS,
                    'launched' => date('Y-m-d H:i:s'),
                ),
                array(
                    'SHEID = ?'  => $lesson->SHEID,
                    'MID = ?'    => $userId,
                    'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART
                )
            );
            }
        }
    }

    public function onLessonFinish($lesson, $result,$fromUnmanaged = false)
    {
        if (!$fromUnmanaged) {
            // не работает из unmanaged
            $roleCondition = $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_STUDENT);
            $userId = $this->getService('User')->getCurrentUserId();

        } else {
            $roleCondition = true;
            $userId = $GLOBALS['s']['mid'];
        }
        if ($roleCondition) {

            if (
                ($lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) &&
                $lesson->vedomost
            ) {
                $score = false;
                // сюда добавлять логику обработки onFinish для других типов занятий и других шкал
                // @todo: рефакторить эти вложенные switch'и
                switch ($lesson->getType()) {
                case HM_Event_EventModel::TYPE_COURSE:
                case HM_Event_EventModel::TYPE_LECTURE:
//                        if ($lesson->getFormulaId()) { // не понятно при чём тут формула; при генерации занятий formula_id в params вообще отсутствует
                        switch ($lesson->getScale()) {
                        case HM_Scale_ScaleModel::TYPE_BINARY:
                            if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                            }
                            break;
                        case HM_Scale_ScaleModel::TYPE_TERNARY:
                            if (isset($result['status'])) {
                                if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                    $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                                } elseif ($result['status'] == HM_Scorm_Track_Data_DataModel::STATUS_FAILED) {
                                    $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                                }
                            }
                            break;
                        case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                            if (isset($result['score']) && isset($result['status'])) {
                                if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                    $score = $result['score'];
                                } else {
                                    // сбрасываем при повторном прохождении
                                    $score = HM_Scale_Value_ValueModel::VALUE_NA;
                                }
                            }
                            break;
                        }
//                        }
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                    if (count($collection = $this->getService('Test')->find($lesson->getModuleId()))) {
                        $test = $collection->current();
                        switch ($lesson->getScale()) {
                        case HM_Scale_ScaleModel::TYPE_BINARY:
                            // @todo: здесь возможна бага если $test->threshold установлен в 0 (если это вообще имеет смысл)
                            if (isset($result['score']) && isset($test->threshold) && ($result['score'] >= $test->threshold)) {
                                $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                            }
                            break;
                        case HM_Scale_ScaleModel::TYPE_TERNARY:
                            if (isset($result['score']) && isset($test->threshold)) {
                                if ($result['score'] >= $test->threshold) {
                                    $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                                } else {
                                    $score = HM_Scale_Value_ValueModel::VALUE_BINARY_OFF;
                                }
                            }
                            break;
                        case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                            if (isset($result['score']) && $lesson->getFormulaId()) {
                                $score = $result['score'];
                            }
                            break;
                        }
                    }
                    break;
                }

                // избавился от внешнего switch. теперь все это в моделях занятий

                $score = $lesson->onFinish($result);

                if ($score !== false) {
                    $this->setUserScore($userId, $lesson->SHEID, $score, 0, true);
                }

            } elseif ($lesson->isfree == HM_Lesson_LessonModel::MODE_FREE) {

                // это для страницы "статистика изучения свободных материалов"
                if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                    $this->updateWhere(array(
                        'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE,
                    ),
                    array(
                        'SHEID = ?'  => $lesson->SHEID,
                        'MID = ?'    => $userId,
                        'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS
                    ));
                }
            }
        }
    }

    public function createEvent(\HM_Model_Abstract $model) {
        switch (get_class($model)) {
            case 'HM_Lesson_LessonModel':
                $event = $this->getService('ESFactory')->newEvent($model, array(
                    'title'
                ), $this);
                $user = $this->getService('User')->getCurrentUser();
                $event->setParam('author', $user->getName());
                $event->setParam('author_id', $user->getPrimaryKey());
                
                $userAvatar = '/'.ltrim($user->getPhoto(),'/');
                $event->setParam('author_avatar', $userAvatar);
                
                break;
            case 'HM_Lesson_Assign_AssignModel':
                $event = $this->getService('ESFactory')->newEvent($model);
                break;
        }
        $event->setParam('date', date('Y-m-d H:i:s'));
        return $event;
    }

    public function getRelatedUserList($id) {
        $result = array();
        if ($this->userLessonScoreSet) {
            $assign = $this->find($id)->current();
            $result[] = intval($assign->MID);
        } else {
            $listeners = $this->getService('LessonAssign')->fetchAll('SHEID = '.$id.' AND MID > 0');
            if ($listeners->count() > 0) {
                foreach ($listeners as $shid) {
                    $result[] = intval($shid->MID);
                }
            }
        }
        return $result;
    }

    public function triggerPushCallback() {
        return function($ev) {
            $params = $ev->getParameters();
            $lesson = $params['lesson'];
            $service = $ev->getSubject();
            $event = $service->createEvent($lesson);
            switch (get_class($lesson)) {
                case 'HM_Lesson_LessonModel':
                    $subject = $service->getService('Subject')->find(intval($lesson->CID))->current();
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ATTACH_LESSON);
                    $event->setParam('lesson_id', $lesson->getPrimaryKey());
                    $event->setParam('course_id', (int)$lesson->CID);
                    $event->setParam('course_name', $subject->name);
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$lesson->CID
                    );
                    break;
                case 'HM_Lesson_Assign_AssignModel':
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $select = $db->select();
                    $select->from(array('l' => 'schedule'), array('title' => 'l.title'))
                        ->join(array('shid' => 'scheduleID'), 'shid.SHEID = l.SHEID', array())
                        ->join(array('s' => 'subjects'), 's.subid = l.CID', array('course_name' => 's.name', 'subid' => 's.subid'))
                        ->where('shid.SSID = ?', $lesson->getPrimaryKey(), 'INTEGER');
                    $stmt = $select->query();
                    $stmt->execute();
                    $row = $stmt->fetch();
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_LESSON_SCORE_TRIGGERED);
                    $event->setParam('course_name', $row['course_name']);
                    $event->setParam('lesson_title', $row['title']);
                    
                    $assignedLesson = $service->getService('Lesson')->find((int)$lesson->SHEID)->current();
                    $event->setParam('lesson_id', $assignedLesson->getPrimaryKey());
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$assignedLesson->CID
                    );
                    $event->setParam('course_id', (int)$assignedLesson->CID);
                    break;
            }
            $eventGroup->setData(json_encode(
                array(
                    'course_name' => $row['course_name'],
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

    public function onLessonScoreChanged($subjectId, $userId){
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->find($subjectId)->current();
        $factory = $this->getService('MarkStrategyFactory')->getStrategy($subject->getMarkType());
        return $factory->onLessonScoreChanged($subjectId, $userId);
    }
    
	
	/**
	 * список оценок студентов указанного урока.
	*/	
	public function getLessonUsersScore($lesson_id){
		return $this->fetchAll( $this->quoteInto('SHEID = ?', $lesson_id) )->getList('MID', 'V_STATUS');	
	}
	
	/**
	 * балл за занятие
	*/	
	public function getLessonScore($lesson_id, $user_id){		
		return $this->getOne($this->fetchAll( $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson_id, $user_id)) ))->V_STATUS;	
	}
	
	/**
	 * запись назначения
	 * @return object
	*/	
	public function getRow($lesson_id, $user_id){		
		return $this->getOne($this->fetchAll( $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson_id, $user_id)) ));	
	}
	
	
	/**
	 * Список предоставленных попыток на прикрепление задания в уроке с типом задание.
	*/
	public function getCurrentAttempt($subject_id, $user_id){
		$lesson_ids = $this->getService('Lesson')->getTaskLessons($subject_id);
		if(empty($lesson_ids)){ return array(); }
		
		$attempts = $this->fetchAll( $this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($lesson_ids, $user_id)) )->getList('SHEID', 'attempts');
		return $attempts;		
	}
	
	/**
	 * отсеивание занятий, в которых проходной балл больше 65%
	*/
	public function filterPassBall($user_id, $lesson_ids){
		if(empty($user_id) || empty($lesson_ids)){ return false; }
		
		$select = $this->getSelect();
		$select->from(array('l' => 'schedule'), array(
			'lesson_id' 	=> 'l.SHEID',						
		));		
		$select->join(
			array('sche' => 'scheduleID'),
            'l.SHEID = sche.SHEID AND sche.V_STATUS < (l.max_ball * '.HM_Lesson_LessonModel::PASS_LESSON_PERCENT.')', # Порог сдачи занятия в 65%. Если больше, то и сброс попыток для него не надо делать
            array()
		);
		$select->where('l.SHEID IN (?)', $lesson_ids);
		$select->where('sche.MID = ?', $user_id);		
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		
		$data = array();		
		foreach($res as $i){
			$data[$i['lesson_id']] = $i['lesson_id'];
		}
		return $data;		
	}
	
	/**
	 * список оценок студентов указанного урока.
	*/	
	public function getByLessons($user_id, $lesson_ids){
		return $this->fetchAll( $this->quoteInto(array('MID = ?', ' AND SHEID IN (?)'), array($user_id, $lesson_ids)));	
	}
	
}
