<?php
class HM_Languages_Result_ResultService extends HM_Service_Abstract
{
	
	/**
	 * @returm false OR HM_Languages_Result_ResultModel Object
	*/
	public function add($item_id, $user_id)
	{
		$item_id = (int)$item_id;
		$user_id = (int)$user_id;
		
		if(empty($item_id) || empty($user_id)){ return false; }
		
		
		$isHas = $this->getOne($this->fetchAll($this->quoteInto(array('languages_id = ?', ' AND MID = ?'), array($item_id, $user_id)))	);
		
		if($isHas){ return false; }		
		
		$data = array(
			'MID' 			=> $user_id,
			'languages_id' 	=> $item_id,
			'date_created' 	=> new Zend_Db_Expr('NOW()'),
		);
		
		try {
			$isInserted = $this->insert($data);		
		} catch (Exception $e) {
			$isInserted = false;
		}
		
		if(!$isInserted){ return false; }
		return $isInserted;
	}
	
	
	public function remove($item_id, $user_id)
	{
		$item_id = (int)$item_id;
		$user_id = (int)$user_id;
		
		if(empty($item_id) || empty($user_id)){ return false; }
		
		
		$isDelete = $this->deleteBy(array('languages_id = ?' => $item_id, 'MID = ?' => $user_id));
		
		if($isDelete){ return true; }
		return false;
	}
	
	
	
}
