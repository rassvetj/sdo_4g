<?php
class HM_Controller_Action_Course extends HM_Controller_Action_Extended
{
    protected $service='Course';
    protected $idName = 'course_id';
    protected $idField = 'CID';

    protected $_courseId = 0;
    protected $_course = null;
        
    public function init()
    {
         
        $this->id = (int) $this->_getParam($this->idName, 0);
        $this->view->setExtendedFile('default.tpl');
        $this->view->setContextNavigation(
            'course',
            array(
                $this->idName => $this->id
            )
        );

        parent::init();

        $this->_courseId = (int) $this->_getParam('course_id', 0);
         $this->_course = $this->getOne($this->getService('Course')->find($this->_courseId));

         if ($this->_course && ($this->_course->format != HM_Course_CourseModel::FORMAT_FREE)) {
             $this->view->addContextNavigationModifier(
                 new HM_Navigation_Modifier_Remove_Page('resource', 'cm:course:page4_2')
             );
         }

    }
}