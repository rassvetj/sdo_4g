<?php
class HM_Payment_Code_CodeService extends HM_Service_Abstract
{
	
	public function getCode($mid_external){
		if(empty($mid_external)){ return false; }
		return $this->getOne($this->fetchAll($this->quoteInto('mid_external = ?', $mid_external)))->person_code;		
	}
	
}