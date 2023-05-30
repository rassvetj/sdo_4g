<?php
class MyPayments_IndexController extends HM_Controller_Action
{
    public function init()
    {
		parent::init();
    }
    
    
    public function indexAction()
    {
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Мои оплаты'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		$this->view->headScript()->appendFile($config->url->base.'themes/rgsu/js/dev.js');
		
		$current_user				= $this->getService('User')->getCurrentUser();	
		
		
		$raw_info	= $this->getService('MyPaymentsInfo')->getByCode($current_user->mid_external);		
		$raw_plan 	= $this->getService('MyPaymentsPlan')->getByCode($current_user->mid_external);
		$raw_fact 	= $this->getService('MyPayments')->getByCode($current_user->mid_external);
		$raw_details= $this->getService('MyPaymentsDetails')->getByCode($current_user->mid_external);

		$info = $this->prepareInfo($raw_info);
		$plan = $this->preparePlan($raw_plan);
		$fact = $this->prepareFact($raw_fact);
		$details = $this->prepareDetails($raw_details);
		
		$additional 			= array();
		$this->view->text_info 	= array();
		
		#  сумма всех фактических поступивших платежей
		$additional['payments_fact_sum_total'] = $this->getFactSumTotal($fact);
		
		
		# расчитываем Фактически оплачено
		foreach($plan as $contract_number => $items){
			foreach($items as $key => $i){
				$plan[$contract_number][$key]['sum_fact'] = $this->getPlanSumFact($i, $plan, $additional['payments_fact_sum_total']);
			}
		}
		
		# расчитываем Долг
		foreach($plan as $contract_number => $items){
			foreach($items as $key => $i){
				$plan[$contract_number][$key]['sum_debt'] = $this->getPlanDebt($i);
			}
		}
		
		
		# получаем сумму следующего платежа. Выводится в сумме квитанции, если нет долга
		$additional['next_sum'] = $this->getNextSum($plan);
		
		
		
		# должно быть до $this->view->render('index/info.tpl');	
		$this->view->info 			= $info;
		
		# итог по долгу
		foreach($plan as $contract_number => $items){
			$total_debt = 0;
			foreach($items as $key => $i){
				$total_debt += $i['sum_debt'];
			}
			$additional['total_debt'][$contract_number] = $total_debt;
			
			$this->view->current_contract_number		= $contract_number;
			$this->view->additional						= $additional;
			$this->view->text_info[$contract_number]	= $this->view->render('index/info.tpl');	
		}
		
		
		$this->view->form 			= new HM_Form_Question();
		$this->view->payments_plan 	= $plan;
		$this->view->payments_fact 	= $fact;
		$this->view->details 	    = $details;
		
		$this->view->additional		= $additional;
		
		
	}

	private function prepareDetails($raw)
	{
		$items = array();
		foreach($raw as $i){
			$items[$i->contract_number][] = array(			
                'contract_number' => $i->contract_number,
                'summ_plan'       => $i->summ_plan,
                'summ_fact'       => $i->summ_fact,
                'balance'         => $i->balance,
                'fine'            => $i->fine,
                'total_debt'      => $i->total_debt,
                'delay_days'      => $i->delay_days,
                'date_created'    => date('d.m.Y', strtotime($i->date_created)),
                'date_operation'  => date('d.m.Y', strtotime($i->date_operation)),
                'date_contract'   => date('d.m.Y', strtotime($i->date_contract)),
                'uik'             => $i->uik,
			);
		}		
		return $items;
	}
	
	
	/**
	 * @return array
	 * оплаты План
	*/
	private function preparePlan($raw)
	{	
		$plan = array();
		# группировка по номеру договора		
		foreach($raw as $i){
			if(empty($i->contract_number)){ continue; }
			$timestamp = strtotime($i->date);
			$plan[$i->contract_number][$timestamp] = array(
				'date'				=> date('d.m.Y', $timestamp), #Ожидаемая дата
				'sum'				=> $i->sum, #Ожидаемая сумма
				'sum_fact'			=> 0, #Фактически оплачено
				'sum_debt'			=> 0, #Долг
				#'sum_overpayment' 	=> 0, #Переплата
				#'days_delay'		=> 0, #Дней просрочки платежа
				#'sum_total'			=> 0, #Сумма
				'contract_number'	=> $i->contract_number,
			);
		}
		# сортировка по дате от меньшей к большей		
		foreach($plan as $contract_number => $items){
			ksort($items);
			$plan[$contract_number] = $items;
		}
		return $plan;
	}
	
	
	/**
	 * @return array
	 * оплаты Фактические
	*/
	private function prepareFact($raw)
	{
		$fact = array();
		
		# группировка по номеру договора		
		foreach($raw as $i){
			$timestamp = strtotime($i->date);
			
			$fact[$i->contract_number][$timestamp]['date'] = date('d.m.Y', $timestamp); # Дата поступления
			
			if(!isset($fact[$i->contract_number][$timestamp]['sum'])){
				$fact[$i->contract_number][$timestamp]['sum'] = 0;
			}			
			# оплаты могут быть в один день. Времени в дате нет.
			$fact[$i->contract_number][$timestamp]['sum']  += $i->sum; # Поступившие оплаты			
		}
		# сортировка по дате от меньшей к большей		
		foreach($fact as $contract_number => $items){
			ksort($items);
			$fact[$contract_number] = $items;
		}
		return $fact;
	}
	
	/**
	 * @return array
	 * данные по договорам
	*/
	private function prepareInfo($raw)
	{
		$info = array();
		# группировка по номеру договора		
		foreach($raw as $i){
			$timestamp = strtotime($i->contract_date);
			$info[$i->contract_number] = array(
				'uik' 				=> $i->uik,
				'contract_number' 	=> $i->contract_number,
				'contract_date' 	=> date('d.m.Y', $timestamp),
				'date_created'		=> date('d.m.Y', strtotime($i->date_created)), # дата актуализации данных
			);
		}		
		return $info;
	}
	
	/**
	 * @return array
	 * сумма всех фактических платежей по договору
	*/
	private function getFactSumTotal($data)
	{
		$sum_list = array();
		foreach($data as $contract_number => $items){
			if(!isset($sum_list[$contract_number])){
				$sum_list[$contract_number] = 0;
			}
			
			foreach($items as $i){				
				$sum_list[$contract_number] = $sum_list[$contract_number] + $i['sum'];
			}
		}
		return $sum_list;
	}
	
	/**
	 * @return float
	 * Фактически оплачено
	 * суммируем все оплаты до указанной даты вкключая ее.
	 * затем эту сумму вычитаем из общей суммы фактических оплат.
	*/
	private function getPlanSumFact($current, $items, $payments_fact_sum_total)
	{
		$current_timestamp 					= strtotime($current['date']);
		$current_contract_number 			= $current['contract_number'];
		$current_payments_fact_sum_total 	= $payments_fact_sum_total[$current_contract_number];
		
		$sum_total = 0; 
		$sum_fact  = 0; # сумма Фактически оплачено до текущей даты. Сортировка должна быть по ДАТЕ
		foreach($items[$current_contract_number] as $i){
			$timestamp = strtotime($i['date']);
			if($timestamp > $current_timestamp){ continue; }
			$sum_total += $i['sum'];
			$sum_fact  += $i['sum_fact'];
		}		
		$delta = $current_payments_fact_sum_total - $sum_total;
		
		if($delta > 0){
			return $current['sum'];
		}
		return $current_payments_fact_sum_total - $sum_fact;
	}
	
	/**
	 * @return float
	 * вычисляем долг по плану
	 * Долг для будущих периодов НЕ расчитываем
	*/
	private function getPlanDebt($current)
	{
		if(strtotime($current['date']) > time()){ return 0; }
		
		$delta = $current['sum_fact'] - $current['sum'];
		
		if($delta > 0){ return 0; }
		
		return $current['sum'] - $current['sum_fact'];
	}
	
	
	private function getNextSum($payments)
	{
		$data = array();
		foreach($payments as $contract_number => $items){
			foreach($items as $i){
				if(!empty($data[$contract_number])){ continue; }
				
				if(empty($i['sum_fact'])){
					$data[$contract_number] = $i['sum'];
				}				
			}
		}
		return $data;
	}
	
}




