<?php
class HM_Internships_InternshipsModel extends HM_Model_Abstract
{
   
	const DEGREE_1 		= 1; # Владеет свободно
	const DEGREE_2 		= 2; # Читает и может объясниться
	const DEGREE_3 		= 3; # Читает и переводит со словарем
	const DEGREE_4 		= 4; # Не знает (Планирует изучать)
	
	# Грант на стажировку в 
	const TYPE_CHINA 	= 1;
	const TYPE_CZECH 	= 2;
	const TYPE_SLOVENIA	= 3;
	const TYPE_SLOVAKIA	= 4;
	
	const EMAIL_TO		= 'dekanat@rgsu.net';
	const EMAIL_THEME	= 'Заявка на стажировку';
	
	public static function getTypeList()
	{
		return array(
			self::TYPE_CHINA		=> _('Китае'),
			self::TYPE_CZECH 		=> _('Чехии'),
			self::TYPE_SLOVENIA 	=> _('Словении'),			
			self::TYPE_SLOVAKIA 	=> _('Словакии'),			
		);
	}
	
	# доступные типы
	public static function getTypeListAllow()
	{
		$types		= self::getTypeList();
		$valid_to	= self::validToTypes();
		
		foreach($valid_to as $type_code => $date){
			if(	strtotime($date) < time()	){
				unset($types[$type_code]);
			}			
		}
		return $types;
	}	
	
	
	public static function getDegreeList()
	{
		return array(
			self::DEGREE_1 	=> _('Владею свободно'),
			self::DEGREE_2 	=> _('Читаю и могу объясниться'),
			self::DEGREE_3 	=> _('Читаю и перевожу со словарем'),
			self::DEGREE_4	=> _('Не знаю (Планирую изучать)'),
		);
	}
	
	
	public static function getMailTo()
	{
		return self::EMAIL_TO;
	}
	
	
	public static function getTheme($type_code)
	{
		$country = self::getTypeName($type_code);
		return self::EMAIL_THEME.' в '.$country;
	}
	
	public static function getTypeName($code)
	{
		$list = self::getTypeList();
		return $list[$code];
	}
	
	
	# Если тут не указан тип, значит он бессрочный
	public static function validToTypes()
	{
		return array(
			self::TYPE_CHINA		=> '2000-02-07 16:00:00',			
			self::TYPE_CZECH 		=> '2020-03-02 16:00:00',
			self::TYPE_SLOVENIA 	=> '2020-03-09 16:00:00',
			self::TYPE_SLOVAKIA 	=> '2020-03-02 16:00:00', 
		);
	}
	
	
   
   
}

