<?php
class HM_Hostel_Claims_ClaimsModel extends HM_Model_Abstract
{
	
	const TYPE_SETTLEMENT = 1; //--заселение
    const TYPE_RE_SETTLEMENT = 2; //--переселение
	
	
	const STATUS_NEW = 0; //--новая
	const STATUS_ACCEPT = 1; //--утверждено
	const STATUS_REJECT = 2; //--отклонено
	
	
	static public function getTypes() {
        return array(
            self::TYPE_SETTLEMENT     	=> _('Заселение'),
            self::TYPE_RE_SETTLEMENT 	=> _('Переселение'),			
        );
    }
	
	
	static public function getStatuses() {
        return array(
            self::STATUS_NEW     	=> _('Новая'),
            self::STATUS_ACCEPT     => _('Утверждена'),
            self::STATUS_REJECT     => _('Отклонена'),            		
        );
    }
	
	
	static public function getStatusesIDs() {
        return array(
           _('Новая') 		=> self::STATUS_NEW,
           _('Утверждена') 	=> self::STATUS_ACCEPT,
           _('Отклонена') 	=> self::STATUS_REJECT,                     		
        );
    }
	
}