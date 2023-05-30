<?php
class HM_MyPayments_Details_DetailsService extends HM_Service_Abstract
{
	public function getByCode($mid_external)
	{	
		return $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external), array('date_contract', 'date_operation'));
	}
	
	public function hasDebt($mid_external)
	{
		$item = $this->getOne($this->fetchAll($this->quoteInto('mid_external = ?', $mid_external), array('date_operation DESC')));	
		
		if($item && $item->balance > 0){
			return true;
		}
		return false;
	}
}