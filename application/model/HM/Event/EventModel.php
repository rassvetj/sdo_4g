<?php
class HM_Event_EventModel extends HM_Model_Abstract
{
    const TYPE_EMPTY     = 1000;
    const TYPE_LECTURE   = 1024;
    const TYPE_TEST      = 2048;
    const TYPE_EXERCISE  = 2049;
    const TYPE_COURSE    = 2050;
    const TYPE_WEBINAR   = 2051;
    const TYPE_RESOURCE  = 2052;
    const TYPE_POLL      = 2053;
    const TYPE_TASK      = 2054;
    const TYPE_JOURNAL   = 2058;
    const TYPE_JOURNAL_LECTURE   = 2059;
    const TYPE_JOURNAL_PRACTICE  = 2060;
    const TYPE_JOURNAL_LAB	     = 2061;
    const TYPE_LANGUAGE	 = 2062;

    //Dean's poll for leader
    const TYPE_DEAN_POLL_FOR_STUDENT = 2055;
    const TYPE_DEAN_POLL_FOR_LEADER  = 2056;
    const TYPE_DEAN_POLL_FOR_TEACHER = 2057;

    const WEIGHT_DEFAULT = 5;

    static public $events = null;

    static public function getTypes()
    {
        $types = array(
            self::TYPE_EMPTY    => '',
            self::TYPE_COURSE   => _('Учебный модуль'),
            self::TYPE_LECTURE  => _('Раздел учебного модуля'),
            self::TYPE_RESOURCE => _('Информационный ресурс'),
            self::TYPE_TEST     => _('Тест'),
            self::TYPE_TASK     => _('Задание'),
			self::TYPE_JOURNAL  => _('Журнал'),
//            self::TYPE_EXERCISE => _('Упражнение'),
            self::TYPE_POLL     => _('Опрос'),
            self::TYPE_WEBINAR  => _('Вебинар'), 
			self::TYPE_JOURNAL_LECTURE   => _('Журнал - лекции'),            
            self::TYPE_JOURNAL_PRACTICE  => _('Журнал - семинарские/практические занятия'),            
            self::TYPE_JOURNAL_LAB  	 => _('Журнал - лабораторные занятия'), 			
            
			self::TYPE_LANGUAGE  	 => _('Распределение языка'), 			
        );
        
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $types);

        return $event->getReturnValue();
    }

    /**
     * Return array of elements, which would't be in schedule list
     * @return multitype:string
     */
    static function getExcludedTypes()
    {
        return array(
                    self::TYPE_DEAN_POLL_FOR_STUDENT => _('Опрос слушателей'),
                    self::TYPE_DEAN_POLL_FOR_LEADER  => _('Опрос руководителей'),
                    self::TYPE_DEAN_POLL_FOR_TEACHER => _('Опрос преподавателей')
                );
    }

    static function getDeanPollTypes()
    {
        return self::getExcludedTypes();
    }

    static function getFeedbackPollTypes()
    {
        $types = self::getTypes();
        $result = self::getExcludedTypes();
        $result[self::TYPE_POLL] = $types[self::TYPE_POLL];
        return $result;
    }

    static function getFeedbackPollTypesShort()
    {
        $types = self::getTypes();
        $result = array(
                    self::TYPE_DEAN_POLL_FOR_STUDENT => _('Слушателя'),
                    self::TYPE_DEAN_POLL_FOR_LEADER  => _('Руководителя'),
                    self::TYPE_DEAN_POLL_FOR_TEACHER => _('Преподавателя')
                );
        $result[self::TYPE_POLL] = $types[self::TYPE_POLL];
        return $result;
    }

    /**
     * Return all types including self types
     * Activities, and custom events
     *
     * @param bool $returnEmptyType999 default="true" return delimiter between self types and custom types as [999] => '---'
     *
     * @return array of events
     */
    static public function getAllTypes($returnEmptyType999 = true)
    {
        $types = self::getTypes();
        $activities = HM_Activity_ActivityModel::getEventActivities();
        foreach($activities as $id => $activity) {
            $types[$id] = $activity;
        }

        // Добавляем Custom Events
        if (self::$events === null) {
            self::$events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll(null, 'title');
        }

        if (count(self::$events)) {
            if($returnEmptyType999){
                $types[999] = '---';
            }
            foreach(self::$events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }

        return $types;
    }

    public function getIcon()
    {
        if (file_exists(Zend_Registry::get('config')->path->upload->event.$this->event_id.'.jpg')) {
            return Zend_Registry::get('config')->base->url.'/upload/events/'.$this->event_id.'.jpg';
        }
        return false;
    }
}