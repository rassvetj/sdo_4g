<?php
class HM_Payment_PaymentService extends HM_Service_Abstract
{
    
	public function getById($payment_id){
		return $this->getOne($this->fetchAll($this->quoteInto('payment_id = ?', $payment_id)));		
	}
	
	
	public function getThemeList(){
		return array(
			1 => _('Корректировка недостоверных сведений в договоре'),
			2 => _('Неприкрепленная оплата по договору'),
			3 => _('Улучшение сервиса по договорам'),
		);
	}
	
	
}