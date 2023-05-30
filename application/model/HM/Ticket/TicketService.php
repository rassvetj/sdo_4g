<?php
require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';
require_once APPLICATION_PATH . '/../library/phpqrcode/qrlib.php';
require_once APPLICATION_PATH . '/../library/phpqrcode/qrconfig.php';
require_once APPLICATION_PATH . '/../library/common/MobileDetect/Mobile_Detect.php';

class HM_Ticket_TicketService extends HM_Service_Abstract
{
	
	#private $_payServerName	= '3dsec.sberbank.ru'; # сервер, через который будет проходить оплата. Тестовый
	private $_payServerName	= 'securepayments.sberbank.ru'; # сервер, через который будет проходить оплата. Рабояий
	
	public function  getServicesList(){
		$list = array();		
		$select = $this->getSelect();
        $select->from(array('ts' => 'ticket_services'), array('service_id' => 'ts.service_id', 'name' => 'ts.name') );
		$res = $select->query()->fetchAll();
		if($res){
			foreach($res as $i){ $list[$i['service_id']] = $i['name']; }
		}		
		return $list;
	}
	
	public function  getServicesItemList(){
		$list = array();		
		$select = $this->getSelect();
        $select->from(array('ti' => 'ticket_items'), array('item_id' => 'ti.item_id', 'name' => 'ti.name') );
		$res = $select->query()->fetchAll();
		if($res){
			foreach($res as $i){ $list[$i['item_id']] = $i['name']; }
		}		
		return $list;
	}
	
	
	public function setTicketStatus($ticket_id, $ticket_status){
		if(!$ticket_id || !$ticket_status){ return false; }
		$this->update(array('ticket_id' => $ticket_id, 'status' => $ticket_status));
		return true;
	}
	
	
	/**
	 * 
	*/	
	public function getTicketBySberbankId($external_id){
		return $this->getOne($this->fetchAll($this->quoteInto('ticket_sberbank_id = ?', $external_id)));
	}
	
	public function getTicketInfo($ticket){
		
		$user = $this->getService('User')->getCurrentUser();
		
		
		
		
		
		if(!$ticket) { return false; }
		$requisite = $this->getService('TicketRequisite')->getRequisiteById($ticket->filial_id);
		
		##### одинаковая часть с финкцией ниже.
		$message = $ticket->ticket_external_id;
		
		$lastName 	= str_replace("  "," ", $ticket->payerLastName);
		$firstName 	= str_replace("  "," ", $ticket->payerFirstName);
		$patronymic = str_replace("  "," ", $ticket->payerPatronymic);
		$User_e = $lastName.'_'.$firstName.'_'.$patronymic;
		$User_e = str_replace(' ', '_', $User_e);
		
		if(!empty($ticket->service_education_id)){
			$Paid_services_e = $this->getServiceCode($ticket->service_education_id); # Код услуги образования
		}
		
		if(!empty($ticket->fine_id)){
			$Penalties_e = $this->getServiceItemCode($ticket->fine_id); # штрафы, тип штрафа
		}
		
		if(!empty($ticket->service_type_id)){
			$TypeService_e = $this->getServiceTypeCode($ticket->service_type_id); # тип услуги
		}
		
		if(!empty($ticket->service_pool_id)){
			$f_Poll_e = $this->getServiceCode($ticket->service_pool_id); # услуги бассейна
		}
		
		if(!empty($ticket->journal_id)){
			$f_Magazine_name_e = $this->getServiceItemCode($ticket->journal_id); # журнал. item
		}
		
		if(!empty($ticket->hotel_id)){
			$f_Hotels_e = $this->getServiceItemCode($ticket->hotel_id); # пансионат
		}
		
		if(!empty($ticket->service_hotel_id)){
			$f_Almaz_e	= $this->getServiceCode($ticket->service_hotel_id); # услуга пансионата
			$f_Hotels_e=$f_Hotels_e.$f_Almaz_e;
		}		
		$f_contract_number = $ticket->contract_number;
		
		// копейки и рубли
		$Cash = $ticket->sum_of*100;
		
		# какой формат дыты?
		if(!empty($ticket->period_id)){
			$periodsValue = HM_Ticket_TicketModel::getPeriodsValue();	
			$Date=str_replace(" ","_", $periodsValue[$ticket->period_id]);
		} elseif(!empty($ticket->period_begin) && $ticket->period_end){
			$Date=str_replace(".","", $ticket->period_begin);
		}
		
		$fine_id = $this->getServiceTypeId(HM_Ticket_TicketModel::SERVICE_TYPE_FINE);
		$library_id = $this->getServiceTypeId(HM_Ticket_TicketModel::SERVICE_TYPE_LIBRARY);
		
		$requisite = $this->getService('TicketRequisite')->getRequisiteById($ticket->filial_id);
		
		//логин и пароль		
		if($ticket->service_type_id == $fine_id){ #штрафы
			$login 		= $requisite->login_fine;
			$password 	= $requisite->password_fine;			
		} elseif ($ticket->service_type_id == $library_id){ #Библиотека			
			$login = "rgsu_mos-api";
			$password = "rgsu_eq_mos_2";
		} else {
			if (!empty($ticket->hotel_id) && $ticket->hotel_id == HM_Ticket_TicketModel::HOTEL_CHAIKOVSKY){  #ЦСГ Чайковский				
				$login = "rgsu_chajkovskij-api";
				$password = "rgsu_eq_pyatigorsk2_22_api";
			} else {		
				$login 		= $requisite->login;
				$password 	= $requisite->password;
			}		  
		};
		
		
		# тестовые логин и пароль + тестовый сервер
		//$login 		= 'rgsu-api';
		//$password 	= 'rgsu';
		
		$keyEncript = '793d340a88d65284b2997342f94b37f5';		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://sber.rgsu.pro/netcat/modules/default/sdo/getPaymentInfo.php");
		#curl_setopt($ch, CURLOPT_URL,"https://old.rgsu.net/netcat/modules/default/sdo/getPaymentInfo.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
		          http_build_query(array(
						'key' 			=> '71a7a9ce0f1139f94d61b6ea69251bdf',
						'userName'		=> $this->encode($login, $keyEncript),
						'password'		=> $this->encode($password, $keyEncript),
						'orderId'		=> $ticket->ticket_sberbank_id,																											
						'language'		=> 'ru',					
					)));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		$url_e = curl_exec ($ch);
		
		
		#if($user->MID == 5829){
		#	var_dump($url_e);
		#	echo 'Curl error: ' . curl_error($ch);
		#	die;
		#}
		
		
		curl_close ($ch);
		$end_url=json_decode($url_e, true);
		
		/*	
		$json="{\"orderNumber\":\"$message\",\"ID_1\":\"$User_e\",\"ID_2\":\"$Paid_services_e$Penalties_e$TypeService_e$f_Poll_e$f_Magazine_name_e$f_Hotels_e|$f_contract_number|$Date\"}";		
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
				'content' => QUERY,
			),
		));
		
		$query = http_build_query(array(
			'orderId'			=> $ticket->ticket_sberbank_id,						
			'password'			=> $password,						
			'userName'			=> $login,						
			'language'			=> 'ru',						
		));
		
		$url_e=file_get_contents(			
			$file = 'https://'.$this->_payServerName.'/payment/rest/getOrderStatusExtended.do?'.$query,
			$use_include_path = false,
			$context
		);

		$end_url=json_decode($url_e, true);
		*/
		if(isset($end_url["orderNumber"]) && !empty($end_url["orderNumber"])){
			return $end_url;
		}
		return false;
		#####		
	}
	
	
	/**
	 * id типа услуги по его коду
	*/
	public function getServiceTypeId($serviceTypeCode){
		if(!$serviceTypeCode){ return false; }
		$select = $this->getSelect();
        $select->from('ticket_service_types', array('type_id') );
		$select->where($this->quoteInto('code = ?', $serviceTypeCode));
		$res = $select->query()->fetchObject();
		if(!$res) { return false; }
		return $res->type_id;
	}
	
    /**
	 * Мписок типов услуг
	*/
	public function getServiceTypeList($ignore_hidden = false){
		$list = array();
		$select = $this->getSelect();
        $select->from('ticket_service_types', array('code','name','is_hidden') );
		$res = $select->query()->fetchAll();
		if($res){
			foreach($res as $i){
				if($ignore_hidden && $i['is_hidden']) { continue; }
				$list[$i['code']] = $i['name']; 
			}
		}
		return $list;
	}
	
	/**
	 * Тип услуги
	*/
	public function getServiceTypeById($id){		
		$select = $this->getSelect();
        $select->from('ticket_service_types', array('type_id', 'code', 'name', 'value') );
		$select->where($this->quoteInto('type_id = ?', $id));
		return $select->query()->fetchObject();		
	}
	
	/**
	 * Услуга
	*/
	public function getServiceById($id){
		$select = $this->getSelect();
        $select->from('ticket_services', array('service_id', 'value', 'service_type_id', 'name') );
		$select->where($this->quoteInto('service_id = ?', $id));
		return $select->query()->fetchObject();			
	}
	
	/**
	 * Элемент услуги
	*/
	public function getItemById($id){
		$select = $this->getSelect();
        $select->from('ticket_items', array('item_id', 'value', 'service_type_id', 'name') );
		$select->where($this->quoteInto('item_id = ?', $id));
		return $select->query()->fetchObject();			
	}
	
	

	/**
	 * список услуг по коду типа услуги
	*/
	public function getServiceListByTypeCode($serviceTypeCode, $ignore_hidden = false){		
		$list = array();
		if(!$serviceTypeCode){ return $list; }
		$select = $this->getSelect();
        $select->from(array('ts' => 'ticket_services'), array('service_id' => 'ts.service_id', 'name' => 'ts.name', 'is_hidden' => 'ts.is_hidden') );
		$select->join(array('s' => 'ticket_service_types'), 's.type_id  = ts.service_type_id', array() );
		$select->where($this->quoteInto('s.code = ?', $serviceTypeCode));
		$select->group(array('ts.service_id','ts.name', 'ts.is_hidden'));
		$res = $select->query()->fetchAll();
		if($res){
			foreach($res as $i){ 
				if($ignore_hidden && $i['is_hidden']) { continue; }
				$list[$i['service_id']] = $i['name']; 
			}
		}		
		return $list;
	}
	
	/**
	 * Список итемов (общежития, журналы и т.п.) по коду типа услуги
	*/
	public function getItemListByTypeCode($serviceTypeCode){		
		$list = array();
		if(!$serviceTypeCode){ return $list; }
		$select = $this->getSelect();
        $select->from(array('i' => 'ticket_items'), array('item_id' => 'i.item_id', 'name' => 'i.name') );
		$select->join(array('s' => 'ticket_service_types'), 's.type_id  = i.service_type_id', array() );
		$select->where($this->quoteInto('s.code = ?', $serviceTypeCode));
		$select->group(array('i.item_id','i.name'));
		$res = $select->query()->fetchAll();
		if($res){
			foreach($res as $i){ $list[$i['item_id']] = $i['name']; }
		}		
		return $list;
	}
	
	
	/**
	 * Есть договор на общежитие
	*/
	public function isExsistHostelContract($user_id){
		if(!$user_id){
			return false;
		}
	
		$select = $this->getSelect();
        $select->from('student_contracts',
            array(
                'student_contract_id',								
            )
        );
		$select->where('mid_external = ?', $user_id);
		$select->where('type_contract = ?', HM_Ticket_TicketModel::TYPE_LIVING);
		$res = $select->query()->fetchObject();
		if($res){
			return true;
		}
		return false;		
	}
	/*
	public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                't' => 'Ticket'
            ),
            array(
                'ticket_id'        	=> 't.ticket_id',
                //'StudyID'			=> 't.StudyID',
                'mid_external'			=> 't.mid_external',
                'contract_number'	=> 't.contract_number',
                'period_id'			=> 't.period_id',
                'sum_of'				=> new Zend_Db_Expr('CAST(t.sum_of AS varchar)'),
				//'DateCreate'		=> new Zend_Db_Expr('CONVERT(VARCHAR, t.DateCreate, 104)'),
				'date_create'		=> 't.date_create',
            )
        );
		
		//$select->order('t.Date DESC');
		
        return $select;
    }
	*/
	
	public function getStudentContractsSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                't' => 'student_contracts'
            ),
            array(
                'contract_id' 		=> 't.student_contract_id',
				'mid_external' 		=> 't.mid_external',
				'contract_number' 	=> 't.contract_number',
				'date_create' 		=> 't.date_create',				
				'sum_contract' 		=> new Zend_Db_Expr('CAST(t.sum_contract AS varchar)'),				
				'sum_payment' 		=> new Zend_Db_Expr('CAST(t.sum_payment AS varchar)'),				
				'type_contract' 	=> 't.type_contract',				
				'is_additional' 	=> 't.is_additional',				
            )
        );
		
		//$select->limit(1);
		//$select->where('t.sum_contract >= 0');
		//$select->where('t.sum_payment >= 0');
        return $select;
    }
	
	
	public function getUserCertificateInfo(){
		
		
		$user = $this->getService('User')->getCurrentUser();
		
		
		$select_contract = $this->getStudentContractsSelect();
		
		
		$where_contract = $this->quoteInto(			
			array('mid_external=?'),
			array(				
				str_replace(' ', '', $user->mid_external), 
			)
		);
		$select_contract->where($where_contract);		
		
		$where_type = $this->quoteInto(			
			array(
				' (type_contract=? OR ',
				' type_contract=?)  ',
				#' is_additional!=?',
			),
			array(				
				HM_Ticket_TicketModel::TYPE_EDUCATION,
				HM_Ticket_TicketModel::TYPE_LIVING,
				#1,
			)
		);		
		$select_contract->where($where_type);		
		
		$stmt = $select_contract->query();
        $stmt->execute();		
		$rows = $stmt->fetchAll();
		$data = array();
		foreach($rows as $r){
			if($r['type_contract'] == HM_Ticket_TicketModel::TYPE_EDUCATION || $r['type_contract'] == HM_Ticket_TicketModel::TYPE_LIVING){
				
				if($r['is_additional'] == 1){ continue; }
				if(!isset($data[$r['type_contract']])){
					$data[$r['type_contract']] = (object)$r;		
				}				
			}
		}
		
		if(count($data) > 0){
			return $data;
		}
		return false;
	}
	
	/**
	 * рафик платежей
	**/
	public function getSchedulePayments($contract_id){
		if(!$contract_id) { return false; }
		
		$select = $this->getSelect();
        $select->from(
            array(
                't' => 'student_schedule_payments'
            ),
            array(
                'schedule_id' 		=> 't.schedule_id',				
				'contract_number' 	=> 't.contract_number',
				'date_payment' 		=> 't.date_payment',				
				'sum' 				=> new Zend_Db_Expr('CAST(t.sum AS varchar)'),								
				'period' 			=> 't.period',				
            )
        );		
		$select->where($this->quoteInto(' contract_number = ?', $contract_id));
		$select->order(array('period'));
		$res = $select->query()->fetchAll();
		$data = array();
		if(!$res){ return false; }		
		foreach($res as $i){			
			$data[] = array(				
				'contract_number' 	=> $i['contract_number'],
				'date_payment' 		=> $i['date_payment'],				
				'sum' 				=> $i['sum'],								
				'period'			=> $i['period'],
			);			
		}
		return $data;
	}
	
	/**
	 * получаем сумму выплат студента.
	*/
	public function getTotalPaidContracts($contract_id){
		if(!$contract_id) { return false; }
		
		$select = $this->getSelect();
        $select->from(
            array(
                't' => 'student_paid_contracts'
            ),
            array(
                'contract_id' 		=> 't.contract_id',				
				'contract_number' 	=> 't.contract_number',				
				'sum_payment' 		=> new Zend_Db_Expr('CAST(t.sum_payment AS varchar)'),								
				'date_payment' 		=> 't.date_payment',								
            )			
        );		
		$select->where($this->quoteInto(' contract_number = ?', $contract_id));		
		$res = $select->query()->fetchAll();
		$data = array();
		if(!$res){ return false; }		
		foreach($res as $i){			
			$data[] = array(				
				'contract_number' 	=> $i['contract_number'],				
				'sum_payment' 		=> $i['sum_payment'],	
				'date_payment' 		=> $i['date_payment'],												
			);			
		}
		return $data;				
	}
	
	public function addTicket($StudyCode, $ContractNumber, $Period, $SumOf)
	{
		if ( !$StudyCode || !$ContractNumber || !$Period || !$SumOf ) {
			return false;
		}
		
		try {
			$ticket = $this->insert(array(	'StudyCode' => (int) $StudyCode,
											'ContractNumber' => $ContractNumber, 
											'Period' => (int) $Period, 
											'SumOf' =>  $SumOf, ));
										
		} catch (Exception $e) {
			return false;
		}
			
		if ( !$ticket ) {
			return false;
		}
		
		return $ticket;
	}
	
	
	public function deleteTicket($id){
		if(!$id) { return false; }
		
		return $this->delete($id);		
	}
	
	public function getServiceTypeCode($id){
		$select = $this->getSelect();
        $select->from('ticket_service_types', array('value')  );
		$select->where($this->quoteInto('type_id = ?', $id));		
		$res = $select->query()->fetchObject();
		if(!$res) { return false; }
		return $res->value;
	}
	
	public function getServiceCode($id){
		$select = $this->getSelect();
        $select->from('ticket_services', array('value')  );
		$select->where($this->quoteInto('service_id = ?', $id));		
		$res = $select->query()->fetchObject();
		if(!$res) { return false; }
		return $res->value;
	}
	
	public function getServiceItemCode($id){
		$select = $this->getSelect();
        $select->from('ticket_items', array('value')  );
		$select->where($this->quoteInto('item_id = ?', $id));		
		$res = $select->query()->fetchObject();
		if(!$res) { return false; }
		return $res->value;
	}
	
	public function payCard($ticket){
				
		$message = $ticket->ticket_external_id;		
		
		$lastName 	= str_replace("  "," ", $ticket->payerLastName);
		$firstName 	= str_replace("  "," ", $ticket->payerFirstName);
		$patronymic = str_replace("  "," ", $ticket->payerPatronymic);
		$User_e = $lastName.'_'.$firstName.'_'.$patronymic;
		$User_e = str_replace(' ', '_', $User_e);
		$f_Magazine_year   = (int)$ticket->journal_year;
		$f_Magazine_number = $ticket->journal_number;
		
		if(!empty($ticket->service_education_id)){
			$Paid_services_e = $this->getServiceCode($ticket->service_education_id); # Код услуги образования
		}
		
		if(!empty($ticket->fine_id)){
			$Penalties_e = $this->getServiceItemCode($ticket->fine_id); # штрафы, тип штрафа
		}
		
		if(!empty($ticket->service_type_id)){
			$TypeService_e = $this->getServiceTypeCode($ticket->service_type_id); # тип услуги
		}
		
		if(!empty($ticket->service_pool_id)){
			$f_Poll_e = $this->getServiceCode($ticket->service_pool_id); # услуги бассейна
		}
		
		if(!empty($ticket->journal_id)){
			$f_Magazine_name_e = $this->getServiceItemCode($ticket->journal_id); # журнал. item
		}
		
		if(!empty($ticket->hotel_id)){
			$f_Hotels_e = $this->getServiceItemCode($ticket->hotel_id); # пансионат
		}
		
		if(!empty($ticket->service_hotel_id)){
			$f_Almaz_e	= $this->getServiceCode($ticket->service_hotel_id); # услуга пансионата
			$f_Hotels_e=$f_Hotels_e.$f_Almaz_e;
		}

		if(!empty($ticket->service_laundry_id)){
			$f_Laundry_e = $this->getServiceCode($ticket->service_laundry_id); # услуги прачечной
		}		
		$f_contract_number = $ticket->contract_number;
		
		// копейки и рубли
		$Cash = $ticket->sum_of*100;
		
		$f_Datefrom = $f_Dateto = '';
		
		# какой формат дыты?
		if(!empty($ticket->period_id)){
			$periodsValue = HM_Ticket_TicketModel::getPeriodsValue();	
			$Date=str_replace(" ","_", $periodsValue[$ticket->period_id]);
		} elseif(!empty($ticket->period_begin) && $ticket->period_end){
			//$Date=str_replace(".","", $ticket->period_begin);
			$Date=substr($ticket->period_begin,0,5)."-".substr($ticket->period_end,0,5); # формат: d.m-d.m
			$f_Datefrom = substr($ticket->period_begin,0,5);
			$f_Dateto   = substr($ticket->period_end,0,5);
		}
		
		$fine_id = $this->getServiceTypeId(HM_Ticket_TicketModel::SERVICE_TYPE_FINE);
		$library_id = $this->getServiceTypeId(HM_Ticket_TicketModel::SERVICE_TYPE_LIBRARY);
		
		$requisite = $this->getService('TicketRequisite')->getRequisiteById($ticket->filial_id);
		
		//логин и пароль		
		if($ticket->service_type_id == $fine_id){ #штрафы
			$login 		= $requisite->login_fine;
			$password 	= $requisite->password_fine;			
		} elseif ($ticket->service_type_id == $library_id){ #Библиотека			
			$login = "rgsu_mos-api";
			$password = "rgsu_eq_mos_2";
		} else {
			if (!empty($ticket->hotel_id) && $ticket->hotel_id == HM_Ticket_TicketModel::HOTEL_CHAIKOVSKY){  #ЦСГ Чайковский				
				$login = "rgsu_chajkovskij-api";
				$password = "rgsu_eq_pyatigorsk2_22_api";
			} else {		
				$login 		= $requisite->login;
				$password 	= $requisite->password;
			}		  
		};
		
        $nds      = 0;
        $nds_sum  = null;
		if(		$ticket->service_type_id == 6		# Оплата по лицензионному договору о предоставлении права использования произведения
			 || $ticket->service_hotel_id == 13		# Услуги по проживанию
			 || $ticket->service_hotel_id == 14){ 	# Доп услуги
					$nds = 3;
					$nds_sum = ($ticket->sum_of * 100) * (18/100); # 18 % от суммы, в копейках
		}
		
		$service_type = $this->getServiceTypeById($ticket->service_type_id);
		
		$forWhomPay_lastName 	= str_replace("  "," ", $ticket->forWhomPayLastName);
		$forWhomPay_firstName 	= str_replace("  "," ", $ticket->forWhomPayFirstName);
		$forWhomPay_patronymic 	= str_replace("  "," ", $ticket->forWhomPayPatronymic);
		$forWhomPay_User_e 		= $forWhomPay_lastName.'_'.$forWhomPay_firstName.'_'.$forWhomPay_patronymic;
		$forWhomPay_User_e 		= str_replace(' ', '_', $forWhomPay_User_e);
		
		$json="{\"orderNumber\":\"$message\",\"ID_1\":\"$forWhomPay_User_e\",\"ID_2\":\"$Paid_services_e$Penalties_e$TypeService_e$f_Poll_e$f_Laundry_e$f_Magazine_name_e$f_Hotels_e|$f_contract_number|$Date\"}";
		
		$name_ofd = '';	

		if(!empty($ticket->service_education_id)){
			$service_id = $ticket->service_education_id;
		} elseif(!empty($ticket->service_pool_id)){
			$service_id = $ticket->service_pool_id;
		} elseif(!empty($ticket->service_hotel_id)){
			$service_id = $ticket->service_hotel_id;
		} elseif(!empty($ticket->service_library_id)){
			$service_id = $ticket->service_library_id;
		} elseif(!empty($ticket->service_laundry_id)){
			$service_id = $ticket->service_laundry_id;
		}		
		
		$service = $this->getServiceById($service_id);
		
		if(!empty($ticket->fine_id)){
			$item_id = $ticket->fine_id;
		} elseif(!empty($ticket->hostel_id)){
			$item_id = $ticket->hostel_id;
		} elseif(!empty($ticket->hotel_id)){
			$item_id = $ticket->hotel_id;
		} elseif(!empty($ticket->journal_id)){
			$item_id = $ticket->journal_id;
		}		
		$item = $this->getItemById($item_id);		
		  
		if($ticket->service_type_id==1) {$name_ofd=", Тип: ".$service->name.", Номер договора: ".$f_contract_number.", Период оплаты: ".$Date.", Студент: ".$forWhomPay_User_e;} #Образование
		if($ticket->service_type_id==2) {$name_ofd=", Тип: штраф ".$item->name.", Номер договора: ".$f_contract_number.", Период оплаты: ".$Date.", Студент: ".$forWhomPay_User_e;} #Штрафы
		if($ticket->service_type_id==3) {$name_ofd=", Общежитие: ".$item->name.", Номер договора: ".$f_contract_number.", Период оплаты: ".$Date.", Студент: ".$forWhomPay_User_e;}#Оплата общежития
		if($ticket->service_type_id==4) {$name_ofd=", Услуга: ".$service->name.", Период оплаты: ".$f_Datefrom."/".$f_Dateto.", Студент: ".$User_e;}#Бассейн
		if($ticket->service_type_id==5) {$name_ofd=", Пансионат: ".$item->name.", Услуга: ".$service->name.", Период оплаты: ".$f_Datefrom."/".$f_Dateto.", Студент: ".$User_e;}#Базы отдыха
		if($ticket->service_type_id==6) {$name_ofd=", Название журнала: ".$item->name.", Год: ".$f_Magazine_year.", Номер: ".$f_Magazine_number.", Период оплаты: ".$f_Datefrom."/".$f_Dateto.", Студент: ".$User_e;}#Оплата по лицензионному договору о предоставлении права использования произведения
		if($ticket->service_type_id==14){$name_ofd=", Услуги прачечной: ".$service->name.", Студент: ".$forWhomPay_User_e;}
		
		
	
		$description = 'Плательщик: '.$User_e.''.$name_ofd;
		
		$service_type = $this->getServiceTypeById($ticket->service_type_id);
			
		$orderBundle = array(
			'orderCreationDate' => time() * 1000, 															# Дата создания заказа в миллисекундах			
			'customerDetails'	=> array(
										'email'	=> $ticket->payerEmail,
			),
			'cartItems'			=> array(
										'items'	=> array(
														array(
															'positionId' 	=> 1, 							# Уникальный идентификатор товарной позиции внутри Корзины Заказа
															'name' 			=> $service_type->name.' '.$name_ofd, # Наименование или описание товарной позиции в свободной форме
															'quantity' 		=> array(
																					'value' 	=> 1, 		# Количество товарных позиций данного positionId
																					'measure' 	=> 'шт',	# Мера измерения количества товарной позиции
															),
															'itemAmount' 	=> ($ticket->sum_of * 100), 	# Сумма стоимости всех товарных позиций одного positionId в деньгах в минимальных единицах валюты (к)
															'itemCode' 		=> $ticket->ticket_external_id, # Номер (идентификатор) товарной позиции в системе магазина
															'tax'			=> array(
																					'taxType' => $nds,
																					'taxSum'  => $nds_sum,
															),
                                                           'itemPrice' 		=> ($ticket->sum_of * 100),
														),
										),
			),
		);
		
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
				'content' => QUERY,
			),
		));
		
		
		/*
		$file = "https://securepayments.sberbank.ru/payment/rest/register.do?userName=".$login."&password=".$password."&orderNumber=".$message."&amount=".$Cash."&language=ru&failUrl=http://rgsu.net/for-students/payment-receipt/fail/&description=".$User_e."&returnUrl=http://rgsu.net/for-students/payment-receipt/ready/&jsonParams=$json",
		*/
		# тестовые логин и пароль + тестовый сервер
		//$login 		= 'rgsu-api';
		//$password 	= 'rgsu';
		
		$keyEncript = '37d64ee73fcf2dc5597048f43ac2a63a';		
		$ch = curl_init();
		
		$user = $this->getService('User')->getCurrentUser();
		
		curl_setopt($ch, CURLOPT_URL,"http://sber.rgsu.pro/netcat/modules/default/sdo/getPaymentLink.php"); # Временная заглушка, пока админы делают сервак доменный
		#curl_setopt($ch, CURLOPT_URL,"https://old.rgsu.net/netcat/modules/default/sdo/getPaymentLink.php");
		
		curl_setopt($ch, CURLOPT_POST, 1);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
		          http_build_query(array(
						'key' 			=> 'b581c59c882385efec2809d8bbeeac2d',
						'userName'		=> $this->encode($login, $keyEncript),
						'password'		=> $this->encode($password, $keyEncript),
						'orderNumber'	=> $message,
						'amount'		=> $Cash,
						'language'		=> 'ru',
						'failUrl'		=> 'http://'.$_SERVER['SERVER_NAME'] . '/ticket/pay/fail/',	
						'description'	=> $description,
						'returnUrl'		=> 'http://'.$_SERVER['SERVER_NAME'] . '/ticket/pay/ready/',	
						'jsonParams'	=> $json,					
						'orderBundle'	=> json_encode($orderBundle),
						'taxSystem'		=> 0,
					)));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		$url_e = curl_exec ($ch);
		
		
		
		#if($user->MID == 5829){
			
			#var_dump(file_get_contents('http://sber.rgsu.pro/netcat/modules/default/sdo/getPaymentLink.php'));
			
			#Ошибка curl: error:1407742E:SSL routines:SSL23_GET_SERVER_HELLO:tlsv1 alert protocol versionbool(false)
			
			#echo 'Ошибка curl: ' . curl_error($ch);
			#var_dump($url_e);
			#die;			
		#}
		
		curl_close ($ch);
		
		/*
		$query = http_build_query(array(
			'userName'		=> $login,
			'password'		=> $password,
			'orderNumber'	=> $message,
			'amount'		=> $Cash,
			'language'		=> 'ru',
			'failUrl'		=> 'http://'.$_SERVER['SERVER_NAME'] . '/ticket/pay/fail/',								
			'description'	=> $User_e,
			'returnUrl'		=> 'http://'.$_SERVER['SERVER_NAME'] . '/ticket/pay/ready/',			
			'jsonParams'	=> $json,			
		));
		
		$url_e=file_get_contents(			
			$file = 'https://'.$this->_payServerName.'/payment/rest/register.do?'.$query,
			$use_include_path = false,
			$context
		);	
		*/
		
		$end_url=json_decode($url_e, true);
		
		if(isset($end_url["orderId"])){			
			$this->update(array('ticket_sberbank_id' => $end_url["orderId"], 'ticket_id' => $ticket->ticket_id));
		}
		
		if(isset($end_url["formUrl"]) && $end_url["formUrl"]!=''){
			return array( 'url' 	=> $end_url["formUrl"] );			 
		} 
		elseif(isset($end_url["errorMessage"])) {
			return array('error' => $end_url["errorMessage"] );			
		} 
		return false;
	}
	
	public function encode($unencoded,$key){//Шифруем
		$string=base64_encode($unencoded);//Переводим в base64

		$arr=array();//Это массив
		$x=0;
		while ($x++< strlen($string)) {//Цикл
		$arr[$x-1] = md5(md5($key.$string[$x-1]).$key);//Почти чистый md5
		$newstr = $newstr.$arr[$x-1][3].$arr[$x-1][6].$arr[$x-1][1].$arr[$x-1][2];//Склеиваем символы
		}
		return $newstr;//Вертаем строку
	}

	public function decode($encoded, $key){//расшифровываем
		$strofsym="qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";//Символы, с которых состоит base64-ключ
		$x=0;
		while ($x++<= strlen($strofsym)) {//Цикл
		$tmp = md5(md5($key.$strofsym[$x-1]).$key);//Хеш, который соответствует символу, на который его заменят.
		$encoded = str_replace($tmp[3].$tmp[6].$tmp[1].$tmp[2], $strofsym[$x-1], $encoded);//Заменяем №3,6,1,2 из хеша на символ
		}
		return base64_decode($encoded);//Вертаем расшифрованную строку
	}
	
	
	
	
	
	public function sendTicket($ticket){
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($ticket->payerEmail)) {
			return false;
		}
		
		$service_type = $this->getServiceTypeById($ticket->service_type_id);
		$ticket->service_type_name = $service_type->name;
		
		if(!empty($ticket->service_education_id)){
			$service_id = $ticket->service_education_id;
		} elseif(!empty($ticket->service_pool_id)){
			$service_id = $ticket->service_pool_id;
		} elseif(!empty($ticket->service_hotel_id)){
			$service_id = $ticket->service_hotel_id;
		} elseif(!empty($ticket->service_library_id)){
			$service_id = $ticket->service_library_id;		
		} elseif(!empty($ticket->service_laundry_id)){
			$service_id = $ticket->service_laundry_id;
		}		
		$service = $this->getServiceById($service_id);
		$ticket->service_name = $service->name;
		
		if(!empty($ticket->fine_id)){
			$item_id = $ticket->fine_id;
		} elseif(!empty($ticket->hostel_id)){
			$item_id = $ticket->hostel_id;
		} elseif(!empty($ticket->hotel_id)){
			$item_id = $ticket->hotel_id;
		} elseif(!empty($ticket->journal_id)){
			$item_id = $ticket->journal_id;
		}		
		$item = $this->getItemById($item_id);
		$ticket->item_name = $item->name;
		
		$additional = array();
		if(!empty($ticket->journal_year))		{ $additional[] = $ticket->journal_year.'г.'; }
		if(!empty($ticket->journal_number))		{ $additional[] = '№'.$ticket->journal_number; }
		if(!empty($ticket->library_card_number)){ $additional[] = 'билет №'.$ticket->library_card_number; }
		$ticket->additional = implode(', ', $additional);
		
		
		
		
		$file_name = $this->createPDF($ticket);
		if(!file_exists($file_name)){
			return false;
		}
		
		$to = $ticket->payerEmail;			
		$from_name = _('Сайт РГСУ');
		$subject = _('Квитанция на оплату');
		$text = '<p>Ваша квитанция сформирована.<br/>Теперь вы можете распечатать ее и оплатить через Сбербанк Онлайн по карте Сбербанка, устройство самообслуживания (банкомат или терминал) картой Сбербанка или наличными, а также обратиться в ближайшее отделение Сбербанка.</p><hr style="border: none;border-bottom: 1px dotted #ccc;">';
		#<p>C любовью, Ваш РГСУ.</p><p style="font-size:12px;line-height:16px;">Call-центр: +7 (495) 748-67-67  |  Приемная комиссия: +7 (495) 748-67-77<br/><a href="http://rgsu.net/">Сайт РГСУ</a>  |  <a href="http://vk.com/rgsu_official">РГСУ Вконтакте</a>  |  <a href="https://twitter.com/RGSU_official">Twitter РГСУ</a>  |  <a href="https://www.facebook.com/rgsu.official">Facebook РГСУ</a></p>';


		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);		
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);		
		
		$mail->setFromToDefaultFrom(); 
		
		$mail->addTo($to);
		$mail->setSubject($subject);

		$content = file_get_contents($file_name);
		$attachment = new Zend_Mime_Part($content);
		$attachment->type = 'application/pdf';
		$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding = Zend_Mime::ENCODING_BASE64;
		$attachment->filename = 'Ticket.pdf';
		
		$mail->addAttachment($attachment);                  
		
		unlink($file_name);		
		
		try {	
			$mail->send();
		} catch (Exception $e) {		
			return false;
		}
		return true;
	}
	
	public function  createPDF($ticket){		
		$user = $this->getService('User')->getCurrentUser();
		if(!$user || !isset($user->organization)){ return false; }
		
		$requisite = $this->getService('TicketRequisite')->getRequisiteById($ticket->filial_id);		
		if(!$requisite){		
			$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);	
		}
		
		if(!$requisite){ return false; }
		
		if(isset($ticket->period_id)){
			$periods = HM_Ticket_TicketModel::getPeriodsPDF();
			$f_Payment_period_id = $periods[$ticket->period_id];
		} elseif(isset($ticket->period_begin) && isset($ticket->period_end)){
			$f_Payment_period_id = $ticket->period_begin.' - '.$ticket->period_end;
		}
		$payer_fio = $ticket->payerLastName.' '.$ticket->payerFirstName;
		$payerPatronymic = str_replace('-', '', $ticket->payerPatronymic);
		if(!empty($payerPatronymic)	) {  $payer_fio = $payer_fio.' '.$ticket->payerPatronymic;  }
		
		# Пожертвование
		if($ticket->service_type_id == 15){
			$requisite->cbc = '00000000000000000150';
		
		# Штрафы
		} elseif($ticket->service_type_id == 2){
			$requisite->cbc = '00000000000000000140';
		}
		
		$data = array(
			'recipient' 			=> $requisite->recipient,
			'INN' 					=> $requisite->inn,
			'KPP' 					=> $requisite->kpp,
			'bank_account' 			=> $requisite->bank_account,
			'OKTMO' 				=> $requisite->oktmo,
			'CBC' 					=> $requisite->cbc,
			'recipient_bank'		=> $requisite->bank_recipient,			
			'BIK' 					=> $requisite->bik,
			'Note' 					=> $requisite->note,
			'personalAccount'		=> $requisite->personalaccount,
			'f_Number'				=> $ticket->contract_number,
			
			'contract_date'			=> ($ticket->contract_date) ? (' Дата заключения: '.date('d.m.Y', strtotime($ticket->contract_date))) : (false),
			
			'f_Email'				=> $ticket->payerEmail, #или емаил из профиля студента?
			'f_Price_r'				=> number_format(floor($ticket->sum_of), 0, ',', ' '),
			'f_Price_k'				=> str_pad( intval ( round( ($ticket->sum_of - floor($ticket->sum_of) )*100 ) ), 2, 0, STR_PAD_LEFT),
			'payment_periods'		=> $f_Payment_period_id,
			'f_FIO'					=> $payer_fio, # фио плательщика
			'LastName'				=> $ticket->payerLastName,   # для QR
			'FirstName'				=> $ticket->payerFirstName,  # для QR
			'MiddleName'			=> $payerPatronymic, 		 # для QR
			
			# данные для QR-кода. Пока не используются
			'pool'					=> '',
			'paid_services'			=> '',
			'penalties'				=> '',
			'f_address'				=> $ticket->payerAddress,
			'period_begin'			=> $ticket->period_begin,
			'period_end'			=> $ticket->period_end,
			'service_type_name'		=> $ticket->service_type_name,			
			'service_type_id'		=> $ticket->service_type_id,			
			'service_name'			=> $ticket->service_name,
			'additional'			=> (!empty($ticket->additional) &&  (!empty($ticket->item_name) || !empty($ticket->service_name))) ? (', '.$ticket->additional) : ($ticket->additional),
			'item_name'				=> (!empty($ticket->item_name) && !empty($ticket->service_name)) ? (': '.$ticket->item_name) : ($ticket->item_name), # для общежитий и журналов: это адрес общежития или название журнала
			'forWhomPay'			=> !empty($ticket->forWhomPayLastName)	? (' Студент: '.$ticket->forWhomPayLastName.' '.$ticket->forWhomPayFirstName.' '.$ticket->forWhomPayPatronymic)	:	'',
			'forWhomPay_QR'			=> $ticket->forWhomPayLastName.' '.$ticket->forWhomPayFirstName.' '.$ticket->forWhomPayPatronymic,
			
		);
		
		$template = $this->getPDFTemplate($data);
		
		//генерация названия файла
		$hashe = md5( microtime() . mt_rand() );
			$result = '';
			$array = array_merge(range('a','z'), range('0','9'));
			for($i = 0; $i < 5; $i++){
				$result .= $array[mt_rand(0, 35)];
			}
		$name_f=$result;
		
		$tempDir = APPLICATION_PATH . '/../public/upload/files/';
		
		$host = new Zend_View_Helper_ServerUrl;
		$publicDir = 'http://'.$host->getHost().'/upload/files/';
				
		$fileName = $name_f.'.png';
		
		$pngAbsoluteFilePath = $tempDir . $fileName;
		
				
		//Генерация изображения QR кода 		
		if (!file_exists($pngAbsoluteFilePath)) {
			$qr = 'ST00011|Name='.$data['recipient'].'|PersonalAcc='.$data['personalAccount'].'|BankName='.$data['recipient_bank'].'|BIC='.$data['BIK'].'|CorrespAcc='.$data['bank_account']; #required;
			$qr .= '|PayeeINN='.$data['INN'].'|KPP='.$data['KPP'].'|OKTMO='.$data['OKTMO'].'|CBC='.$data['CBC'].'|LastName='.$data['LastName'].'|FirstName='.$data['FirstName'].'|MiddleName='.$data['MiddleName'];
			
			switch ($data['service_type_id']) {
				case 1: #Образование
					$qr .= '|ChildFio='.$data['forWhomPay_QR'].'|PayerAddress='.$data['f_address'].'|Contract='.$data['f_Number'].'|Purpose='.$data['paid_services'].'|PaymPeriod='.$data['payment_periods'];        
					break;
				case 2: #Штрафы
					$qr .= '|ChildFio='.$data['forWhomPay_QR'].'|PayerAddress='.$data['f_address'].'|Contract='.$data['f_Number'].'|Purpose='.$data['penalties'].'|PaymPeriod='.$data['payment_periods'];	  
					break;
				case 3: #Оплата общежития
					$qr .= '|ChildFio='.$data['forWhomPay_QR'].'|PayerAddress='.$data['item_name'].'|Contract='.$data['f_Number'].'|Purpose=ОПЛАТА ОБЩЕЖИТИЯ|PaymPeriod='.$data['payment_periods'];		  
					break;
				case 4: #Бассейн
					$qr .= '|Purpose='.$data['pool'];
					break;
				case 5: #Базы отдыха
					$qr .= '|PayerAddress='.$data['item_name'].'|Purpose='.$data['service_name'].'|PaymPeriod='.$data['payment_periods'];
					break;
			}
			/*
QRcode::png(
	"1 Яблоко Арбуз Тыква Дыня 2 Яблоко Арбуз Тыква Дыня 3 Яблоко Арбуз Тыква Дыня 4 Яблоко Арбуз Тыква Дыня 5 Яблоко Арбуз Тыква Дыня 6 Яблоко Арбуз Тыква Дыня 7 Яблоко Арбуз Тыква Дыня 8 Яблоко Арбуз Тыква Дыня 9 Яблоко Арбуз Тыква Дыня 10 Яблоко Арбуз Тыква Дыня 11 Яблоко Арбуз Тыква Дыня 12 Яблоко Арбуз Тыква Дыня 13 Яблоко Арбуз Тыква Дыня 14 Яблоко Арбуз Тыква Дыня 15 Яблоко Арбуз Тыква Дыня 16 Яблоко Арбуз Тыква Дыня 17 Яблоко Арбуз Тыква Дыня 18 Яблоко Арбуз Тыква Дыня 19 Яблоко Арбуз Тыква Дыня 20 Яблоко Арбуз Тыква Дыня 21 Яблоко Арбуз Тыква Дыня 22 Яблоко Арбуз Тыква Дыня 23 Яблоко Арбуз Тыква Дыня 24 Яблоко Арбуз Тыква Дыня"
	, $pngAbsoluteFilePath, 3, 1);
	*/
			# Раскомментировать, как будет переделана строка формирования QR
			#QRcode::png($qr, $pngAbsoluteFilePath, QR_ECLEVEL_M);
			
			
			#QRcode::png($qr, $pngAbsoluteFilePath, 'H');
			
		/*	
		QRcode::png(
			'ST00011|Name='.$data['recipient'].'|PersonalAcc='.$data['personalAccount'].'|BankName='.$data['recipient_bank'].'|BIC='.$data['BIK'].'|CorrespAcc='.$data['bank_account'].'|Purpose='.$data['pool'].$data['paid_services'].$data['penalties']
		   .'|PayeeINN='.$data['INN'].'|KPP='.$data['KPP'].'|CBC='.$data['CBC'].'|OKTMO='.$data['OKTMO'].'|payerFio='.$data['f_FIO'].'|Address='.$data['f_address'].'|personalAccount='.$data['personalAccount']
		   .'|contract='.$data['f_Number'].'|Period='.$f_Payment_period_id, $pngAbsoluteFilePath, QR_ECLEVEL_M);
		}
		*/
		
		}
		
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		$pdf->SetFont('times', 'BI', 20, '', 'false');
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(_('RGSU'));
		$pdf->SetTitle(_('Квитанция на оплату'));
		$pdf->SetSubject(_('Квитанция на оплату'));
		$pdf->SetKeywords(_('Квитанция на оплату'));

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(2, 5, 5);

		$pdf->AddPage(); 
		$pdf->SetXY(2, 5, 5);
		$pdf->SetDrawColor(100, 100, 0);

		# Раскомментировать, как будет переделана строка формирования QR 		
		#$pdf->Image($publicDir.$name_f.'.png', 7, 15, 40, 40, '', '', '', false, 300); # QR-код
		
		$pdf->writeHTML($template, true, false, false, false, '');  
		$pdf->Output($tempDir.$name_f.'.pdf','F');
		
		unlink($publicDir.$name_f.'.png');	
		
		return $tempDir.$name_f.'.pdf';		
	}
	
	/**
	 * return string;
	*/
	public function  getPDFTemplate($data){
		
		$tbl = '
			<style>
			.kvitancia tr {height: 11.25pt;}
			.kvitancia td {
				padding:0 0 0 1.5pt;
				font-family: arial;
				font-size: 8pt;
			}
			.border-right {border-right: 1pt solid #000000;}
			.border-top {border-top: 1pt solid #000000;}
			.border-bottom {border-bottom: 1pt solid #000000;}
			</style>
			<table class="kvitancia" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
			<tbody>
				<tr>
					<td style="width:152.25pt;"></td>
					<td style="width:5.25pt;"></td>
					<td style="width:57.75pt;"></td>
					<td style="width:47.25pt;"></td>
					<td style="width:47.25pt;"></td>
					<td style="width:26.25pt;"></td>
					<td style="width:38.25pt;"></td>
					<td style="width:26.25pt;"></td>
					<td style="width:105pt;"></td>
					<td style="width:73.5pt;"></td>
				</tr>
				<tr >
					<td class="border-right"></td>
					<td colspan="9" valign="top" style="font-size:6pt; text-align: right;">Форма&nbsp;№&nbsp;ПД-4</td>

				</tr>
				<tr style="height:21.75pt;">
					<td align="center" class="border-right" style="text-align: center">
						<span>ИЗВЕЩЕНИЕ</span>
					</td>
					<td style="height:21.75pt;"></td>
					<td colspan="7" style="height:21.75pt; font-size:8pt;">
						<b>'.$data['recipient'].'</b>
					</td>
					<td></td>

				</tr>
				<tr >
					<td rowspan="14" class="border-right"></td>
					<td></td>
					<td colspan="8" valign="top" class="border-top" style="font-size: 6pt; text-align: center;">
						(&nbsp;наименование&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" style="text-align: center;">
						<b>'.$data['INN'].'/'.$data['KPP'].'</b>
					</td>
					<td style="text-align: right;">
						№
					</td>
					<td colspan="5" style="text-align: center;">
						<b>'.$data['bank_account'].' ОКТМО '.$data['OKTMO'].'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" valign="top" class="border-top" style="font-size:6pt;">
						(ИНН/КПП)&nbsp;получателя
					</td>
					<td valign="top"></td>
					<td colspan="5" valign="top" class="border-top" style="font-size:6pt; text-align: center;">
						<span style="">(номер&nbsp;счета&nbsp;получателя&nbsp;платежа)</span>
					</td>

				</tr>
				<tr >
					<td></td>
					<td>
						в
					</td>
					<td colspan="7">
						<b>'.$data['recipient_bank'].' БИК '.$data['BIK'].' </b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top" style="font-size: 6pt; text-align: center;">
						(наименование&nbsp;банка&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8">
						<b>КБК&nbsp;'.$data['CBC'].'</b>
					</td>

				</tr>
				<tr style="height:12pt;">
					<td></td>
					<td colspan="8" valign="bottom" class="border-top"></td>

				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						'.$data['service_type_name'].'
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['service_name'].$data['item_name'].$data['additional'].'
					</td>
				</tr>
				<tr >
					<td></td>
					<td colspan="8"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Ф.И.О плательщика:
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['f_FIO'].'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" class="border-bottom">
						'.(	!empty($data['f_Number'])	?	'Номер договора'	:	''	).'
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['f_Number'].' '.(	!empty($data['payment_periods'])	?	'Период оплаты'	:	''	).' '.$data['payment_periods'].' '.$data['contract_date'].' '.$data['forWhomPay'].'
					</td>
				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2">
						Сумма&nbsp;платежа
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$data['f_Price_r'].'
					</td>
					<td >
						руб.
					</td>
					<td class="border-bottom" style="text-align: center;">
						'.$data['f_Price_k'].'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr >
					<td class="border-right" style="text-align: center;">
						Кассир
					</td>
					<td></td>
					<td colspan="2">
						Итого
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$data['f_Price_r'].'
					</td>
					<td>
						руб.
					</td>
					<td class="border-bottom" style="text-align: center">
						'.$data['f_Price_k'].'
					</td>
					<td>
						коп.
					</td>

				</tr>
				<tr style="height:6pt;">
					<td class="border-right border-bottom"></td>
					<td class="border-bottom"></td>
					<td colspan="2" class="border-bottom"></td>
					<td colspan="3" class="border-bottom"></td>
					<td colspan="2" class="border-bottom"></td>
					<td class="border-bottom"></td>
				</tr>
				<tr>
					<td class="border-right"></td>
					<td colspan="9"></td>
				</tr>
				<tr style="height:21.75pt;">
					<td align="center" class="border-right" style="text-align: center">
						
					</td>
					<td style="height:21.75pt;"></td>
					<td colspan="7" style="height:21.75pt; font-size:8pt;">
						<b>'.$data['recipient'].'</b>
					</td>
					<td></td>

				</tr>
				<tr >
					<td rowspan="14" class="border-right"></td>
					<td></td>
					<td colspan="8" valign="top" class="border-top" style="font-size: 6pt; text-align: center;">
						(&nbsp;наименование&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" style="text-align: center;">
						<b>'.$data['INN'].'/'.$data['KPP'].'</b>
					</td>
					<td style="text-align: right;">
						№
					</td>
					<td colspan="5" style="text-align: center;">
						<b>'.$data['bank_account'].' ОКТМО '.$data['OKTMO'].'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" valign="top" class="border-top" style="font-size:6pt;">
						(ИНН/КПП)&nbsp;получателя
					</td>
					<td valign="top"></td>
					<td colspan="5" valign="top" class="border-top" style="font-size:6pt; text-align: center;">
						<span style="">(номер&nbsp;счета&nbsp;получателя&nbsp;платежа)</span>
					</td>

				</tr>
				<tr >
					<td></td>
					<td>
						в
					</td>
					<td colspan="7">
						<b>'.$data['recipient_bank'].' БИК '.$data['BIK'].'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top" style="font-size: 6pt; text-align: center;">
						(наименование&nbsp;банка&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8">
						<b>КБК&nbsp;'.$data['CBC'].'</b>
					</td>

				</tr>
				<tr style="height:12pt;">
					<td></td>
					<td colspan="8" valign="bottom" class="border-top"></td>

				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						'.$data['service_type_name'].'
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['service_name'].$data['item_name'].$data['additional'].'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Ф.И.О плательщика:
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['f_FIO'].'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" class="border-bottom">
						'.(	!empty($data['f_Number'])	?	'Номер договора'	:	''	).'
					</td>
					<td colspan="6" class="border-bottom">
						'.$data['f_Number'].' '.(	!empty($data['payment_periods'])	?	'Период оплаты'	:	''	).' '.$data['payment_periods'].' '.$data['contract_date'].' '.$data['forWhomPay'].'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8" valign="top"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
						Сумма&nbsp;платежа
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$data['f_Price_r'].'
					</td>
					<td >
						руб.
					</td>
					<td class="border-bottom" style="text-align: center;">
						'.$data['f_Price_k'].'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr>
					<td class="border-right" style="text-align: center;">
						Квитанция
					</td>
					<td></td>
					<td colspan="2">
						Итого
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$data['f_Price_r'].'
					</td>
					<td>
						руб.
					</td>
					<td class="border-bottom" style="text-align: center">
						'.$data['f_Price_k'].'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr style="height:6pt;">
					<td class="border-right" style="text-align: center">
						Кассир
					</td>
					<td></td>
					<td colspan="2"></td>
					<td colspan="3"></td>
					<td colspan="2"></td>
					<td></td>
				</tr>
			</tbody>
		</table>';
		
		return $tbl;
	}
	
	
	
	
	/**
	 * @deprecated
	 * $f_Number - номер контракта
	 * $f_Payment_period_id - период (1\2 полугодие) (int)
	 * $f_Price_r - рубли
	 * $f_Price_k - копейки
	*/
	public function  sendTicketPDF($f_Number, $f_Payment_period_id, $f_Email, $f_FIO, $f_Price_r, $f_Price_k = '00'){
		
		$user = $this->getService('User')->getCurrentUser();
		if(!$user || !isset($user->organization)){ return false; }
		$requisite = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);
		
		if(!$requisite){ return false; }
		
			
		if(!$f_Number || !$f_Payment_period_id || !$f_Email || !$f_FIO || !$f_Price_r){
			return false;			
		}
		
		//генерация названия файла
		$hashe = md5( microtime() . mt_rand() );
			$result = '';
			$array = array_merge(range('a','z'), range('0','9'));
			for($i = 0; $i < 5; $i++){
				$result .= $array[mt_rand(0, 35)];
			}
		$name_f=$result;
		
		$periods = HM_Ticket_TicketModel::getPeriodsPDF();
		
		$Payment_periods = $periods[$f_Payment_period_id];
		
		//шапка квитанции
		$Recipient = $requisite->recipient;
		$INN = $requisite->inn;
		$KPP = $requisite->kpp;
		$Bank_account = $requisite->bank_account;
		$OKTMO = $requisite->oktmo;
		$CBC = $requisite->cbc;
		$Recipient_bank = $requisite->bank_recipient;
		$BIK = $requisite->bik;
		$Note = $requisite->note;
		$personalAccount = $requisite->personalaccount;
		
		$tempDir = APPLICATION_PATH . '/../public/upload/files/';
		
		$host = new Zend_View_Helper_ServerUrl;
		$publicDir = 'http://'.$host->getHost().'/upload/files/';
		
		
		$fileName = $name_f.'.png';
		
		$pngAbsoluteFilePath = $tempDir . $fileName;
		
		//--Генерация PDF
		//Генерация изображения QR кода 
		if (!file_exists($pngAbsoluteFilePath)) {
		  QRcode::png('ST00012|Name='.$Recipient.'|PersonalAcc='.$Bank_account.'|BankName='.$Recipient_bank.'|BIC='.$BIK.'|CorrespAcc=0|Purpose='.$Pool.$Paid_services.$Penalties.'|PayeeINN='.$INN.'|KPP='.$KPP.'|CBC='.$CBC.'|OKTMO='.$OKTMO.'|payerFio='.$fio.'|Address='.$f_address.'|personalAccount='.$personalAccount.'|contract='.$f_contract_number.'|Period='.$Datefrom.$Dateto, $pngAbsoluteFilePath, QR_ECLEVEL_M);
		}
		
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		$pdf->SetFont('times', 'BI', 20, '', 'false');
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('RGSU');
		$pdf->SetTitle('Квитанция на оплату');
		$pdf->SetSubject('Квитанция на оплату');
		$pdf->SetKeywords('Квитанция на оплату');

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(2, 5, 5);

		$pdf->AddPage(); 
		$pdf->SetXY(2, 5, 5);
		$pdf->SetDrawColor(100, 100, 0); 		
		$pdf->Image($publicDir.$name_f.'.png', 7, 15, 40, 40, '', '', '', false, 300);
		
		$tbl = '
			<style>
			.kvitancia tr {height: 11.25pt;}
			.kvitancia td {
				padding:0 0 0 1.5pt;
				font-family: arial;
				font-size: 8pt;
			}
			.border-right {border-right: 1pt solid #000000;}
			.border-top {border-top: 1pt solid #000000;}
			.border-bottom {border-bottom: 1pt solid #000000;}
			</style>
			<table class="kvitancia" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
			<tbody>
				<tr>
					<td style="width:152.25pt;"></td>
					<td style="width:5.25pt;"></td>
					<td style="width:57.75pt;"></td>
					<td style="width:47.25pt;"></td>
					<td style="width:47.25pt;"></td>
					<td style="width:26.25pt;"></td>
					<td style="width:38.25pt;"></td>
					<td style="width:26.25pt;"></td>
					<td style="width:105pt;"></td>
					<td style="width:73.5pt;"></td>
				</tr>
				<tr >
					<td class="border-right"></td>
					<td colspan="9" valign="top" style="font-size:6pt; text-align: right;">Форма&nbsp;№&nbsp;ПД-4</td>

				</tr>
				<tr style="height:21.75pt;">
					<td align="center" class="border-right" style="text-align: center">
						<span>ИЗВЕЩЕНИЕ</span>
					</td>
					<td style="height:21.75pt;"></td>
					<td colspan="7" style="height:21.75pt; font-size:8pt;">
						<b>'.$Recipient.'</b>
					</td>
					<td></td>

				</tr>
				<tr >
					<td rowspan="14" class="border-right"></td>
					<td></td>
					<td colspan="8" valign="top" class="border-top" style="font-size: 6pt; text-align: center;">
						(&nbsp;наименование&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" style="text-align: center;">
						<b>'.$INN.'/'.$KPP.'</b>
					</td>
					<td style="text-align: right;">
						№
					</td>
					<td colspan="5" style="text-align: center;">
						<b>'.$Bank_account.' ОКТМО '.$OKTMO.'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" valign="top" class="border-top" style="font-size:6pt;">
						(ИНН/КПП)&nbsp;получателя
					</td>
					<td valign="top"></td>
					<td colspan="5" valign="top" class="border-top" style="font-size:6pt; text-align: center;">
						<span style="">(номер&nbsp;счета&nbsp;получателя&nbsp;платежа)</span>
					</td>

				</tr>
				<tr >
					<td></td>
					<td>
						в
					</td>
					<td colspan="7">
						<b>'.$Recipient_bank.' БИК '.$BIK.' </b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top" style="font-size: 6pt; text-align: center;">
						(наименование&nbsp;банка&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8">
						<b>КБК&nbsp;'.$CBC.'</b>
					</td>

				</tr>
				<tr style="height:12pt;">
					<td></td>
					<td colspan="8" valign="bottom" class="border-top"></td>

				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Оплата за обучение
					</td>
					<td colspan="6" class="border-bottom">
						
					</td>
				</tr>
				<tr >
					<td></td>
					<td colspan="8"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Ф.И.О плательщика:
					</td>
					<td colspan="6" class="border-bottom">
						'.$f_FIO.'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" class="border-bottom">
						Номер договора:
					</td>
					<td colspan="6" class="border-bottom">
						'.$f_Number.' Период оплаты '.$Payment_periods.' '.$data['contract_date'].' '.$data['forWhomPay'].'
					</td>
				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2">
						Сумма&nbsp;платежа
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$f_Price_r.'
					</td>
					<td >
						руб.
					</td>
					<td class="border-bottom" style="text-align: center;">
						'.$f_Price_k.'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr >
					<td class="border-right" style="text-align: center;">
						Кассир
					</td>
					<td></td>
					<td colspan="2">
						Итого
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$f_Price_r.'
					</td>
					<td>
						руб.
					</td>
					<td class="border-bottom" style="text-align: center">
						'.$f_Price_k.'
					</td>
					<td>
						коп.
					</td>

				</tr>
				<tr style="height:6pt;">
					<td class="border-right border-bottom"></td>
					<td class="border-bottom"></td>
					<td colspan="2" class="border-bottom"></td>
					<td colspan="3" class="border-bottom"></td>
					<td colspan="2" class="border-bottom"></td>
					<td class="border-bottom"></td>
				</tr>
				<tr>
					<td class="border-right"></td>
					<td colspan="9"></td>
				</tr>
				<tr style="height:21.75pt;">
					<td align="center" class="border-right" style="text-align: center">
						
					</td>
					<td style="height:21.75pt;"></td>
					<td colspan="7" style="height:21.75pt; font-size:8pt;">
						<b>'.$Recipient.'</b>
					</td>
					<td></td>

				</tr>
				<tr >
					<td rowspan="14" class="border-right"></td>
					<td></td>
					<td colspan="8" valign="top" class="border-top" style="font-size: 6pt; text-align: center;">
						(&nbsp;наименование&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" style="text-align: center;">
						<b>'.$INN.'/'.$KPP.'</b>
					</td>
					<td style="text-align: right;">
						№
					</td>
					<td colspan="5" style="text-align: center;">
						<b>'.$Bank_account.' ОКТМО '.$OKTMO.'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" valign="top" class="border-top" style="font-size:6pt;">
						(ИНН/КПП)&nbsp;получателя
					</td>
					<td valign="top"></td>
					<td colspan="5" valign="top" class="border-top" style="font-size:6pt; text-align: center;">
						<span style="">(номер&nbsp;счета&nbsp;получателя&nbsp;платежа)</span>
					</td>

				</tr>
				<tr >
					<td></td>
					<td>
						в
					</td>
					<td colspan="7">
						<b>'.$Recipient_bank.' БИК '.$BIK.'</b>
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8" valign="top" style="font-size: 6pt; text-align: center;">
						(наименование&nbsp;банка&nbsp;получателя&nbsp;платежа)
					</td>

				</tr>
				<tr >
					<td></td>
					<td colspan="8">
						<b>КБК&nbsp;'.$CBC.'</b>
					</td>

				</tr>
				<tr style="height:12pt;">
					<td></td>
					<td colspan="8" valign="bottom" class="border-top"></td>

				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Оплата за обучение:
					</td>
					<td colspan="6" class="border-bottom">
						
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2" class="border-bottom">
						Ф.И.О плательщика:
					</td>
					<td colspan="6" class="border-bottom">
						'.$f_FIO.'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8"></td>

				</tr>
				<tr >
					<td></td>
					<td colspan="2" class="border-bottom">
						Номер договора
					</td>
					<td colspan="6" class="border-bottom">
						'.$f_Number.' Период оплаты '.$Payment_periods.' '.$data['contract_date'].' '.$data['forWhomPay'].'
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="8" valign="top"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
						Сумма&nbsp;платежа
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$f_Price_r.'
					</td>
					<td >
						руб.
					</td>
					<td class="border-bottom" style="text-align: center;">
						'.$f_Price_k.'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr>
					<td class="border-right" style="text-align: center;">
						Квитанция
					</td>
					<td></td>
					<td colspan="2">
						Итого
					</td>
					<td colspan="3" class="border-bottom" style="text-align: center">
						'.$f_Price_r.'
					</td>
					<td>
						руб.
					</td>
					<td class="border-bottom" style="text-align: center">
						'.$f_Price_k.'
					</td>
					<td>
						коп.
					</td>
				</tr>
				<tr style="height:6pt;">
					<td class="border-right" style="text-align: center">
						Кассир
					</td>
					<td></td>
					<td colspan="2"></td>
					<td colspan="3"></td>
					<td colspan="2"></td>
					<td></td>
				</tr>
			</tbody>
		</table>';


		$pdf->writeHTML($tbl, true, false, false, false, '');  
		$pdf->Output($tempDir.$name_f.'.pdf','F');
		
		$to = $f_Email;
		$from = 'noreply@rgsu.net';
		$reply = $f_Email;
		$from_name = 'Сайт РГСУ';
		$subject = 'Квитанция на оплату';
		$text = '<p>Ваша квитанция сформирована.<br/>Теперь вы можете распечатать ее и оплатить через Сбербанк Онлайн по карте Сбербанка, устройство самообслуживания (банкомат или терминал) картой Сбербанка или наличными, а также обратиться в ближайшее отделение Сбербанка.</p><hr style="border: none;border-bottom: 1px dotted #ccc;">';
		#<p>C любовью, Ваш РГСУ.</p><p style="font-size:12px;line-height:16px;">Call-центр: +7 (495) 748-67-67  |  Приемная комиссия: +7 (495) 748-67-77<br/><a href="http://rgsu.net/">Сайт РГСУ</a>  |  <a href="http://vk.com/rgsu_official">РГСУ Вконтакте</a>  |  <a href="https://twitter.com/RGSU_official">Twitter РГСУ</a>  |  <a href="https://www.facebook.com/rgsu.official">Facebook РГСУ</a></p>';


		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);		
		
		//$mail->setFrom($from, $from_name);
		$mail->setFromToDefaultFrom(); //--отсылка от имени юзера запрещен настройками почтовика. По этому отылаем от имени СДО
		
		$mail->addTo($to);
		$mail->setSubject($subject);

		$content = file_get_contents($tempDir.$name_f.'.pdf'); 
		$attachment = new Zend_Mime_Part($content);
		$attachment->type = 'application/pdf';
		$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding = Zend_Mime::ENCODING_BASE64;
		$attachment->filename = $name_f.'.pdf';
		
		$mail->addAttachment($attachment);                  
		
		unlink($tempDir.$name_f.'.pdf');
		unlink($tempDir.$name_f.'.png');
		
		try {	
			$mail->send(); //--Проверить отправку писем. 5.7.1 ошибка
		} catch (Exception $e) {		
			return false;
		}
		return true;
	}
	
	# ссылки для быстрой оплаты.
	public function getAllowFastPayLinks()
	{
		$detect = new Mobile_Detect;
		
		if(!$detect->isMobile() && !$detect->isTablet()){ return false; }
		
		$data = array();
			
		if($detect->isiOS()){
			# Оплата общежития
			$data[HM_Ticket_TicketModel::SERVICE_TYPE_HOSTEL] = 'sberbankonline://payments/services/init/?ids=eyJjbiI6eyJiIjoiMjg0IiwibiI6ItCe0LHRidC10LbQuNGC0LjQtSIsInBzIjoiNTUwNTE1ODEwIn0sIm5zIjp7Im5vZGUwLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjUwMDQ4NDM2NSJ9LCJub2RlMS5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI2NjY3MjcifSwibm9kZTIub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwNDgzNzc1In0sIm5vZGUzLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjUwMDQ4MzEwNyJ9LCJub2RlNC5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI1MDA0Nzc0ODIifSwibm9kZTUub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwNDc3NDYyIn19LCJhdCI6ZmFsc2V9';
			
			# Образование (для студентов)
			$data[HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION]	= 'sberbankonline://payments/services/init/?ids=eyJjbiI6eyJiIjoiMjg0IiwibiI6ItCe0LHRgNCw0LfQvtCy0LDQvdC40LUgKNC00LvRjyDRgdGC0YPQtNC10L3RgtC-0LIpIiwicHMiOiI1NTA0MDUyMTEifSwibnMiOnsibm9kZTAub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwMzczODA5In0sIm5vZGUxLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjU1NjI1MyJ9LCJub2RlMi5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI1MDAzNzMzMDkifSwibm9kZTMub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwMzcyNTA4In19LCJhdCI6ZmFsc2V9';
			
			return $data;
		}
		
		if($detect->isAndroidOS()){
			# Оплата общежития
			$data[HM_Ticket_TicketModel::SERVICE_TYPE_HOSTEL] = 'android-app://ru.sberbankmobile/android-app/ru.sberbankmobile/payments/services/init?ids=eyJjbiI6eyJiIjoiMjg0IiwibiI6ItCe0LHRidC10LbQuNGC0LjQtSIsInBzIjoiNTUwNTE1ODEwIn0sIm5zIjp7Im5vZGUwLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjUwMDQ4NDM2NSJ9LCJub2RlMS5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI2NjY3MjcifSwibm9kZTIub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwNDgzNzc1In0sIm5vZGUzLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjUwMDQ4MzEwNyJ9LCJub2RlNC5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI1MDA0Nzc0ODIifSwibm9kZTUub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwNDc3NDYyIn19LCJhdCI6ZmFsc2V9';
			
			# Образование (для студентов)
			$data[HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION] = 'android-app://ru.sberbankmobile/android-app/ru.sberbankmobile/payments/services/init?ids=eyJjbiI6eyJiIjoiMjg0IiwibiI6ItCe0LHRgNCw0LfQvtCy0LDQvdC40LUgKNC00LvRjyDRgdGC0YPQtNC10L3RgtC-0LIpIiwicHMiOiI1NTA0MDUyMTEifSwibnMiOnsibm9kZTAub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwMzczODA5In0sIm5vZGUxLm9ubGluZS5zYmVyYmFuay5ydSI6eyJwcyI6IjU1NjI1MyJ9LCJub2RlMi5vbmxpbmUuc2JlcmJhbmsucnUiOnsicHMiOiI1MDAzNzMzMDkifSwibm9kZTMub25saW5lLnNiZXJiYW5rLnJ1Ijp7InBzIjoiNTAwMzcyNTA4In19LCJhdCI6ZmFsc2V9';
			
			return $data;
		}
		return false;
	}
	
	
}