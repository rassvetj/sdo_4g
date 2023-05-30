<?php
class HM_Lesson_Assign_Tutor_TutorService extends HM_Service_Abstract
{
	private $_cacheAssignSubjectItems = array(); # накопительный кэш. key = $tutor_id . '~' . $subject_id. value = HM_Collection
	private $_maxCacheItems = 300; # Предел накопительного кэша. Если ограничений не будет, может произойти переполнение ОЗУ

	/**
	 * @return HM_Lesson_Assign_Tutor_TutorModel
	*/
    public function assignTutor($tutor_id, $lesson_id, $subject_id = NULL, $role_id = null){
		if(!$tutor_id || !$lesson_id){ return false; }
		
		$data = array(
			'MID' 			=> (int) $tutor_id,
			'CID' 			=> (int) $subject_id,
			'LID' 			=> (int) $lesson_id,
			'date_assign' 	=>  new Zend_Db_Expr('GETDATE()'),			                    			
			'author_id' 	=>  $this->getService('User')->getCurrentUserId(),
			'role_id'		=> (int) $role_id,
		);		
		
		$assign = $this->getAssign($tutor_id, $lesson_id);	
		
		if(count($assign)){ return $this->getOne($assign); }

		if($subject_id){ $this->getService('Subject')->assignTutor($subject_id, $tutor_id); }
		
		return $this->insert($data);		
	}
	
	
	/**
	 * @return HM_Collection
	*/
	public function getAssign($tutor_id, $lesson_id){		
		return $this->fetchAll($this->quoteInto(array('MID = ?', ' AND LID = ?'), array($tutor_id, $lesson_id)));
	}
	
	/**
	 * @return HM_Collection
	*/
	public function getAssignSubject($tutor_id, $subject_id)
	{
		$key = $tutor_id . '~' . $subject_id;
		
		if(count($this->_cacheAssignSubjectItems) > $this->_maxCacheItems){
			$this->_cacheAssignSubjectItems = array();
		}
		
		if(!array_key_exists($key, $this->_cacheAssignSubjectItems)){
			$this->_cacheAssignSubjectItems[$key] = $this->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?'), array($tutor_id, $subject_id)));
		}
		return $this->_cacheAssignSubjectItems[$key];
	}
	
	/**
	 * @return int count rows
	*/
	public function unAssignTutor($tutor_id, $lesson_id){
		if(!$tutor_id || !$lesson_id){ return false; }
		
		$where = $this->quoteInto(array('MID = ?', ' AND LID = ?'), array($tutor_id, $lesson_id));
		return $this->deleteBy($where);
	}
	
	
	/**
	 * назначение на все лекционные занятия 2059 + занятия "итоговый контроль"
	 * Если произошла ошибка при назначении на занятие, последующие назначения на занятия прекращаются.
	*/
	
	public function assignLector($subject_id, $tutor_id){		
		if(!$tutor_id || !$subject_id){ return false; }
	
		$lectures = $this->getService('Lesson')->fetchAll($this->quoteInto(
																			array(' (typeID = ?  ', ' OR title LIKE ? ', ' OR title LIKE ? )', ' AND isfree = ?', ' AND CID = ?'), 
																			array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, '%итоговый тест%', '%итоговый контроль%', HM_Lesson_LessonModel::MODE_PLAN, $subject_id)
		))->getList('SHEID', 'SHEID');
		
		if(!count($lectures)) { return false; }
		foreach($lectures as $lesson_id){
			$isAssign = $this->assignTutor($tutor_id, $lesson_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR);
			if(!$isAssign){ return false; }				
		}
		return true;		
	}
	
	/**
	 * Все, кроме занятий «Посещаемость лекций» (2059) и «Посещаемость лабораторных занятий" (2061)
	 * Если произошла ошибка при назначении на занятие, последующие назначения на занятия прекращаются.
	*/
	
	public function assignSeminarian($subject_id, $tutor_id){
		if(!$tutor_id || !$subject_id){ return false; }
	
		$lectures = $this->getService('Lesson')->fetchAll($this->quoteInto(
					array('typeID NOT IN (?)', ' AND typeID NOT IN (?) ',  ' AND isfree = ?', ' AND CID = ?'),
					array(array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_LAB), array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN, $subject_id)
		))->getList('SHEID', 'SHEID');
		
		if(!count($lectures)) { return false; }
		foreach($lectures as $lesson_id){
			$isAssign = $this->assignTutor($tutor_id, $lesson_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE);
			if(!$isAssign){ return false; }				
		}
		return true;		
	}
	
	/**
	 * Все, кроме занятий «Посещаемость лекций» (2059) и «Семинары/практические занятия» (2060)
	 * Если произошла ошибка при назначении на занятие, последующие назначения на занятия прекращаются.
	*/
	
	public function assignLaborant($subject_id, $tutor_id){
		if(!$tutor_id || !$subject_id){ return false; }
	
		$lectures = $this->getService('Lesson')->fetchAll($this->quoteInto(
					array('typeID NOT IN (?)', ' AND typeID NOT IN (?) ',  ' AND isfree = ?', ' AND CID = ?'),
					array(array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE), array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN, $subject_id)
		))->getList('SHEID', 'SHEID');
		
		if(!count($lectures)) { return false; }
		foreach($lectures as $lesson_id){
			$isAssign = $this->assignTutor($tutor_id, $lesson_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB);
			if(!$isAssign){ return false; }				
		}
		return true;		
	}
	
	/**
	 * определяет роль тьютора, к которой соотнести данное занятие.
	 * Если занятие подходит к нескольким ролям, тогда берем первое совпадение.
	*/
	public function getLessonRoleType($lesson){		
		# 
		if(		$lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE
			||	stristr($lesson->title, 'итоговый тест') !== FALSE	
			||	stristr($lesson->title, 'итоговый контроль') !== FALSE				
		){
			return HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR;	# 1		
		}
		
		#
		if(		!in_array($lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_LAB))
			&&	!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))			
		){			
			return HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE; # 2
		}
		
		#
		if(		!in_array($lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))
			&&	!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))			 
		){
			return HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB; # 3
		}		
		
		return 0;
	}
	
	# с учетом того, что тютор может иметь более одной роли, нужно переделать условия. 1 - лектор, 3 (1+2) - лектор + практик,  и т.д См HM_Lesson_Assign_Tutor_TutorModel
	public function isLector($tutor_id, $subject_id){		
		//pr($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND role_id IN (?)'), array($tutor_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::getAvailableLectorRoleIDs())));
		return (bool)count($this->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND role_id IN (?)'), array($tutor_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::getAvailableLectorRoleIDs())))->getList('MID'));
	}
	
	public function isSeminarian($tutor_id, $subject_id){		
		return (bool)count($this->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND role_id IN (?)'), array($tutor_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::getAvailablePracticeRoleIDs())))->getList('MID'));
	}
	
	public function isLaborant($tutor_id, $subject_id){		
		return (bool)count($this->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND role_id IN (?)'), array($tutor_id, $subject_id, HM_Lesson_Assign_Tutor_TutorModel::getAvailableLabRoleIDs())))->getList('MID'));
	}
	
	
	/**
	 * Удаление со всех занятий сессии
	 * @return int count rows
	*/
	public function unAssignFromSubject($tutors, $subjects)
	{		
		$subjects	= (array) $subjects;
        $tutors  	= (array) $tutors;
		
		$subjects	= array_map('intval', $subjects);
		$tutors 	= array_map('intval', $tutors);
		
		$subjects	= array_filter($subjects);
		$tutors		= array_filter($tutors);
		
		if(empty($subjects) || empty($tutors)){ return false; }
		
		$where = $this->quoteInto(array('MID IN (?)', ' AND CID IN (?)'), array($tutors, $subjects));
		return $this->deleteBy($where);
	}
	
	
	public function unAssignLector($subjects, $tutors)
	{
		$subjects	= (array) $subjects;
        $tutors  	= (array) $tutors;
		
		$subjects	= array_map('intval', $subjects);
		$tutors 	= array_map('intval', $tutors);
		
		$subjects	= array_filter($subjects);
		$tutors		= array_filter($tutors);
		
		if(empty($subjects) || empty($tutors)){ return false; }
			
		$where = $this->quoteInto(array('MID IN (?)', ' AND CID IN (?)', ' AND role_id = ?'), array($tutors, $subjects, HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR));
		return $this->deleteBy($where);
	}
	
	public function unAssignSeminarian($subjects, $tutors)
	{
		$subjects	= (array) $subjects;
        $tutors  	= (array) $tutors;
		
		$subjects	= array_map('intval', $subjects);
		$tutors 	= array_map('intval', $tutors);
		
		$subjects	= array_filter($subjects);
		$tutors		= array_filter($tutors);
		
		if(empty($subjects) || empty($tutors)){ return false; }
		
		$where = $this->quoteInto(array('MID IN (?)', ' AND CID IN (?)', ' AND role_id = ?'), array($tutors, $subjects, HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE));
		return $this->deleteBy($where);
	}
	
	public function unAssignLaborant($subjects, $tutors)
	{
		$subjects	= (array) $subjects;
        $tutors  	= (array) $tutors;
		
		$subjects	= array_map('intval', $subjects);
		$tutors 	= array_map('intval', $tutors);
		
		$subjects	= array_filter($subjects);
		$tutors		= array_filter($tutors);
		
		if(empty($subjects) || empty($tutors)){ return false; }		
		
		$where = $this->quoteInto(array('MID IN (?)', ' AND CID IN (?)', ' AND role_id = ?'), array($tutors, $subjects, HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB));
		return $this->deleteBy($where);
	}
	
	
	

}