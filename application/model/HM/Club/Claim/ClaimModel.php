<?php
class HM_Club_Claim_ClaimModel extends HM_Model_Abstract
{
	 /**
	 * периоды, в которые можно подавать заявки
	 * Если до илипосле, то подавать или отменять нельзя
	*/
	public static function getAvailablePeriods(){
		return array(
			0 => array('begin' => strtotime('01.10.2018 00:00:00'), 'end' => strtotime('01.11.2018 23:59:59')),
			1 => array('begin' => strtotime('01.10.2017 00:00:00'), 'end' => strtotime('20.10.2017 23:59:59')),
		);
	}
}