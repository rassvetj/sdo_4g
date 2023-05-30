<?php
class HM_Form_AskQuestion  extends HM_Form
{
	protected $id = 'ask_question';
	
    public function init()
	{
		$user   = $this->getService('User')->getCurrentUser();		
		$types  = HM_StudentCertificate_StudentCertificateModel::getTypes();		
		$fio    = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$groups = $this->getService('StudyGroupUsers')->getUserGroups($user->MID);		
		$email  = $user->EMail;
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName($this->id);
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module'     => 'student-certificate',
                    'controller' => 'ask-question',
                    'action'     => 'send',
                )
            )
        );
		
		$this->addElement('text', 'fio_q', array(
            'Label'    => _('Ф.И.О.:'),
            'Required' => true,
			'Value'    => $fio,			
			'class'    => 'input-disabled',
			'Readonly' => true,
        ));
		
		
		$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($email)) {		
			$this->addElement('text', 'email_q', array(                        
				'Label'      => _('E-Mail:'),
				'Value'      => $email,			
				'Required'   => true,	
				'Validators' => array('EmailAddress'),								
				'class'      => 'input-disabled',
				'Readonly'   => true,
			));
		} else {
			$this->addElement('text', 'email_q', array(                        
				'Label'      => _('E-Mail:'),
				'Value'      => '',			
				'Required'   => true,	
				'Validators' => array('EmailAddress'),				
			));
		}
		
		
		$this->addElement('text', 'group_q', array(
            'Label'    => _('Группа:'),
            'Required' => false,
			'Value'    => isset($groups[0]['name']) ? ($groups[0]['name']) : (''),									
        ));
		
		$this->addElement('textarea', 'question', array(            
			'Label'    => _('Задайте свой вопрос'),
            'Required' => true,
			'rows'     => '5',			
        ));	


		$this->addDisplayGroup(
            array(
                'fio_q',
                'email_q',
                'group_q',
                'question',
            ),
            'additional',
            array(
				'legend' => _(''),
				'class'  => 'fio_q email_q group_q question',
			)
        );		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
}