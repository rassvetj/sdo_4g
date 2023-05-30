<?php
class HM_Blog_BlogModel extends HM_Lesson_LessonModel
{

    protected $_primaryName = 'id';

    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_BLOG;
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

    public function fullViewEnabled()
    {
        return max(strpos($this->body, '<!--more-->'), strpos($this->body, '<!-- pagebreak -->'));
    }

    public function getCut()
    {
        $mPos = strpos($this->body, '<!--more-->');
        if($mPos === false) {
            $mPos = strpos($this->body, '<!-- pagebreak -->');
        }
        $body = $this->body;
        if($mPos !== false) {
            $body = substr($this->body, 0, $mPos);
    }
        return stripslashes($body);
    }
}
