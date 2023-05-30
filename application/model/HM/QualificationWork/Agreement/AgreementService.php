<?php
class HM_QualificationWork_Agreement_AgreementService extends HM_Service_Abstract
{
	public function hasAgreement($mid){
		$mid  = (int)$mid;
		
		return (bool)count($this->fetchAll($this->quoteInto('MID = ?', $mid)));		
	}
	
	
	
	public function add($raw){
		
		$data = array(
			'MID'			=> (int)$raw['mid'],			
			'description'	=> $raw['description'],			
			'date_created'	=> date('Y-m-d H:i:s'),			
			'theme'			=> $raw['theme'],
			'manager'		=> $raw['manager'],
		);
		return $this->insert($data);
	}
	
}