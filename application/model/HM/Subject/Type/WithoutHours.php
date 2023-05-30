<?php
/**
 * Сессии, в которых все часы пусты: аудиторные, лекции, практики.
 *
 */
class HM_Subject_Type_WithoutHours
{
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

	public function getInadmissibilityReasons($subjectId, $studentId)
	{
		return true;
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
		$issetIpz = $this->getService('Lesson')->issetIPZ($subject_id);
		
		foreach($lessons as $lesson){
			$assign = $assigns->exists('SHEID', $lesson->SHEID);
			if(!$assign){ continue; }
			
			$mark += $assign->getScoreFormatted();
			
			if($lesson->isLandmarkRating()){
				$mark_landmark += $assign->getScoreFormatted();
			}
		}
		
		$mark_landmark	= $this->getService('Subject')->getExamBall($mark);
		$mark_current	= $this->getService('Subject')->getCurrentBall($mark, $mark_landmark);
		
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