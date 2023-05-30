<?php
# задать права на этот контроллер только для студентов
class Ticket_PayController extends HM_Controller_Action
{
    protected $_ticketService = null;
    protected $_ticketID  = 0;    
    
    public function init()
    {		
		//$this->_ticketID = (int) $this->_getParam('TicketID', 0);
        //$this->_ticketService = $this->getService('Ticket');				
        //parent::init();
    }
    
    
    public function failAction()
    {
		if(empty($_GET['orderId'])){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Указан неверный параметр'))
			);
			$this->_redirect('/');		
		}	
		$ticket = $this->getService('Ticket')->getTicketBySberbankId(strip_tags($_GET['orderId']));	
		
		if(!$ticket){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Звявка не найдена'))
			);
			$this->_redirect('/');		
		}
		
		$this->view->setHeader(_('Оплата не прошла'));	
		
		$user = $this->getService('User')->getCurrentUser();
		if($ticket->mid_external != $user->mid_external){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('У вас нет доступа к этому разделу'))
			);
			$this->_redirect('/');				
		} 
		$ticketInfo = $this->getService('Ticket')->getTicketInfo($ticket); # инофо о заказе тянем со сбера			
		
		if($ticketInfo){
			if(empty($ticket->status) && $ticketInfo['orderStatus'] != 2){					
				$this->getService('Ticket')->setTicketStatus($ticket->ticket_id, HM_Ticket_TicketModel::PAY_FAIL); # сохраняем статус заказа.
			}	
			
			if(!empty($ticketInfo['actionCodeDescription'])){
				$this->view->reason = $ticketInfo['actionCodeDescription'];
			}
		}
	}
	
	public function readyAction()
    {		
		$user = $this->getService('User')->getCurrentUser();
		
		
		
		
		if(empty($_GET['orderId'])){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Указан неверный параметр'))
			);
			$this->_redirect('/');		
		}	
		
		
		$ticket = $this->getService('Ticket')->getTicketBySberbankId(strip_tags($_GET['orderId']));		
		
		if(!$ticket){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Звявка не найдена'))
			);
			$this->_redirect('/');		
		}
		
		$this->view->setHeader(_('Оплата по карте успешно произведена'));
		$user = $this->getService('User')->getCurrentUser();
		if($ticket->mid_external != $user->mid_external){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('У вас нет доступа к этому разделу'))
			);
			#if($user->MID == 5829){
				
			#} else {
				$this->_redirect('/');
			#}			
		} 
		
		$ticketInfo = $this->getService('Ticket')->getTicketInfo($ticket); # инофо о заказе тянем со сбера					
		if($ticketInfo){
			if(empty($ticket->status) && $ticketInfo['orderStatus'] == 2){					
				$this->getService('Ticket')->setTicketStatus($ticket->ticket_id, HM_Ticket_TicketModel::PAY_SUCCSESS); # сохраняем статус заказа.
			}
			$this->view->LastName 			= $user->LastName;
			$this->view->FirstName 			= $user->FirstName;
			$this->view->orderNumber 		= $ticketInfo["orderNumber"];
			$this->view->cardholderName 	= $ticketInfo["cardAuthInfo"]["cardholderName"];
			$this->view->pan 				= $ticketInfo["cardAuthInfo"]["pan"];
			$this->view->authDateTime 		= $ticketInfo["authDateTime"];
			$this->view->approvedAmount		= $ticketInfo["paymentAmountInfo"]["approvedAmount"];						
		}			
		
		
	}
}