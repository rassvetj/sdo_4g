<?php
class HM_Ticket_Requisite_RequisiteModel extends HM_Model_Abstract
{
	const MAIN_ORGANIZATION = 'РГСУ';
	const MAIN_REQUISITE_NAME = 'Москва';
	
	
	static public function getDisableFilials()
    {
        return array(
            _('Ош'),
            _('Минск'),            
        );
    }
}