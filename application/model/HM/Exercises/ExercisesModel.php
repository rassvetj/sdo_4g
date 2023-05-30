<?php

class HM_Exercises_ExercisesModel extends HM_Test_TestModel
{
    //TYPE
    const LOCALE_TYPE_LOCAL  = 0;
    const LOCALE_TYPE_GLOBAL = 1;
    
    static public function getLocaleStatuses()
    {
        return array(
            self::LOCALE_TYPE_LOCAL => _('Локальный'),
            self::LOCALE_TYPE_GLOBAL => _('Глобальный')
        );
    }
	/*
    const MODE_FORWARD_ONLY = 0;
    const MODE_BACK = 1;
    const MODE_SKIP = 2;

    const QUESTIONS_BY_THEMES_SAME = 0;
    const QUESTIONS_BY_THEMES_SPECIFIED = 1;

    static public function getModes()
    {
        return array(
            self::MODE_FORWARD_ONLY => _('нельзя пропускать вопросы, нельзя возвращаться назад'),
            self::MODE_BACK => _('с возможностью возврата к предыдущим вопросам'),
            self::MODE_SKIP => _('с возможностью пропускать вопросы')
        );
    }

    static public function getQuestionsByThemes()
    {
        return array(
            self::QUESTIONS_BY_THEMES_SAME => _('Одинаковое из каждой темы'),
            self::QUESTIONS_BY_THEMES_SPECIFIED => _('Задать из каждой темы ')
        );
    }
	*/
}