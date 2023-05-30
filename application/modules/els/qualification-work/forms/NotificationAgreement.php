<?php
class HM_Form_NotificationAgreement extends HM_Form
{
    public function init()
	{
		$user 		= $this->getService('User')->getCurrentUser();
		$fio 		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$date_birth = date('d.m.Y', strtotime($user->BirthDate));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('qualification_work_agreement');
		
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'qualification-work',
                    'controller' 		=> 'index',
                    'action' 			=> 'send-correct-data',
                )
            )
        );
		
		$this->addElement('textarea', 'description', array(
            'Label' 	=> _('Обращение'),
			'Required' 	=> true,			
			'rows' 		=> '5',	
        ));
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	

}