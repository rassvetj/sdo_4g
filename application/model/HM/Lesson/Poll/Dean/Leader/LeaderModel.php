<?php
class HM_Lesson_Poll_Dean_Leader_LeaderModel extends HM_Lesson_Poll_Dean_DeanModel
{

    public function getType()
    {
        return HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER;
    }
    
    public function getServiceName()
    {
        return 'LessonDeanPollLeader';
    }

}