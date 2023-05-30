<?php

class Feedback_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }

    public function studentsListAction()
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', (int) $this->_getParam('lesson_id', 0)));
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $q = urldecode($this->_getParam('q', ''));

        $students = $this->getGraduatedStudents($subjectId, $q);

        if (is_array($students) && count($students)) {
            $count = 0;
            foreach($students as $studentId => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($lesson && $lesson->isStudentAssigned($studentId)) {
                    $studentId .= '+';
                }
                echo sprintf("%s=%s", $studentId, $name);
                $count++;
            }
        }
    }


    public function studentsListForLeaderAction()
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', (int) $this->_getParam('lesson_id', 0)));
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $q = urldecode($this->_getParam('q', ''));

        $students = $this->getGraduatedStudents($subjectId, $q);

        if (is_array($students) && count($students)) {
            $count = 0;
            foreach($students as $studentId => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($lesson && $lesson->getService()->isUserAssigned($studentId, $lesson->SHEID)) {
                    $studentId .= '+';
                }
                echo sprintf("%s=%s", $studentId, $name);
                $count++;
            }
        }
    }

    protected function getGraduatedStudents($subjectId, $q)
    {
        $where = "CID = '".$subjectId."'";
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where = '('.
                    $this->getService('User')->quoteInto('LOWER(LastName) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(FirstName) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(Patronymic) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(Login) LIKE LOWER(?)', $q).
                    ')';
        }

        $collection = $this->getService('User')->fetchAllDependenceJoinInner(
            'Graduated',
            $where,
            array('LastName', 'FirstName', 'Patronymic', 'Login')
        );

        $students = array();

        if (count($collection)) {
            foreach($collection as $student) {
                $students[$student->MID] = $student->getName();
            }
        }
        return $students;
    }
}