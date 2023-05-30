<?php
class HM_Survey_Answers_AnswersService extends HM_Service_Abstract
{
	public function getAnswerList($type_id){
		if(empty($type_id)){ return array(); } 
		$select = $this->getSelect();
		$select->from(array('q' => 'survey_questions'),
			array(				
				'question_code' => 'q.code',
				'answer_id' 	=> 'a.answer_id',				
				'answer_name' 	=> 'a.name',
			)
		);
		$select->joinLeft(array('a' => 'survey_answers'), 'q.question_id = a.question_id AND q.type = a.type', array());		 
        $select->where($this->quoteInto('q.type = ?', $type_id));
        $result = $select->query()->fetchAll();
		$data = array();
		if(!empty($result)){
			foreach($result as $i){
				if(!empty($i['answer_id'])){
					$data[$i['question_code']][$i['answer_id']] = $i['answer_name'];
				} else {
					$data[$i['question_code']] = array();
				}
			}			
		}		
		return $data;
	}


  
  
    
}