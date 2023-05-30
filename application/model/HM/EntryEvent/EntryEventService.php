<?php
class HM_EntryEvent_EntryEventService extends HM_Service_Abstract
{ 

	/**
	 * Уже было согласие на это мероприятие
	*/
	public function hasAgreement($user_id, $event_id){
		return $this->getOne($this->fetchAll(
						array(
							'user_id = ?'	=> $user_id,
							'event_id = ?'	=> $event_id,
						)
		));		
	}
	
	public function addAgreement($user_id, $event_id){
		$user_id = (int)$user_id;
		if(!$user_id){ return false; }
		
		return $this->insert(array(
                'user_id'		=> $user_id,
                'event_id'  	=> $event_id,
				'date_created'	=> new Zend_Db_Expr('NOW()'),
		));		
	}
	
	
	public function getInnerBlock($event_id){
		$allow_users = HM_EntryEvent_EntryEventModel::getAllowUsers($event_id);
		$user_id	 = $this->getService('User')->getCurrentUserId();
		
		if(!in_array($user_id, $allow_users)){ return false; }
		
		if($this->hasAgreement($user_id, $event_id)){ return false; }
		
		$protocol = isset($_SERVER['HTTPS'])	? 'https' : 'http';
		$protocol = 'http';		
		echo file_get_contents($protocol.'://'.$_SERVER['HTTP_HOST'].'/entry-event/inner/index/event_id/'.$event_id);
	}
	
}