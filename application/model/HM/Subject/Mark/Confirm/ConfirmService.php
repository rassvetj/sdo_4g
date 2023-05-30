<?php
class HM_Subject_Mark_Confirm_ConfirmService extends HM_Service_Abstract
{
	
	public function save($subject_id, $marksheet_external_id, $mark = false, $student_id = false)
	{
		
		$user_id 	= $this->getService('User')->getCurrentUserId();
		$exsist_row = $this->getRow($user_id, $subject_id, $marksheet_external_id, $student_id);
		if($exsist_row){
			return $exsist_row->confirm_id;
		}	
		
		$data = array(
			'subject_id' 			=> (int)$subject_id,			
			'tutor_id' 				=> (int)$user_id,
			'marksheet_external_id'	=> (int)$marksheet_external_id,			
		);
		
		if($mark !== false){
			$data['mark'] = str_replace(',', '.', $mark);
		}
		if($student_id !== false){
			$data['student_id'] = (int)$student_id;
		}
		
		return $this->insert($data);
	}
	
	
	public function getRow($user_id, $subject_id, $marksheet_external_id, $student_id = false)
	{
		if($student_id){
			$criteria = $this->quoteInto(	array('tutor_id = ?', ' AND subject_id = ?', ' AND 	marksheet_external_id = ?', ' AND 	student_id = ?'), 
											array($user_id, 			$subject_id, 			$marksheet_external_id, 			$student_id));
		} else {
			$criteria = $this->quoteInto(	array('tutor_id = ?', ' AND subject_id = ?', ' AND 	marksheet_external_id = ?'), 
											array($user_id, 			$subject_id, 			$marksheet_external_id));
		}
		return $this->getOne($this->fetchAll($criteria));
	}
	
	public function insert($data)
	{
		$data['date_created'] = new Zend_Db_Expr('NOW()');
		return parent::insert($data);
	}
	
	public function getBySubject($subject_id)
	{
		return $this->fetchAll($this->quoteInto('subject_id = ?', $subject_id));
	}
	
	public function getByMarksheet($marksheet_external_id)
	{
		return $this->fetchAll($this->quoteInto('marksheet_external_id = ?', $marksheet_external_id));
	}
	
	
}