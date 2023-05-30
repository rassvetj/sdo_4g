<?php
class HM_Workload_WorkloadModel extends HM_Model_Abstract
{
    const TYPE_WELCOME_MESSAGE = 1; //-приветственное сообщение
    const TYPE_SUBJECT_ASSESSMENT = 2; //--выставление оценки за занятие.
    const TYPE_FORUM_ANSWER = 3; //--ответ на форуме
    const TYPE_SHEET_PASSED = 4; //--ведомость передана
	
	const SAFE_TIME = 259200; //--период, за который не начисляется штраф. 3 дня		
	//const SAFE_TIME = 0;
	
	//--диапазоны оценок.
	const MARK_2 = 65;
	const MARK_3 = 75;
	const MARK_4 = 85;
	const MARK_5 = 100;
	
	//-_название может измениться, все зависит от модели уведомлений в системе.
	const MESSAGE_TEMPLATE_MARK_2 = 2;
	const MESSAGE_TEMPLATE_MARK_3 = 3;
	const MESSAGE_TEMPLATE_MARK_4 = 4;
	const MESSAGE_TEMPLATE_MARK_5 = 5;
	
	
	const DISABLE_VIOLATION_WELCOME_MESSAGE = true; //Просрочка по прив. письму. Временно, до тех пор, пока тьюторы не начнут отправлять письма
	const DISABLE_VIOLATION_SHEET_PASSED = true; //Просрочка по ведомости. Временно, до тех пор, пока тьюторы не начнут предоставлять ведомости.
	
	const DISABLE_WORKLOAD_WELCOME_MESSAGE = true; //Нагрузка по прив. письму. Временно, до тех пор, пока тьюторы не начнут отправлять письма
	const DISABLE_WORKLOAD_SHEET_PASSED = true; //Нагрузка по ведомости. Временно, до тех пор, пока тьюторы не начнут предоставлять ведомости.
	
	 const REPORT_TYPE_CURRENT 	= 1;  # Текущие сессии
	 const REPORT_TYPE_EXTENDED	= 2;  # Продленные сессии
	 const REPORT_TYPE_ALL		= 3;  # Текущие + Продленные сессии
	
	/**
	 * список шаблонов сообщений
	*/
	static public function getTypes() {
        return array(
            self::TYPE_WELCOME_MESSAGE     	=> _('Приветственное сообщение'),
            self::TYPE_SUBJECT_ASSESSMENT 	=> _('Выставление оценки за занятие'),
			self::TYPE_FORUM_ANSWER 		=> _('Ответы на форуме'),              
			self::TYPE_SHEET_PASSED 		=> _('Ведомость передана'),              
        );
    }
	
	/**
	 * Определяет, какой тип шаблона выбрать в зависимости от оценки
	*/
	static public function getMessageTemplate($curMark){		
		$curMark = (int) $curMark;		
		if($curMark < self::MARK_2){			
			return self::MESSAGE_TEMPLATE_MARK_2;			
		} elseif(self::MARK_2 <= $curMark && $curMark < self::MARK_3){			
			return self::MESSAGE_TEMPLATE_MARK_3;			
		} elseif(self::MARK_3 <= $curMark && $curMark < self::MARK_4){
			return self::MESSAGE_TEMPLATE_MARK_4;
		} elseif(self::MARK_4 <= $curMark && $curMark <= self::MARK_5){
			return self::MESSAGE_TEMPLATE_MARK_5;			
		}
		return false;
	}
	
	
	/**
	 * Получает текст сообщения для уведомления. Выводится в разделе /subject/message/motivation/
	*/
	static public function getMessageText($template){
		if(!$template){
			return false;
		}
		$text = array(
			self::MESSAGE_TEMPLATE_MARK_2 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 2. Трудитесь дальше.'),
			self::MESSAGE_TEMPLATE_MARK_3 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 3. Уже лучше.'),
			self::MESSAGE_TEMPLATE_MARK_4 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 4. Неплохо.'),
			self::MESSAGE_TEMPLATE_MARK_5 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 5. Очень хорошо.'),
		);
		if(isset($text[$template])){
			return $text[$template];			
		}
		return false;			
	}


	/**
	 * Получает текст сообщения для уведомления. Короткая форма. Для отображения в разделе оповещений.
	*/
	/*
	static public function getMessageTextShort($template){
		if(!$template){
			return false;
		}
		$text = array(
			self::MESSAGE_TEMPLATE_MARK_2 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 2. Трудитесь дальше.'),
			self::MESSAGE_TEMPLATE_MARK_3 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 3. Уже лучше.'),
			self::MESSAGE_TEMPLATE_MARK_4 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 4. Неплохо.'),
			self::MESSAGE_TEMPLATE_MARK_5 => _('Уважаемый [USER_NAME], Вы закрыли сессию на 5. Очень хорошо.'),
		);
		if(isset($text[$template])){
			return $text[$template];			
		}
		return false;			
	}	
	*/
	
}