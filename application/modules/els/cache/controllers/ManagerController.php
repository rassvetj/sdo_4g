<?php
class Cache_ManagerController extends HM_Controller_Action
{
	public function indexAction()
	{
		#if($_GET['dev']==1){
			#$subjectId = 76677;
			#$studentId = 75627;
			#$this->getService('Subject')->updateLandmark($subjectId);
			#$score = $this->getService('Subject')->setScore($subjectId, $studentId);			
			#echo '<pre>';
			#var_dump($score);
			#echo '</pre>';
			#die;
		#}
		
		
		$this->view->setHeader(_('Управление кэшем'));
		
		$items = array();
		$items[] = array(
			'name' => _('Назначения тьюторов на сессии'),
			'url'  => $this->view->url(array('module' => 'cache', 'controller' => 'manager', 'action' => 'clear', 'type' => 'tutor-assigns')),
		);
		
		$this->view->items = $items;
	}
	
	public function clearAction()
	{
		$type = $this->_getParam('type', false);
		$message      = _('Не определен тип операции');
		$message_type =  HM_Notification_NotificationModel::TYPE_ERROR;
		
		if($type == 'tutor-assigns'){
			Zend_Registry::get('cache')->remove(HM_Subject_SubjectService::CACHE_NAME);	
			$message      = _('Кэш "Назначения тьюторов на сессии" очищен');
			$message_type =  HM_Notification_NotificationModel::TYPE_SUCCESS;
		}
		
		$this->_flashMessenger->addMessage(array(
			'message' => $message, 
			'type'    => $message_type,
		));
		$this->_redirector->gotoSimple('index', 'manager', 'cache', array());
		die;
	}
	
	
	
	
	
}