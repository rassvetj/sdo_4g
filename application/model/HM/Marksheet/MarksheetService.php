<?php
class HM_Marksheet_MarksheetService extends HM_Service_Abstract
{
	
	/**
	 * Получение информации по ведомости/ИН
	 * Если группа не задана, берем первую запись.	 
	 * 
	*/
	public function getInfo($subject_id, $user_id = false, $group_external_id = false){
		if(empty($subject_id)){ return false; }
		$subject = $this->getService('Subject')->getById($subject_id);
		if(empty($subject->external_id)){ return false; }
		
		if(!empty($user_id)){
			$user = $this->getService('User')->getById($user_id);
			if(empty($user->mid_external)){ return false; }
			return $this->getOne($this->fetchAll($this->quoteInto(array(' subject_external_id = ? ', ' AND mid_external = ? '), array($subject->external_id, $user->mid_external) )));
		}
		
		$collections = $this->fetchAll($this->quoteInto("subject_external_id = ? AND ( mid_external IS NULL OR mid_external = 0 OR mid_external = '' ) ", $subject->external_id));
		
		if(empty($collections)){ return false; }
		
		if(empty($group_external_id)){	return $collections->current();	}
		
		foreach($collections as $item){
			if($item->group_external_id == $group_external_id){ return $item; }
		}
		return false; 
	}
	
	
	public function getBySubjectCode($subject_external_id)
	{
		return $this->fetchAll($this->quoteInto('subject_external_id = ?', $subject_external_id));
	}
	
	
	public function getById($marksheet_id)
	{
		$marksheet_id = (int)$marksheet_id;
		if(empty($marksheet_id)){ return false; }
		return $this->getOne($this->fetchAll($this->quoteInto('marksheet_id = ?', $marksheet_id)));
	}
	
}