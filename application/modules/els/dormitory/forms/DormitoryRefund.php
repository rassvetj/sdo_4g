<?php
class HM_Form_DormitoryRefund  extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();
		
		$fio 		= trim($user->LastName.' '.$user->FirstName.' '.$user->Patronymic);
		$email		= $user->EMail;
		$user_id	= (int)$user->MID;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('dormitory-refund');
		$this->setAction($this->getView()->url(array('module' => 'dormitory', 'controller' => 'refund', 'action' => 'send')));
		
		
		$this->addElement('select','type', array(
			'label' 		=> _('Тип возврата'),
			'value' 		=> '1', 
			'Required' 		=> true,
			'multiOptions' 	=> HM_Dormitory_Refund_RefundModel::getTypes(),
		));
		
		
		if(empty($fio)){
			$this->addElement('text', 'fio', array(
				'Label' 	=> _('Ф.И.О.:'),
				'Required' 	=> true,
				'Value' 	=> '',
			));
		} else {
			$this->addElement('text', 'fio', array(
				'Label' 	=> _('Ф.И.О.:'),
				'Required' 	=> true,
				'Value' 	=> $fio,					
				'Disabled' 	=> 'disabled',
				'Readonly' 	=> true,
			));
		}
		
		$validator   = new Zend_Validate_EmailAddress();
		if ($validator->isValid($email)) {		
			$this->addElement('text', 'email', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> $email,			
				'Required' 		=> true,		
				'Validators' 	=> array('EmailAddress'),								
				'Disabled' 		=> 'disabled',
				'Readonly' 		=> true,
			));
		} else {
			$this->addElement('text', 'email', array(                        
				'Label' => _('E-Mail:'),
				'Value' => '',			
				'Required' => true,	
				'Validators' => array('EmailAddress'),					
			));
		}
		
		
		
		
		#$this->addElement('text', 'course', array(
		#	'Label' 	=> _('Курс:'),
		#	'Value' 	=> '',			
		#	'Required' 	=> true,					
		#));
		
		#$this->addElement('text', 'specialty', array(
		#	'Label' 	=> _('Специальность:'),
		#	'Value' 	=> '',			
		#	'Required' 	=> true,					
		#));
		
		#$this->addElement('text', 'faculty', array(                        
		#		'Label' 	=> _('Факультет:'),
		#		'Value' 	=> '',			
		#		'Required' 	=> true,					
		#));
		
		$this->addElement('text', 'phone', array(                        
				'Label' 	=> _('Контактный телефон:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'bank_name', array(                        
				'Label' 	=> _('Наименование банка:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'inn', array(                        
				'Label' 	=> _('ИНН:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'kpp', array(                        
				'Label' 	=> _('КПП :'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'bik', array(                        
				'Label' 	=> _('БИК :'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'correspondent_account', array(                        
				'Label' 	=> _('Корреспондентский счет:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'settlement_account', array(                        
				'Label' 	=> _('Расчетный счет:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'recipient_name', array(                        
				'Label' 	=> _('Ф.И.О. получателя платежа:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		$this->addElement('text', 'recipient_personal_account', array(                        
				'Label' 	=> _('Номер лицевого счета получателя платежа:'),
				'Value' 	=> '',			
				'Required' 	=> true,					
		));
		
		
		$this->addElement($this->getDefaultFileElementName(), 'statement', array(            
			'Label' 			=> _('Заявление'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> true,
            'Description' 		=> _('Заполните бланк заявления и приложите. Для загрузки использовать файлы форматов: pdf, doc, docx. Максимальный размер файла &ndash; 5 Mb'),
            'Filters' 			=> array('StripTags'),
            'file_size_limit' 	=> 5242880,
            'file_types' 		=> '*.pdf,*.doc,*.docx',            
            'file_upload_limit' => 1,	
			'user_id' 			=> $user_id,
        ));
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		
		
		$this->addDisplayGroup(
            array(
                'type',
                'fio',
                'email',
                'course',
                'specialty',
                'faculty',
                'phone',
            ),
            'base',
            array(
				#'class' => '',
				#'legend' => _('Дополнительно'),
			)
        );
		
		$this->addDisplayGroup(
            array(                
                'bank_name',
                'inn',
                'kpp',
                'bik',
                'correspondent_account',
                'settlement_account',
                'recipient_name',
                'recipient_personal_account',
            ),
            'bank',
            array(
				#'class' => '',
				#'legend' => _('Дополнительно'),
			)
        );
		
		$this->addDisplayGroup(
            array(                
                'statement',
                'submit',
            ),
            'additional',
            array(
				#'class' => '',
				#'legend' => _('Дополнительно'),
			)
        );
		
		
		
		
		
		parent::init();
	}	
	  
}