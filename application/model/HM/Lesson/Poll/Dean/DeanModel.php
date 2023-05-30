<?php
class HM_Lesson_Poll_Dean_DeanModel extends HM_Lesson_Poll_PollModel
{
    
    static public function factory($data, $default = 'HM_Lesson_Poll_Dean_DeanModel')
    {

        if (isset($data['typeID']))
        {
            switch($data['typeID']) {
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Leader_LeaderModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Student_StudentModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Teacher_TeacherModel');
                    break;
                default:
                    // Если занятие на основе сервиса взаимодействия
                    $activities = HM_Activity_ActivityModel::getActivityServices();
                    if (isset($activities[$data['typeID']])) {
                        $service = HM_Activity_ActivityModel::getActivityService($data['typeID']);
                        $class = Zend_Registry::get('serviceContainer')->getService($service)->getLessonModelClass();
                        return parent::factory($data, $class);
                    }
                    break;
            }
        }
        if ($default != 'HM_Lesson_Poll_Dean_DeanModel') {
            return parent::factory($data, $default);
        }
    }

    public function getServiceName()
    {
        return 'LessonDeanPoll';
    }

}