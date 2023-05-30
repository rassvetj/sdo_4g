<?php
/**
 * содержит методы по работе с форумом
*/
class HM_Workload_Forum_ForumService extends HM_Workload_WorkloadService
{	
	/**
	 * фиксирует вермя просрочки ответа на форуме.
	*/
	public function setForumViolation($teacherID, $forum_id){				
		if(!$teacherID || !$forum_id){
			return false;
		}
		$subject_id = $this->getSubjectID($forum_id);
		
		$begin = $this->getOneTimeForumViolation($teacherID, $forum_id);
				
		if($begin){//-_если нет $begin, значит нет периода, т.е. послений отвечыал тьютор. И фиксировать в БД не надо.
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			$violationSeconds = $this->_serviceWorkload->getViolationSeconds($begin); //--заносим в бд просрочку секунд. Даже если и 0
			$type = HM_Workload_WorkloadModel::TYPE_FORUM_ANSWER;											
			$data = array(
				'MID' => $teacherID,
				'subid' => $subject_id,					
				'type' => $type,
				'violation_time' => $violationSeconds,							
				'intervals' => 1,	
			);				
			$this->_serviceWorkload->addViolations($data);
		}
	}
	
	
	/**
	 * получает id сессии по id форума. 
	*/
	public function getSubjectID($forumID){
		if(!$forumID){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect(); // сервис ForumForum оч. долго работает. поэтому через Workload.
        $select->from('forums_list', array(
            'subject_id',            
        ));
        $select->where('forum_id = ?', $forumID);
		$select->limit(1);
		$tt = $select->query()->fetchObject();
		if(!$tt){
			return false;			
		}
		return $tt->subject_id;		
	}
	
	/**
	 * выборка всех последних сообщений преподавателей и тьюторов в форуме.
	 * @return array: [forum_id][date] - не так.
	*/
	public function getLastForumMessages($userID, $forumID){
		if(!$userID || !$forumID){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('m' => 'forums_messages'),
            array(
				//'message_id' => 'MAX(m.message_id)',		
				'forum_id' => 'm.forum_id',		
				//'user_id' => 'm.user_id',		
				'date' => 'MAX(m.created)',						
            )
        );	
		
		$select->where('m.user_id = ?', $userID);		
		$select->where('m.forum_id = ?', $forumID);		
		$select->group(array('m.forum_id'));
		
		$res = $select->query()->fetchAll();
		
		if(!$res){
			return false;
		}
		return $res;			
	}
	
	/**
	 * выборка всех сообщений студентов.
	 * @return array: [forum_id][][date]
	*/
	public function getUserForumMessages($forumID, $subject_id){		
		if(!$forumID || !$subject_id){
			return false;
		}
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$usersSelect = $this->_serviceWorkload->getSelect();
		$usersSelect->from(
            array('p' => 'People'),
            array('MID' => 'p.MID')
        );
		$usersSelect->join(
			array('s' => 'Students'),
            's.MID = p.MID',
            array()
        );
		$usersSelect->where('s.CID = ?', $subject_id);	
		
		
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('m' => 'forums_messages'),
            array(						
				'forum_id' 	=> 'm.forum_id',						
				'date' 		=> 'm.created',												  
            )
        );
		$select->join(
			array('u' => $usersSelect),
            'u.MID = m.user_id',
            array()
        );				
		$select->where('m.forum_id = ?', $forumID);	
		$res = $select->query()->fetchAll();
		
		if(!$res){
			return false;
		}
		
		$data = array();
		foreach($res as $i){
			$data[$i['forum_id']][] = $i['date'];
		}
		return $data;	
	}
	
	/**
	 * даты начала отсчета просрочки.
	 * сравниваем дату последнего сообщения тьютора с датами сообщений студентов. Берем наименьшую дату студента, которая больше даты тьютора.
	 * @return array: [forum_id][date]
	 * привязывать ли к тьютору? Может ли быть на форуме разные интервалы на несколькоих тьюторов? Может понадобиться при формировании отчетов.	 
	*/
	public function getDateBeginForumViolation($forum_id, $teacherID){ 
				
		$subject_id = $this->getSubjectID($forum_id);
		
		$msgT = $this->getLastForumMessages($teacherID, $forum_id);		
		$msgS = $this->getUserForumMessages($forum_id, $subject_id);		
		if(!$msgS){
			return false;
		}		
		
		$tutorDates = array();
		if($msgT){ //--изменяем массив для удобной обработки
			foreach($msgT as $i){
				$tutorDates[$i['forum_id']] = strtotime($i['date']);
			}
		}		
		$data = array();
		
		foreach($msgS as $forumId => $dates){
			$timestampT = isset($tutorDates[$forumId]) ? ($tutorDates[$forumId]) : (false);
			foreach($dates as $d){
				$timestampS = strtotime($d);
				if($timestampT < $timestampS){
					if(!isset($data[$forumId])){								
						$data[$forumId] = $timestampS;
					} else {								
						if($data[$forumId] > $timestampS){									
							$data[$forumId] = $timestampS;
						}
					}
				}
			}
		}
		return $data;		
	}
	
	/**
	 * возвращает кол-во секунд просрочки по одному форуму.
	 * @return integer
	*/
	public function getOneTimeForumViolation($teacherID, $forumID){
		if(!$teacherID || !$forumID){
			return false;
		}
		$violations = $this->getDateBeginForumViolation($forumID, $teacherID);		
		if(isset($violations[$forumID])){
			return $violations[$forumID];
		}
		return false;
	}
	
	
	/**
	 * Выборка активных студентов форума. Активные - те, которые хотя бы 1 раз написали на форуме.
	 * @return array('student_id', 'tutor_id', 'subject_id', 'forum_id')
	*/
	public function getForumActiveStudents($tutorIDs = false, $subjectIDs = false){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}		
		$select_active = $this->_serviceWorkload->getSelect();			
		$select_active->from(
			array('s' => 'Students'),
			array(				
				'student_id' => 's.MID',																								
				'tutor_id' => 't.MID',	
				'subject_id' => 'subj.subid',	
				'forum_id' => 'f.forum_id',	//-на случай, если у одной сессии будет несколько форумов.
			)
		);
		$select_active->join(
			array('subj' => 'subjects'),
			's.CID = subj.subid',
			array()
		);		
		$select_active->join(
			array('f' => 'forums_list'),
			'f.subject_id = subj.subid',
			array()
		);
		$select_active->join(
			array('fm' => 'forums_messages'),
			'fm.forum_id = f.forum_id',
			array()
		);		
		$select_active->join(
			array('t' => 'Tutors'),
			'subj.subid = t.CID',
			array()
		);
		$select_active->where('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
		$select_active->where('subj.isSheetPassed IS NULL');		
		
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
		$select_active->group(array('s.MID', 't.MID','subj.subid', 'f.forum_id'));			
		$res_students = $select_active->query()->fetchAll();		
		if($res_students){
			return $res_students;			
		}
		return false;
	}
	
	
	
	/*
	 * получаем время просрочки по форуму
	 * @return array
	 * [tutorID] => [форум] = [violation time in seconds]
	*/
	public function getTimeForumViolation($tutorIDs){		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		if(!$this->_serviceWorkloadForum){ $this->_serviceWorkloadForum = $this->getService('WorkloadForum');	}
		
		$select = $this->_serviceWorkload->getSelect();
        $select->from(array('t' => 'Tutors'),
            array(
				'tutor_id' => 't.MID',
				'forum_id' => 'f.forum_id',				
            )
        );
		$select->join(
			array('subj' => 'subjects'),
            'subj.subid = t.CID',
            array()
		);
		$select->join(
			array('f' => 'forums_list'),
			'f.subject_id = subj.subid',
			array()
		);
		if($tutorIDs){
			$tutorIDs = (array)$tutorIDs;
			if(count($tutorIDs)){
				$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));				
			}
		}	
		$forums = $select->query()->fetchAll();	
		if(!$forums){
			return false;
		}		
		$dates = array();
		foreach($forums as $f){						
			$begin = $this->_serviceWorkloadForum->getOneTimeForumViolation($f['tutor_id'], $f['forum_id']);
			if($begin){//-_если нет $begin, значит нет периода, т.е. послений отвечыал тьютор.
				$violationSeconds = $this->_serviceWorkload->getViolationSeconds($begin);
			} else {
				$violationSeconds = 0;
			}
			$dates[$f['tutor_id']][$f['forum_id']]  = $violationSeconds;
		}
		return $dates;
	}
}