<?php
class HM_Form_Ticket  extends HM_Form
{
	/**
	* список id элементов, которые должны быть скрыты при определенном типе услуге находитмя в моделе HM_Ticket_TicketModel
	*/
    public function init()
	{
	try {
 
		$t_service = $this->getService('Ticket');
		$user = $this->getService('User')->getCurrentUser();
		$tt = $this->getService('TicketRequisite')->getRequisiteByName($user->organization);
		$defaultFilial = '';
		if($tt){ $defaultFilial = $tt->requisite_id; }
		
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('form-ticket');
		$this->setAction($this->getView()->url(array('module' => 'ticket', 'controller' => 'send', 'action' => 'index')));
		
		
		
		$type = HM_Ticket_TicketModel::getTypeContract();
		$userCertificateInfo = $this->getService('Ticket')->getUserCertificateInfo();

		$contractNumber = '';
		if(isset($userCertificateInfo[HM_Ticket_TicketModel::TYPE_EDUCATION])){
			$contractNumber = $userCertificateInfo[HM_Ticket_TicketModel::TYPE_EDUCATION]->contract_number.' ('.mb_strtolower($type[HM_Ticket_TicketModel::TYPE_EDUCATION]).')';			
			$dateCertificate = date('d.m.Y',strtotime($userCertificateInfo[HM_Ticket_TicketModel::TYPE_EDUCATION]->date_create));
		}
		
		if(isset($userCertificateInfo[HM_Ticket_TicketModel::TYPE_LIVING])){
			if(empty($contractNumber)){
				$contractNumber = $userCertificateInfo[HM_Ticket_TicketModel::TYPE_LIVING]->contract_number.' ('.mb_strtolower($type[HM_Ticket_TicketModel::TYPE_LIVING]).')';
			}							
			if(!$dateCertificate){
				$dateCertificate = date('d.m.Y',strtotime($userCertificateInfo[HM_Ticket_TicketModel::TYPE_LIVING]->date_create));
			}
		}
		
		
		$this->addElement('hidden', 'is_pay_card', array(
           'Value' => 0,						
        ));
		
		
		$this->addElement('select','filial_id', array( 
			'label' 		=> _('Подразделение'),			
			'multiOptions' 	=> $this->getService('TicketRequisite')->getOrganizationNameList(),
			'Required' 		=> true,	
			'value'			=> $defaultFilial,
			'onChange'		=> 'setActiveService($("option:selected",this).text(), true); updatePaymentButton($(this).val());',	
		));
		
		$this->addElement('select','service_type_id', array( 
			'label' => _('Выберите тип услуги'),									
			'multiOptions' => $t_service->getServiceTypeList(true),			
			'Required' => true,	
			'onChange'	=> 'changeTicketForm($(this).val());'
		));
		
		//--Услуги Образование		
		$this->addElement('select','service_education_id', array( 
			'label' => _('Выберите услугу'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getServiceListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION, true),
			'Required' => true,						
		));
		
		
		//--Услуги Штрафы
		$this->addElement('select','fine_id', array( 
			'label' => _('Выберите тип штрафа'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getItemListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_FINE),					
			'Required' 	=> true,					
		));
		
		//--Услуги Общежитие
		$this->addElement('select','hostel_id', array( 
			'label' => _('Общежитие'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getItemListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_HOSTEL),
			'Required' => true,				
		));
		
		//--Услуги Бассейн
		$this->addElement('select','service_pool_id', array( 
			'label' => _('Выберите услугу'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getServiceListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_POOL, true),
			'Required' => true,				
		));
		
		//--Услуги прачечной
		$this->addElement('select','service_laundry_id', array( 
			'label' => _('Выберите услугу'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getServiceListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_LAUNDRY, true),
			'Required' => true,				
		));
		
		
		//--Услуги Базы отдыха Пансионат
		$this->addElement('select','hotel_id', array( 
			'label' => _('Пансионат'),			
			'multiOptions' => array('' => _('--выбрать--')) + $t_service->getItemListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_HOTEL),			
			'Required' => true,	
			'onChange'	=> 'changeHotelServiceList($(this).val());',			
		));
		
		//--Услуги Базы отдыха Выберите услугу
		$this->addElement('select','service_hotel_id', array( 
			'label' => _('Выберите услугу'),			
			'multiOptions' => array('' => _('--выбрать--')) + $t_service->getServiceListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_HOTEL, true),									
			'Required' => true,				
		));
		
		
		//--Услуги Журналы
		$this->addElement('select','journal_id', array( 
			'label' => _('Название журнала'),			
			'multiOptions' => array('' => _('--выбрать--') ) + $t_service->getItemListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_JOURNAL),	
			'Required' => true,				
		));
		
		
		$this->addElement('text','journal_year', array( 
			'label' 	=> _('Год журнала'),									
			'filters'   => array('Int'),
			'Required' 	=> true,
			'maxlength'	=> 4,			
		));	
		
		$this->addElement('text','journal_number', array( 
			'label' 	=> _('Номер журнала'),									
			'filters'   => array('Int'),
			'Required' 	=> true,
			'maxlength'	=> 2,			
		));	
		
		
		//--Услуги Библиотека
		$this->addElement('select','service_library_id', array( 
			'label' => _('Услуги библиотеки'),			
			'multiOptions' => $t_service->getServiceListByTypeCode(HM_Ticket_TicketModel::SERVICE_TYPE_LIBRARY, true),			 
			'Required' => true,				
		));
		
		$this->addElement('text','library_card_number', array( 
			'label' 	=> _('№ читательского билета'),									
			'filters'   => array('Int'),
			'Required' 	=> true,						
		));	
		
		
		$this->addElement('select','period_id', array( 
			'label' => _('Период оплаты'),			
			'multiOptions' => array('' => _('--выбрать--') ) + HM_Ticket_TicketModel::getPeriods(),			
			'Required' => true,				
		));
		
		
		$this->addElement('DatePicker', 'period_begin', array(
            'Label' => _('Период оплаты c'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),			
        ));
		
		$this->addElement('DatePicker', 'period_end', array(
            'Label' => _('Период оплаты по'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),			
        ));
		
		
		
		
		
		$this->addElement('text','contract_number', array( 
			'label' 	=> _('№ договора:'),									
			'filters'   => array('StringTrim'),
			'Required' 	=> true,
			'value' 	=> $contractNumber,
		));	
		
		
		$this->addElement('DatePicker', 'contract_date', array(
            'Label' => _('Дата договора'),
            'Required' => true,
			'value' => $dateCertificate,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),							
        ));
		
		
		$this->addElement('text', 'sum1', array(
            'Label' => _('Сумма (руб.):'),
            'Required' => true,						
        ));
		
		$this->addElement('text', 'sum2', array(
            'Label' 	=> _('Сумма (коп.):'),
			'filters'   => array('Int'),
			'maxlength'	=> 2,
        ));
		
		
		$this->addElement('text', 'payerLastName', array(
            'Label' => _('Фамилия'),
            'Required' => true,
			'Value' => $user->LastName,						
        ));
		$this->addElement('text', 'payerFirstName', array(
            'Label' => _('Имя'),
            'Required' => true,
			'Value' => $user->FirstName,						
        ));
		$this->addElement('text', 'payerPatronymic', array(
            'Label' => _('Отчество (возможно поставить "-")'),
            'Required' => true,
			'Value' => $user->Patronymic,						
        ));
		$this->addElement('text', 'payerEmail', array(
            'Label' => _('Электронная почта'),
            'Required' => true,
			'Value' => $user->EMail,	
			'Validators' => array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')			
        ));
		$this->addElement('textarea', 'payerAddress', array(            
			'Label' => _('Адрес проживания'),
            'Required' => true,			
			'rows' => '5',				
        ));
		 
		
		$this->addElement('text', 'forWhomPayLastName', array(
            'Label' => _('Фамилия'),
            'Required' => true,
			'Value' => $user->LastName,									
        ));
		$this->addElement('text', 'forWhomPayFirstName', array(
            'Label' => _('Имя'),
            'Required' => true,
			'Value' => $user->FirstName,					
        ));
		$this->addElement('text', 'forWhomPayPatronymic', array(
            'Label' => _('Отчество (возможно поставить "-")'),
            'Required' => true,
			'Value' => $user->Patronymic,					
        ));
	
		$this->addDisplayGroup(array(
			'service_type_id',						
		),
		'groupBase_1',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase',									
		));
		
		$this->addDisplayGroup(array(
			'service_education_id',	
			'fine_id',	
			'hostel_id',	
			'service_pool_id',	
			'service_laundry_id',	
			'hotel_id',	
			'journal_id',	
			'service_library_id',			
		),
		'groupBase_2',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase',									
		));
		
		$this->addDisplayGroup(array(
			'contract_number',
			'service_hotel_id',
			'journal_year',
			'library_card_number',
		),
		'groupBase_3',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase  ticket_groupBase_short ticket_groupBase_middle',									
		));
		
		$this->addDisplayGroup(array(
			'contract_date',	
			'journal_number',
		),
		'groupBase_4',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase ticket_groupBase_short',			
		));
		
		$this->addDisplayGroup(array(
			'period_id',							
		),
		'groupBase_5',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase',	
			'style'		=> 'clear:both;', //--чтобы не заскакивал элемент при сокрытии предшествующих ему элементов
		));
		
		$this->addDisplayGroup(array(			
			'period_begin',			
		),
		'groupBase_6',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase ticket_groupBase_short hideElement',	
			'style'		=> 'clear:both;', //--чтобы не заскакивал элемент при сокрытии предшествующих ему элементов			
		));
		$this->addDisplayGroup(array(						
			'period_end',	
		),
		'groupBase_7',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase ticket_groupBase_short hideElement',				
		));
		
		$this->addDisplayGroup(array(
			'sum1',						
		),
		'groupBase_8',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase ticket_groupBase_short ticket_groupBase_middle',				
		));
		
		$this->addDisplayGroup(array(
			'sum2',						
		),
		'groupBase_9',
		array(
			'legend' 	=> _(''),
			'class' 	=> 'ticket_groupBase ticket_groupBase_short',									
		));
	
		
		
		$this->addDisplayGroup(array(
			'payerLastName',						
			'payerFirstName',						
			'payerPatronymic',						
			'payerEmail',						
			'payerAddress',						
		),
		'groupPayer',
		array(
			'legend' 	=> _('Плательщик'),	
			'class' 	=> 'ticket_groupPayer',	
			'style'		=> 'clear: both;',
		));
		
		
		
		
		$this->addDisplayGroup(array(
			'forWhomPayLastName',						
			'forWhomPayFirstName',						
			'forWhomPayPatronymic',						
		),
		'groupForWhomPay',
		array(
			'legend' 	=> _('Студент, за которого вносится оплата'),
			'class' 	=> 'ticket_groupForWhomPay',						
		));
		
		
		$this->addElement('submit', 'submit', array(
            'Label' 	=> _('Получить квитанцию'),
			'onClick'	=> '$("#is_pay_card").val(0)',
        ));
		
		$this->addElement('submit', 'btn_pay_card', array(
            'Label' => _('Оплатить картой'),			
			'onClick'	=> '$("#is_pay_card").val(1)',
        ));
		
		
		parent::init();
		
	} catch (Exception $e) {
		echo $e->getMessage(), "\n";
	}
	}

	public function getElementDecorators($alias, $first = 'ViewHelper') {
		$classes = 'ticket_element_container ticket_element_'.$alias;
		if(in_array($alias, $this->getDefaultHideAliasList())){
			$classes .= ' hideElement';
		}
		$decorators = parent::getElementDecorators($alias, $first);			
		$decorators[] = array(array('dl' => 'HtmlTag'), array('tag' => 'div', 'class'  => $classes));						
		return $decorators;		
	}
	
	// id элементов формы, которые по умолчанию скрыты.
	public function getDefaultHideAliasList(){
		return array(
			'fine_id',
			'hostel_id',
			'service_pool_id',
			'service_laundry_id',
			'hotel_id',
			'journal_id',
			'service_library_id',
			'service_hotel_id',
			'period_begin',
			'period_end',
		);
	}
	
}