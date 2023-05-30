<?php
class HM_Club_Claim_ClaimService extends HM_Service_Abstract
{
	
	/**
	 * может ли студент подавать/отменять заявку.
	*/
	public function isPeriodAvailable(){
		$cur_timestamp 		= time();
		$available_periods	= HM_Club_Claim_ClaimModel::getAvailablePeriods();	
		foreach($available_periods as $i){
			if(	$i['begin'] <= $cur_timestamp && $cur_timestamp <= $i['end']	){
				return true;
			}
		}
		return false;		
	}
	
	public function isExist($user_id){
		$row = $this->getByUser($user_id);
		if(empty($row)){ return false; }
		return true;		
	}
	
	public function getByUser($user_id){
		return $this->getOne($this->fetchAll($this->quoteInto('user_id = ?', $user_id)));
	}
	
	public function add($raw){
		$user_id = (int)$raw['user_id'];
		$club_id = (int)$raw['club_id'];
		
		if(empty($user_id) || empty($club_id)){
			return false;			
		}
		
		$data = array(
			'user_id' 		=> $user_id,
			'club_id' 		=> $club_id,
			'group_name' 	=> $raw['group_name'],
			'fio' 			=> $raw['fio'],
			'email' 		=> $raw['email'],	
			'date_created'	=> new Zend_Db_Expr('NOW()'),
		);		
		$new_row = $this->insert($data);		
		if(!$new_row){ return false; }
		return $new_row;		
	}
	
	public function deleteByUser($user_id){
		if(empty($user_id)){ return false; }		
		return $this->deleteBy($this->quoteInto('user_id = ?', $user_id));			
	}
	
}