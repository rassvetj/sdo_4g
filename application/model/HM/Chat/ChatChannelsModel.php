<?php
class HM_Chat_ChatChannelsModel extends HM_Lesson_LessonModel
{
    const TIMETYPE_FREE       = 0;
    const TIMETYPE_DATES      = 1;
    const TIMETYPE_TIMES      = 2;

    static public function getDateTypes()
    {
        return array(
            self::TIMETYPE_FREE      => _('Без ограничений'),
            self::TIMETYPE_DATES     => _('Диапазон дат'),
            self::TIMETYPE_TIMES     => _('Диапазон времени')
        );

    }

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

    public function getStartTime()
    {
        return substr($this->start_time, 0, -2).':'.substr($this->start_time, -2);
    }

    public function getEndTime()
    {
        return substr($this->end_time, 0, -2).':'.substr($this->end_time, -2);
    }

    public function isAvialable()
    {
        if($this->start_time != null && $this->start_time != 0 && $this->end_time != null && $this->end_time != 0) {
            $time = (int)date('Hi');
            return (
                date('Ymd', strtotime($this->start_date)) == date('Ymd') &&
                $time >= (int)$this->start_time && $time <= (int)$this->end_time
            );
        }
        if($this->start_date != null && $this->end_date != null) {
            $date = strtotime(date('Y-m-d'));
            return ($date >= strtotime($this->start_date) && $date <= strtotime($this->end_date));
        }
        return true;
    }
}