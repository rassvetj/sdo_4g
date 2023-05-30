<?php
class HM_Form_Vaccination extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();		
		
		$this->setAction($this->getView()->url(array('module' => 'survey', 'controller' => 'save', 'action' => 'index')));
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('vaccination');
		
		$this->addElement('hidden', 'type', array(
			'Value' 	=> HM_Survey_SurveyModel::TYPE_VACCINATION,
        ));
		
		$this->addElement('text', 'fio', array(
            'Label' 	=> _('Фамилия Имя Отчество'),
            'Required' 	=> false,
			'Value' 	=> $user->getName(),
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'birth_date', array(
            'Label' 	  => _('Число месяц год рождения'),
            'Required' 	  => false,
			'Value' 	  => '',
			'Filters' 	  => array('StripTags'),
			'placeholder' => 'дд.мм.гггг',
        ));
		
		$this->addElement('text', 'registration_address', array(
            'Label' 	=> _('Адрес постоянной регистрации (прописки)'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'passport_series', array(
            'Label' 	   => _('Серия и номер паспорта гражданина РФ'),
            'Required' 	   => false,
			'Value' 	   => '',
			'Filters' 	   => array('StripTags'),
			'placeholder ' => 'серия',
        ));
		
		$this->addElement('text', 'passport_number', array(
            'Label' 	   => _('Серия и номер паспорта гражданина РФ'),
            'Required' 	   => false,
			'Value' 	   => '',
			'Filters' 	   => array('StripTags'),
			'placeholder ' => 'номер',
        ));
		
		$this->addElement('text', 'policy_number', array(
            'Label' 	=> _('Номер полиса ОМС'),
            'Required' 	=> true,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('checkbox', 'vaccination_confirm', array(
            'Label'      => _('Согласие на вакцинацию против гриппа подтверждаю'),
            'Required'   => true,
            'Validators' => array('Int'),
			'Filters'    => array('Int'),			
		));
		
		$this->addElement('checkbox', 'data_confirm', array(
            'Label'      => _('Достоверность предоставленных данных подтверждаю'),
            'Required'   => false,
            'Validators' => array('Int'),
			'Filters'    => array('Int'),			
		));
		
		
		
		$this->addElement('radio', 'status', array(
            'Label' 		=> _('Статус'),
            'Required' 		=> true,
            'multiOptions' 	=> HM_Survey_SurveyModel::getVaccinationStatuses(),
            'Validators'	=> array('Int'),
            'Filters' 		=> array('Int'),
            'separator' 	=> '&nbsp;<br />',
            'Value' 		=> '',
			'onChange'		=> 'updateVaccinationForm($(this))',
        ));
		
		
		$this->addElement('text', 'vaccination_date', array(
            'Label' 	  => _('Дата вакцинации'),
            'Required' 	  => false,
			'Value' 	  => '',
			'Filters' 	  => array('StripTags'),
			'placeholder' => 'дд.мм.гггг',
        ));
		
		$this->addElement('text', 'vaccine_name', array(
            'Label' 	=> _('Название вакцины'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'vaccine_series', array(
            'Label' 	=> _('Серия и номер вакцины'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		$this->addElement('text', 'vaccine_institution', array(
            'Label' 	=> _('Учреждение, где проводилась вакцинация'),
            'Required' 	=> false,
			'Value' 	=> '',
			'Filters' 	=> array('StripTags'),
        ));
		
		
		$this->addElement($this->getDefaultFileElementName(), 'vaccination_bid', array(            
			'Label' 			=> _('Прикрепите заявление'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> false,
            'Description' 		=> _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 10485760,
            'file_types' 		=> '*.jpg;*.png;*.gif;*.jpeg, *.pdf,*.doc,*.docx,*.xls,*.xlsx',            
            'file_upload_limit' => 1,	
			'user_id' 			=> 0,								
        ));	
		
		$this->addDisplayGroup(array(
            'status', 'vaccination_date', 'vaccine_name', 'vaccine_series',  'vaccination_bid'
        ),
            'area-base',
            array('legend' => _(''))
        );
				
		
		$this->addElement('submit', 'save_button', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
}