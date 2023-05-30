<?php
class HM_Form_Vpo extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();		
		$bd = DateTime::createFromFormat('Y-m-d 00:00:00.000', $user->BirthDate);
		
		$this->setAction($this->getView()->url(array('module' => 'survey', 'controller' => 'ajax', 'action' => 'save')));
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('spo');
		
		$this->addElement('hidden', 'type', array(
			'Value' 	=> HM_Survey_SurveyModel::TYPE_VPO,
        ));
		
		$this->addElement('text', 'LastName', array(
            'Label' 	=> _('Фамилия:'), 
            'Required' 	=> true,
			'Value' 	=> $user->LastName,
			'Filters' => array('StripTags'),
        ));
		
		$this->addElement('text', 'FirstName', array(
            'Label' 	=> _('Имя:'),
            'Required' 	=> true,
			'Value' 	=> $user->FirstName,
			'Filters' => array('StripTags'),
        ));
		
		$this->addElement('text', 'Patronymic', array(
            'Label' 	=> _('Отчество:'),
            'Required' 	=> false,
			'Value' 	=> $user->Patronymic,
			'Filters' => array('StripTags'),
        ));
		
		$this->addElement('DatePicker', 'BirthDate', array(
            'Label' => _('Дата рождения'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
			'Value' 	=> ($bd) ? ($bd->format('d.m.Y')) : (''),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );
		
		$this->addElement('text', 'address_residence', array(	
            'Label' 	=> _('Адрес фактического проживания'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'Phone', array(
            'Label' 	=> _('Телефон'),
            'Required' 	=> false,
			'Value' 	=> $user->Phone,
        ));
		
		$this->addElement('text', 'EMail', array(
            'Label' 	=> _('Электронная почта'),
            'Required' 	=> false,
			'Value' 	=> $user->EMail,
			'Validators' => array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')
        ));
		
		$this->addElement('text', 'vk', array( 
            'Label' 	=> _('vk.com'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'instagram', array(
            'Label' 	=> _('instagram.com'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'facebook', array(
            'Label' 	=> _('facebook.com'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'twitter', array(
            'Label' 	=> _('twitter.com'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'course', array(
            'Label' 	=> _('Курс'),
            'Required' 	=> false,
			'Value' 	=> '',
			'filters'	=> array('int'),
			'Validators'=> array('Int'),
        ));
		
		$this->addElement('text', 'year_graduation', array(
            'Label' => _('Год окончания'),
            'Required' => false,
            'Validators' => array(
                array('Between', false, array(1910, date('Y')))
            ),
            'Filters' => array(
            )
        ));
		
		$this->addElement('text', 'faculty', array(
            'Label' 	=> _('Факультет'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'specialty', array(
            'Label' 	=> _('Специальность'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('radio', 'education_level', array(
            'Label' => _('Уровень образования'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Среднее профессиональное образование'),
				2 => _('Бакалавр'),
				3 => _('Специалист'),
				4 => _('Магистр'),
			),
            'Validators'	=> array('Int'),
            'Filters' 		=> array('Int'),
            'separator' 	=> '&nbsp;',
            'Value' 		=> ''
        ));
		
		$this->addElement('radio', 'plan_after_graduation', array(
            'Label' => _('Что Вы планируете делать после получения диплома?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Работать (уже трудоустроен)'),
				2 => _('Работать (нахожусь в поиске работы)'),
				3 => _('Продолжить обучение '),
				4 => _('Пройти службу в армии'),
				5 => _('Уйти в отпуск по уходу за ребенком'),
				6 => _('Не работать'),			
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		$this->addElement('radio', 'education_after_graduation', array(
            'Label' => _('Планируете ли Вы продолжить обучение?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Нет'),
				2 => _('Да, планирую получать высшее образование'),						
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		
		$this->addElement('radio', 'is_working', array(
            'Label' => _('Работаете ли Вы в данный момент?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да, работаю по специальности'),
				2 => _('Да, работаю в области, близкой к специальности'),						
				3 => _('Да, работаю не по специальности'),						
				4 => _('Не работаю'),															
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		
		$this->addElement('radio', 'work_on_specialty', array(
            'Label' => _('Планируете ли Вы в дальнейшем работать по специальности, полученной в РГСУ?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да'),
				2 => _('Нет'),			
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		$this->addElement('radio', 'target_set', array(
            'Label' => _('Обучение по целевому набору?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да'),
				2 => _('Нет'),			
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		
		$this->addElement('radio', 'is_target_employment', array(
            'Label' => _('Трудоустройство по результатам целевого обучения?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да'),
				2 => _('Нет'),			
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		
		$this->addElement('text', 'actual_work_place_company', array(
            'Label' 	=> _('Компания'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'actual_work_place_address', array(
            'Label' 	=> _('Адрес'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'actual_work_place_phone', array(
            'Label' 	=> _('Телефон'),
            'Required' 	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'actual_work_place_position', array(
            'Label' 	=> _('Должность'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		
		
		
		
		$this->addElement('text', 'planned_work_place_company', array(
            'Label' 	=> _('Компания'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'planned_work_place_address', array(
            'Label' 	=> _('Адрес'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'planned_work_place_phone', array(
            'Label' 	=> _('Телефон'),
            'Required' 	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'planned_work_place_position', array(
            'Label' 	=> _('Должность'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		
		$this->addElement('radio', 'is_invalid', array(
            'Label' => _('Имеете ли Вы инвалидность?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да'),
				2 => _('Нет'),			
			),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'separator' => '&nbsp;',
            'Value' => ''
        ));
		
		
		$this->addElement('text', 'invalid_degree', array(
            'Label' 	=> _('Степень инвалидности'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
	
	
		$this->addElement('radio', 'is_ready_join_club', array(
            'Label' => _('Вы готовы вступить в Клуб выпускников РГСУ (vk.com/rgsuclub, instagram.com/rgsuclub, facebook.com/rgsuclub)?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Да'),
				2 => _('Нет'),			
			),
            'Validators'	=> array('Int'),
            'Filters' 		=> array('Int'),
            'separator'		=> '&nbsp;',
            'Value' 		=> ''
        ));
		
		
		$this->addElement('radio', 'see_in_club', array(
            'Label' => _('Что Вы хотели бы видеть в Клубе выпускников РГСУ?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Мероприятия'),			
				2 => _('Полезные контакты выпускников РГСУ'),			
				3 => _('Трудоустройство'),			
				4 => _('Скидки компаний-партнеров'),			
				5 => _('Другое (укажите)'),			
			),
            'Validators'	=> array('Int'),
            'Filters' 		=> array('Int'),
            'separator' 	=> '&nbsp;',
            'Value' 		=> ''
        ));
		
		$this->addElement('text', 'see_in_club_other', array(
            'Label' 	=> _(''),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),			
        ));
		
		
		$this->addElement('radio', 'criteria_quality_training', array(
            'Label' => _('Что, по Вашему мнению, необходимо РГСУ для повышения качества подготовки специалистов?'),
            'Required' => false,
            'multiOptions' => array(
				1 => _('Повышать качество подготовки не требуется (оно уже обеспечено)'),			
				2 => _('Качественно организованная практика'),			
				3 => _('Содействие в трудоустройстве студентам и выпускникам'),			
				4 => _('Современные методы обучения'),			
				5 => _('Квалифицированные преподаватели'),			
				6 => _('Современное программное обеспечение'),			
				7 => _('Четкие требования к студентам со стороны преподавателей'),			
				8 => _('Дополнительные образовательные программы'),			
				9 => _('Современное учебно-методическое обеспечение и лабораторные базы	'),			
				10=> _('Другое (укажите)'),	
			),
            'Validators' 	=> array('Int'),
            'Filters' 		=> array('Int'),
            'separator' 	=> '&nbsp;',
            'Value' 		=> ''
        ));
		
		$this->addElement('text', 'criteria_quality_training_other', array(
            'Label' 	=> _(''),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),			
        ));
		
		
		
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