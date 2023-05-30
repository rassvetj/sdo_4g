<?php
class HM_Survey_Questions_QuestionsService extends HM_Service_Abstract
{
	private $_sessionNamespace = 'Survey';
	
	/**
	 * получаем результат предыдущих ответов
	*/
	public function getSessionData(){
		$session 	 	= new Zend_Session_Namespace($this->_sessionNamespace);
		return $session->data; 
	}
	
	public function saveSessionData($data){
		$session 	 	= new Zend_Session_Namespace($this->_sessionNamespace);
		$session->data	= $data;
	}
	
	public function clearSessionData(){
		$session 	 	= new Zend_Session_Namespace($this->_sessionNamespace);
		$session->data	= NULL;
	}
	
	
	public function getFirst($type_id){
		return	$this->getOne(	$this->getAllByType($type_id)	);
	}
	
	public function getById($question_id){
		 return $this->getOne($this->fetchAll($this->quoteInto('question_id = ?', $question_id)));		
	}
	
	
	public function getAllByType($type_id){
		return $this->fetchAll(
							$this->quoteInto('type = ?', $type_id),
							array('priority DESC', 'question_id')
				);
	}
	
	public function getCount($type_id){
		return count($this->getAllByType($type_id));		
	}
	
	/**
	 * 
	*/
	public function getNext($type_id, $skip_ids){		
		$skip_ids	= (is_array($skip_ids)) ? $skip_ids : array($skip_ids);		
		$skip_ids[] = 0; # на случяй, если этот массив будет пустым. 
		
		return	$this->getOne($this->fetchAll(
								$this->quoteInto(array('type = ?', ' AND question_id NOT IN (?)'), array($type_id, $skip_ids)),
								array('priority DESC', 'question_id')
				));
		
	}
	
  
  
    
}