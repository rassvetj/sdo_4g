<?php
/*
 * Чат
 */
class HM_Lesson_Chat_ChatModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_CHAT;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}chat.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(array(
            'module' => 'chat',
            'controller' => 'index',
            'action' => 'index',
            'channel_id' => $this->getModuleId(), null, true)));
    }

    public function getResultsUrl($options = array())
    {
        return $this->getExecuteUrl();
    }
    
    public function isResultInTable()
    {
        return false;
    }
    
    public function isFreeModeEnabled()
    {
        return false;
    }
}