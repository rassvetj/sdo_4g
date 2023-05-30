<?php
class HM_Test_Result_ResultModel extends HM_Model_Abstract
{
    const STATUS_EXECUTED = 0;
    const STATUS_FINISHED = 1;
    const STATUS_DROPPED = 2;
    const STATUS_BREAKED = 3;
    const STATUS_FORCED = 4;
    const STATUS_LIMIT = 5;

    static public function getStatuses()
    {
        return array(
            self::STATUS_EXECUTED => _('сейчас идёт'),
            self::STATUS_FINISHED => _('закончен'),
            self::STATUS_DROPPED => _('брошен'),
            self::STATUS_BREAKED => _('прерван'),
            self::STATUS_FORCED => _('досрочно завершён'),
            self::STATUS_LIMIT => _('лимит времени')
        );
    }
    
    static public function getStatus($status){
    	
    	$statuses = self::getStatuses();
    	return ($status != '') ? $statuses[$status] : _('не начат');
    	
    }
}
