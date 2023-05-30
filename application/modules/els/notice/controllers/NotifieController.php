<?php
class Notice_NotifieController extends HM_Controller_Action
{	
	public function indexAction() {
		$this->view->setHeader(_('Настройка уведомлений внутри личного кабинета.'));		
	}
	
	public function setTutorDefaultAction(){
		
		$this->_result = array();
				
		$userService = $this->getService('User');
		$select = $userService->getSelect();        
		$select->from(array('p' => 'People'),
            array(
				'MID'  => 'p.MID',									
            )
        );
        $select->join(
			array('t' => 'Tutors'),
			't.MID = p.MID',
			array()
		);		
		$select->group(array('p.MID'));		
		$tutors = $select->query()->fetchAll();
		
		if(!$tutors){
			$this->_flashMessenger->addMessage(_('Нет тьюторов.'));
		}
		
		foreach($tutors as $t){	
			$userService->setDefaultNotifies($t['MID']);						
		}		
		$this->_flashMessenger->addMessage(_('Изменение настроек уведомлений прошло успешно.'));
		$this->_redirector->gotoSimple('index', 'notifie', 'notice');		
	}
}