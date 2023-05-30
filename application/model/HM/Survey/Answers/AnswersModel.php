<?php
class HM_Survey_Answers_AnswersModel extends HM_Model_Abstract
{
   	public static function otherAnswers(){ #id ответов в БД, для которых значения брать из поля с постфиксом "_other"
		return array(
			31, 41, 78, 99
		);
	}

}