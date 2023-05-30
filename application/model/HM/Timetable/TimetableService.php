<?php
class HM_Timetable_TimetableService extends HM_Service_Abstract
{
	public function getById($timetable_id)
	{
		return $this->getOne($this->fetchAll($this->quoteInto('timetable_id = ?', $timetable_id)));
	}
	
	
	
}