<?php
class HM_StudentDebt_StudentDebtModel extends HM_Model_Abstract
{
	
	const STATE_NOT_EXTENDED = 0;
	const STATE_EXTENDED 	 = 1;
	
	
	const TYPE_MARKSHEET_NOT_SET = 0;
	const TYPE_MARKSHEET_SET = 1;
	
	
	static function getMarksheetTypes(){
		return array(
			self::TYPE_MARKSHEET_NOT_SET	=> _('Нет'),
			self::TYPE_MARKSHEET_SET		=> _('Да'),
		);
	}
	
	
	static function getStates(){
		return array(
			self::STATE_NOT_EXTENDED 	=> _('Не продлена'),
			self::STATE_EXTENDED		=> _('Продлена'),			
		);
	}
	
    
}