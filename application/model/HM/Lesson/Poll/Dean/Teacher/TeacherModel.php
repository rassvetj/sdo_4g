<?php
class HM_Lesson_Poll_Dean_Teacher_TeacherModel extends HM_Lesson_Poll_Dean_DeanModel
{

    public function getType()
    {
        return HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER;
    }

    public function getServiceName()
    {
        return 'LessonDeanPollTeacher';
    }
  
}