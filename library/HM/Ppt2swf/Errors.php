<?php


class HM_Ppt2swf_Errors{

	const WRONG_FILETYPE               = 0;
	const WRONG_APIKEY                 = 1;
    const CONVERT_COUNT_PER_DAY_EXCEED = 3;
	
	
	public static function getMessages(){
		return array(self::WRONG_FILETYPE => _('Конвертация невозможна. Неправильный формат файла.'),
		             self::WRONG_APIKEY   => _('Неправильный ApiKey. Проверьте настройки.'),
		             self::CONVERT_COUNT_PER_DAY_EXCEED => _('Исчерпан лимит конвертаций в день.'),
		);
	
	
	}
	
	public static function getMessage($errCode){
		$messages = self::getMessages();
		return $messages[$errCode];
	}
	
	
	
}