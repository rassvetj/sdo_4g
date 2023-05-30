<?php
class HM_Programm_Event_EventModel extends HM_Model_Abstract
{
    const EVENT_TYPE_AT = 0;
    const EVENT_TYPE_SUBJECT = 1; // Учебные курсы и сессии

    static public function factory($data, $default = 'HM_Programm_Event_EventModel')
    {
        if (isset($data['type']))
        {
            switch($data['type']) {
                case self::EVENT_TYPE_AT:
                    return parent::factory($data, 'HM_Programm_Event_Type_AtModel');
                    break;
            }
            return parent::factory($data, $default);

        }
    }







}