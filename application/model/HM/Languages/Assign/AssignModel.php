<?php
class HM_Languages_Assign_AssignModel extends HM_Model_Abstract
{
	const LEVEL_BEGIN  = 38;
	const LEVEL_MIDDLE = 39;
	const LEVEL_HIGH   = 40;
	
	const LEVEL_COUNT  = 3;
	
	public function isBlocked()
	{
		return $this->sync_1c ? true : false;
	}	
	
	
	public static function dependenceLevels()
	{
		return array(
			1 => self::LEVEL_HIGH,
			2 => self::LEVEL_MIDDLE,
			3 => self::LEVEL_BEGIN,
		);
	}
	
	public static function getLevelCodeByPosition($position)
	{
		$list = self::dependenceLevels();
		return $list[$position];
	}
	
	
}
