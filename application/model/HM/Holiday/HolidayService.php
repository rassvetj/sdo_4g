<?php

class HM_Holiday_HolidayService extends HM_Service_Abstract
{
	const CACHE_NAME = 'HM_Holiday_HolidayService';
	private $_holidaysCache = array();
	
	public function createForYear($weekdays)
	{
		$date = Zend_Date::now()->getDate();
		$to = clone $date;
		$to->add(1, Zend_Date::YEAR);
		
		while($date < $to) {
			
			$weekday = $date->get('e');
			$weekday = $weekday ? $weekday - 1 : 6;
			
			if (in_array($weekday, $weekdays)) { 
				$this->getService('Holiday')->insert(array(
						'type' => HM_Holiday_HolidayModel::TYPE_PERIODIC,
						'title' => $date->get('EEEE'),
						'date' => $date->get('Y-M-d'),
				));					
			}
			$date->add(1, Zend_Date::DAY);
		}
	}
	
	public function getHolidaysCached($from = false)
	{
		$holidays = array();
		if (!$holidays = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
			$where = ($from) ? array('date >= ?' => $from) : array();
			foreach($this->fetchAll($where, 'date') as $row) {
				$holidays[] = $row->date;
// 				$holidays[] = new Zend_Date($row['date']);
			} 
			Zend_Registry::get('cache')->save($holidays, self::CACHE_NAME);
		}
		return $holidays;
	}
}