<?php
class HM_Lesson_Poll_Dean_Student_StudentModel extends HM_Lesson_Poll_Dean_DeanModel
{


    public function getType()
    {
        return HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT;
    }
    
    public function getServiceName()
    {
        return 'LessonDeanPollStudent';
    }

}