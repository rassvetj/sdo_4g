<?php
class HM_Form_ConfirmingStudent extends HM_Form
{
    public function init()
	{
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('confirming-student');
		
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'certificates',
                    'controller' 		=> 'index',
                    'action' 			=> 'send-error',                    
                )
            )
        );
		
		$this->addElement('hidden', 'type', array(
            'Required'	=> false,
			'Value' 	=> HM_CertificatesStudent_CertificatesStudentModel::TYPE_CONFIRMING_STUDENT,
        ));
	
		
		$this->addElement('textarea', 'reason', array(
            'Label' 	=> _('Текст'),
			'Required'	=> true,
			'Value' 	=> '',
        ));
		
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
	
	
	
}