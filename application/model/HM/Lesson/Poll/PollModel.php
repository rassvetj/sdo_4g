<?php
/*
 * Опрос
 */

class HM_Lesson_Poll_PollModel extends HM_Lesson_LessonModel
{
    const NOTICE_NONE     = 0;
    const NOTICE_ASSIGNED = 1;
    const NOTICE_MARKED   = 2;
    const NOTICE_DATE     = 3;
    const NOTICE_REPEAT   = 4;
    // при назначении, - с даты доступности опроса, - не отправлять, - повторять уведомление каждые _ дней;

    static public function getNotices()
    {
        return array(
            self::NOTICE_NONE     => _('Не отправлять'),
            self::NOTICE_ASSIGNED => _('При назначении опроса'),
            //self::NOTICE_MARKED   => _('При выставлении итоговой оценки'),
            self::NOTICE_REPEAT   => _('Повторять уведомление каждые N дней с даты назначения опроса'), // @TODO Сколько дней??
            self::NOTICE_DATE     => _('Повторять уведомление каждые N дней с даты доступности опроса'),
        );
    }

    public function getType()
    {
        return HM_Event_EventModel::TYPE_POLL;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}poll.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('config')->url->base.sprintf(
            'test_start.php?mode=start&tid=%d&sheid=%d',
            $this->getModuleId(),
            $this->SHEID
        );
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params));

    }
    
    public function isResultInTable()
    {
        return true;
    }
    
    
    public function isFreeModeEnabled()
    {
        return false;
    }
    
}