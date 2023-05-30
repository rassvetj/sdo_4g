<?php
class Workload_SheetController extends HM_Controller_Action
{
    public function indexAction()
    {   
		$this->view->setHeader(_('Сессии тьюторов'));
		
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}	
	
		$userId = $this->getService('User')->getCurrentUserId();
		$users = $this->getService('WorkloadSheet')->getOrgstructurePersons($userId);
		$subjects = $this->getService('WorkloadSheet')->getSubjectList($users);
		
		$this->view->msg = _('Внимание! Приветственные сообщения студентам НЕ отсылаются.'); //--до момента реализации полного функционала модуля	
		$this->view->subjects = $subjects;	
		//$curMark = '1';
		//$template = HM_Workload_WorkloadModel::getMessageTemplate($curMark);
		//$msg = HM_Workload_WorkloadModel::getMessageText($template);
		
    }
	
	
	/**
	 * при нажатии на кнопку "Закрыть сессию"
	*/
	public function closeAction()
    {   
		$this->_helper->viewRenderer->setNoRender(true);
		if ($this->_request->isPost() || $this->_request->isGet()) {
			
			$subjectId = $this->_request->getParam('subject_id',0);
			if(!$subjectId){
				$this->_redirector->gotoSimple('index', 'sheet');
			}
			
			
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}		
		
			$userId = $this->getService('User')->getCurrentUserId();
			
			//--проверяем, есть ли право у пользователя изменять эту сессию.
			$users = $this->getService('WorkloadSheet')->getOrgstructurePersons($userId);
			$subjects = $this->getService('WorkloadSheet')->getSubjectList($users);
			$isCanUpdate = false;
			foreach($subjects as $s){
				if($s['subid'] == $subjectId){
					$isCanUpdate = true;
					break;
				}				
			}			
			if(!$isCanUpdate){
				$this->_flashMessenger->addMessage(_('У вас нет права на редактирование курса!'));
				$this->_redirector->gotoSimple('index', 'sheet');
			}
			
			$isUpdate = $this->getService('Subject')->update(
				array(
					'subid' => $subjectId,
					'isSheetPassed' => 1,
				)				
			);
			
			if(!$isUpdate){
				$this->_flashMessenger->addMessage(_('Не удалось закрыть курс!'));	
			} else {				
				$this->getService('WorkloadSheet')->setSheetViolation($subjectId); //--фиксируем просрочку тьюторов
				
				//--Пока не отправляем мотивированное заключение
				//$isSend = $this->getService('WorkloadSheet')->sendMotivationMessages($subjectId);//--отылаем мотивированное заключение студентам (уведомление внутри кабинета СДО).
				
				if(!$isSend){ //-на данный момент всегда выполняется условие.					
					$this->_flashMessenger->addMessage(_('Ошибка отправки мотивированного сообщения!'));
				}							
							
				$this->_flashMessenger->addMessage(_('Курс успешно закрыт!'));	
			}
			$this->_redirector->gotoSimple('index', 'sheet');				
		}		
		$this->_redirect('/');			
    }
    
   

}