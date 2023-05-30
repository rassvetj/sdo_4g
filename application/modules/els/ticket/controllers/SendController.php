<?php

//подключаем библиотеки
//require_once("/web/www/netcat/modules/default/tcpdf/tcpdf.php");
require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';

//require_once('/web/www/netcat/modules/default/phpqrcode/qrlib.php'); 
require_once APPLICATION_PATH . '/../library/phpqrcode/qrlib.php';

//require_once('/web/www/netcat/modules/default/phpqrcode/qrconfig.php');
require_once APPLICATION_PATH . '/../library/phpqrcode/qrconfig.php';

class Ticket_SendController extends HM_Controller_Action {
	
	
	public function init(){
		$this->_ticketService = $this->getService('Ticket');
		parent::init();
	}
	
	
	public function indexAction()
    {
		
        $this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        if (!$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
		
		$form = new HM_Form_Ticket();
		$request = $this->getRequest();
		
		$is_pay_card = (bool) $request->getParam('is_pay_card', false); # оплата картой
		
		$filial_id = $request->getParam('filial_id', false);
		if($filial_id < 1){ echo 'Не выбран филиал'; exit(); }
		
		$service_type_code = $request->getParam('service_type_id', false);
		if(empty($service_type_code)){ echo 'Не выбран сервис'; exit(); }
		
		$this->updateFormRequired($form, $service_type_code);
		
		if ($form->isValid($request->getParams())) {
			$data = array();
			$user = $this->getService('User')->getCurrentUser();
			$fields_all = HM_Ticket_TicketModel::getFormShowElements();
			$fields_service = $fields_all[$service_type_code]; //--поля для определенной услуги			
			$values = $request->getParams();
			$sum_of = 0;
			foreach($values as $field_name => $value){				
				$value = str_replace('"', '', $value);
				if($field_name == 'sum1'){ $sum_of = $sum_of + intval($value); } # рубли
				elseif($field_name == 'sum2'){ $sum_of = $sum_of + (intval($value) / 100); } # копейки
				elseif(in_array($field_name,$fields_service)){
					$data[$field_name] = strip_tags($value);
					unset($values[$field_name]);
				} 
			}
			$data['sum_of'] 		= $sum_of;			 
			$data['mid_external'] 	= $user->mid_external;		 
			$data['filial_id'] 		= $filial_id;		 
			$data['date_create'] 	= date('Y-m-d H:i:s');
			$data['service_type_id']= $this->getService('Ticket')->getServiceTypeId($service_type_code); # в $data['service_type_id'] не id, а код. Поэтому переопределяем
			$data['type']			= ($is_pay_card) ? HM_Ticket_TicketModel::PAY_TYPE_ACQUIRING : HM_Ticket_TicketModel::PAY_TYPE_PDF;
			try {				
				$insert = $this->_ticketService->insert($data);				
				if($insert){					
					$isUpdate = $this->_ticketService->update(array('ticket_id' => $insert->ticket_id, 'ticket_external_id' => 'SDO'.$insert->ticket_id));				
					$insert->ticket_external_id = $isUpdate->ticket_external_id;
					
					if($is_pay_card){ # оплата картой
						if(isset($insert->contract_number) && !empty($insert->contract_number)){ # в Сбер номер договора нужен без различных приписок.
							$insert->contract_number = str_replace(array('(образование)','(проживание)'), '', $insert->contract_number);
							$insert->contract_number = trim($insert->contract_number);
						}
						
						$isPay = $this->_ticketService->payCard($insert);						
						if(!$isPay){
							$return['message'] 	= _('Не удалось сформировать запрос на оплату картой. Попробуйте позже.');
							$form->setDefaults($values);
						} elseif(isset($isPay['error'])){
							$return['message'] 	= $isPay['error'];
							$form->setDefaults($values);
						} elseif(isset($isPay['url'])){
							echo _('Сейчас вы будете перенаправлены на платежный шлюз.');							
							echo '<script>
									setTimeout(function(){
										document.location.href = "'.$isPay['url'].'";
									}, 2000);
								  </script>';							
							exit();
						}
					} else {
						$isSend = $this->_ticketService->sendTicket($insert); # отсылаем письмо с сгенерированной pdf.								
						if($isSend){
							$form->reset();
							$this->setDefaultFormRequired($form);							
							
							$tt = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);							
							$dafaultValues = array(
								'filial_id' 			=> ($tt) ? ($tt->requisite_id) : (''),
								'payerLastName' 		=> $user->LastName,
								'payerFirstName' 		=> $user->FirstName,
								'payerPatronymic' 		=> $user->Patronymic,
								'payerEmail' 			=> $user->EMail,
								'forWhomPayLastName'	=> $user->LastName,
								'forWhomPayFirstName'	=> $user->FirstName,
								'forWhomPayPatronymic'	=> $user->Patronymic,								
							);
							$form->setDefaults($dafaultValues);
							$return['code'] 	= 1;
							$return['message'] 	= _('Квитанция отправлена.');	
						} else{
							$return['message'] 	= _('Не удалось отправить квитанцию. Попробуйте позже.');
							$form->setDefaults($values);
						}	
					}
				} else {					
					$return['message'] 	= _('Не удалось создать квитанцию. Попробуйте позже.');
				}										
			} catch (Exception $e) {				
				//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
				$return['message'] 	= _('Не удалось создать квитанцию. Попробуйте позже.');
			}
			
			if($this->getRequest()->isXmlHttpRequest()){ //--аякс
				echo $this->view->notifications(array(array(
					'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => $return['message']
				)), array('html' => true));				
				echo $form;
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage(array(
					'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' 	=> $return['message']
				));
				$this->_redirector = $this->_helper->getHelper('Redirector');           
				$this->_redirector->gotoSimple('index', 'index', 'ticket');	
			}
		} else {			
			$this->setDefaultFormRequired($form);
			if($this->getRequest()->isXmlHttpRequest()){ //--аякс
				$values = $request->getParams();
				$form->setDefaults($values);
				echo $form;
			} else {	
				$return['message'] = _('Не все поля заполнены.');
				$this->_helper->getHelper('FlashMessenger')->addMessage(array(
						'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
						'message' 	=> $return['message']
				));
				$this->_redirector = $this->_helper->getHelper('Redirector');           
				$this->_redirector->gotoSimple('index', 'index', 'ticket');				
			}			
		}	
		
		
		/*
		$this->getHelper('viewRenderer')->setNoRender();
        
        $form = new HM_Form_Ticket();
		
		$return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );

				
        $request = $this->getRequest();

        if ($request->isPost() || $request->isGet()) {
			
			
			$request->setParam('fio', strip_tags($request->getParam('fio')));
			$request->setParam('email', strip_tags($request->getParam('email')));
			$request->setParam('contractNumber', strip_tags($request->getParam('contractNumber')));
			$request->setParam('period', strip_tags($request->getParam('period')));
			$request->setParam('sum1', strip_tags($request->getParam('sum1')));
			$request->setParam('sum2', strip_tags($request->getParam('sum2')));
			
            $systemInfo = $request->getParam('systemInfo');
			if ($form->isValid($request->getParams())) {				
				$fio = ($request->getParam('fio') != '') ? $request->getParam('fio') : false;				
				$email = filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL) ? $request->getParam('email') : false;				
				$contractNumber = ($request->getParam('contractNumber') != '') ? $request->getParam('contractNumber') : false;		
				$period = intval($request->getParam('period')) > 0 ? intval($request->getParam('period')) : false;
				$sum1 = intval($request->getParam('sum1')) > 0 ? intval($request->getParam('sum1')) : false; //--рубли
				$sum2 = intval($request->getParam('sum2')) > 0 ? intval($request->getParam('sum2')) : '00'; //--копейки
				
				
				//$cuid = $this->getService('User')->getCurrentUserId();
				$user = $this->getService('User')->getCurrentUser();
				$uCode = str_replace(' ', '', $user->mid_external); //--На случай, если встретится код вида "XXX XXX".
				//var_dump($user);
				//var_dump($user->mid_external);
				
				$is_send = false; //--признак успешной отправки письма.
				$is_create = false;
				
				if($fio && $email && $contractNumber && $period && $sum1){
					
					$result = $this->_ticketService->addTicket($uCode, $contractNumber, $period, $sum1.'.'.$sum2);
					//var_dump($uCode);
					if($result === false){
						
					}
					else {
						$is_create = true;
						
						$is_send = $this->_ticketService->sendTicketPDF($contractNumber, $period, $email, $fio, $sum1, $sum2); //--Быть может перенести сюда из сервисного слоя.					
					}
				}				
				
				if($is_create){
					if($is_send){
						$return['code']    = 1;
						$return['message'] = _('Квитанция успешно отправлена');		
						$form = new HM_Form_Ticket(); //--костыль для обнуления полей формы по умолчанию					
					}
					else {					
						$this->_ticketService->deleteTicket($result->TicketID); //--/Если не удалось отправить письмо, то удаляем запись в БД
						
						$return['code']    = 0;
						$return['message'] = _('Не удалось отправить квитанцию.');						
					}
				}
				else {
					$return['code']    = 0;
					$return['message'] = _('Не удалось сформировать квитанцию.');					
				}
			}			
			echo $this->view->notifications(array(array(
				'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => $return['message']
			)), array('html' => true));
			
		}
		
		//var_dump($form);
		
		echo $form->render();
		*/
	}
	
	/**
	 * задаем обязательность полей в зави симости от выбранного сервиса
	*/
	public function updateFormRequired($form, $service_type_id){
		if(!($form instanceof HM_Form) || !$service_type_id) { return; }
				
		foreach($form->getElements() as $element){		
			$element->setRequired(false);							
		}					
		$rulesElements = HM_Ticket_TicketModel::getFormRequiredElements();
		$serviceRule = $rulesElements[$service_type_id]; //--список обязательных полей
		if(count($serviceRule)){
			foreach($serviceRule as $item_id){								
				$element = $form->getElement($item_id);
				if($element){
					$element->setRequired(true);				
				}	
			}
		}
	}
	
	
	/**
	 * задаем обязательные поля по умолчанию
	*/
	public function setDefaultFormRequired($form){
		if(!($form instanceof HM_Form)) { return; }
		foreach($form->getElements() as $element){		
			$element->setRequired(false);							
		}					
		$rulesElements = HM_Ticket_TicketModel::getFormRequiredElements();
		$defaultElements = array();
		if(count($rulesElements)){
			foreach($rulesElements as $service_type){
				if(count($service_type)){
					foreach($service_type as $item_id){
						$defaultElements[$item_id] = $item_id;		
					}
				}				
			}
		}
		if(!count($defaultElements)) { return; }
		
		foreach($defaultElements as $item_id){								
			$element = $form->getElement($item_id);
			if($element){
				$element->setRequired(true);				
			}	
		}
	}
	
}