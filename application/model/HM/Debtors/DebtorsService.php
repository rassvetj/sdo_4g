<?php
class HM_Debtors_DebtorsService extends HM_Service_Abstract
{
    public function getImportSelect() {
        try {
 

		$select = $this->getSelect();
        $select->from(
            array(
                'st' => 'Students'
            ),
            array(
				'CID' 					=> 'st.CID',
				'SID' 					=> 'st.SID',
				'session_external_id' 	=> 'subj.external_id',											
				'time_ended_debtor' 	=> 'st.time_ended_debtor',				
				'session_name' 			=> 'subj.name',				
				'base' 					=> 'subj.base',				
				'MID' 					=> 'st.MID',	
				'mark' 					=> 'cm.mark',
				'teacher_MID'			=> 'th.MID',	
				'time_ended_debtor_2'	=> 'st.time_ended_debtor_2',	
            )
        );
	
		$select->join(array('subj' => 'subjects'),
            'subj.subid = st.CID',
            array()
		);
		
		$select->joinLeft( //--оценки может и не быть
			array('cm' => 'courses_marks'),
			'cm.cid = subj.subid AND cm.mid = st.MID',
			array()
		);
		
		$select->joinLeft( //--преподаватель на данной сессии
			array('th' => 'Teachers'),			
			'th.CID = subj.subid',
			array()
		);

		$select->group(array(
			'st.CID',
			'st.SID',
			'subj.external_id',
			'st.time_ended_debtor',			
			'subj.name',
			'subj.base',				
			'st.MID',	
			'cm.mark',								
			'th.MID',
			'st.time_ended_debtor_2',				
		));
				
		$select->where('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION); //-- 2 - Отбираем сессии.
		$select->where('subj.external_id IS NOT NULL'); 
		
        return $select;
		
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
    }   

	
	/**
	 * выбираем все курсы указанных студентов	
     * return array
	 * param array
	*/
	public function getUserSessions($usersID){
		try {
		if(empty($usersID)){
			return false;
		}
		
		if(!is_array($usersID)){
			return false;
		}
	
		
		$select = $this->getSelect();
        $select->from(
            array(
                'st' => 'Students'
            ),
            array(
				'CID' => 'st.CID',												
				'mid_external' => 'p.mid_external',												
				'session_id_external' => 'subj.external_id',								
				'session_name' => 'subj.name',								
				'session_ended' => 'subj.end',								
				'MID' => 'st.MID',																												
				//'programm_id' => 'pe.programm_id',																												
				'group_id_programm' => new Zend_Db_Expr('GROUP_CONCAT(sgp.group_id)'),			
				'group_id_user' => new Zend_Db_Expr('GROUP_CONCAT(sgc.group_id)'),
            )
        );

		$select->join(
			array(
				'p' => 'People'
			),
            'p.MID = st.MID',
            array(
			)
		);
		
		$select->join(
			array(
				'subj' => 'subjects'
			),
            'subj.subid = st.CID',
            array(
			)
		);
		
		$select->joinLeft(
			array(
				'pe' => 'programm_events'
			),
            'pe.item_id = subj.subid',
            array(
			)
		);
		
		$select->joinLeft(
			array(
				'sgp' => 'study_groups_programms'
			),
            'sgp.programm_id = pe.programm_id',
            array(
			)
		);
		
		$select->joinLeft(
			array(
				'sgc' => 'study_groups_custom'
			),
            'sgc.user_id = st.MID',
            array(
			)
		);

		
		
		
		//--получаем масив списка групп. Переделать через модель группы.
		$g_select = $this->getSelect();
        $g_select->from(
            array(
                'sg' => 'study_groups'
            ),
            array(
				'group_id' => 'sg.group_id',												
				'name' => 'sg.name',																
            )
        );
		$g_res = $g_select->query()->fetchAll();
		$groups = array();
		foreach($g_res as $g){
			$groups[$g['group_id']] = $g['name'];
			
		}
	
		$select->group(array('st.CID', 'p.mid_external', 'subj.external_id', 'subj.name', 'subj.end', 'st.MID'));
				
		//--это лишнее условие, иначе не все группы в выборке. А надо по всем студентам.
		//$select->where($this->quoteInto(
			//'p.mid_external IN (?)',											
			//new Zend_Db_Expr("'".implode("','", $usersID)."'")
		//));
		
		
		$res = $select->query()->fetchAll();
		
		
		
		
		foreach($res as $k => $i){	
		
			$group_programms = explode(',', $i['group_id_programm']);
			$group_users = explode(',', $i['group_id_user']);
			if(count($group_users) == 1){
				$res[$k]['group_current'] = $group_users;
				
			} else {	
				$inArray = false;
				foreach($group_users as $g){
					if(in_array($g, $group_programms)){
						$inArray = true;
						$res[$k]['group_current'][$g] = $g;
					}
				}
			
				
				if(!$inArray){
					$res[$k]['group_current'] = $group_users;
				}
			}
			
		}
		
		
		$groups_on_courses = array(); //--группируем по курсам.
		foreach($res as $i){				
			
			$t = array();
			foreach($i['group_current'] as $g){
				$t[$g] = $g;
			}
			//$groups_on_courses[$i['CID']] = (array)$groups_on_courses[$i['CID']] + $i['group_current'];
			$groups_on_courses[$i['CID']] = (array)$groups_on_courses[$i['CID']] + $t;
		}
		
		
		$res2 = array();
		foreach($res as $i){	
			
			$str = '';
			if(isset($groups_on_courses[$i['CID']])){
				foreach($groups_on_courses[$i['CID']] as $g){
					$str .= $groups[$g].', ';
				}
				#$i['groups'] = trim(trim($str), ','); # Error: Allowed memory size при опр. значении.
				$i['groups'] = $str;
			} else {
				$i['groups'] = false;
			}
			$res2[$i['mid_external']][] = $i;
		}		
		
		if(!$res2){
			return false;
		}
		
		return $res2;
		
		}
		catch(Exception $e){
			//echo $e->getMessage();
			return false;			
		}
	}

	/**
	 * нужна?
	*/
	public function getGroupList(){
		//--получаем масив списка групп. Переделать через модель группы.
		$g_select = $this->getSelect();
        $g_select->from(
            array(
                'sg' => 'study_groups'
            ),
            array(
				'group_id' => 'sg.group_id',												
				'name' => 'sg.name',																
            )
        );
		$g_res = $g_select->query()->fetchAll();
		
		if(!count($g_res)){
			return false;
		}
		
		$groups = array();
		foreach($g_res as $g){
			$groups[$g['group_id']] = $g['name'];
			
		}		
		return $groups;	
	}		

	
	/**
	 * выборка кол-ва попыток прикрепления задания к уроку (кол-во раз выставления оценки). Ограничение по кол-ву баллов за сессию.
	 * @return array
	*/	
	public function getLessonAttempts($userIDs = array()){
		
		if(!is_array($userIDs) || empty($userIDs)){
			return false;
		}		
		
		$select = $this->getSelect();
        $select->from(
            array(
                'sche' => 'scheduleID'
            ),
            array(
				'SSID' 		=> 'sche.SSID',	  
				'SHEID' 	=> 'l.SHEID',	  
				'CID' 		=> 'l.CID',	  
				'MID' 		=> 'sche.MID',	  
				'attempts' 	=> 'sche.attempts',					
            )
        );	
		$select->join(
			array('l' => 'schedule'),
            'l.SHEID = sche.SHEID AND sche.V_STATUS < (l.max_ball * '.HM_Lesson_LessonModel::PASS_LESSON_PERCENT.')', # Порог сдачи занятия в 65%. Если больше, то и сброс попыток для него не надо делать
            array()
		);
		$select->joinLeft(
			array('cm' => 'courses_marks'),
            'cm.cid = l.CID AND cm.mid = sche.MID',
            array()
		);		
		$select->where($this->quoteInto('sche.MID IN (?)', $userIDs));				
		$select->where(
			$this->quoteInto(			
				array("CAST(REPLACE(cm.mark,',','.') AS float) < ? ", " OR cm.mark IS NULL"),						
				array(65, false)
			)
		);	
		
		$res = $select->query()->fetchAll();
		
		return $res;
	}
	
	/**
	 * Если надо, добавляет попытку на прикрепление решения на проверку во всех уроках с типом задание.
	*/
	public function addLessonAttemptUser($subject_id, $student_id){
		if(!$subject_id || !$student_id){ return false; }
		
		# кол-во сбросов на данный момент в формате key - lesson_id, value - count of attempts.
		$userAttempt = $this->getService('LessonAssign')->getCurrentAttempt($subject_id, $student_id);
		if(empty($userAttempt)){ return true; }
		
		# уроки, в которыне студент не можут прикреплять задания
		$unavailable_attach_lessons = $this->getService('Interview')->getUnavailableAttachLessons($student_id, $userAttempt);
		
		# отсеивание занятий, в которых проходной балл больше 65%
		$unavailable_attach_lessons = $this->getService('LessonAssign')->filterPassBall($student_id, $unavailable_attach_lessons);
		
		if(!empty($unavailable_attach_lessons)){
			$isUpdate = $this->getService('LessonAssign')->updateWhere(
				array('attempts' => 
					new Zend_Db_Expr('
						CASE 
							WHEN attempts IS NULL
								THEN 1 
							ELSE attempts + 1
						END
					')
				),				
				$this->quoteInto(array('MID = ? ', ' AND SHEID IN (?)'), array($student_id, $unavailable_attach_lessons))
			);
			if($isUpdate){ return true;}
		}
		return false;
	}
	
	
	/**
	 * DEPRECATED
	 * добавляет 1 попытку на прикрепление решения к заданию, если больше нет возможности прикреплять задание к уроку.
	*/
	public function incrementLessonAttempts($userIDs = array()){
		if(!is_array($userIDs) || empty($userIDs)){
			return false;
		}
		
		$res = $this->getLessonAttempts($userIDs);		
		$needUpdateAttempts = array(); //--записи уроков студента, в которых надо увеличить счетчик прикреплений заданий
				
		foreach($res as $v){
			$newKey = $v['MID'].'~'.$v['CID'];		
			if(isset($userIDs[$newKey])){
				$needUpdateAttempts[$v['SSID']] = $v['SHEID'];
			}
		}		
		//--отбираем записи, у которых сообщений препода с типом оценка выставлена меньше, чем кол-во в поле attampts
		$needAttempted = array(); //--окончательный массив id, у которых надо увеличить на 1
		$exsistAttempted = $this->getNeedAttempted($userIDs);
		
		foreach($needUpdateAttempts as $SSID => $v){
			if(isset($exsistAttempted[$SSID])){
				if(
				( ((int)$exsistAttempted[$SSID]['attempts'] + 1) - $exsistAttempted[$SSID]['total'] ) < 1 //--студент прикрепить ответ уже не может				
				){
					$needAttempted[$SSID] = 	$SSID;
				}
			}
		}
		
		//--если записи из $needUpdateAttempts отсутствуют в $exsistAttempted, значит увеличивать кол-во не надо, т.к. оценка еще не была ни разу выставлена.		
		if(!empty($needAttempted)){			
			$this->getService('LessonAssign')->updateWhere(
				array('attempts' => 
					new Zend_Db_Expr('
						CASE 
							WHEN attempts IS NULL
								THEN 1 
							ELSE attempts + 1
						END
					')
				),				
				$this->quoteInto('SSID IN (?)', $needAttempted)
			);			
		}
		return true;
	}
	
	
	/**	 
	 * выбираем кол-во попыток на прикрепление и кол-во выставления оценки за урок студенту
	*/
	public function getNeedAttempted($userIDs = array()){		
		
		$select = $this->getSelect();
        $select->from(
            array(
                'i' => 'interview'
            ),
            array(
				'SSID' 		=> 'sche.SSID',	
				'lesson_id' => 'i.lesson_id',	
				'to_whom' 	=> 'i.to_whom',	  	
				'attempts' 	=> 'sche.attempts',
				'total' 	=> new Zend_Db_Expr('COUNT(i.interview_id)'),
            )
        );
		
		$select->join(
			array(
				'sche' => 'scheduleID'
			),
            'i.lesson_id = sche.SHEID AND i.to_whom = sche.MID',
            array()
		);
		$select->group(array('i.lesson_id', 'sche.attempts',  'i.to_whom', 'sche.SSID'));
		$select->where('i.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
		
		$res = $select->query()->fetchAll();
		
		$t = array();
		foreach($res as $i){
			$t[$i['SSID']] = $i;
		}
		
		return $t;
	}
	
}