<?php
class HM_Form_Statement extends HM_Form
{
    public function init()
	{
		$user 		= $this->getService('User')->getCurrentUser();
		$fio 		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$date_birth = date('d.m.Y', strtotime($user->BirthDate));
		#$email = $user->EMail;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('statement');
		
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'student-certificate',
                    'controller' 		=> 'statement',
                    'action' 			=> 'save',
                    'referer_redirect' => 1
                )
            )
        );
		
		
		
		
		$this->addElement('select','type_id', array(  //Ex: 1_3 - первый тип формы, 3 тип справки
			'label' 		=> _('Тип заявления'),			
			'multiOptions'	=> HM_StudentCertificate_Statement_StatementModel::getTypes(),
			'onChange'		=> 'changeForm($(this).val());',
		));
		
		$this->addElement('text', 'LastName', array(
            'Label' 	=> _('Фамилия'),
            'Required' 	=> false,
			'Value' 	=> $user->LastName,											
        ));
		
		$this->addElement('text', 'FirstName', array(
            'Label' 	=> _('Имя'),
            'Required' 	=> false,
			'Value' 	=> $user->FirstName,											
        ));
		
		$this->addElement('text', 'Patronymic', array(
            'Label' 	=> _('Отчество'),
            'Required' 	=> false,
			'Value' 	=> $user->Patronymic,											
        ));
		
		
		$this->addElement('DatePicker', 'date_birth', array( 
            'Label'			=> _('Дата рождения'),
			'value'			=> $date_birth,
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
		
		$this->addElement('text', 'passport_series', array(
            'Label' 	=> _('Серия паспорта'), 
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'passport_number', array(
            'Label' 	=> _('Номер паспорта'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'passport_issued_name', array(
            'Label' 	=> _('Кем выдан'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
						
		$this->addElement('DatePicker', 'passport_issued_date', array( 
            'Label'			=> _('Дата выдачи'),
			'value'			=> '',
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
				
		$this->addElement('text', 'passport_issued_code', array(
            'Label' 	=> _('Код подразделения'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'address_registration', array(
            'Label' 	=> _('Адрес прописки'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'address_birth', array(
            'Label' 	=> _('Место рождения'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('text', 'reason', array(
            'Label' 	=> _('Причина'),
			'Required'	=> false,
			'Value' 	=> '',
        ));
		
		$this->addElement('DatePicker', 'date_begin', array( 
            'Label'			=> _('Дата начала'),
			'value'			=> '',
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
		
		$this->addElement('DatePicker', 'date_end', array( 
            'Label'			=> _('Дата окончания'),
			'value'			=> '',
            'Required' 		=> false,
            'Validators' 	=> array(array('StringLength', false, array('min' => 10, 'max' => 50))),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 		  => 'button',
                'buttonImage' 	  => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),
			'class'	=> 'date_picker'
        ));
		
		
		$this->addElement('select','remand_type_id', array(  
			'label' 		=> _('Тип отчисления'),			
			'multiOptions'	=> array('' => '-- выберите --') + HM_StudentCertificate_Statement_StatementModel::getRemandTypes(),
		));
		
		
		
		$this->addDisplayGroup(
            array(
                'type_id',
                'LastName',
                'FirstName',
                'Patronymic',
                'date_birth', 
				'reason',				
            ),
            'base_info',
            array(
				'class' => 'fields-block',
				'legend' => _('Основная информация'),				
			)
        );
		
		
		
		$this->addDisplayGroup(
            array(
                'passport_series',
                'passport_number',
                'passport_issued_name',
                'passport_issued_date',
                'passport_issued_code',
                'address_registration',
                'address_birth',
            ),
            'passport_info',
            array(
				'class' => 'fields-block',
				'legend' => _('Паспортные данные'),				
			)
        );
		
		
		$this->addDisplayGroup(
            array(                                
                'date_begin',
                'date_end',                
            ),
            'additional_info',
            array(
				'class' => 'fields-block',
				'legend' => _('Дополнительная информация'),				
			)
        );
		
		
		$this->addDisplayGroup(
            array(                                                
                'remand_type_id',
            ),
            'additional_info_2',
            array(
				'class' => 'fields-block',
				'legend' => _('Дополнительная информация'),				
			)
        );
		
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	
	
	  public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'u_document') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }
	
}