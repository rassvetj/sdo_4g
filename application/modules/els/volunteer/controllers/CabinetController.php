<?php
class Volunteer_CabinetController extends HM_Controller_Action
{    
   public function init() {		
		
		if(!$this->getService('User')->isMainOrganization()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('У вас нет доступа к этому разделу'))
			);			
			$this->_redirect('/');		
		}
	}


    public function indexAction()
    {	
		$this->view->setHeader(_('Личный кабинет волонтера'));
		$this->_volService = $this->getService('Volunteer');
		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		$this->view->gridAjaxRequest = $this->isAjaxRequest();
		
				
		if($this->_volService->isVolunteerRequestExist()){
			$user = $this->getService('User')->getCurrentUser();			
			$status = $this->_volService->getVolunteerStatus($user->mid_external);	
			$reason = $this->_volService->getVolunteerReason($user->mid_external);	
						
			
			$volunteerEvents = false;
			
			if($status == HM_Volunteer_VolunteerModel::VOLUNTEER_APPROVE){ //--если волонтер.				
				$request = $this->getRequest();
				//$eventStatus = $request->getParam('status');				
				//$volunteerEvents = $this->_volService->getVolunteerEvents($eventStatus);
				
		
				$selectEvents = $this->_volService->getSelectEvents();								
				$gridId = 'grid_volunteer';				
				$grid = $this->getGrid(
					$selectEvents,
					array(				            
						'event_member_id' => array('hidden' => true),
						'event_name'	=> array('title' => _('Название')),							
						'date_create'	=> array(
							'title' => _('Подана заявка'),
							'callback' => array('function' => array($this, 'updateGridDate'), 'params' => array('{{date_create}}'))
						), 						
						
						'status'	=> array(
							'title' => _('Статус заявки'),
							'callback' => array('function' => array($this, 'updateMemberStatus'), 'params' => array('{{status}}')),					
						), 

						
						'filial'	=> array('title' => _('Филиал')),						
						'member_function'	=> array('title' => _('Функции')),												
						
						'role'	=> array(
							'title' => _('Роль'),
							'callback' => array('function' => array($this, 'updateRole'), 'params' => array('{{role}}')),					
						), 
						
						'hours'	=> array('title' => _('Часы')),																								
											
						
						'date_begin'	=> array(
							'title' => _('С'),
							'callback' => array('function' => array($this, 'updateGridDate'), 'params' => array('{{date_begin}}'))
						), 						
						'date_end'	=> array(
							'title' => _('До'),
							'callback' => array('function' => array($this, 'updateGridDate'), 'params' => array('{{date_end}}'))
						), 																		
						'address'	=> array('title' => _('Адрес')),						
						'manager'	=> array('title' => _('Ответственный')),						
						'phone'	=> array('title' => _('Телефон')),												
						//'ended'	=> array('title' => _('Статус мероприятия')),	
						'ended'	=> array(
							'title' => _('Статус мероприятия'),
							'callback' => array('function' => array($this, 'updateStstusEvent'), 'params' => array('{{ended}}'))
						), 	
					),
					array(				               													
						'date_create' => array('render' => 'DateSmart'),	
						'status' => array('values' => HM_Volunteer_VolunteerModel::getMemberStatusEvents()),	
						'filial' => null,						
						'member_function' => null,						
						//'role' => null,	
						'role' => array('values' => HM_Volunteer_VolunteerModel::getRoles()),						
						'hours' => null,						
						'event_name' => null,
						'date_begin' => array('render' => 'DateSmart'),						
						'date_end' => array('render' => 'DateSmart'),	
						'address' => null,						
						'manager' => null,						
						'phone' => null,						
						'ended' => array('values' => HM_Volunteer_VolunteerModel::getStatusEvents()),							
					),
					$gridId
				);					
				$this->view->grid_events = $grid->deploy();
				
				$formEventRequest = new HM_Form_VolunteerEventRequest();				
				$this->view->formEventRequest = $formEventRequest;
			}
						
			$this->view->volunteerEvents = $volunteerEvents;
			$this->view->isVolunteerRequest = true;			
			$this->view->volunteerStatus = $status;
			$this->view->volunteerReason = $reason;
		} else {
			$form = new HM_Form_VolunteerMemberReqest();			
			
			$this->view->member_reqest_form = $form;							
			$this->view->isVolunteerRequest = false;
			$this->view->info = $this->view->render('cabinet/info.tpl');;
		}
    }    
	
	public function sendMemberReqestAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();        
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$request = $this->getRequest();
		if ($request->isPost() || $request->isGet()) {
			
			
			$form = new HM_Form_VolunteerMemberReqest();
			
			$return = array(
				'code' => 0,
				'message' => _('Не удалось подать заявку. Повторите попытку позже.')
			);
			
			if ($form->isValid($request->getParams())) {
				$this->_volService = $this->getService('Volunteer');
								
				$isSend = $this->_volService->sendVolunteerMemberReqest();
				if($isSend){
					$return = array(
						'code' => 1,
						'message' => _('Заявка успешно отправлена. Ее обработка займет от 1 до 3 дней.')
					);
				}
				
			}
			echo $this->view->notifications(array(array(
					'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => $return['message']
			)), array('html' => true));
			
			if($return['code'] == 0){
				echo $form->render();
			}
			
			
		} 
	}
	
	/**
	 *  запрос на участие в мероприятии
	*/
	public function sendEventReqestAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
		if ($request->isPost() || $request->isGet()) {
			
			$form = new HM_Form_VolunteerEventRequest();
			
			$return = array(
				'code' => 0,
				'message' => _('Не удалось подать заявку на участие в мероприятии. Повторите попытку позже.')
			);
			
			if ($form->isValid($request->getParams())) {				
				$eventId = $request->getParam('event');	
				if(isset($eventId) && !empty($eventId)){										
					$this->_volService = $this->getService('Volunteer');
					$isSens = $this->_volService->sendEventRequest($eventId);
					if($isSens){
						$return = array(
							'code' => 1,
							'message' => _('Заявка на мероприятие успешно отправлена. Ее обработка займет от 1 до 3 дней.')
						);
					}
				}				
			}
			
			echo $this->view->notifications(array(array(
					'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => $return['message']
			)), array('html' => true));
			
			
			$form = new HM_Form_VolunteerEventRequest(); //--для обновления списка меропирятий.
			echo $form->render();			
		}
	}
	
	public function infoAction(){ //--весь контерт в шаблоне (HTML).
		
	}
	
	
	public function updateGridDate($date){
		if (!strtotime($date)) return '';
		
		return date('d.m.Y',strtotime($date));
	}
	
	public function updateMemberStatus($status){
		$statuses = HM_Volunteer_VolunteerModel::getMemberStatusEvents();
        return $statuses[$status];
	}
	 
	public function updateRole($role){
		$roles = HM_Volunteer_VolunteerModel::getRoles();
        return $roles[$role];
	}
	
	public function updateStstusEvent($eventStatus){	
		$eventStatuses = HM_Volunteer_VolunteerModel::getStatusItemEvents();
        return $eventStatuses[$eventStatus];
	}
	 
	 
}