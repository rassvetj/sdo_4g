<?php
/*
 * Учебный модуль
 */
class HM_Lesson_Course_CourseModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_COURSE;
    }

    public function isNewWindow(){
        $params=$this->getParams();
        $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($params['module_id']));
        return ($course->new_window && Zend_Registry::get('serviceContainer')->getService('CourseItem')->isDegeneratedTree($course->CID)) ? '_blank' : '_self';
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}course.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(
            Zend_Registry::get('view')->url(
                array(
                    'module' => 'subject',
                    'controller' => 'index',
                    'action' => 'index',
                    'subject_id' => $this->CID,
                    'lesson_id' => $this->SHEID,
                    'course_id' => $this->getModuleId()
                )
            )
        );
    }

    public function getResultsUrl($options = array())
    {
        // todo: scorm log по всему электронному курсу
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID,);

        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $params['userdetail'] = 'yes' . Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        }

        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'lesson',
                	'controller' => 'execute',
                	'action' => 'index',
                	'lesson_id' => $this->SHEID
                );
    }

    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'subject',
                	'controller' => 'index',
                	'action' => 'courses'
                );
    }

    public function isResultInTable()
    {
        return true;
    }

    public function isFreeModeEnabled()
    {
        return true;
    }

    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_TERNARY;
    }

    public function onFinish($result)
    {
        $score = false;
        if ($this->getFormulaId()) {
            $subject = $this->getService()->getSubjectByLesson($this->SHEID);
            switch ($this->getScale($subject->mark_type)) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    if (isset($result['status'])) {
                        if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                            $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                        } elseif ($result['status'] == HM_Scorm_Track_Data_DataModel::STATUS_FAILED) {
                            $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                        }
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    if (isset($result['score']) && isset($result['status'])) {
                        if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                            $score = $result['score'];
                        } else {
                            // сбрасываем при повторном прохождении
                            $score = HM_Scale_Value_ValueModel::VALUE_NA;
                        }
                    }
                    break;
            }
        }
        return $score;
    }

}