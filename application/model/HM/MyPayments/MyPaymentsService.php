<?php
class HM_MyPayments_MyPaymentsService extends HM_Service_Abstract
{	
	public function getByCode($mid_external)
	{	
		return $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external));
	}
	
}