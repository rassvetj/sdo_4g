<?php
class HM_Lesson_Journal_Result_ResultModel extends HM_Model_Abstract
{
    const IS_BE_YES = 1;
    const IS_BE_NO  = 0;
	
	const FORMAT_ATTENDANCE_FULL_TIME = 1;
	const FORMAT_ATTENDANCE_ONLINE    = 2;
	
	static public function getFormatAttendanceList()
	{
		return array(
            self::FORMAT_ATTENDANCE_FULL_TIME => _('офф-лайн'),
			self::FORMAT_ATTENDANCE_ONLINE    => _('он-лайн'),
        );
	}
	
	static public function getFormatAttendanceName($id)
	{
		$list = self::getFormatAttendanceList();
		return $list[$id];
	}
	
	static public function getIsBeList()
	{
		return array(
            self::IS_BE_YES => _('был'),
			self::IS_BE_NO  => _('не был'),
        );
	}
	
	static public function getIsBeName($id)
	{
		$list = self::getIsBeList();
		return $list[$id];
	}
	
	
	
	
}