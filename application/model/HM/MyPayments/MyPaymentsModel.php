<?php
class HM_MyPayments_MyPaymentsModel extends HM_Model_Abstract
{
   
	const THEME_SCHEDULE 		= 1; # График платежей 
	const THEME_RECEIVED 		= 2; # Поступившие платежи
	const THEME_DEBT_RESTRUCT	= 3; # Реструктуризация долга
	const THEME_OTHER 			= 4; # Прочее

	
	const MAIL_THEME_QUESTION = 'Вопрос по моим оплатам';
	
   
   public static function priceFormat($price, $currency = false)
   {
	   if(empty($price)){ $price = 0; }
	   
	   if(empty($currency)){
		   $currency = _('р').'.';
	   }
	   return ( (strpos($price, '.') === false) ? number_format($price, 0, ',', ' ') : number_format($price, 2, ',', ' ') ).' '.$currency;
   }
   
  
   public static function getThemeList()
   {
		return array(
			self::THEME_SCHEDULE 		=> _('График платежей'),
			self::THEME_RECEIVED 		=> _('Поступившие платежи'),
			self::THEME_DEBT_RESTRUCT 	=> _('Реструктуризация долга'),
			self::THEME_OTHER 			=> _('Прочее'),
		);
   }
   
   
   public function getMailByTheme($theme_id)
   {
	   $data = array(
			self::THEME_SCHEDULE 		=> 'dekanat@rgsu.net',
			self::THEME_RECEIVED 		=> 'oplata@rgsu.net',
			self::THEME_DEBT_RESTRUCT 	=> 'dekanat@rgsu.net',
			self::THEME_OTHER 			=> 'dekanat@rgsu.net',
	   );
	   return $data[$theme_id];
   }
  
   
}