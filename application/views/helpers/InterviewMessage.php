<?php
class HM_View_Helper_InterviewMessage extends HM_View_Helper_Abstract
{
    public function interviewMessage($message, $teacher, $lesson, $mark='')
    {
        if ($message->user) {
            $message->author = Zend_Registry::get('serviceContainer')->getService('Interview')->getOne($message->user);
        } else {
            $message->author = null;
        }
        $this->view->teacher = $teacher;
        $date = new HM_Date($message->date);
        $this->view->date =  $date->toString();
        $this->view->message = $message;
        $this->view->lesson = $lesson;
		if (!empty($mark))
			$this->view->mark = $mark;
        return $this->view->render('interviewMessage.tpl');
    }

}