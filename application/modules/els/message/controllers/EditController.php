<?php
class Message_EditController extends HM_Controller_Action_Activity
{
    public function deleteAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $messageId = $this->_getParam('message_id', false);
        
        
		$url = $request->getHeader('referer');
		
		
		if (
			!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR) &&
			!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
		){
			$this->_flashMessenger->addMessage(array('message' => _('У вас нет прав на удаление'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));			
			$this->_redirect($url);
		}
		
		
		if(!$messageId){			
			$this->_flashMessenger->addMessage(array('message' => _('Не удалось удалить сообщение'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));			
			$this->_redirect($url);
		}
		/*
		$isAllow = false; 
		if ($subjectId && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR) ) {
			$isAllow = $this->getService('Subject')->isTutor($subjectId, $this->getService('User')->getCurrentUserId());	
		} elseif($subjectId && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)){
			$isAllow = $this->getService('Subject')->isTeacher($subjectId, $this->getService('User')->getCurrentUserId());    
		} 
		*/
		/*
		if(!$isAllow && $subjectId){
			$this->_flashMessenger->addMessage(array('message' => _('Вы не назначены на эту сессию'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect($url);
		}
		*/
		
		$message = $this->getOne($this->getService('Message')->find($messageId));
		if(!$message){
			$this->_flashMessenger->addMessage(array('message' => _('Сообщение не найдено.'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect($url);
		}
		
		if(	$message->from != $this->getService('User')->getCurrentUserId()	&&	$message->to != $this->getService('User')->getCurrentUserId() ){ //--удаляем только если вы автор или адресат сообщения
			$this->_flashMessenger->addMessage(array('message' => _('Не удалось удалить сообщение. Оно не Ваше.'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect($url);
		}
		
		if($this->getService('Message')->deleteBy($this->quoteInto('message_id = ?', $messageId))){						
			$this->_flashMessenger->addMessage(_('Сообщение удалено'));
		} else {
			$this->_flashMessenger->addMessage(array('message' => _('Не удалось удалить сообщение'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
		}		
		$this->_redirect($url);
		exit();		
    }
}