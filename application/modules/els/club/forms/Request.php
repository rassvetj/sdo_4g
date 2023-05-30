<?php
class HM_Form_Request extends HM_Form
{
	private $_serviceClub = null;
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('morm-club-request');
        $this->setAction($this->getView()->url(array('module' => 'club', 'controller' => 'index', 'action' => 'send-request')));

		$this->_serviceClub = $this->getService('Club');		
		
        $user 		= $this->getService('User')->getCurrentUser();
		$fio 		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$groups 	= $this->getService('StudyGroupUsers')->getUserGroups($user->MID);
		$group_name = isset($groups[0]['name']) ? ($groups[0]['name']) : ('');
		
		$email 		= $user->EMail;
		$validator 	= new Zend_Validate_EmailAddress();
		
		$this->addElement('text', 'fio', array(
            'Label' 	=> _('Ф.И.О.:'),
            'Required' 	=> true,
			'Value' 	=> $fio,			
			#'Disabled' 	=> 'disabled',
			'Readonly' 	=> true,
        ));
		
		
		if ($validator->isValid($email)) {				
			$this->addElement('text', 'email', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> $email,			
				'Required' 		=> true,	
				'Validators' 	=> array('EmailAddress'),								
				#'Disabled' 		=> 'disabled',
				'Readonly' 		=> true,
			));
		} else {
			$this->addElement('text', 'email', array(                        
				'Label' 		=> _('E-Mail:'),
				'Value' 		=> '',			
				'Required' 		=> true,	
				'Validators' 	=> array('EmailAddress'),				
			));
		}
		
		$this->addElement('text', 'group_name', array(
            'Label' 	=> _('Группа:'),
            'Required' 	=> false,
			'Value' 	=> $group_name,									
        ));
        
		
		$this->addElement('select', 'club_id', array( 
			'label' 		=> _('Кружок'),
			'value' 		=> 0, 			
			'multiOptions' 	=> array('' => '- выберите -')+$this->_serviceClub->getSelectClubList(),
		));
      
        $this->addElement('Submit', 'submit', array('Label' => _('Отправить заявку')));

        parent::init(); // required!
	}

}