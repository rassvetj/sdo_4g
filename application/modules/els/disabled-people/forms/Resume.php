<?php
class HM_Form_Resume  extends HM_Form
{
    public function init()
	{
		
		$user = $this->getService('User')->getCurrentUser();
		$serviceResume = $this->getService('DisabledPeopleResume');
		$resume = $serviceResume->getOne($serviceResume->fetchAll($serviceResume->quoteInto('mid_external = ?', $user->mid_external)));
		
		
		$this->setAction($this->getView()->url(array('module' => 'disabled-people', 'controller' => 'resume', 'action' => 'save')));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('resume');
		
		$beginElement = array('' => _('-- Выберите --'));
		
		
		#Основная информация
		$this->addElement('select','type_id', array( 
			'label' 		=> _('Тип'),
			'value' 		=> $resume->type_id,
			'multiOptions' 	=> $beginElement + HM_DisabledPeople_Resume_ResumeModel::getTypes(),
		));
		
		
		$this->addElement('text', 'job_vacancy', array(
            'Label'		=> _('Название вакансии'),            
			'Value'		=> $resume->job_vacancy,			
        ));
		
		
		$this->addElement('text', 'income_level', array(
            'Label'		=> _('Уровень дохода (руб.)'),            
			'Value'		=> $resume->income_level,
			'filters' 	=> array('int'),			
        ));
		
		
		$this->addElement('text', 'phone', array(
            'Label'		=> _('Контактный телефон'),            
			'Value'		=> $resume->phone,			
        ));
		
		$this->addElement('text', 'email', array(
            'Label'		=> _('E-mail'),            
			'Value'		=> $resume->email,		
        )); 	
	

		#Абилимпикс
		$this->addElement('text', 'competence', array(
            'Label'		=> _('Название компетенции'),            
			'Value'		=> $resume->competence,			
        ));
		
		$this->addElement('textarea', 'result_competition', array(
            'Label'		=> _('Результат регионального/национального конкурса'),            
			'Value'		=> $resume->result_competition,		
        ));

		
		#Образование
		$this->addElement('text', 'institution', array(
            'Label'		=> _('Учебное заведение'),            
			'Value'		=> $resume->institution,			
        ));
		
		
		$dt = DateTime::createFromFormat('Y-m-d', $resume->graduation_date);		
		$this->addElement('DatePicker', 'graduation_date', array(
            'Label' => _('Дата окончания'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
			'Value'		=> ($dt)?($dt->format('d.m.Y')):(''),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );
		
		$this->addElement('text', 'faculty', array(
            'Label'		=> _('Факультет'),            
			'Value'		=> $resume->faculty,			
        ));
		
		$this->addElement('text', 'specialty', array(
            'Label'		=> _('Специальность'),            
			'Value'		=> $resume->specialty,			
        ));

		$this->addElement('text', 'form_study', array(
            'Label'		=> _('Форма обучения'),            
			'Value'		=> $resume->form_study,			
        ));
		
                    	 
		#Опыт работы
		$dt = DateTime::createFromFormat('Y-m-d', $resume->work_period_begin);		
		$this->addElement('DatePicker', 'work_period_begin', array(
            'Label' => _('Период работы с'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
			'Value'		=> ($dt)?($dt->format('d.m.Y')):(''),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );
		
		$dt = DateTime::createFromFormat('Y-m-d', $resume->work_period_end);		
		$this->addElement('DatePicker', 'work_period_end', array(
            'Label' => _('Период работы по'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
			'Value'		=> ($dt)?($dt->format('d.m.Y')):(''),
            'Filters' 	=> array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );
		
		
		
		$this->addElement('text', 'position', array(
            'Label'		=> _('Должность'),            
			'Value'		=> $resume->position,			
        ));
		
		$this->addElement('text', 'organization', array(
            'Label'		=> _('Название организации'),            
			'Value'		=> $resume->organization,			
        ));
		
		$this->addElement('textarea', 'job_function', array(
            'Label'		=> _('Должностные обязанности'),            
			'Value'		=> $resume->job_function,			
        ));

		$this->addElement('textarea', 'achievements', array(
            'Label'		=> _('Достижения'),            
			'Value'		=> $resume->achievements,			
        ));


		#Личная информация
		$this->addElement('text', 'city', array(
            'Label'		=> _('Город проживания'),            
			'Value'		=> $resume->city,			
        ));
		
		$this->addElement('text', 'metro', array(
            'Label'		=> _('Ближайшее метро'),            
			'Value'		=> $resume->metro,			
        ));
		
		
		$dt = DateTime::createFromFormat('Y-m-d', $resume->date_birth);		
		$this->addElement('DatePicker', 'date_birth', array(
            'Label' => _('Дата рождения'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
			'Value'		=> ($dt)?($dt->format('d.m.Y')):(''),
            'Filters' 	=> array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );


		#Иностранные языки и компьютерные навыки
		$this->addElement('text', 'english', array(
            'Label'		=> _('Английский язык'),            
			'Value'		=> $resume->english,			
        ));
		
		$this->addElement('textarea', 'computer_skills', array(
            'Label'		=> _('Компьютерные навыки и знания'),            
			'Value'		=> $resume->computer_skills,			
        ));

		
		#Дополнительная информация
		$this->addElement('textarea', 'about', array(
            'Label'		=> _('О себе'),            
			'Value'		=> $resume->about,			
        ));		
		$this->addElement('textarea', 'recommendations', array(
            'Label'		=> _('Рекомендации'),            
			'Value'		=> $resume->recommendations,			
        ));						
	
		
		
		$this->addDisplayGroup(	
			array('type_id', 'job_vacancy', 'income_level', 'phone', 'email'),
			'base',
			array('legend' => _('Основная информация'))
		);
		
		$this->addDisplayGroup(	
			array('competence','result_competition'),
			'abilimpiks',
			array('legend' => _('Абилимпикс'))
		);
		
		$this->addDisplayGroup(	
			array('institution', 'graduation_date', 'faculty', 'specialty', 'form_study'),
			'education',
			array('legend' => _('Образование'))
		);
		
		$this->addDisplayGroup(	
			array('work_period_begin', 'work_period_end', 'position', 'organization', 'job_function', 'achievements'),
			'experience',
			array('legend' => _('Опыт работы'))
		);
		
		$this->addDisplayGroup(	
			array('city', 'metro', 'date_birth'),
			'personal_information',
			array('legend' => _('Личная информация'))
		);
		
		$this->addDisplayGroup(	
			array('english', 'computer_skills'),
			'languages',
			array('legend' => _('Иностранные языки и компьютерные навыки'))
		);
		
		$this->addDisplayGroup(	
			array('about', 'recommendations', 'specialty'),
			'additional',
			array('legend' => _('Дополнительная информация'))
		);
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Сохранить'),
        ));
		
		
		parent::init();
		
		
	}
	
	

}