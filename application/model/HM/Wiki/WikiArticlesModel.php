<?php

class HM_Wiki_WikiArticlesModel extends HM_Model_Abstract
{

    protected $_primaryName = 'id';

    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_WIKI;
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

    public function getUrl($title = null)//for static call
    {
        if(!$title) {
            $title = $this->title;
        }
        $title = str_replace(' ', '_', $title);
        $title = str_replace('.', '', $title);
        return urlencode($title);
    }
}
