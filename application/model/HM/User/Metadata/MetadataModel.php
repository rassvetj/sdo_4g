<?php

class HM_User_Metadata_MetadataModel extends HM_Metadata_MetadataModel
{
    const GENDER_MALE   = 1;
    const GENDER_FEMALE = 2;

    static public function getGenderValues()
    {
        return array(
            self::GENDER_MALE => _('Мужской'),
            self::GENDER_FEMALE => _('Женский')
        );
    }

    public function getGenderValue($key)
    {
        $genders = self::getGernderValues();
        return $genders[$key];
    }

    static public function getTeamValues()
    {
        return array(
            0 => _('Нет'),
            1 => _('Штатные работники'),
            2 => _('Временные работники  (на срок до 2-х месяцев)'),
            3 => _('Внутренние совместители'),
            4 => _('Внешние совместители'),
            5 => _('Декрет (женщины, находящиеся в отпуске по уходу за ребенком)'),
            6 => _('Работники по договорам ГПХ'),
            7 => _('Неработающие пенсионеры'),
            8 => _('Студенты (практиканты)'),
            9 => _('Иностранные граждане, прибывшие в командировку в Компанию'),
            999 => _('Прочие физические лица, бывшие работники, которым проводятся какие-либо выплаты')
        );
    }

    public function getTeamValue($key)
    {
        $teams = self::getTeamValues();
        return $teams[$key];
    }
}