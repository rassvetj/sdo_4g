<?php
class Ticket_IndexController extends HM_Controller_Action
{
    protected $_ticketService = null;
    protected $_ticketID  = 0;    
    
    public function init()
    {		
		$this->_ticketID = (int) $this->_getParam('TicketID', 0);
        $this->_ticketService = $this->getService('Ticket');		
		
        parent::init();
    }
    
    
    public function indexAction()
    {		
		try {
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Оплата услуг'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$user = $this->getService('User')->getCurrentUser();
		
		$this->view->link_fast_pay_education	= false;
		$this->view->link_fast_pay_hostel		= false;
		
		$allow_fast_pay_links 					= $this->_ticketService->getAllowFastPayLinks();
		$this->view->link_fast_pay_education	= $allow_fast_pay_links[HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION];
		$this->view->link_fast_pay_hostel		= $allow_fast_pay_links[HM_Ticket_TicketModel::SERVICE_TYPE_HOSTEL];

		$orderCost 		= $this->getService('TicketCost')->getUserCosts($user->mid_external); 
		$orderPayments  = $this->getService('TicketPayment')->getUserPayments($user->mid_external);	
		$this->view->orderCost 		= $orderCost;
		$this->view->orderPayments  = $orderPayments;
		$this->view->formOrder 		= new HM_Form_Order();	
		
        $select = $this->_ticketService->getSelect();
        $select->from(
            array(
                't' => 'Ticket'
            ),
            array(
                'ticket_external_id'	=>	't.ticket_external_id',	
				'ticket_id'        		=> 't.ticket_id',                
				'mid_external'			=> 't.mid_external',
                'contract_number'		=> 't.contract_number',                
                'sum_of'				=> new Zend_Db_Expr('CAST(t.sum_of AS varchar)'),				
				'date_create'			=> 't.date_create',
				'filial'				=>	'filial.name',				
				'service_type'			=>	's_type.name',
				
				'service_education_id'	=>	't.service_education_id',
				'service_pool_id'		=>	't.service_pool_id',
				'service_library_id'	=>	't.service_library_id',
				'service_hotel_id'		=>	't.service_hotel_id',
				'service_laundry_id'	=>	't.service_laundry_id',
				
				//'fine_id'				=>	't.fine_id',
				//'hostel_id'				=>	't.hostel_id',
				//'hotel_id'				=>	't.hotel_id',
				//'journal_id'			=>	't.journal_id',
				//'journal_year'			=>	't.journal_year',
				//'journal_number'		=>	't.journal_number',
				'contract_date'			=>	't.contract_date',				
				//'library_card_number'	=>	't.library_card_number',
				
				'period'				=>  't.period_id',
				'period_begin'			=>	't.period_begin',
				'period_end'			=>	't.period_end',
				
				'payerFio' 				=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t.payerLastName, ' ') , t.payerFirstName), ' '), t.payerPatronymic)"),								
				'payerEmail'			=>	't.payerEmail',
				'payerAddress'			=>	't.payerAddress',
				
				'forWhomPayFio' 		=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t.forWhomPayLastName, ' ') , t.forWhomPayFirstName), ' '), t.forWhomPayPatronymic)"),								
				'status'				=>	't.status',
				'type'					=>	't.type',
            )
        );	
		$select->joinLeft(array('filial' => 'ticket_requisites'), 'filial.requisite_id = t.filial_id', array());		 
		$select->joinLeft(array('s_type' => 'ticket_service_types'), 's_type.type_id = t.service_type_id', array());		 
		$select->where($this->_ticketService->quoteInto('mid_external=?', str_replace(' ', '', $user->mid_external))); //--На случай, если встретится код вида "XXX XXX".			
		
      
		
		
		
		$gridId = 'grid';
		
		$grid = $this->getGrid(
            $select,
            array(
                'ticket_external_id' 	=> array('title' => _('Номер документа')),	
                'filial' 				=> array('title' => _('Подразделение')),	
                'service_type' 			=> array('title' => _('Тип услуги')),	
                'contract_date' 		=> array('title' => _('Дата договора')),	
                'payerFio' 				=> array('title' => _('Плательщик')),	
                'payerEmail' 			=> array('title' => _('Email плательщика')),	
                'payerAddress' 			=> array('title' => _('Адрес плательщика')),	
                'forWhomPayFio' 		=> array('title' => _('За кого вносится оплата')),	
				'ticket_id' 			=> array('hidden' => true),                			               
                'mid_external' 			=> array('hidden' => true),				                               
                'contract_number' 		=> array('title' => _('Номер договора')),				               
                'sum_of' 				=> array('title' => _('Сумма')),
                'date_create' 			=> array('title' => _('Дата')),	
				
				'service_education_id' 	=> array(
					'title' => _('Услуга'),
					'callback' => array('function' => array($this, 'updateService'), 'params' => array('{{service_education_id}}', '{{service_pool_id}}', '{{service_library_id}}', '{{service_hotel_id}}', '{{service_laundry_id}}')),
				),
				'service_pool_id' 		=> array('hidden' => true), 
				'service_library_id' 	=> array('hidden' => true), 
				'service_hotel_id' 		=> array('hidden' => true), 
				'service_laundry_id'	=> array('hidden' => true), 
				
				/*
				'fine_id' 	=> array(
					'title' => _('Детали'),
					'callback' => array(
						'function' => array($this, 'updateServiceItem'),
						'params' => array('{{fine_id}}', '{{hostel_id}}', '{{hotel_id}}', '{{journal_id}}', '{{journal_year}}', '{{journal_number}}',  '{{library_card_number}}')),
				),
				'hotel_id' 				=> array('hidden' => true), 				
				'hostel_id'				=> array('hidden' => true), 				
				'journal_id' 			=> array('hidden' => true), 				
				'journal_year' 			=> array('hidden' => true), 				
				'journal_number' 		=> array('hidden' => true), 												
				'library_card_number' 	=> array('hidden' => true), 
				*/
				
				
				'period' 		=> array(
					'title' => _('Период'),
					'callback' => array('function' => array($this, 'updatePeriod'), 'params' => array('{{period}}', '{{period_begin}}', '{{period_end}}')),
				), 
				'period_begin' 		=> array('hidden' => true), 												
				'period_end' 		=> array('hidden' => true),				
				
				
				'status' 		=> array(
					'title' => _('Статус'),
					'callback' => array('function' => array($this, 'updateStatus'), 'params' => array('{{status}}'))
				), 
				
				'type' 		=> array(
					'title' => _('Способ оплаты'),
					'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{type}}'))
				), 				
				
            ),
            array(
				'ticket_external_id' 	=> null,				
				'filial' 				=> null,				
				'service_type' 			=> null,				
				'contract_number' 		=> null,				
				'payerFio' 				=> null,				
				'payerEmail' 			=> null,				
				'payerAddress' 			=> null,				
				'forWhomPayFio' 		=> null,				
				'date_create' 			=> array('render' => 'DateSmart'),	
				'contract_date' 		=> array('render' => 'DateSmart'),	
				'sum_of' 				=> null,												
				'status' 				=> array('values' => HM_Ticket_TicketModel::getStatuses()),								
				'type' 					=> array('values' =>  HM_Ticket_TicketModel::getTypes()),								
            ),
            $gridId
        );		
		
		$grid->updateColumn('date_create', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())                
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{date_create}}')
            )
        )
        );
		
		
		$grid->updateColumn('contract_date', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())                
															 
            ),
            'callback' => array(
                'function' => array($this, 'updateDate'),
                'params' => array('{{contract_date}}')
            )
        )
        );
		
		
		$grid->updateColumn('sum_of', array(            
            'callback' => array(
                'function' => array($this, 'updateSum'),
                'params' => array('{{sum_of}}')
            )
        )
        );
		
		
		
		
		$this->view->grid = $grid->deploy();
		
		if(in_array($user->organization, HM_Ticket_Requisite_RequisiteModel::getDisableFilials())){
			$this->view->form = _('Вы не можете формировать квитанции.');
		} else {
			$form = new HM_Form_Ticket();		
			$form = $this->setFormValues($form);
			$this->view->form = $form;			
		}
		
		$user_contract = $this->_ticketService->getUserCertificateInfo();
		if($user_contract){
			if(isset($user_contract[HM_Ticket_TicketModel::TYPE_EDUCATION])){
				# получаем связанные контракты				
				$selectAddEdu = $this->_ticketService->getStudentContractsSelect();
				$selectAddEdu->where($this->_ticketService->quoteInto(array('mid_external = ?', ' AND is_additional = ?', ' AND type_contract = ?'), array($user->mid_external, 1, HM_Ticket_TicketModel::TYPE_EDUCATION)));
				$res_ad_edu = $selectAddEdu->query()->fetchAll();				
				$data_ad_edu = array();
				if($res_ad_edu){
					foreach($res_ad_edu as $e){
						$data_ad_edu[] = array(
							'date' 				=> $e['date_create'],
							'type_contract' 	=> $this->getTypeContract($e['type_contract']),
							'contract_number' 	=> $e['contract_number'],					
							'sum_contract' 		=> $e['sum_contract'],
							'sum_payment' 		=> $e['sum_payment'],
						);
					}
				}
				
				
				$contract_edu = $user_contract[HM_Ticket_TicketModel::TYPE_EDUCATION];
				
				
				# оплачана сумма всего.
				$this->view->historyPayed = $this->_ticketService->getTotalPaidContracts($contract_edu->contract_number); 
				
				# получаем график платежей.
				$schedulePayments = $this->_ticketService->getSchedulePayments($contract_edu->contract_number);				
				$this->view->schedulePayments = $schedulePayments;
				
				$this->view->contractEducation = array(
					'date' 				=> $contract_edu->date_create,
					'type_contract' 	=> $this->getTypeContract($contract_edu->type_contract),
					'contract_number' 	=> $contract_edu->contract_number,					
					'sum_contract' 		=> $contract_edu->sum_contract,
					'sum_payment' 		=> $contract_edu->sum_payment,	
					'additional'		=> $data_ad_edu,
				);
			}
			
			if(isset($user_contract[HM_Ticket_TicketModel::TYPE_LIVING])){
				# получаем связанные контракты							
				$selectAddLiv = $this->_ticketService->getStudentContractsSelect();
				$selectAddLiv->where($this->_ticketService->quoteInto(array('mid_external = ?', ' AND is_additional = ?', ' AND type_contract = ?'), array($user->mid_external, 1, HM_Ticket_TicketModel::TYPE_LIVING)));
				$res_ad_liv = $selectAddLiv->query()->fetchAll();				
				$data_ad_liv = array();
				if($res_ad_liv){
					foreach($res_ad_liv as $e){
						$data_ad_liv[] = array(
							'date' 				=> $e['date_create'],
							'type_contract' 	=> $this->getTypeContract($e['type_contract']),
							'contract_number' 	=> $e['contract_number'],					
							'sum_contract' 		=> $e['sum_contract'],
							'sum_payment' 		=> $e['sum_payment'],
						);
					}
				}
				
				$contract_liv = $user_contract[HM_Ticket_TicketModel::TYPE_LIVING];				
				$this->view->contractLiving = array(
					'date' 				=> $contract_liv->date_create,
					'type_contract' 	=> $this->getTypeContract($contract_liv->type_contract),
					'contract_number' 	=> $contract_liv->contract_number,
					'sum_contract' 		=> $contract_liv->sum_contract,
					'sum_payment' 		=> $contract_liv->sum_payment,
					'additional'		=> $data_ad_liv,
				);				
			}
		}
		
		$this->view->gridAjaxRequest 			= $this->isAjaxRequest();
		$this->view->cardPaymentOrganizations 	= $this->getService('TicketRequisite')->getCardPaymentOrganizationList();
		
	} catch (Exception $e) {
		echo $e->getMessage(), "\n";
	}
        
    }
	
	
	public function updatePeriod($period, $period_begin, $period_end) {
        if(!empty($period)){
			$periods = HM_Ticket_TicketModel::getPeriods();
			return $periods[$period];
		} elseif(!empty($period_begin)){
			return date('d.m.Y', strtotime($period_begin)).' - '.date('d.m.Y', strtotime($period_end));
		}
		return _('нет');
        
    }
	
	public function updateDate($date)
    {
     	if (!strtotime($date)) return '';
		
        return $date;
    }
	
	public function getTypeContract($type){
		$types = HM_Ticket_TicketModel::getTypeContract();
		return $types[$type];
	}
	
	
	public function updateService($service_education_id, $service_pool_id, $service_library_id, $service_hotel_id, $service_laundry_id){
		if(empty($this->servicesList)){ $this->servicesList = $this->_ticketService->getServicesList(); }
		$arr = array();
		if(isset($this->servicesList[$service_education_id]))	{ $arr[] = $this->servicesList[$service_education_id];}
		if(isset($this->servicesList[$service_pool_id]))		{ $arr[] = $this->servicesList[$service_pool_id]; 	}
		if(isset($this->servicesList[$service_library_id]))		{ $arr[] = $this->servicesList[$service_library_id]; 	}
		if(isset($this->servicesList[$service_hotel_id]))		{ $arr[] = $this->servicesList[$service_hotel_id]; 	}
		if(isset($this->servicesList[$service_laundry_id]))		{ $arr[] = $this->servicesList[$service_laundry_id]; 	}
		
		if(count($arr)){
			return implode(', ', $arr);
		}
		return _('нет');		
	}
	
    public function updateServiceItem($fine_id, $hostel_id, $hotel_id, $journal_id, $journal_year, $journal_number, $library_card_number){
		if(empty($this->servicesItemList)){ $this->servicesItemList = $this->_ticketService->getServicesItemList(); }
		$arr = array();
		if(isset($this->servicesItemList[$fine_id])) 			{ $arr[] = $this->servicesItemList[$fine_id]; 													}
		if(isset($this->servicesItemList[$hostel_id])) 			{ $arr[] = $this->servicesItemList[$hostel_id]; 												}
		if(isset($this->servicesItemList[$hotel_id])) 			{ $arr[] = $this->servicesItemList[$hotel_id]; 												}
		if(isset($this->servicesItemList[$journal_id])) 		{ $arr[] = $this->servicesItemList[$journal_id].' №'.$journal_number.', '.$journal_year.'г.'; 	}
		if(isset($this->servicesItemList[$library_card_number])){ $arr[] = _('читательский билет №').$this->servicesItemList[$library_card_number]; 			}
		
		if(count($arr)){
			return implode(', ', $arr);
		}
		return _('нет');		
	}
	
	public function updateStatus($status_id){
		if(empty($this->statuses)){ $this->statuses = HM_Ticket_TicketModel::getStatuses(); }
		if(isset($this->statuses[$status_id])){ return $this->statuses[$status_id]; }
		return _('нет');
	}
	
	public function updateType($type_id){
		if(empty($this->types)){ $this->types = HM_Ticket_TicketModel::getTypes(); }
		if(isset($this->types[$type_id])){ return $this->types[$type_id]; }
		return _('нет');
	}
	
	
	public function updateSum($sum){
		return $sum._(' руб.');
	}
	
	/**
	 * Задаем предустановленные данные для формы из GET параметров
	 * @return zend form
	*/
	private function setFormValues($form)
	{
		$request = $this->getRequest();
		
		$contract_number	= $request->getParam('contract', false); # договор
		if(!empty($contract_number)){ $form->getElement('contract_number')->setValue($contract_number);	}
		
		$contract_date	= $request->getParam('date', false); # дата договора		
		if(!empty($contract_date)){ 
			$contract_date  = date('d.m.Y', $contract_date);
			$form->getElement('contract_date')->setValue($contract_date);
		}
		
		$sum	= $request->getParam('sum', false); # сумма
		if(!empty($sum)){		
			$sum   = str_replace(',', '.', $sum);
			$parts = explode('.', $sum);
			$form->getElement('sum1')->setValue(intval($parts[0]));
			$form->getElement('sum2')->setValue(intval($parts[1]));
			
		}
		
		return $form;
	}
    
}