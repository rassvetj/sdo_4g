<?php
class Hostel_EditController extends HM_Controller_Action
{
    public function createAction(){        		
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$type_id = $this->_request->getParam('type_id', false);		
		$addres_id = $this->_request->getParam('addres_id', false);
		$room_id = $this->_request->getParam('room_id', false);
		
		$allow_types = array_keys(HM_Hostel_Claims_ClaimsModel::getTypes());		
		if(!in_array($type_id, $allow_types)){						
			if ($this->isAjaxRequest()){
				echo Zend_Json::encode(array('type' => 'error', 'message' => _('Некорректные данные')));				
			} else {				
				$this->_flashMessenger->addMessage(array(
                    'message' => _('Некорректные данные'),
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                ));				
			}						
		}  else {						
			if(HM_Hostel_Claims_ClaimsModel::TYPE_SETTLEMENT == $type_id){
				$addres_id = '';
				$room_id = '';
			}
			
			$user = $this->getService('User')->getCurrentUser();
			
			if($this->getService('HostelClaims')->createClaim($user->mid_external, $type_id, $addres_id, $room_id)){
				if ($this->isAjaxRequest()){
					echo Zend_Json::encode(array('type' => 'success', 'message' => _('Ваша заявка отправлена на рассмотрение')));									
				} else {
					$this->_flashMessenger->addMessage(array(
						'message' => _('Ваша заявка отправлена на рассмотрение'),
						'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
					));
				}
			} else {
				if ($this->isAjaxRequest()){
					echo Zend_Json::encode(array('type' => 'error', 'message' => _('Не удалось отправить заявку. Повторите попытку позже.')));												
				} else {
					$this->_flashMessenger->addMessage(array(
						'message' => _('Не удалось отправить заявку. Повторите попытку позже.'),
						'type' => HM_Notification_NotificationModel::TYPE_ERROR,
					));
				}
			}
		}
		if(!$this->isAjaxRequest()){
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}		
    }
	
	/**
	 * отмена заявки студентом
	 * @return boolean	 
	*/
	public function cancelAction(){
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$claim_id = (int)$this->_request->getParam('claim_id', false);
		
		if($claim_id < 1){
			$this->_flashMessenger->addMessage(array(
				'message' => _('Не удалось отменить заявку'),
				'type' => HM_Notification_NotificationModel::TYPE_ERROR,
			));
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}
		
		$order = $this->getService('HostelClaims')->getOne(
			$this->getService('HostelClaims')->find($claim_id)
		);
		
		if(!$order){
			$this->_flashMessenger->addMessage(array(
				'message' => _('Заявка не найдена'),
				'type' => HM_Notification_NotificationModel::TYPE_ERROR,				
			));
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}
		
		if($order->status != HM_Hostel_Claims_ClaimsModel::STATUS_NEW){
			$this->_flashMessenger->addMessage(array(
				'message' => _('Эту заявку уже нельзя отменить'),
				'type' => HM_Notification_NotificationModel::TYPE_ERROR,				
			));
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}
		
		$data = array(
			'claim_id' => $claim_id,
			'status' => HM_Hostel_Claims_ClaimsModel::STATUS_REJECT,
			'date_reject' => new Zend_Db_Expr('NOW()'),
			'reason_reject' => _('По требованию студента'),
		);
		
		try {
			$this->getService('HostelClaims')->update($data);
			
			$this->_flashMessenger->addMessage(array(
				'message' => _('Заявка отменена'),
				'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,				
			));			
		} catch (Exception $e) {
			$this->_flashMessenger->addMessage(array(
				'message' => _('Не удалось отменить заявку'),
				'type' => HM_Notification_NotificationModel::TYPE_ERROR,
			));			
		}				
		$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
	}
}