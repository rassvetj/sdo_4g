<?php
class HM_Chat_ChatHistoryModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_CHAT;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        return Zend_Registry::get('config')->url->base.'images/events/redmond_test.png';

    }

    public function isExternalExecuting() {
        return true;
    }

    public function getExecuteUrl() {
        return '';
    }

    public function getResultsUrl($options=array())
    {

    }
}