<?php
class HM_Lesson_Wiki_WikiModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_WIKI;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}wiki.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'view',
            'subject' => 'subject',
            'id' => $this->getModuleId()
        )));
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