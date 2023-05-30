<?php
class HM_Ticket_Requisite_RequisiteService extends HM_Service_Abstract
{
    public function getRequisiteByName($filial_name){
		$filial_name = $this->filterOrganization($filial_name);
		return $this->getOne($this->fetchAll($this->quoteInto('name = ?', $filial_name)));
	}
	
	
	public function filterOrganization($organization){
		if(HM_Ticket_Requisite_RequisiteModel::MAIN_ORGANIZATION == $organization){
			return HM_Ticket_Requisite_RequisiteModel::MAIN_REQUISITE_NAME;
		}
		return $organization;
	}
	
	public function getOrganizationNameList(){
		#return $this->fetchAll()->getList('requisite_id', 'name');
		return $this->fetchAll($this->quoteInto('requisite_id = ?', 1))->getList('requisite_id', 'name');		
	}
	
	/**
	 * список организаций, которым доступна оплата картой.
	*/
	public function getCardPaymentOrganizationList(){
		$res = $this->fetchAll($this->quoteInto('is_pay_card = ?', 1));
		$data = array();
		if($res && count($res)){
			foreach($res as $i){
				$data[] = $i->requisite_id;
			}
		}
		return $data;			
	}
	
	
	public function getRequisiteById($filial_id){
		return $this->getOne($this->fetchAll($this->quoteInto('requisite_id = ?', $filial_id)));
	}
	
}