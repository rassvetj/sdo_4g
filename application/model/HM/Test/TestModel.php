<?php

class HM_Test_TestModel extends HM_Model_Abstract
{
    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_STUDYONLY   = 1;

    const MODE_FORWARD_ONLY = 0;
    const MODE_BACK = 1;
    const MODE_SKIP = 2;

    const QUESTIONS_BY_THEMES_SAME = 0;
    const QUESTIONS_BY_THEMES_SPECIFIED = 1;
    const QUESTIONS_ADAPTIVE = 2;
    
    const VARIANT_ASSIGN_RANDOM = 1;
    const VARIANT_ASSIGN_MANUAL = 0;
    
    const TYPE_TEST     = 0;
    const TYPE_POLL     = 1;
    const TYPE_EXERCISE = 2;
    const TYPE_TASK     = 3;

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
        );
    }

    static public function getModes()
    {
        return array(
            self::MODE_FORWARD_ONLY => _('нельзя пропускать страницы, нельзя возвращаться назад'),
            self::MODE_BACK => _('с возможностью возврата к предыдущим страницам'),
            self::MODE_SKIP => _('с возможностью пропускать страницы')
        );
    }

    static public function getQuestionsByThemes()
    {
        return array(
            self::QUESTIONS_BY_THEMES_SAME => _('одинаковое количество из каждой темы'),
            self::QUESTIONS_BY_THEMES_SPECIFIED => _('определенное количество из тем: '),
            self::QUESTIONS_ADAPTIVE => _('в случае неверного ответа включать вопрос из той же темы')
        );
    }

    static public function getTaskVariantAssign()
    {
        return array(
            self::VARIANT_ASSIGN_RANDOM => _('назначать варианты задания случайным образом'),
            self::VARIANT_ASSIGN_MANUAL => _('назначить вариант задания персонально каждому слушателю')
        );
    }
    
    static public function mapEvent2TestType($eventTypeId)
    {
        switch ($eventTypeId) {
            case HM_Event_EventModel::TYPE_TEST:
                return HM_Test_TestModel::TYPE_TEST;
            case HM_Event_EventModel::TYPE_TASK:
                return HM_Test_TestModel::TYPE_TASK;
            case HM_Event_EventModel::TYPE_POLL:
                return HM_Test_TestModel::TYPE_POLL;
        }   
        return HM_Test_TestModel::TYPE_TEST;
    }
}