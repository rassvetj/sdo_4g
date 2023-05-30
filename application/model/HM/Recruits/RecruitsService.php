<?php
class HM_Recruits_RecruitsService extends HM_Service_Abstract
{
   	public function getRecruitInfo($mid_external){		
		return $this->getOne($this->fetchAll(
			$this->quoteInto(array('mid_external = ?', ' AND (isConfirmed IS NULL OR isConfirmed = ?)'), array($mid_external, 0))			
		));		
	}
	
	/**
	* получаем звания военнослужащих
	*/
	public function getRanks(){
		$select = $this->getSelect();
		$select->from('recruit_ranks', array('rank_id', 'name'));		
		$res = $select->query()->fetchAll();
		$list = array();
		if(empty($res)) { return $list; }
		foreach($res as $r){ $list[$r['rank_id']] = $r['name']; }
		return $list;		
	}
	
	/**
	* получаем Состав (профиль)
	*/
	public function getProfiles(){
		$select = $this->getSelect();
		$select->from('recruit_profiles', array('profile_id', 'name'));		
		$res = $select->query()->fetchAll();
		$list = array();
		if(empty($res)) { return $list; }
		foreach($res as $r){ $list[$r['profile_id']] = $r['name']; }
		return $list;		
	}
	
	/**
	* получаем военкоматы
	*/
	public function getRecruitmentOffices(){
		$select = $this->getSelect();
		$select->from('recruitment_offices', array('id_external', 'name', 'address', 'code'));		
		$select->order('name');
		$res = $select->query()->fetchAll();
		$list = array();
		if(empty($res)) { return $list; }
		foreach($res as $r){
			$name = $r['name'];
			$name = (!empty($r['address']))	?($name.' ('.$r['address'].')')	:($name);
			$name = (!empty($r['code']))	?($name.' код подразделения '.$r['code'])		:($name);
			$list[$r['id_external']] = $name;
		}
		return $list;		
	}
	
	
	
}