<?php
class HM_Dormitory_Refund_RefundModel extends HM_Model_Abstract
{
    const TUPE_RETURN 		= 1; 
    const TUPE_IN_PAYMENT 	= 2; 
	
	public static function getTypes()
	{
		return array(
			self::TUPE_RETURN 		=> _('Вернуть деньги'),
			self::TUPE_IN_PAYMENT 	=> _('Зачесть в счет оплаты'),
		);
	}	
	
	public static function getMailTheme($type_id)
	{
		$themes = array(
			self::TUPE_RETURN 		=> _('Вернуть деньги'),
			self::TUPE_IN_PAYMENT 	=> _('Зачесть в счет оплаты'),
		);
		return $themes[$type_id];
	}
	
	public static function getTypeName($type_id)
	{
		$types = self::getTypes();
		return $types[$type_id];
	}
	
	
	
}