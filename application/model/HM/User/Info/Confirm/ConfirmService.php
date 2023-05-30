<?php
class HM_User_Info_Confirm_ConfirmService extends HM_Service_Abstract
{
	public function save($data, $user_id)
	{
		$exsist_row = $this->getByUser($user_id);
		if($exsist_row){
			$data['confirm_id'] = $exsist_row->confirm_id;
			return $this->update($data);
		}		
		$data['MID'] = $user_id;		
		return $this->insert($data);
	}
	
	
	public function getByUser($user_id)
	{
		return $this->getOne($this->fetchAll($this->quoteInto('MID = ?', $user_id)));
	}	
	
	public function update($data)
	{
		$data['date_update'] = new Zend_Db_Expr('NOW()');
		return parent::update($data);
	}
	
	public function insert($data)
	{
		$data['date_created'] = new Zend_Db_Expr('NOW()');
		return parent::insert($data);
	}
	
}