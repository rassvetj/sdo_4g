<?php
class HM_Form_Question extends HM_Form
{
    public function init()
	{
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('my-payments-question');
		
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'my-payments',
                    'controller' 		=> 'send',
                    'action' 			=> 'question',                    
                )
            )
        );
		
		$user 	= $this->getService('User')->getCurrentUser();
		$email 	= $user->EMail;
		
		$this->addElement('hidden', 'contract_number',	array('value' => ''));
		$this->addElement('hidden', 'contract_date',  	array('value' => ''));
		$this->addElement('hidden', 'total_debt',		array('value' => ''));
		$this->addElement('hidden', 'update_date',		array('value' => ''));
		
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($email)) {		
			$this->addElement('text', 'email', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> '',			
				'Required' 		=> true,	
				'Validators' 	=> array('EmailAddress'),					
			));
		}
		
		
		$this->addElement('select', 'theme', array( 
			'label' 		=> _('Тема вопроса'),			
			'multiOptions'	=> HM_MyPayments_MyPaymentsModel::getThemeList(),			
		));
	
		
		$this->addElement('textarea', 'message', array(
            'Label' 	=> _('Ваше обращение'),
			'Required'	=> true,
			'Value' 	=> '',
        ));
		
		
		$this->addElement($this->getDefaultFileElementName(), 'files', array(            
			'Label' 			=> _('Прилагаемые сканы документов'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> false,
            'Description' 		=> _('Максимальный размер файла &ndash; 500 Кb.'),
            'Filters'			=> array('StripTags'),
            #'file_size_limit'	=> 512000,
			'file_size_limit'	=> 3145728, # 3 Мб #5242880, # 5 Mb	
            'file_types' 		=> '*.*',
            'file_upload_limit' => 5,
			'user_id' 			=> 0,								
        ));	
		
		
		/*
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		*/
		
		parent::init();
	}
	
	
	
}