<?php
class HM_Subject_Type_ClassicPractice
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
		
		$assigns         = $serviceAssign->getByLessons($studentId, $lessons->getList('SHEID'));		
		$landmark_count  = $serviceLesson->getLandmarkCount($subjectId);		
		
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
		
		# влияет на приоритет вывода сообщения для студента - первая причина из массива		
		ksort($reasons);
		
		return $reasons;
	}
	
	public function calculateScore($subject_id, $student_id)
	{
		$mark 			= 0;
		$mark_current	= 0;
		$mark_landmark	= 0;
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
		}
		
		$mark_current	= $this->getService('Subject')->getPracticeMarkCurrent($mark);
		$mark_landmark	= $this->getService('Subject')->getPracticeMarksLandmark($mark);
		
		$model->mark          = $mark;
		$model->mark_current  = $mark_current;
		$model->mark_landmark = $mark_landmark;
		
		return $model;
	}
	
	public function getMaxMarkLandmark()
	{
		return HM_Subject_SubjectModel::MAX_AUTOMATIC_MARK_LANDMARK;
	}
	
	public function getMaxMarkCurrent()
	{
		return HM_Subject_SubjectModel::MAX_AUTOMATIC_MARK_CURRENT;
	}
	
	
    
}