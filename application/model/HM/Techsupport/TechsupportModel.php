<?php
class HM_Techsupport_TechsupportModel extends HM_Model_Abstract
{
    const SATUS_NEW      = 1;
    const SATUS_ACCEPTED = 2;
    const SATUS_RESOLVED = 3;
    const SATUS_REJECTED = 4;
    
    static public function getStatuses() {
        return array(
            self::SATUS_NEW      => _('Новый'),
            self::SATUS_ACCEPTED => _('В работе'),
            self::SATUS_RESOLVED => _('Решен'),
            self::SATUS_REJECTED => _('Отказ'),
        );
    }

    
    
    
}