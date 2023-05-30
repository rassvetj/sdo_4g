<?php
class HM_CertificatesStudent_CertificatesStudentModel extends HM_Model_Abstract
{
	const TIME_DELAY    = 604800; # время задержки между созданием новой справки. 1 неделя
	const LIMIT_PER_DAY = 5; # Лимит справок в день
	
	const TYPE_CONFIRMING_STUDENT = 1; # Справка, подтверждающая статус студента 
	
	
	const MAIL_TO_ERROR = 'dekanat@rgsu.net';
	#const MAIL_TO_ERROR = 'HramovSV@rgsu.net';
	
	const PREFIX_NUMBER = 'ЭД/РГСУ/'; # префикс для номера справки
	
	
	/**
	 * Список разрешенных для вывода в справке типов приказов
	*/
	public static function getAllowOrderTypes()
	{
		return array(
			'восстановление',
			'зачисление',
			'восстановление и перевод',
			'перевод',			
		);
	}
	
	
	public static function getBasedFormatted($based)
	{
		$based = mb_strtolower (trim($based), 'UTF-8');
		
		$list = array(
			'контракт'  => _('по договору об оказании платных образовательных услуг'),
			'бюджетная' => _('за счет бюджетных ассигнований федерального бюджета'),
		);
		return $list[$based];
	}
	
}
