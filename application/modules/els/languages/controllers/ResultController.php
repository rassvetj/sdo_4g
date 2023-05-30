<?php
class Languages_ResultController extends HM_Controller_Action
{
    public function extendedAction()
	{		
		$subject_id	= (int)$this->_getParam('subject_id', 0);
        $lesson_id	= (int)$this->_getParam('lesson_id', 0);
		
		$this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));
        if ($this->_lesson) {
            $this->getService('Unmanaged')->setSubHeader($this->_lesson->title);
        }
		
		
		
		
	}	
	
}