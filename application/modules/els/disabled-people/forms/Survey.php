<?php
class HM_Form_Survey extends HM_Form
{
    public function init()
	{
		
		$this->setAction($this->getView()->url(array('module' => 'disabled-people', 'controller' => 'ajax', 'action' => 'save-survey')));
		$this->setMethod(Zend_Form::METHOD_POST);
		
		$request		= $this->getRequest();
		
		
		$fields_type 	= HM_Survey_SurveyModel::TYPE_FIELDS_DISABLED_PEOPLE;
		$type_id		= HM_Survey_SurveyModel::TYPE_DISABLED_PEOPLE;
		
		
		$this->setName('disabled_people');
		$this->addElement('hidden', 'type_id',		array('Value' => $type_id));
		$this->addElement('hidden', 'fields_type',	array('Value' => $fields_type));
		
		
		$user = $this->getService('User')->getCurrentUser();
		$bd = DateTime::createFromFormat('Y-m-d 00:00:00.000', $user->BirthDate);
		$user->BirthDate = ($bd) ? ($bd->format('d.m.Y')) : ('');
		
		try {

			$fields		= $this->getService('SurveyQuestions')->fetchAll(array('type = ?' => HM_Survey_SurveyModel::TYPE_FIELDS_DISABLED_PEOPLE));
			$resAnswers = $this->getService('SurveyAnswers')->fetchAll(array('type = ?' => HM_Survey_SurveyModel::TYPE_FIELDS_DISABLED_PEOPLE));
			$answers 	= array();
			
			if(!empty($resAnswers)){
				foreach($resAnswers as $a){
					$answers[$a->question_id][$a->answer_id] = $a->name;
				}			
			}
			
			if(!empty($fields)) :
				foreach($fields as $f){			
					$params = array(
						'Label' 	=> _($f->name), 
						'Required' 	=> false,
						'Value' 	=> $user->{$f->code},
						'Filters' 	=> array('StripTags'),
					);
					if($f->field_type == 'radio'){
						$params['multiOptions'] = (isset($answers[$f->question_id])) ? ($answers[$f->question_id]) : (array());
						$params['Validators'] 	= array('Int');
						$params['Filters']		= array('Int');
						$params['separator']	= '<br />';
					} elseif($f->field_type == 'DatePicker'){
						$params['Validators'] = array(
							array('StringLength', false, array('min' => 10, 'max' => 50))
						);												
						$params['JQueryParams'] = array(
							'showOn' 			=> 'button',
							'buttonImage' 		=> "/images/icons/calendar.png",
							'buttonImageOnly' 	=> 'true'
						);						
					}
					
					
					if($f->code == 'EMail'){
						$params['Validators'] = array(array('EmailAddress'));						
					}
					
					$this->addElement($f->field_type, $f->code, $params);
					
					
					if(in_array($f->code, array('dp7', 'dp10'))){						
						$this->addElement('text', $f->code.'_other', array(
							'Label' 	=> _(''),
							'Required' 	=> false,
							'Value' 	=> '',
							'Filters' 	=> array('StripTags'),			
							'class'		=> 'isHidden',
						));
					}
				}
			endif;
				
		} catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
			
		
		$this->addDisplayGroup(array(
            'dp9', 'dp10', 'dp10_other', 'dp1', 
        ),
            'blockBase',
            array('legend' => _(''))
        );
		
		$this->addDisplayGroup(array(
            'dp2', 'dp3', 'dp4', 'dp5',
        ),
            'blockEffective',
            array('legend' => _('Для эффективного обучения отметьте свои функциональные особенности:'))
        );
		
		$this->addDisplayGroup(array(
            'dp6', 'dp7', 'dp7_other', 'dp8',
        ),
            'blockBase2',
            array('legend' => _(''))
        );
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Сохранить'),
        ));
		
		parent::init();
	}
}