<?php

class HM_Task_TaskModel extends HM_Test_Abstract_AbstractModel
{
    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_STUDYONLY   = 1;

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
        );
    }

    public function getTestType()
    {
        return HM_Test_TestModel::TYPE_TASK;
    }
}