<?php
class HM_Dormitory_Refund_RefundService extends HM_Service_Abstract
{
	
	
	
	public function insert($raw)
	{
		$data = array(
			'type' 							=> (int)$raw['type'],
			'MID' 							=> (int)$raw['MID'],
			'fio' 							=> strip_tags($raw['fio']), 
			'email' 						=> strip_tags($raw['email']),
			'course' 						=> (int)$raw['course'],
			'specialty' 					=> strip_tags($raw['specialty']),
			'faculty' 						=> strip_tags($raw['faculty']),
			'phone' 						=> strip_tags($raw['phone']),
			'bank_name' 					=> strip_tags($raw['bank_name']),
			'inn' 							=> strip_tags($raw['inn']),
			'kpp' 							=> strip_tags($raw['kpp']),
			'bik' 							=> strip_tags($raw['bik']),
			'correspondent_account' 		=> strip_tags($raw['correspondent_account']),
			'settlement_account' 			=> strip_tags($raw['settlement_account']),
			'recipient_name' 				=> strip_tags($raw['recipient_name']),
			'recipient_personal_account' 	=> strip_tags($raw['recipient_personal_account']),
			'date_created' 					=> new Zend_Db_Expr("NOW()"),
		);
		
		return parent::insert($data);
	}
	
}