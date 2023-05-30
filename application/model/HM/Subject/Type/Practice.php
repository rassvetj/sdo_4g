<?php
class HM_Subject_Type_Practice
{
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

	public function getInadmissibilityReasons($subjectId, $studentId)
	{
		$reasons         = array();   
		$landmark_passed = 0;
		$serviceLesson	 = $this->getService('Lesson');
		$serviceAssign	 = $this->getService('LessonAssign');		
		$lessons         = $serviceLesson->getBySubject($subjectId);
		if(!$lessons){ return false; }
		
		$assigns           = $serviceAssign->getByLessons($studentId, $lessons->getList('SHEID'));
		$landmark_count    = $serviceLesson->getLandmarkCount($subjectId);
		$max_rating_medium = $serviceLesson->getMaxBallMediumRating($subjectId, $lessons);
		
		foreach($lessons as $lesson){
			if($lesson->required != 1){ continue; }
			
			$assign = $assigns->exists('SHEID', $lesson->SHEID);
			
			if($assign->V_STATUS < 0){ continue; }
			
			if( $lesson->isBoundaryControl() || $lesson->isTest() ){				
				if($assign->V_STATUS > 0 && $serviceLesson->isPassMediumRating($lesson->max_ball, $assign->V_STATUS)){
					$landmark_passed++;
				} else {					
					$reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING][$lesson->SHEID] = $lesson->SHEID;					
				}
				continue;
			}
			
			if($lesson->isTotalPractic() && $assign->V_STATUS <= 0){
				$reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_PRACTIC][$lesson->SHEID] = $lesson->SHEID;
				continue;
			}						
		}
		
		# сдал рубежных контролей менее 65% от общего кол-ва рубежных контролей
        $part = empty($landmark_count) ? 1 : ($landmark_passed/$landmark_count);
		
		if($part < 0.65){
			$reasons[HM_Subject_SubjectModel::FAIL_PASS_MIN_LANDMARK_COUNT] = true;
		}
		
		if(empty($max_rating_medium)){
			ksort($reasons);
			return $reasons;
		}
		
		$rating  = $this->getService('Subject')->getScore($subjectId, $studentId, true);
			
		if(!$serviceLesson->isPassMediumRating($max_rating_medium, $rating->mark_current, false)){
			$reasons[HM_Subject_SubjectModel::FAIL_PASS_MIDDLE] = true;
		}
		
		# влияет на приоритет вывода сообщения для студента - первая причина из массива		
		ksort($reasons);
		return $reasons;
	}
	
	public function calculateScore($subject_id, $student_id)
	{
		$mark 			= 0;
		$mark_current	= 0;
		$mark_landmark	= 0;
		$academRating   = 0;
		$taskRating 	= 0;		
		$data    = array(
			'cid' => $subject_id,
			'mid' => $student_id,
		);
		$model   = new HM_Subject_Mark_MarkModel($data);
		$subject = $this->getService('Subject')->getById($subject_id);
		if(!$subject){ return $model; }
        
		$lessons  = $this->getService('Lesson')->getBySubject($subject_id);		
		$assigns  = $this->getService('LessonAssign')->getByLessons($student_id, $lessons->getList('SHEID'));
		
		foreach($lessons as $lesson){
			$assign = $assigns->exists('SHEID', $lesson->SHEID);
			if(!$assign){ continue; }
			
			$mark += $assign->getScoreFormatted();
			
			if($lesson->isLandmarkRating()){
				$mark_landmark += $assign->getScoreFormatted();
			}
			if($lesson->isCurrentRating()){
				$mark_current += $assign->getScoreFormatted();
			}
			
			if($lesson->isJournal()){
				$academRating += $assign->getScoreAcademActivity();
				$taskRating   += $assign->getScoreAcademPractic();
			}
			
			if($lesson->isTask()){
				$taskRating   += $assign->getScoreFormatted();
			}
		}
		
		$mark_current += $taskRating;
		$mark_current += $academRating;		
		$mark_current  = $this->getService('Lesson')->normalizeTotalCurrentRating($mark_current);
		
		$model->mark          = $mark;
		$model->mark_current  = round($mark_current);
		$model->mark_landmark = round($mark_landmark);
		
		return $model;
	}
	
	public function getMaxMarkLandmark()
	{
		return false;
	}
	
	public function getMaxMarkCurrent()
	{
		return false;
	}
    
}