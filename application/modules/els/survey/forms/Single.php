<?php
class HM_Form_Single extends HM_Form
{
    public function init()
	{
		
		$request		= $this->getRequest();
		$type_id		= (int)$request->getParam('type', false);
		$qestion_id		= (int)$request->getParam('q', false);
		$qestion		= $this->getService('SurveyQuestions')->getById($qestion_id);
		
		if($qestion->type != $type_id){ # если вручную указали не тот id не от того типа.
			/*
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Неверные параметры'))
			);	
			*/			
			$_redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$_redirector->gotoUrl('/'); 
		}
		
		$user			= $this->getService('User')->getCurrentUser();	
		$key 		 	= $user->MID.'~'.$type_id; 
		
		
		$action = $this->getView()->url(array('module' => 'survey', 'controller' => 'single', 'action' => 'save'));
		
		$this->setAction($action);
		$this->setMethod(Zend_Form::METHOD_POST);
				
		$oldData 		= $this->getService('SurveyQuestions')->getSessionData(); # результат предыдущих ответов
		$oldDataCurrent = $oldData[$key]['answers'][$qestion_id]; # Данные по текущему вопросу
		$btnCaption		= ($oldData[$key]['isLast'] == 1) ? 'Завершить' : 'Следующий';
		# А как чек боксы обрабатывать?
		# это радиобатон
		$value 			= '';
		$required		= false;
		
		if(isset($oldDataCurrent['answer_id'])){
			$value = $oldDataCurrent['answer_id'];
		} elseif(isset($oldDataCurrent['answer_name'])){
			$value = $oldDataCurrent['answer_name'];
		}
		
		
		$resAnswers = $this->getService('SurveyAnswers')->fetchAll(array('type = ?' => $type_id));
		$answers 	= array();
		
		
		
		
		if(!empty($resAnswers)){
			foreach($resAnswers as $a){
				$answers[$a->question_id][$a->answer_id] = $a->name;
			}			
		}
		
		$params = array(
			'Label' 	=> $qestion->name, 
			'Required' 	=> $required,
			'Value' 	=> $value,
			'Filters' 	=> array('StripTags'),
		);
		
		
		if($qestion->field_type == 'radio'){
			$params['multiOptions'] = (isset($answers[$qestion->question_id])) ? ($answers[$qestion->question_id]) : (array());
			$params['Validators'] 	= array('Int');
			$params['Filters']		= array('Int');
			$params['separator']	= '&nbsp;<br>';
		} elseif($qestion->field_type == 'DatePicker'){
			$params['Validators'] = array(
				array('StringLength', false, array('min' => 10, 'max' => 50))
			);												
			$params['JQueryParams'] = array(
				'showOn' 			=> 'button',
				'buttonImage' 		=> "/images/icons/calendar.png",
				'buttonImageOnly' 	=> 'true'
			);										
		}
		
		$this->addElement($qestion->field_type, $qestion->code, $params);
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => $btnCaption,
			'disabled' => 'disabled',
        ));
		
		parent::init();
	}
}