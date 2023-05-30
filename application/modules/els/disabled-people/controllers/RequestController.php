<?php
class DisabledPeople_RequestController extends HM_Controller_Action
{
    public function init(){
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
		$this->getService('Unmanaged')->setHeader(_('Кабинет ОВЗ: обращения студента'));
		$form = new HM_Form_Request();
		$user = $this->getService('User')->getCurrentUser();
		
		$this->view->form = $form;
		$serviceRequest = $this->getService('DisabledPeople');
		$select = $serviceRequest->getSelect();
		$select->from(
            array('disabled_people_requests'),
            array(
				'request_id',
				'mid_external',
				'type',
				'person',
				'question',
				'answer',
				'created',
			)
		);
		$select->where($this->quoteInto('mid_external = ?',  $user->mid_external));
		
		
		$grid = $this->getGrid(
            $select,
            array(
                'request_id' => array('hidden' => true), 
                'mid_external' => array('hidden' => true), 
                'type' => array('hidden' => true), 
                'person' => array('title' => _('Подразделение')), 
                'question' => array('title' => _('Вопрос')), 
                'created' => array('hidden' => true), 
                'answer' => array('title' => _('Ответ')), 
			),
			array()
		);
		$this->view->grid = $grid->deploy();
	
    }
	
	public function sendAction() {
	
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$serviceResume = $this->getService('DisabledPeopleResume');
		$user = $this->getService('User')->getCurrentUser();
		
		
		$groups = HM_DisabledPeople_DisabledPeopleModel::getFundGroups();
		$special_funds = array();
		foreach($groups as $group_id => $group_name){
			$fund = $this->_getParam('fund_'.$group_id, array());
			if(!empty($fund)){ $special_funds = array_merge($special_funds, $fund); }
		}
		
		$data = array(		
			'type' 				=> intval($this->_getParam('type', HM_DisabledPeople_DisabledPeopleModel::TYPE_REQUEST_QUESTION)),
			'person' 			=> strip_tags($this->_getParam('person', '')),
			'question' 			=> strip_tags($this->_getParam('question', '')),
			'created' 			=> date('Y-m-d H:i:s'),
			'mid_external' 		=> $user->mid_external,
			'special_funds'		=> implode(',', $special_funds),
		);
		$db = $this->getService('User')->getMapper()->getTable()->getAdapter();
		$result = $db->insert('disabled_people_requests', $data);
		if($result) { $this->_flashMessenger->addMessage(_('Заявка успешно создана.')); }
		else		{ $this->_flashMessenger->addMessage(_('Не удалось создать заявку. Попробуйте позже.')); }
		
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'request', 'disabled-people');	
	}
	
    
   
}