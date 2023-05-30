<?php
class HM_Subject_Group_GroupService extends HM_Service_Abstract
{
	private $_groupIds = array(); # tutorId => subjectId => groupId
	
	/**
	 * назначается тьютор на группу в сесси.
	 * Если на сессию он не назначен, то назначается.
	 * Если группа не указана, то назначается целиком на сесию.
	*/
	public function assignTutorToGroup($tutor_id, $session_id, $group_id = false){
		if(!$tutor_id || !$session_id){ return false; }
		
		try {			
			$tutorService = $this->getService('Tutor');
			$isAssign = $tutorService->getOne($tutorService->fetchAll($this->quoteInto(array('CID = ?', ' AND MID = ?'), array($session_id, $tutor_id))));			
			
			if(!$isAssign){						
				$isAssign = $tutorService->insert(array(
								'MID' => $tutor_id,
								'CID' => $session_id,
								'date_assign' => date('Y-m-d 23:59:59',time()),
				));				
				if(!$isAssign){ return false; }
			}
			
			if(!$group_id){ return true; }
			
			$isAssignGroup = $this->getOne($this->fetchAll($this->quoteInto(array('CID = ?', ' AND MID = ?', ' AND GID = ? '), array($session_id, $tutor_id, $group_id))));			
			
			if($isAssignGroup) { return true; }
			
			return $this->insert(array(
						'MID' 			=> $tutor_id,
						'CID' 			=> $session_id,
						'GID' 			=> $group_id,
						'date_assign' 	=> date('Y-m-d H:i:s',time()),
					)); 			
		} catch (Exception $e) {									
			return false;
		}
	}
	
	/**
	 * Удаление всех назначенных тьюторов с группы
	 *
	*/
	public function unassignTutorToGroup($session_id, $group_id){
		if(!$session_id || !$group_id){ return false; }		
		$this->deleteBy($this->quoteInto(array(' CID = ? ', ' AND GID = ? '), array($session_id, $group_id)));
		return true;		
	}
	
	/**
	 * Удаление назначения указанного тьюторов с группы
	*/
	public function unassignTutor($tutor_id, $session_id, $group_id){
		if(!$tutor_id || !$session_id || !$group_id){ return false; }		
		$this->deleteBy($this->quoteInto(array('MID = ? ', ' AND CID = ? ', ' AND GID = ? '), array($tutor_id, $session_id, $group_id)));
		return true;		
	}
	
	
	/**
	 * Удаление тьютора со всех групп сессии
	 * @return int count rows
	*/
	public function unassignTutorToGroupAll($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){ return false; }
		
		$where = $this->quoteInto(array(' CID = ? ', ' AND MID = ? '), array($subject_id, $tutor_id));
		return $this->deleteBy($where);		
	}
	
	# избыточные данные. Сохраняем в моделе данные по всем сессиям тьютора.
	# нужно хранить tutor_id в _groupIds для случаев, когда метод вызывается один за другим для разных тьюторов.
	public function getGroupIds($subject_id, $tutor_id)
	{
		if(!array_key_exists($tutor_id, $this->_groupIds)){
			$items = $this->fetchAll($this->quoteInto('MID = ?', $tutor_id));
			if(count($items)){
				foreach($items as $item){
					$this->_groupIds[$tutor_id][$item->CID][$item->GID] = $item->GID;
				}
			}
		}
		return $this->_groupIds[$tutor_id][$subject_id];
	}
	
	
}
