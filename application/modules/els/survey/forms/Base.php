<?php
class HM_Form_Base extends HM_Form
{
    public function init()
	{
		$this->setAction($this->getView()->url(array('module' => 'survey', 'controller' => 'ajax', 'action' => 'save')));
		$this->setMethod(Zend_Form::METHOD_POST);
		
		$request		= $this->getRequest();
		$form_type		= $request->getParam('type', false);
		
		switch ($form_type) {			
			case 'vpo':				
				$fields_type 	= HM_Survey_SurveyModel::TYPE_FIELDS_PO;
				$type_id		= HM_Survey_SurveyModel::TYPE_VPO;
				break;			
		}
		
		$this->setName($form_type);
		$this->addElement('hidden', 'type_id', array('Value' => $type_id));
		$this->addElement('hidden', 'fields_type', array('Value' => $fields_type));
		
		
		$user		  = $this->getService('User')->getCurrentUser();				
		$groups 	  = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		$groupCurrent = (!empty($groups)) ? current($groups) : array();
		
		$bd			= DateTime::createFromFormat('Y-m-d 00:00:00.000', $user->BirthDate);
		$birthDate	= '';
		if($bd){
			if($bd->getTimestamp() > 0){
				$birthDate = $bd->format('Y');
			} 
		}		
		
		try {

			$fields		= $this->getService('SurveyQuestions')->fetchAll(array('type = ?' => 1));
			$resAnswers = $this->getService('SurveyAnswers')->fetchAll(array('type = ?' => 1));
			$answers 	= array();
			
			if(!empty($resAnswers)){
				foreach($resAnswers as $a){
					$answers[$a->question_id][$a->answer_id] = $a->name;
				}			
			}
			
			if(!empty($fields)) :
				foreach($fields as $f){					
					$value 		= '';
					$required	= true;
					if(!in_array($f->code, array('Phone', 'EMail'))){
						$value = $user->{$f->code};
					}
					
					if(in_array($f->code, array('vk', 'instagram', 'facebook', 'twitter', 'address_residence', 'actual_work_place_address', 'actual_work_place_phone'))){
						$required	= false;
					}
					
					$params = array(
						'Label' 	=> $f->name, 
						'Required' 	=> $required,
						'Value' 	=> $value,
						'Filters' 	=> array('StripTags'),
					);
					
					if($f->field_type == 'radio'){
						$params['multiOptions'] = (isset($answers[$f->question_id])) ? ($answers[$f->question_id]) : (array());
						$params['Validators'] 	= array('Int');
						$params['Filters']		= array('Int');
						$params['separator']	= '&nbsp;';
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
					
					
					if($f->code == 'EMail')		{ $params['Validators'] = array(array('EmailAddress')); }
					if($f->code == 'BirthDate' && $f->field_type == 'text')	{
						$params['Value'] 		= $birthDate;
						$params['Validators'] = array(array('Between', false, array(1910, date('Y'))));					
					}
					
					if($f->code == 'course')	{							
						$params['Value'] = (!empty($groupCurrent['course'])) ? $groupCurrent['course'] : _('нет');								
					}
					
					if($f->code == 'year_graduation')	{	
						$params['Value'] = (!empty($groupCurrent['year_graduated'])) ? $groupCurrent['year_graduated'] : _('нет');								
					}
					
					if($f->code == 'faculty')	{						
						$params['Value'] = (!empty($groupCurrent['faculty'])) ? $groupCurrent['faculty'] : _('нет');
					}
					
					if($f->code == 'specialty')	{								
						$params['Value'] = (!empty($groupCurrent['speciality'])) ? $groupCurrent['speciality'] : _('нет');						
					}
					
					if(
						in_array($f->code, array('LastName', 'FirstName', 'Patronymic', 'course', 'year_graduation', 'faculty', 'specialty'))
						||
						($f->code == 'BirthDate' && !empty($birthDate))
					){
						$params['readonly'] = 'readonly';
						$params['class'] 	= 'disabled';						
						$params['onClick'] 	= "$('#ui-datepicker-div').hide();";						 
						unset($params['JQueryParams']);						
					}
					
					$this->addElement($f->field_type, $f->code, $params);
					
					if(in_array($f->code, array('see_in_club', 'criteria_quality_training'))){
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
            'LastName', 'FirstName', 'Patronymic', 'BirthDate',  'address_residence', 'Phone', 'EMail',			
        ),
            'blockBase',
            array('legend' => _(''))
        );
		
		$this->addDisplayGroup(array(
            'vk', 'instagram', 'facebook', 'twitter',			
        ),
            'blockSocial',
            array('legend' => _('Аккаунты в социальных сетях'))
        );
		
		$this->addDisplayGroup(array(
            'course', 'year_graduation','faculty', 'specialty',
			'education_level', 'plan_after_graduation', 'education_after_graduation',
			'is_working', 'work_on_specialty',
			'target_set', 'is_target_employment',		
        ),
            'blockEducation',
            array('legend' => _(''))
        );
		
		
		
		
		
		$this->addDisplayGroup(array(
            'actual_work_place_company','actual_work_place_address', 'actual_work_place_phone', 'actual_work_place_position', 
        ),
            'blockActualWork',
            array('legend' => _('Место работы фактическое'))
        );
		
		$this->addDisplayGroup(array(
            'planned_work_place_company', 'planned_work_place_address', 'planned_work_place_phone', 'planned_work_place_position', 
        ),
            'blockPlannedWork',
            array('legend' => _('Место работы планируемое'))
        );
		
		$this->addDisplayGroup(array(
			'is_invalid', 'invalid_degree', 
			'is_ready_join_club', 'see_in_club', 'see_in_club_other',
			'criteria_quality_training', 'criteria_quality_training_other',
        ),
            'blockOther',
            array('legend' => _(''))
        );
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Сохранить'),
        ));
		
		parent::init();
	}
}