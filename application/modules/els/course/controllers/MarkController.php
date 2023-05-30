<?php
class Course_MarkController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->_helper
             ->ContextSwitch()
             ->setAutoJsonSerialization(true)
             ->addActionContext('get-stat', 'json')
             ->initContext('json');
    }

    public function getStatAction()
    {
        $courseId = (int) $this->_getParam('course_id', 0);
        $userId   = (int) $this->_getParam('user_id', 0);

        $this->view->assign(
            $this->getService('SubjectMark')->getCourseProgress($courseId, $userId)
        );

    }
}