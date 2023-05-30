<?php
class HM_Survey_SurveyService extends HM_Service_Abstract
{
	/**
	 * старый формат: код_вопроса => Текст_ответа
	 * Новый формат: id_вопроса => array(
											id_ответа  	 # для чекбоксов будет или массив 
											текст_ответа # для чекбоксов будет или массив 
											код_вопроса
										)
	*/
	public function saveSessionData($data){
		if(empty($data)){ return false; }
		$oldFormat = array();
		$answer_ids = array();		
		foreach($data['answers'] as $i){				
			if(empty($i['question_code'])){ continue; }
			$answers = (is_array($i['answer_name'])) ? implode(', ', $i['answer_name']) : $i['answer_name'];
			$oldFormat[	$i['question_code']	] = $answers;
			$answer_ids[$i['answer_id']] = $i['answer_id'];					
		}
		
		$values = array();
		if(!empty($answer_ids)){		
			$values = $this->getService('SurveyAnswers')->fetchAll( $this->quoteInto(array('type = ?', ' AND answer_id IN (?)'), array($data['type_id'], $answer_ids)) )->getList('answer_id', 'value');			
		}
		
		if($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_MOTIVE){			
			$divideGroups = HM_Survey_SurveyModel::getProfMotiveGroups();
		
		} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){
			$divideGroups = HM_Survey_SurveyModel::getProfFutureGroups(); # зачем тут?
		
		} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG){
			$divideGroups = HM_Survey_SurveyModel::getExpressDiagGroups();
			
		} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_SELF){
			$divideGroups = HM_Survey_SurveyModel::getProfSelfGridAnswer();
		}
		
		$summ = 0;
		foreach($data['answers'] as $q_id => $i){	
			$val = (int)$values[$i['answer_id']];
			
			# анкета 2
			if($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_MOTIVE){ # разделение по группам ответы.
				foreach($divideGroups as $group_name => $codes){
					if(!isset($data['result']['total_point'][$group_name])){
						$data['result']['total_point'][$group_name] = 0;
					}
					if(in_array($i['question_code'], $codes)){
						$data['result']['total_point'][$group_name] += $val;
					}
				}
			# анкета 3
			} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){ # группировка по выбранным ответам а) б) в) г)
				$data['result']['total_point'][$val]++;
				$summ = $data['result']['total_point']; # для данного типа в summ - массив ответов по группам.
			
			# анкета 4
			} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG){ # разлеление ответов к А или к Б с назначением им баллов и распределением на 6 групп.				
				$answer_A = 0;
				$answer_B = 0;
				$number_q = (int) preg_replace('~[^0-9]+~','',$i['question_code']); # порядковый номер вопроса
				if($val == 1){ # выбрали А
					$answer_A = 3;
				} elseif($val == 1){ # выбрали B
					$answer_B = 3;
				} elseif($val == 3){ # согласны с А и Б, или не согласны ни с А, ни с Б, но больше склоняются к А
					$answer_A = 2;
					$answer_B = 1;
				} elseif($val == 4){ # согласны с А и Б, или не согласны ни с А, ни с Б, но больше склоняются к Б
					$answer_A = 1;
					$answer_B = 2;
				}
				
				
				foreach($divideGroups as $group_name => $codes){
					if(!isset($data['result']['total_point'][$group_name])){
						$data['result']['total_point'][$group_name] = 0;
					}
					if(in_array($number_q.'a', $codes)){
						$data['result']['total_point'][$group_name] += $answer_A;
					}
					
					if(in_array($number_q.'b', $codes)){
						$data['result']['total_point'][$group_name] += $answer_B;
					}
				}
				
				$data['answers'][$q_id]['a'] = $answer_A;
				$data['answers'][$q_id]['b'] = $answer_B;
				
			# анкета 5
			} elseif($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_SELF){ # разделение по группам ответы.
				$number_q		= (int) preg_replace('~[^0-9]+~','',$i['question_code']); # порядковый номер вопроса
				$ansver_char	= ($val == 1) ? 'a' : 'b'; # выбрали А или Б
				
				
				
				foreach($divideGroups as $group_name => $codes){
					if(!isset($data['result']['total_point'][$group_name])){
						$data['result']['total_point'][$group_name] = 0;
					}
					if(in_array($number_q.$ansver_char, $codes)){
						$data['result']['total_point'][$group_name]++;
					}
				}
				
			
			# анкета 1
			} else {
				$summ += $val;					
			}			
			
			$data['answers'][$q_id]['answer_value'] = $val;
		}
		
		if($data['type_id'] == HM_Survey_SurveyModel::TYPE_PROF_FUTURE){
			if( 
				(
					$data['result']['total_point'][HM_Survey_SurveyModel::PF_PRODUCT] == $data['result']['total_point'][HM_Survey_SurveyModel::PF_SOCIAL]
					&&
					$data['result']['total_point'][HM_Survey_SurveyModel::PF_SOCIAL] == $data['result']['total_point'][HM_Survey_SurveyModel::PF_RESEARCH]				
				) || (
					$data['result']['total_point'][HM_Survey_SurveyModel::PF_SOCIAL] == $data['result']['total_point'][HM_Survey_SurveyModel::PF_RESEARCH]
					&&
					$data['result']['total_point'][HM_Survey_SurveyModel::PF_RESEARCH] == $data['result']['total_point'][HM_Survey_SurveyModel::PF_HUMANITAR]				
				)
			){
				return false; # некорректный результат. Нужно перепройт еще раз.
			}			 
		}
		
		
		
		if(		$data['type_id'] != HM_Survey_SurveyModel::TYPE_PROF_MOTIVE 
			 && $data['type_id'] != HM_Survey_SurveyModel::TYPE_PROF_FUTURE 
			 && $data['type_id'] != HM_Survey_SurveyModel::TYPE_EXPRESS_DIAG
			 && $data['type_id'] != HM_Survey_SurveyModel::TYPE_PROF_SELF
		){
			$data['result']['total_point']	= $summ;  # набрано баллов
		}
		
		$data['result']['code']  			= HM_Survey_SurveyModel::getCode($data['type_id'], $summ);		# код результата
		$data['result']['message_short'] 	= HM_Survey_SurveyModel::getMessage($data['type_id'], $summ);	# краткое описание резуьтата
		
		$insert_data = array(
					'mid_external'						=> $data['mid_external'],
					'type'								=> $data['type_id'],
					'data'								=> json_encode($oldFormat),					
					'data_details'						=> json_encode(array('answers' => $data['answers'], 'result' => $data['result'])),
					'DateCreated' 						=>  new Zend_Db_Expr("NOW()"),					
				);			
		return $this->getService('Survey')->insert($insert_data);		
	}
	
	
	public function getResultByType($type_id){
		if(empty($type_id)){ return false; }
		$mid_external = $this->getService('User')->getCurrentUser()->mid_external;
		if(empty($mid_external)){ return false; }
		
		return $this->getOne(	$this->fetchAll($this->quoteInto(array('mid_external = ?', ' AND type = ?'), array($mid_external, $type_id)))	);
	}
}
