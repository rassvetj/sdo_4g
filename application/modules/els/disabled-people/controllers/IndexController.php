<?php
class DisabledPeople_IndexController extends HM_Controller_Action_Crud
{
    public function init(){
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'survey', 'disabled-people');		
		
		
		/*
		$user = $this->getService('User')->getCurrentUser();
		$recruitInfo = $this->getService('Recruits')->getRecruitInfo($user->mid_external);
		if(empty($recruitInfo)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('message' => 'Нет доступной информации для подтверждения.', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect('/');
		}
		*/	
		parent::init();		
	}
	
	public function indexAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Кабинет ОВЗ'));
		$serviceWays = $this->getService('DisabledPeople');
		
		$specialFunds = $serviceWays->getSpecialFunds();
		$this->view->specialFunds = $specialFunds;
		
		
		$user = $this->getService('User')->getCurrentUser();
		
		
		
		
		$res = $serviceWays->getOne($serviceWays->fetchAll($serviceWays->quoteInto('mid_external = ?', $user->mid_external),array('way_id DESC')));
		
		#$res = $serviceWays->getOne($serviceWays->fetchAll($serviceWays->quoteInto('mid_external = ?', $user->mid_external)));
		
		$this->view->isDisableForm = ($res->status == HM_DisabledPeople_DisabledPeopleModel::STATUS_IN_WORK)?(true):(false); # Если заявка ушла в 1С и согласовывается, то студент не может изменить
		if($res){
			$statuses = HM_DisabledPeople_DisabledPeopleModel::getWayStatuses();		
			
			$this->view->ways 			  = HM_DisabledPeople_DisabledPeopleModel::getWays();
			$this->view->description_ways = HM_DisabledPeople_DisabledPeopleModel::getDescriptionWays();
			$this->view->status 		  = $statuses[$res->status];
			
			$special_fund_array = array();
			
			if(!empty($res->special_fund_manual)){
				$special_fund_array[] = $res->special_fund_manual;
			}
			
			if(!empty($res->special_funds)){
				$userSpecialFunds = explode(',',$res->special_funds);					
				foreach($specialFunds as $s){
					if(in_array($s['code'], $userSpecialFunds)){
						$special_fund_array[] = $s['name'];						
					}
				}				
			}
			$this->view->special_funds = $special_fund_array;
			
			if(!empty($res->selected_way)){
				$this->view->selected_way = $res->selected_way;
			}			
		} else {
			$this->view->isEmpty = true;
		}
    }
	
	public function saveWayAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$selected_way = $this->_getParam('selected_way', false);
		$fund_array = $this->_getParam('fund', false);
		$special_fund_manual = $this->_getParam('special_fund_manual', false);
		$special_funds = implode(',',$fund_array);
		
		if(!$selected_way){
			$this->_flashMessenger->addMessage(array('message' => 'Выберите один из выриантов освоения программы.', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));			
		} else {
			$user = $this->getService('User')->getCurrentUser();
			$serviceWays = $this->getService('DisabledPeople');			
			$res = $serviceWays->getOne($serviceWays->fetchAll($serviceWays->quoteInto('mid_external = ?', $user->mid_external),array('way_id DESC')));
			if($res){
				if($res->status == HM_DisabledPeople_DisabledPeopleModel::STATUS_NEW){
					$needUpdate = true;					
				}
			}
#			try {
 

			if($needUpdate){
				$this->getService('DisabledPeople')->updateWhere(array('selected_way' => $selected_way, 'timestamp' => time(), 'special_fund_manual' => $special_fund_manual, 'special_funds' => $special_funds, 'status' => HM_DisabledPeople_DisabledPeopleModel::STATUS_NEW), array('way_id = ?' => $res->way_id, 'mid_external = ?' => $user->mid_external));	
			} else {
				$this->getService('DisabledPeople')->insert(
					array(
						'selected_way' => $selected_way,
						'timestamp' => time(),
						'special_fund_manual' => $special_fund_manual, 
						'special_funds' => $special_funds,
						'status' => HM_DisabledPeople_DisabledPeopleModel::STATUS_NEW,
						'mid_external' => $user->mid_external,					
					)
				);	
			}
	#		} catch (Exception $e) {
   # echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
#}
			$this->_flashMessenger->addMessage(_('Данные успешно сохранены.'));			
		}
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'index', 'disabled-people');			    
	}
    
   
}