<?php
class HM_Crontask_Task_TaskModel extends HM_Model_Abstract
{
    /**
     * @var int интервал выполнения задания в мин.
     */
    private $_interval;

    public function __construct($interval = 3600)
    {
        $this->_interval = $interval;
    }

    /**
     * Возвращает интервал выполнения задания
     * @param bool $inSec указывает возвращать интервал в секундах или оставлять в минутах
     * @return int
     */
    public function getInterval($inSec = false)
    {
        return ($inSec)? $this->_interval * 60 : $this->_interval;
    }
}
