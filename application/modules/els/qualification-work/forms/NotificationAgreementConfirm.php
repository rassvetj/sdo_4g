<?php
class HM_Form_NotificationAgreementConfirm extends HM_Form
{
    public function init()
	{
		$user 		= $this->getService('User')->getCurrentUser();
		$fio 		= $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$date_birth = date('d.m.Y', strtotime($user->BirthDate));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('qualification_work_agreement_confirm');
		
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'qualification-work',
                    'controller' 		=> 'index',
                    'action' 			=> 'save-agreement',
                )
            )
        );
		
		$this->addElement('checkbox', 'is_confirm',
                array(
                	'Label' 	=> _('Информация верна'),
                    'Required' 	=> true,                                        
                    'Value' 	=> 0,
                )
        );
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Подтвердить'),
        ));
		
		parent::init();
	}
	

}