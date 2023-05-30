<?php
class HM_Ticket_Cost_CostService extends HM_Service_Abstract
{
	/*public function getUserCosts($mid_external){
		return $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external))->getList('year', 'cost');		
	}*/
	
	public function getUserCosts($mid_external){
		$res=$this->fetchAll($this->quoteInto('mid_external = ?', $mid_external));
		
		$data=array();
		
		foreach ($res as $i) {
			$data[$i->year]=array(
				'cost'=>$i->cost,
				'uik'=>$i->uik
			);
		}
		return $data;
	}
}