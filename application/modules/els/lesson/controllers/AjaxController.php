<?php

class Lesson_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }

    public function groupsListAction()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $collection = $this->getService('StudyGroupCourse')->getCourseGroups($this->getParam('subject_id', 0));

        $groups = array();
        if (count($collection)) {
            foreach($collection as $group) {
                $groups[$group->group_id] = $group->name;
            }
        }

        /*
        if (is_array($groups) && count($groups)) {
            $count = 0;
            foreach($groups as $groupId => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($lesson && $lesson->isStudentAssigned($studentId)) {
                    $studentId .= '+';
                }
                echo sprintf("%s=%s", $groupId, $name);
                $count++;
            }
        }
        */
    }

    public function studentsListAction()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', $lessonId));

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $q = urldecode($this->_getParam('q', ''));

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

        $collection = $this->getService('User')->fetchAllJoinInner(
            'Student',
            $where,
            array('LastName', 'FirstName', 'Patronymic', 'Login')
        );

        $students = array();

        if (count($collection)) {
            foreach($collection as $student) {
                $students[$student->MID] = $student->getName();
            }
        }

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

    public function modulesListAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $itemId = $this->_getParam('item_id', 0);

        $items = array();

        $parent = 0;

        if (!$itemId) {
            $collection = $this->getService('Subject')->getCourses($subjectId);
            if (count($collection)) {
                foreach($collection as $course) {
                    $items[] = '<item id="course_'.$course->CID.'" value="'.htmlspecialchars($course->Title).'"/>';
                }
            }
        }

        $courseId = 0;
        if (substr($itemId, 0, 7) == 'course_') {
            $courseId = substr($itemId, 7);
            $itemId = -1;
        } else {
            $itemId = (int) $itemId;
            $item = $this->getService('CourseItem')->getOne($this->getService('CourseItem')->find($itemId));
            if ($item) {
                $courseId = $item->cid;
            }
        }

        if ($courseId) {
            $parentItem = $this->getService('Course')->getParentItem($itemId);
            if ($parentItem) {
                $parent = $parentItem->oid;
            }

            $collection = $this->getService('Course')->getChildrenLevelItems($courseId, $itemId);
            if (!count($collection)) {
                $collection = $this->getService('Course')->getChildrenLevelItems($courseId, $parent);
                $parentItem = $this->getService('Course')->getParentItem($parent);
                if ($parentItem) {
                    $parent = $parentItem->oid;
                } else {
                    $parent = 0;
                }
            }

            if (count($collection)) {
                foreach($collection as $item) {
                    $items[] = '<item id="'.$item->oid.'" value="'.htmlspecialchars($item->title).'"/>';
                }
            }

            if (($parent <= 0) && ($itemId > 0)) {
                $parent = 'course_'.$courseId;
            }

        }

        $xml = "<?xml version=\"1.0\" encoding=\"".Zend_Registry::get('config')->charset."\"?><tree owner=\"".$parent."\">".join('', $items)."</tree>";
        echo $xml;
    }
}