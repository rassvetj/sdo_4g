<?php
class HM_Form_Survey extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
		
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'survey');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('survey');

		
		
		
		$current_specialty	= false;
		$current_course		= false;
		$current_study_form	= false;
		
		$current_user 					= $this->getService('User')->getCurrentUser();
		$groups 	 					= $this->getService('StudyGroupUsers')->getUsersGroups($current_user->MID);
		$first_group 					= reset($groups);
		
		#Направление подготовки/Специальность, 
		$current_specialty	= $first_group['speciality'];
		
		$recordbook_info = $this->getService('RecordCard')->getRecordbookInfo($current_user->mid_external);
		if(!empty($recordbook_info)){			
			#Курс
			$current_course = ceil(intval($recordbook_info->semester) / 2);
			#$current_course= 1;
			
			$study_form_list = array(
									'Очная (дневная)' 			=> 'О',
									'Очно-заочная (вечерняя)' 	=> 'В',
									'Заочная' 					=> 'О-В',
								);
			#форма обучения
			$current_study_form  = $study_form_list[$recordbook_info->study_form];
		}
		
		
		
		
        
        $this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'languages',
                    'controller' 		=> 'survey',
                    'action' 			=> 'save',                    
                )
            )
        );
		
		$first_item = array('' => _('-- выберите --'));
		
		$surveyService 			= $this->getService('LanguagesSurvey');
		$list_linked			= $surveyService->toArray($surveyService->getAll());
		$list_faculty 			= $first_item + $surveyService->getListByName('faculty');
		$list_chair 			= $first_item + $surveyService->getListByName('chair');
		$list_semester 			= $first_item + $surveyService->getListByName('semester');
		$list_discipline 		= $first_item + $surveyService->getListByName('discipline');
		$list_code				= $first_item + $surveyService->getListByName('code');
		$list_specialty 		= $first_item + $surveyService->getListByName('specialty');
		$list_specialization 	= $first_item + $surveyService->getListByName('specialization');
		$list_course 			= $first_item + $surveyService->getListByName('course');
		$list_study_form		= $first_item + $surveyService->getListByName('study_form');
		$list_control			= $first_item + $surveyService->getListByName('control');
		$list_teacher			= $first_item + $surveyService->getListByName('teacher');
		
		
		$this->addElement('hidden', 'item_id', array('value' => 0));
		
		$this->addElement('hidden', 'linked', array('value' => json_encode($list_linked)));
		
		
		$this->addElement('select','faculty', array(  
			'label' 		=> _('Факультет'),			
			'multiOptions'	=> $list_faculty,
			'data-link'		=> 'chair',
		));
		
		
		$this->addElement('select','chair', array(  
			'label' 		=> _('Кафедра'),			
			'multiOptions'	=> $list_chair,	
			#'class' 		=> 'hidden',
			'data-link'		=> 'semester',	
		));
		
		$this->addElement('select','semester', array(  
			'label' 		=> _('Семестр'),			
			'multiOptions'	=> $list_semester,
			#'class' 		=> 'hidden',
			'data-link'		=> 'discipline',
		));
		
		$this->addElement('select','discipline', array(  
			'label' 		=> _('Название дисциплины'),			
			'multiOptions'	=> $list_discipline,
			#'class' 		=> 'hidden',
			'data-link'		=> 'code',
		));
		
		$this->addElement('select','code', array(  
			'label' 		=> _('Шифр'),
			'multiOptions'	=> $list_code,
			#'class' 		=> 'hidden',
			'data-link'		=> 'specialty',
		));
		
		$this->addElement('select','specialty', array(  
			'label' 		=> _('Направление подготовки / Специальность'),
			'multiOptions'	=> $list_specialty,
			#'class' 		=> 'hidden',
			'data-link'		=> 'specialization',
			'value'			=> $current_specialty,
		));
		
		
		$this->addElement('select','specialization', array(  
			'label' 		=> _('Направленность / Специализация'),
			'multiOptions'	=> $list_specialization,
			#'class' 		=> 'hidden',
		));
		
		$this->addElement('select','course', array(
			'label' 		=> _('Курс'),
			'multiOptions'	=> $list_course,
			#'class' 		=> 'hidden',
			'value' 		=> $current_course,
		));
		
		$this->addElement('select','study_form', array(
			'label' 		=> _('Форма обучения'),
			'multiOptions'	=> $list_study_form,
			#'class' 		=> 'hidden',
			'value' 		=> $current_study_form,
		));
		
		$this->addElement('select','control', array(
			'label' 		=> _('Рубежный контроль'),
			'multiOptions'	=> $list_control,
			#'class' 		=> 'hidden',
		));
		
		$this->addElement('select','teacher', array(
			'label' 		=> _('ФИО ответственного преподавателя'),
			'multiOptions'	=> $list_teacher,
			#'class' 		=> 'hidden',
		));
		
		#$this->addElement('Submit', 'button', array(
		#	'Label' 	=> _('Отправить'),
		#	'id'		=> 'survey',
		#	#'class' 	=> 'hidden',
		#));
		
		
		$this->addDisplayGroup(array('faculty'), '1', array('legend' => _('')));		
		$this->addDisplayGroup(array('chair'), '2', array('legend' => _('')) );
		$this->addDisplayGroup(array('semester'), '3', array('legend' => _('')) );
		$this->addDisplayGroup(array('discipline'), '4', array('legend' => _('')) );
		$this->addDisplayGroup(array('code'), '5', array('legend' => _('')) );
		$this->addDisplayGroup(array('specialty'), '6', array('legend' => _('')) );
		$this->addDisplayGroup(array('specialization'), '7', array('legend' => _('')) );
		$this->addDisplayGroup(array('course'), '7', array('legend' => _('')) );
		$this->addDisplayGroup(array('study_form'), '8', array('legend' => _('')) );
		$this->addDisplayGroup(array('control'), '9', array('legend' => _('')) );
		$this->addDisplayGroup(array('teacher'), '10', array('legend' => _('')) );
		
		
		

        parent::init(); // required!
	}

}
