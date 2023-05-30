<?php
class HM_Subject_User_UserService extends HM_Service_Abstract
{
	private $_studentIds = array(); # tutorId => subjectId => studentId
	
	/**
	 * закрепление тьютора за студентов в сессии
	 * не назначает тьютора на сессию, если он не назначен, но запись назначения делает.
	 * @return bool
	*/
	public function assignTutorToUsers($tutor_id, $subject_id, $student_id){		
		$isAssign = $this->getOne($this->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND SID = ?'), array($tutor_id, $subject_id, $student_id))));
		if($isAssign){ return true; }
			
		$isInsert = $this->insert(array(
						'MID' 			=> $tutor_id,
						'CID' 			=> $subject_id,
						'SID' 			=> $student_id,
						'date_assign'	=> date('Y-m-d H:i:s', time()),
					));		
		
		if($isInsert){ return true; }
		return false;		
	}	
	
	/**
	 * удаление нанзачения тьютора к студенту
	 * @return bool
	*/
	public function unAssignTutorToUsers($tutor_id, $subject_id, $student_id){
		$this->deleteBy($this->quoteInto(array('MID = ?', ' AND CID = ?', ' AND SID = ?'), array($tutor_id, $subject_id, $student_id)));
		return true;
	}
	
	
	/**
	 * удаление нанзачения тьютора со всех студентов
	 * @return int count rows
	*/
	public function unAssignTutorToUsersAll($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){ return false; }
		
		$where = $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($tutor_id, $subject_id));
		return $this->deleteBy($where);
	}
	
	# избыточные данные. Сохраняем в моделе данные по всем сессиям тьютора.
	# нужно хранить tutor_id в _studentIds для случаев, когда метод вызывается один за другим для разных тьюторов.
	public function getStudentIds($subject_id, $tutor_id)
	{
		if(!array_key_exists($tutor_id, $this->_studentIds)){
			$items = $this->fetchAll($this->quoteInto('MID = ?', $tutor_id));
			if(count($items)){
				foreach($items as $item){
					$this->_studentIds[$tutor_id][$item->CID][$item->SID] = $item->SID;
				}
			}
		}
		return $this->_studentIds[$tutor_id][$subject_id];
	}
	
}
