<?php
/*
 * Раздел учебного модуля
 */
class HM_Lesson_Lecture_LectureModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_LECTURE;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}lecture.png";
    }

    public function isExternalExecuting()
    {
        return false;
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
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        
        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $params['userdetail'] = 'yes' . Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        }        
        
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }
    
    public function getCourseId() 
    {
        
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