<?php
class HM_Timetable_Additional_AdditionalService extends HM_Service_Abstract
{
	public function save($data)
	{
		$row_exist = $this->getOne($this->fetchAll($this->quoteInto('external_id = ?', $data['external_id'])));
		if(!$row_exist){
			return $this->insert($data);
		}
		return $this->update($row_exist->additional_id, $data);
	}
	
	public function insert($raw)
	{
		$data = array(
			'external_id' 	=> $raw['external_id'],
			'date_updated' 	=> new Zend_Db_Expr('NOW()'),
			'last_user_id' 	=> (int)$this->getService('User')->getCurrentUserId(),
		);
		$data = $this->joinData($data, $raw);
		
		return parent::insert($data);
	}
	
	public function update($additional_id, $raw)
	{
		$data = array(
			'date_updated' 	=> new Zend_Db_Expr('NOW()'),
			'last_user_id' 	=> (int)$this->getService('User')->getCurrentUserId(),
		);
		$data = $this->joinData($data, $raw);
		return $this->updateWhere($data, array('additional_id = ? ' => intval($additional_id)));
	}
	
	private function joinData($data, $raw)
	{
		if(array_key_exists('link', 		$raw)){ $data['link'] 			= $raw['link']; }
		if(array_key_exists('link2', 		$raw)){ $data['link2'] 			= $raw['link2']; }
		if(array_key_exists('link3', 		$raw)){ $data['link3'] 			= $raw['link3']; }
		if(array_key_exists('users', 		$raw)){ $data['users'] 			= (int)$raw['users']; }
		if(array_key_exists('file_path', 	$raw)){ $data['file_path'] 		= $raw['file_path']; }
		if(array_key_exists('subject_path', 	$raw)){ $data['subject_path'] 	= $raw['subject_path']; }
		return $data;
	}
	
	
}