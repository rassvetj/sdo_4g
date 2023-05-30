<?php
class Course_IndexController extends HM_Controller_Action_Course 
{

    public function init()
    {
        parent::init();

        if ($this->getService('Unmanaged')->getController()->page_id == 'm1001') {
            // Убираем конструктор курса если из Базы знаний
            $this->view->addContextNavigationModifier(
                new HM_Navigation_Modifier_Remove_Page('resource', 'cm:course:page4_2')
            );
        }

    }

    public function indexAction()
    {
        $courseId = $this->_courseId;
        $course = $this->_course;
        
        if($course->Status == HM_Course_CourseModel::STATUS_ACTIVE || $course->Status == HM_Course_CourseModel::STATUS_ARCHIVED){
            $this->view->_withoutActivities = true;
            $container = $this->view->getContextNavigation();
            $page = $container->findBy('resource', 'cm:course:page7');
            $page->visible = false;
        }
        $this->view->addInfoBlock('CourseStatusBlock', array('course' => $course, 'title' => _('Статус учебного модуля')));
        $opened = $this->getService('CourseItem')->getOpenedBranch($courseId);
        $tree = $this->getService('CourseItem')->getTreeContent($courseId, $opened);
        $isDegeneratedTree = $this->getService('CourseItem')->isDegeneratedTree($courseId);
        $userId = $this->getService('User')->getCurrentUserId();

        $this->view->current = $this->getService('CourseItemCurrent')->getCurrent($userId, 0, $courseId);
        if ($this->view->current) {
            $this->view->itemCurrent = $this->getService('CourseItem')->getOne(
                $this->getService('CourseItem')->find($this->view->current)
            );
        }

        $this->view->courseContent = true;
        $this->view->courseObject = $course;
        $this->view->tree = $tree;
        $this->view->isDegeneratedTree = $isDegeneratedTree;
    }

    public function gettreechildAction()
    {

        $branch = $this->_getParam('key', 0);
        $courseId = $this->_getParam('course_id' , 0);
        
        if ( !$courseId ) {
            $item = $this->getService('CourseItem')->getOne($this->getService('CourseItem')->find($branch));
            if ($item) {
                $courseId = $item->cid;
            }
        }

        if(0 == $branch || 0 == $courseId){
            exit;
        }

        $this->getService('CourseItem')->addOpenedBranch($courseId, $branch);
        $branch = $this->getService('CourseItem')->getBranchContent($courseId, $branch);
        $str = Zend_Json::encode($branch);

        echo $str;
        exit;

    }

    public function deletetreechildAction()
    {

        $branch = $this->_getParam('key', 0);
        $courseId = $this->_getParam('course_id' , 0);
        //pr($this->_getAllParams());
        if(0 == $branch || 0 == $courseId){
            exit;
        }

        $this->getService('CourseItem')->deleteOpenedBranch($courseId, $branch);

        exit;

    }

}
