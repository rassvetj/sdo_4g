<?php
class HM_Club_ClubService extends HM_Service_Abstract
{
    /**
	 * список всех кружков для вывода в SELECT-е
	*/
	public function getSelectClubList(){
		$res 	= $this->fetchAll(array(), 'faculty, name');
		$data 	= array();
		if(empty($res)){ return $data ; }
		
		foreach($res as $i){
			$data[$i->faculty][$i->club_id] = $i->name;			
		}
		return $data;
	}
	
	public function getById($club_id){
		return $this->getOne($this->fetchAll($this->quoteInto('club_id = ?', $club_id)));		
	}
	
	
	public function getName($club_id){
		$club = $this->getById($club_id);
		if(!$club){ return false; }
		return $club->name;
	}
	
	
}