<?php
class HM_DisabledPeople_DisabledPeopleService extends HM_Service_Abstract
{
    public function getSpecialFunds(){
		$select = $this->getSelect();
		$select->from('disabled_special_funds', array('code', 'name', 'description', 'group_id', 'isHidden'));
		$res = $select->query()->fetchAll();
		return $res;
	}
	
	
	public function getListFunds(){
		$res = $this->getSpecialFunds();
		if(empty($res)) { return array(); }
		
		$result = array();
		foreach($res as $i){
			if($i['isHidden'] == 1){ continue; }
			$group_id = (!empty($i['group_id'])) ? ($i['group_id']) : (HM_DisabledPeople_DisabledPeopleModel::TYPE_GROUP_OTHER);
			
			$result[$group_id][$i['code']] = $i['name'];
		}
		return $result;
		 
	}
	
}