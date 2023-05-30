<?php
class HM_Debtors_DebtorsModel extends HM_Model_Abstract
{
    const REASON_PASS_TO_MARK 	= 1;
    const REASON_NO_DATES 		= 2;
    const REASON_INCORRECT_DATE = 3;
    const REASON_PASS_TO_TOTAL_RATING 	= 4;
    const REASON_NOT_CHANGE 			= 5;
    const REASON_NOT_FOUND_USER 		= 6;
    const REASON_NOT_FOUND_SUBJECT 		= 7;
    const REASON_NEVER_ASSIGN_USER 		= 8;
    const REASON_SUBJECT_IS_CURRENT 	= 9;
	
	public static function getReasonList(){
		return array(
			self::REASON_PASS_TO_MARK 	=> 'Набрано больше 65 баллов',
			self::REASON_NO_DATES 		=> 'Не заданы даты продления',
			self::REASON_INCORRECT_DATE => 'Некорректная дата продления',
			self::REASON_PASS_TO_TOTAL_RATING	=> 'Рубежный рейтинг сдан на 65% или более',
			self::REASON_NOT_CHANGE				=> 'Даты продления не изменились',
			self::REASON_NOT_FOUND_USER			=> 'Не найден студент',
			self::REASON_NOT_FOUND_SUBJECT		=> 'Не найдена сессия',
			self::REASON_NEVER_ASSIGN_USER		=> 'Студент никогда не обучался на сессии',
			self::REASON_SUBJECT_IS_CURRENT		=> 'Сессия в текущих',
		);
	}
}