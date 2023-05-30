<?php
class Club_IndexController extends HM_Controller_Action
{
	private $_serviceClub 		= null;
	private $_serviceClubClaim 	= null;
	private $_isPeriodAvailable	= false; # доступна ли подача/отмена заявки
	
	
	#$isPeriodAvailable = $this->_serviceClubClaim->isPeriodAvailable();
	
	public function init(){
		parent::init();
		
		$action = $this->getRequest()->getActionName();
		if(!$this->_serviceClubClaim)	{ $this->_serviceClubClaim 	= $this->getService('ClubClaim');	}
		$this->_isPeriodAvailable = $this->_serviceClubClaim->isPeriodAvailable();	
		
		if($action != 'index' && $action != 'get-club-info'){			
			if(!$this->_isPeriodAvailable){
				$data['message'] = _('Время подачи заявки истекло или еще не наступило');
				$data['error'] 	 = 1;
				
				if ($this->isAjaxRequest()){
					echo json_encode($data);
					die;			
				}
		
				$this->_helper->getHelper('FlashMessenger')->addMessage(array(
					'type' 		=> $data['error'] == 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' 	=> $data['message']
				));
				$this->_redirector = $this->_helper->getHelper('Redirector');           
				$this->_redirector->gotoSimple('index', 'index', 'club');	
				die;
		
			}			
		}
		
	}
	
	
	public function indexAction(){
		
		$this->view->setHeader(_('Запись на кружок'));
			
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		if(!$this->_serviceClub)		{ $this->_serviceClub 		= $this->getService('Club'); 		}
		if(!$this->_serviceClubClaim)	{ $this->_serviceClubClaim 	= $this->getService('ClubClaim');	}
		
		$user_id 	= (int)$this->getService('User')->getCurrentUserId();
		$claim 		= $this->_serviceClubClaim->getByUser($user_id);
		
		$this->view->isPeriodAvailable = $this->_isPeriodAvailable;
		
		if(!$claim){
			if($this->_isPeriodAvailable){
				$this->view->form = new HM_Form_Request();
			}
		} else {
			
			$this->view->claim = array(
				'id' 			=> $claim->claim_id,							
				'group_name' 	=> $claim->group_name,
				'email' 		=> $claim->email,
				'date_created' 	=> date('d.m.Y H:i', strtotime($claim->date_created)),
			);	
					
			$club = $this->_serviceClub->getById($claim->club_id);
			$this->view->club = array(
				'name' 			=> $club->name,
				'faculty' 		=> $club->faculty,
				'description' 	=> $club->description,
				'organizer' 	=> $club->organizer,
				'manager' 		=> $club->manager,
			);
			$this->view->claim_info 	= $this->view->render('index/claim-info.tpl');			
			$this->view->claimExist		= true;
		}
	}
	
	
	public function removeClaimAction(){
		if(!$this->_serviceClubClaim)	{ $this->_serviceClubClaim 	= $this->getService('ClubClaim');	}
		
		$user_id 	= (int)$this->getService('User')->getCurrentUserId();
		
		$isDeleted	= $this->_serviceClubClaim->deleteByUser($user_id);
		$data 		= array();
		if($isDeleted){
			$data['message'] = _('Заявка удалена');
		} else {
			$data['error'] = 1;			
			$data['message'] = _('Не удалось удалить заявку');
		}
		$this->_helper->getHelper('FlashMessenger')->addMessage(array(
			'type' 		=> $data['error'] == 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' 	=> $data['message']
		));
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'index', 'club');	
	}
	
	
	
	
	
	public function sendRequestAction(){
		if (!$this->_request->isPost()) {
			die;
		}
		
		$data 		= array();
		$user_id 	= (int)$this->getService('User')->getCurrentUserId();		
		$club_id 	= (int)$this->_request->getParam('club_id', false);
		$group_name = $this->_request->getParam('group_name', false);
		$fio 		= $this->_request->getParam('fio', false);
		$email 		= $this->_request->getParam('email', false);
		
		
		
		if(!$club_id){
			$data['message'] = _('Не выбран кружок');
			$data['error'] 	 = 1;
		} else {
			if(!$this->_serviceClub)		{ $this->_serviceClub 		= $this->getService('Club'); 		}
			if(!$this->_serviceClubClaim)	{ $this->_serviceClubClaim 	= $this->getService('ClubClaim');	}
			
			$isExist = $this->_serviceClubClaim->isExist($user_id);
			if($isExist){
				$data['message'] = _('Вы уже подали заявку');
				$data['error'] 	 = 1;
			} else {
				$raw = array(
					'user_id' 		=> $user_id,
					'club_id' 		=> $club_id,
					'group_name' 	=> $group_name,					
					'fio' 			=> $fio,					
					'email' 		=> $email,					
				);				
				$new_claim = $this->_serviceClubClaim->add($raw);				
				if(!$new_claim){
					$data['message'] = _('Не удалось подать заявку');
					$data['error'] 	 = 1;
				} else {
					$data['message'] = _('Заявка успешно подана');
					$this->view->claim = array(
						'id' 			=> $new_claim->claim_id,							
						'group_name' 	=> $new_claim->group_name,
						'email' 		=> $new_claim->email,						
					);	
					
					$club = $this->_serviceClub->getById($new_claim->club_id);
					$this->view->club = array(
						'name' 			=> $club->name,
						'faculty' 		=> $club->faculty,
						'description' 	=> $club->description,
						'organizer' 	=> $club->organizer,
						'manager' 		=> $club->manager,
					);
					
					$data['claim']   = $this->view->render('index/claim-info.tpl');
				}
			}
		}
		
		
		
		if ($this->isAjaxRequest()){
			echo json_encode($data);
			die;			
		}
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(array(
			'type' 		=> $data['error'] == 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
			'message' 	=> $data['message']
		));
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'index', 'club');	
	}
	
	
	
	public function getClubInfoAction(){
		$this->_helper->getHelper('layout')->disableLayout();		
		$this->getHelper('viewRenderer')->setNoRender();
		if (!$this->_request->isPost()) {
			die;
		}
			
		$club_id = $this->_request->getParam('club_id', false);
		if($club_id){
			if(!$this->_serviceClub){ $this->_serviceClub = $this->getService('Club'); }
			$club = $this->_serviceClub->getById($club_id);
			$this->view->club = array(
				 'name' 		=> $club->name,
				 'faculty' 		=> $club->faculty,
				 'description' 	=> $club->description,
				 'organizer' 	=> $club->organizer,
				 'manager' 		=> $club->manager,
			);
		}
		
		echo $this->view->render('index/club-info.tpl');		
		die;		
	}
	
	
}