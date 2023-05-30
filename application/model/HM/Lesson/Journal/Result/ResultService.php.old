<?php
class HM_Lesson_Journal_Result_ResultService extends HM_Service_Abstract
{
	public function getJournalResult($lesson_id){
		if(!$lesson_id){ return false; }
		$res = $this->fetchAll($this->quoteInto('lesson_id = ?', $lesson_id));
		if(!$res){ return false; }
		$data = array();
		foreach($res as $i){			
			$data[$i->MID][$i->journal_id] = array(
				'isBe' => $i->isBe,
				'mark' => $i->mark,
				'format_attendance' => $i->format_attendance,
			);
		}
		return $data;
	}
	
	/**
	 * максимальный балл за практическую работу (оценки) - это сумма максимальных баллов занятий с типом "задание"
	 * максимальный балл в этом занятии - балл за академ. активность.	 
	 * 
	*/
	public function recalculateMark($user_id, $lesson){

		if(!$user_id || !$lesson){ return false; }
		
		$userMarks = $this->getUserMarks($user_id, $lesson->SHEID);
		if(empty($userMarks)){ return false; }
		
		$user_isBe		 = 0;
		$user_total_ball = 0;		
		
		$ball_weight_academic 	= $this->getWeightAcademic($lesson);
		$ball_weight_practic 	= $this->getWeightPractic($lesson);
		

		foreach($userMarks as $r){					
			if($r['isBe'] == HM_Lesson_Journal_Result_ResultModel::IS_BE_YES) { $user_isBe++; }
			if($r['mark'] >= 0)												  {
				$user_total_ball += $r['mark'];			
			}
		}		
		$type_journal_AA_maxball = $lesson->max_ball == HM_Lesson_Journal_JournalModel::MAX_BALL_AA_WITHOUT_PZ_AND_LAB ? HM_Lesson_Journal_JournalModel::MAX_BALL_AA_WITHOUT_PZ_AND_LAB : HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY;
		$academic_activity 	= $this->filterBallAcademic(round(($ball_weight_academic * $user_isBe), 2), $type_journal_AA_maxball); 		# академическая активность
		$practical_task     = $this->filterBallPractic(round(($user_total_ball * $ball_weight_practic), 2), $lesson->CID);     # выполнение практического задания                    		
		#$freePoints			= HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY - $academic_activity;
		
		$data = array(
			'V_STATUS' 		=> $practical_task + $academic_activity,
			'ball_academic' => $academic_activity,
			'ball_practic' 	=> $practical_task,
			'updated' 		=> new Zend_Db_Expr('GETDATE()'),
		);
		/*
		if($promotion_ball >= 0){
			$data['ball_promotion'] = $promotion_ball;
			
			if($freePoints < $promotion_ball){ $data['V_STATUS'] += $freePoints; 	}
			else 							 { $data['V_STATUS'] += $promotion_ball;}
			
		} else {
			$row = $this->getService('LessonAssign')->getRow($lesson->SHEID, $user_id);
			
			if($row->ball_promotion > 0){
				if($freePoints < $row->ball_promotion){ $data['V_STATUS'] += $freePoints; 		   }
				else 							 	  { $data['V_STATUS'] += $row->ball_promotion; }								
			}
		}
		*/
		
		return $this->getService('LessonAssign')->updateWhere(
            $data,
            array('SHEID = ?' => $lesson->SHEID, 'MID = ?' => $user_id)
        );		
	}
	
	/**
	 * @param HM Lesson Model
	*/
	public function getWeightAcademic($lesson){
		$total_days = count($this->getService('LessonJournal')->getDayList($lesson->SHEID));				
		if(empty($total_days)){ return 0; }
		#if(!empty($lesson->max_ball_academic) || !empty($lesson->max_ball_practic)){
		#	return $lesson->max_ball_academic / $total_days;					
		#} 		
		return $lesson->max_ball / $total_days;
	}
	
	
	/**
	 * @param HM Lesson Model
	 * Сумма всех максимальных баллов занятий с типом "задание" / кол-во 
	*/
	public function getWeightPractic($lesson){		
		$total_days 	= count($this->getService('LessonJournal')->getDayList($lesson->SHEID));
		if(empty($total_days)){ return 0; }
		if(empty($lesson->max_ball_practice_or_lab))    {
			$tasksMaxBall = $this->getPracticMaxBall($lesson->CID);
			}
		else {
			$tasksMaxBall = $lesson->max_ball_practice_or_lab;
			}
 			
		return $tasksMaxBall  / ($total_days * 100); # т.к. 100-бальная шкала	
	}
	
	public function filterBallAcademic($ball_academic, $type_journal_AA_maxball){
		if($ball_academic >= $type_journal_AA_maxball){ return $type_journal_AA_maxball; }
		return $ball_academic;		
	}
	
	public function filterBallPractic($ball_practic, $subject_id = false){
		$max_ball = HM_Lesson_Journal_JournalModel::MAX_BALL_PRACTICAL_TASK;
		if($subject_id){
			if(!$this->getService('Lesson')->issetIPZ($subject_id)){
				$max_ball = HM_Lesson_Journal_JournalModel::MAX_BALL_PRACTICAL_TASK_WITHOUT_IPZ;
			}			
		}
		
		if($ball_practic >= $max_ball){ return $max_ball; }
		return $ball_practic;		
	}
	
	public function getPracticMaxBall($subject_id){
		return $this->filterBallPractic($this->getService('Lesson')->getTaskSumMaxBall($subject_id), $subject_id);
	}
	
	public function getUserMarks($student_id, $lesson_id){
		$select = $this->getSelect();
		$select->from(array('r' => 'schedule_journal_result'), array(
				'r.journal_id',
				'r.mark',
				'r.isBe',
		));
		$select->join(array('j' => 'schedule_journal'), 'r.journal_id = j.journal_id', array());	
		$select->where($this->quoteInto(array('r.lesson_id = ?', ' AND r.MID = ?'), array($lesson_id, $student_id)));
		$select->where($this->quoteInto('(j.is_hidden != ? OR j.is_hidden IS NULL)', HM_Lesson_Journal_JournalModel::STATUS_HIDDEN_YES));
		$select->where('j.lesson_id = ?', $lesson_id);		
		return $select->query()->fetchAll();
	}
	
	
	public function calculateTotalBall($subject_id, $user_id){	
		$data = $this->getRatingSeparated($subject_id, $user_id);
		if(!empty($data)){ return $data['total'] + $data['medium']; }
		return false;
	}
	

	/**
	 * Вычисляет раздельный балл за сессию: "Итоговый текущий рейтинг" и "Рубежный рейтинг"	 
	 * Для ДО немного иначе считает балл, по принципу выгрузки "Экспорт результатов обучения"
	 * @return array (RatingTotal, RatingMedium)
	*/
	public function getRatingSeparated($subject_id, $user_id)
	{
		$serviceSubject = $this->getService('Subject');
		$subject 		= $serviceSubject->getById($subject_id);
		if(empty($subject)){ return false; }
		
		$collection = $this->getService('Lesson')->fetchAll($this->quoteInto(array('CID  = ?', ' AND vedomost = ?', ' AND isfree = ?'), array($subject_id, 1, HM_Lesson_LessonModel::MODE_PLAN)));
		if(empty($collection)){ return false; }
		
		$lessons = array();
		foreach($collection as $c){ $lessons[$c->SHEID] = $c; }
		
		if(empty($lessons)){ return false; }
		
		$assigns = $this->getService('LessonAssign')->fetchAll(
			$this->quoteInto(array('MID  = ?', ' AND SHEID IN (?)'), array($user_id, array_keys($lessons)))
		);
		
		if(empty($assigns)){ return false; }
		
		$dataRatingTotal 	= 0;
		$taskRating 		= 0; # баллы за задания + журнал-практическая часть.
		$academRating 		= 0; # баллы за академическую активность только в журнале
		$dataRatingMedium 	= 0;
		$issetIPZ 			= false; # Есть ли ИПЗ
		$serviceLesson	= $this->getService('Lesson');
		$summ_ball			= 0; 
		$taskMax            = 0;
		$type_journal_AA_maxball = HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY; # если только журнал лекций и других занятий нет, то за АА максимум 80 баллов
		
		$maxBallTotalRating = $this->getService('Lesson')->getMaxBallTotalRating($subject_id);
		
		foreach($assigns as $i){
			
			if($subject->isPractice()){
				if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
					if($i->V_STATUS <= 0) { continue; }
					$summ_ball += $i->V_STATUS;
					continue;
				}
			}
		
			
			if($issetIPZ === false){
				# Если это ИПЗ
				if($serviceLesson->isTotalPractic($lessons[$i->SHEID]->title)){
					$issetIPZ = true;
				}
			} 
			
			$title 	= $lessons[$i->SHEID]->title;
			$typeID = $lessons[$i->SHEID]->typeID;
			
			if($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'задание') !== FALSE)){ 
				$taskMax    += $lessons[$i->SHEID]->max_ball;
			}
		
			if($i->V_STATUS <= 0) { continue; }
			
			if($typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE || $typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB){ 
				if ($lessons[$i->SHEID]->max_ball == HM_Lesson_Journal_JournalModel::MAX_BALL_AA_WITHOUT_PZ_AND_LAB) { $type_journal_AA_maxball = MAX_BALL_AA_WITHOUT_PZ_AND_LAB; }
				$academRating += $i->ball_academic;
			
			# журнал - практическое занятие				
			} elseif($typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ 
				$taskRating 	+= $i->ball_practic;							
				$academRating 	+= $i->ball_academic; #  Итоговый текущий рейтинг		
			
			# Рубежный рейтинг											
			} elseif( (stristr($title, 'Итоговый тест') !== false) || (stristr($title, 'Итоговый контроль') !== false) ){ 
				$dataRatingTotal += $i->V_STATUS;
			
			# на поощрения не должно накладываться ограниение в максимальный балл.
			} elseif($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'поощрени') !== FALSE)){ 
				$dataRatingMedium += $i->V_STATUS;
			
			} elseif($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'задание') !== FALSE)){ 
				$taskRating += $i->V_STATUS;
			
			#  Итоговый текущий рейтинг
			} else { 
				$dataRatingMedium += $i->V_STATUS;
			} 
		}
		
		if($subject->isPractice()){
			if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
				return array(
					'medium'	=> $serviceSubject->getPracticeMarkCurrent($summ_ball),
					'total'		=> $serviceSubject->getPracticeMarksLandmark($summ_ball),
				);
			}
		}
		
		if(
			$this->getService('Subject')->isDOT($subject_id)
			||
			$subject->isWithoutHours()
		){
			# для соответствия формата входных данных в reDivideDotBalls
			$marks_current[$user_id]  = $dataRatingMedium + $dataRatingTotal + $taskRating + $academRating;
			$marks_landmark[$user_id] = 0;
			
			$divide_balls = $this->getService('Lesson')->reDivideDotBalls($marks_current, $marks_landmark);
			$dataRatingMedium = $divide_balls['marks_current'][$user_id];
			$dataRatingTotal  = $divide_balls['marks_landmark'][$user_id];
			
			if($dataRatingMedium > 80){
				$dataRatingMedium = 80;
			}
			
			return array(
				'total'		=> $dataRatingTotal,
				'medium' 	=> $dataRatingMedium,
			);
		}
		
		
		
		
		$serviceLesson 	  = $this->getService('Lesson');	
		$dataRatingMedium += $serviceLesson->normalizeTask($taskRating, $issetIPZ, $taskMax);
		if ($type_journal_AA_maxball == HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY) {
			$dataRatingMedium += $serviceLesson->normalizeAcadem($academRating); # 	max 15
		}
		else {
			$dataRatingMedium += $academRating < $type_journal_AA_maxball ? $academRating : $type_journal_AA_maxball;
		}
		$dataRatingMedium = $serviceLesson->normalizeTotalCurrentRating($dataRatingMedium);

		if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
			$old_mark_landmark = $dataRatingTotal;
			$dataRatingMedium = HM_Subject_SubjectModel::normalizMarkCurrent($dataRatingMedium, $maxBallTotalRating, $old_mark_landmark);
			$dataRatingTotal  = HM_Subject_SubjectModel::normalizMarkLandmark($dataRatingTotal, $maxBallTotalRating);
		}
		
		
		if($dataRatingMedium > 80){
			$dataRatingMedium = 80;
		}		
				
		
		return array(
			'total'		=> $dataRatingTotal,
			'medium' 	=> $dataRatingMedium,
		);
	}
	
	
	
	
}