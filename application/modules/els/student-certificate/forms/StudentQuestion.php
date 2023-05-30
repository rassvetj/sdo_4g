<?php
class HM_Form_StudentQuestion  extends HM_Form
{
	protected $id = 'question';
	
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();
		
		$types = HM_StudentCertificate_StudentCertificateModel::getTypes();
		
		$fio = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$groups = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		
		$email = $user->EMail;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('question');
		
		/*
		$this->addElement('hidden', 'email', array(                        
			'Value' => $email,			
			'disabled' => 'disabled',
        ));
		*/
		
		$this->addElement('text', 'fio_q', array(
            'Label' => _('Ф.И.О.:'),
            'Required' => true,
			'Value' => $fio,			
			'Disabled' => 'disabled',
			'Readonly' => true,
        ));
		
		
		$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($email)) {		
		//if($email &&  $email != ''){
			$this->addElement('text', 'email_q', array(                        
				'Label' => _('E-Mail:'),
				'Value' => $email,			
				'Required' => true,	
				'Validators' => array('EmailAddress'),								
				'Disabled' => 'disabled',
				'Readonly' => true,
			));
		} else {
			$this->addElement('text', 'email_q', array(                        
				'Label' => _('E-Mail:'),
				'Value' => '',			
				'Required' => true,	
				'Validators' => array('EmailAddress'),				
			));
		}
		
		
		$this->addElement('text', 'group_q', array(
            'Label' => _('Группа:'),
            'Required' => false,
			'Value' => isset($groups[0]['name']) ? ($groups[0]['name']) : (''),									
        ));
		
		$this->addElement('textarea', 'question', array(            
			'Label' => _('Задайте свой вопрос'),
            'Required' => true,
			//'cols' => '12',
			'rows' => '5',			
        ));		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
}