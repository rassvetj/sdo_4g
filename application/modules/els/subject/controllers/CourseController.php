<?php
class Subject_CourseController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseId = (int) $this->_getParam('course_id', 0);

        $course = $this->getOne($this->getService('Course')->find($courseId));

        if ($course) {
            $this->getService('Unmanaged')->setSubHeader($course->Title);
        }

        $this->view->course = $course;
        $this->view->subjectId = $subjectId;
        $this->view->courseId = $courseId;
    }
}