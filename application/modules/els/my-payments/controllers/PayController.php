<?php
class MyPayments_PayController extends HM_Controller_Action
{
  
    public function cardAction()
    {
		
		$current_user		= $this->getService('User')->getCurrentUser();
		

		$requisite			= $this->getService('TicketRequisite')->getRequisiteByName($current_user->organization);		
		$serviceTicket		= $this->getService('Ticket');
		$request 			= $this->getRequest();
		
		$sum				= $request->getParam('sum', 0); # сумма		
		$sum 				= str_replace(',', '.', $sum);
		
		$contract_number	= $request->getParam('contract', false); # договор
		$contract_number 	= str_replace(',', '.', $contract_number);
		
		$contract_date		= $request->getParam('date', false); # дата договора в формате timestamp
		$contract_date		= date('d.m.Y', $contract_date);
		
		
		$dt 		= new DateTime();
		$cur_year	= (int)$dt->format('Y');		
		
		$dt_from  	= new DateTime('01.02.'.$cur_year.' 00:00:00');
		$dt_to    	= new DateTime('01.07.'.$cur_year.' 23:59:59');
		
		if(	$dt->getTimestamp() >= $dt_from->getTimestamp() && $dt->getTimestamp() <= $dt_to->getTimestamp()	) {
			$period_id = 1; # первое полугодие
		} else {
			$period_id = 2; # второе полугодие
		}
		
		
		
		
		$data = array(
			'sum_of' 				=> $sum, 
			'mid_external' 			=> $current_user->mid_external,
			'filial_id'				=> $requisite->requisite_id,
			'date_create' 	 		=> date('Y-m-d H:i:s'),
			'service_type_id'		=> $this->getService('Ticket')->getServiceTypeId('EDUCATION'),
			'service_education_id'	=> 1, # Платные образовательные услуги
			'type'			 		=> HM_Ticket_TicketModel::PAY_TYPE_ACQUIRING,
			'contract_number'		=> $contract_number,
			'contract_date'			=> $contract_date,
			'period_id'				=> $period_id, 
			
			# Плательщик
			'payerLastName'			=> $current_user->LastName,
			'payerFirstName'		=> $current_user->FirstName,
			'payerPatronymic'		=> $current_user->Patronymic,
			'payerEmail'			=> $current_user->Email,
			
			# Студент, за которого вносится оплата
			'forWhomPayLastName'	=> $current_user->LastName,
			'forWhomPayFirstName'	=> $current_user->FirstName,
			'forWhomPayPatronymic'	=> $current_user->Patronymic,
		);
		
		
		$insert = $serviceTicket->insert($data);
		if(!$insert){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось сформировать оплату'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'my-payments');
			die;
		}
		
		$isUpdate = $serviceTicket->update(array('ticket_id' => $insert->ticket_id, 'ticket_external_id' => 'SDO'.$insert->ticket_id));				
		if(!$isUpdate){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось присвоить номер документу'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'my-payments');
			die;
		}
		
		$insert->ticket_external_id = $isUpdate->ticket_external_id;
		$isPay  = $serviceTicket->payCard($insert);			
		
		if(!$isPay){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось сформировать запрос на оплату картой. Попробуйте позже.'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'my-payments');
			die;
		}
							
		

		if(isset($isPay['error'])){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => $isPay['error'])
			);			
			$this->_redirector->gotoSimple('index', 'index', 'my-payments');
			die;
		}
		
		
		if(isset($isPay['url'])){
			header('Location: '.$isPay['url']);
			die;
		}
		

		$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Неизвестная ошибка'))
			);			
		$this->_redirector->gotoSimple('index', 'index', 'my-payments');
		die;
	}
	
	
}




