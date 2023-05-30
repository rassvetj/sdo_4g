<?php
class Languages_SurveyController extends HM_Controller_Action
{
    public function indexAction()
	{		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		$this->view->setHeader(_('Выбор курсов иностранного языка'));
		$serviceSurvey 					= $this->getService('LanguagesSurvey');		
		$serviceResult 					= $this->getService('LanguagesResult');		
		$items			 				= $serviceSurvey->toArray($serviceSurvey->getAll());
		
		$current_user 					= $this->getService('User')->getCurrentUser();
		
		#$this->view->selected_items 	= $serviceResult->fetchAll($serviceResult->quoteInto('MID = ?', $this->getService('User')->getCurrentUserId()))->getList('languages_id');
		$selected_items 	= $serviceResult->fetchAll($serviceResult->quoteInto('MID = ?', $current_user->MID));
		
		$this->view->selected_items = array();
		foreach($selected_items as $i){
			$this->view->selected_items[$i->languages_id]['date_created_timestamp'] = strtotime($i->date_created);
		}
		
		
		$this->view->timestamp_limit 			= time() - HM_Languages_Survey_SurveyModel::REMOVE_TIME_LIMIT;		
		$this->view->gridAjaxRequest 	= $this->isAjaxRequest();
				
		$this->view->form = new HM_Form_Survey();
		
		$current_specialty 	= $this->view->form->getElement('specialty')->getValue();
		$current_course 	= $this->view->form->getElement('course')->getValue();
		$current_study_form = $this->view->form->getElement('study_form')->getValue();
		
		
		$this->view->has_selected_is_free 	  = false;
		$this->view->has_selected_is_not_free = false;
		
		$this->view->selected_items_ids = array_keys($this->view->selected_items);
		
		$this->view->items = array();
		foreach($items as $key => $i){
			$i['is_free'] = 0;
			
			if(
				$current_specialty 	!= $i['specialty']
				||
				$current_course 	!= $i['course']
				||
				$current_study_form != $i['study_form']
			) { 
				# курс для вольных слушателей
				$i['is_free'] = 1;
				
				# среди выбранных есть курсы для вольных слушателей
				if(in_array($i['languages_id'], $this->view->selected_items_ids)){
					$this->view->has_selected_is_free = true;
				}
			} else {				
				# среди выбранных есть курсы для НЕ вольных слушателей
				if(in_array($i['languages_id'], $this->view->selected_items_ids)){
					$this->view->has_selected_is_not_free = true;
				}
			}
			
			# нужно для сортировки: сначала все обычные курсы, затем для вольных слушателей.
			$this->view->items[$i['is_free'].'~'.$key] = $i;			
		}
		ksort($this->view->items);		
	}
	
	
	public function saveAction()
	{
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$item_id = (int)$this->_request->getParam('item_id', false);
		$user_id = $this->getService('User')->getCurrentUserId();
		
		$serviceResult	= $this->getService('LanguagesResult');
		$selected_items	= $serviceResult->fetchAll($serviceResult->quoteInto('MID = ?', $user_id))->getList('languages_id');
		
		if(in_array($item_id, $selected_items)){
			$return = array('code' => 0, 'message' 	=> _('Вы уже записаны на этот курс.'));
		} else {		
			$isAdded = $this->getService('LanguagesResult')->add($item_id, $user_id);		
			$return = array('code' => 0, 'message' 	=> _('Не удалось отправить заявку.'));	
		
			if($isAdded){
				$return = array('code' => 1, 'message' => _('Спасибо! Ваша заявка отправлена.'));
			}
		}
		
		echo json_encode(array(
			'error' => $return['code'] != 1 ? 1 : 0,
			'data'  => $this->view->notifications(array(array(
							'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
							'message' 	=> $return['message']
						)), array('html' => true)),
		));
		
		die;		
	}
	
	
	public function deleteAction()
	{
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$item_id = (int)$this->_request->getParam('item_id', false);
		$user_id = $this->getService('User')->getCurrentUserId();
		
		$serviceResult	= $this->getService('LanguagesResult');
		$selected_items	= $serviceResult->fetchAll($serviceResult->quoteInto('MID = ?', $user_id))->getList('languages_id');
		
		$isRemove = $this->getService('LanguagesResult')->remove($item_id, $user_id);	
				
		$return = array('code' => 0, 'message' 	=> _('Не удалось отменить заявку.'));	
		
		if($isRemove){
			$return = array('code' => 1, 'message' => _('Ваша заявка отменена.'));
		}
		
		
		echo json_encode(array(
			'error' => $return['code'] != 1 ? 1 : 0,
			'data'  => $this->view->notifications(array(array(
							'type' 		=> $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
							'message' 	=> $return['message']
						)), array('html' => true)),
		));
		
		die;		
	}
	
	
}