<?php
class HM_StudentNotification_Agreement_AgreementService extends HM_Service_Abstract
{
	private $_userAgreements = NULL;
	
	public function hasAgreement($mid, $type, $additional = false)
	{
		$mid  = (int)$mid;
		$type = (int)$type;
		
		if(!$this->_userAgreements){
			$this->_userAgreements = $this->fetchAll($this->quoteInto('MID = ?', $mid));
		}
		
		if(!empty($additional)){
			$additional = $this->prepareAdditional($additional);
			
			foreach($this->_userAgreements as $agreement){
				if($agreement->type == $type && $agreement->additional == $additional){ return true; }
			}
			return false;
		}
		
		if(in_array($type, HM_StudentNotification_Agreement_AgreementModel::getInfiniteTypes())){
			return $this->_userAgreements->exists('type', $type) ? true : false;
		}
		
		if($type == HM_StudentNotification_Agreement_AgreementModel::TYPE_SOCIAL_SCHOLARSHIP){
			return $this->isHasInThisMonth($mid, $type);
		}
		
		$lifeTime = HM_StudentNotification_Agreement_AgreementModel::LIFE_TIME;
		
		if($type == HM_StudentNotification_Agreement_AgreementModel::TYPE_FIN_DEBT_31){
			$lifeTime = HM_StudentNotification_Agreement_AgreementModel::LIFE_TIME_DAY;
		}
		
		$lifeTimestamp = time() - $lifeTime;

		foreach($this->_userAgreements as $agreement){
			if($agreement->type == $type &&  strtotime($agreement->date_created) > $lifeTimestamp  ){ return true; }
		}
		return false;
	}
	
	
	
	public function add($mid, $type, $additional = false){
		
		$data = array(
			'MID'			=> (int)$mid,
			'type'			=> (int)$type,
			'date_created'	=> date('Y-m-d H:i:s'),
		);
		
		$additional = $this->prepareAdditional($additional);
		
		if($additional){
			$data['additional'] = $additional;
		}
		
		return $this->insert($data);
	}
	
	public function isHasInThisMonth($mid, $type)
	{
		$year  = (int)date('Y');
		$month = (int)date('m');
		
		if(!$this->_userAgreements){
			$this->_userAgreements = $this->fetchAll($this->quoteInto('MID = ?', $mid));
		}
		
		foreach($this->_userAgreements as $agreement){
			if(  $agreement->type == $type  &&  date('m.Y', strtotime($agreement->date_created)) ==  date('m.Y')  ){ 
				return true; 
			}
		}
		return false;
	}
	
	private function prepareAdditional($raw)
	{
		if(empty($raw)){ return false; }
		return Zend_Json::encode($raw);
	}
	
}