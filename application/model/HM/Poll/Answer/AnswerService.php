<?php
class HM_Poll_Answer_AnswerService extends HM_Service_Abstract
{

	public function synchronize($params){
        $question_id = (string) $params['question_id'];
        if (!isset($params['theme']) || !$params['theme']) {
            $params['theme'] = '';
        }
		//$db_answers = $this->fetchAll(array('quiz_id = ?' => $quiz_id, 'question_id = ?' => $question_id));
		if(!count($params['answers'])){
			
			// для вопроса со свободным ответом
            $find = $this->find($params['quiz_id'], $question_id, 0);
            $answer = array(
                        'quiz_id' => $params['quiz_id'],
                        'question_id' => $question_id,
                        'answer_id' => 0,
                        'question_title' => $params['question_title'],
                        'theme' => $params['theme'],
                        'answer_title' => _('свободный ответ'),
            );
            if(!count($find)) $this->insert($answer);
            else $this->update($answer);
				
		}
		else{
			foreach ($params['answers'] as $id => $title){
				$find = $this->find($params['quiz_id'], $question_id, $id);
				$answer = array(
                    'quiz_id' => $params['quiz_id'],
                    'question_id' => $question_id,
                    'answer_id' => $id,
                    'question_title' => $params['question_title'],
                    'theme' => $params['theme'],
                    'answer_title' => $title
                );
				if(!count($find)) $this->insert($answer);
				else $this->update($answer);
				
			}
		}
		return TRUE;
	}
	
}