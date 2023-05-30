<?php
class HM_Form_Internship extends HM_Form
{
    public function init()
	{
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('internship');
		
		$current_user	= $this->getService('User')->getCurrentUser();
		$type 			= (int)$this->getRequest()->getParam('type', false);
		
		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'internships',
                    'controller' 		=> 'send',
                    'action' 			=> 'index',
                )
            )
        );
		
		$this->addElement('select', 'type', array(
			'label' 		=> _('Грант на стажировку в '),
			'multiOptions'	=> HM_Internships_InternshipsModel::getTypeListAllow(),
			'Value'			=> $type,			
		));
		
		$this->addElement('text', 'fio', array(
			'Label'			=> _('ФИО'),
			'Value'			=> $current_user->LastName.' '.$current_user->FirstName.' '.$current_user->Patronymic,
            'Required'		=> true,
            'Filters'		=> array('StripTags'),
        ));
		
		$this->addElement('text', 'phone', array(
			'Label'			=> _('Контактный номер телефона'),
			'Value'			=> $current_user->Phone,
            'Required'		=> true,
            'Filters'		=> array('StripTags'),
        ));
		
		$this->addElement('text', 'email', array(
			'Label'			=> _('E-mail'),
			'Value'			=> $current_user->EMail,			
            'Required'		=> true,
            'Validators'	=> array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')
        ));
		
		
		
		$this->addElement('select', 'language_list', array(
			'label' 		=> _('Язык'),
			'multiOptions'	=> array('' => _('- выберите -')) + $this->getService('Subject')->getSubGroupList(),					
		));
		
		
		$this->addElement('select', 'degree_list', array(
			'label' 		=> _('Степерь знания'),			
			'multiOptions'	=> array('' => _('- выберите -')) + HM_Internships_InternshipsModel::getDegreeList(),
			'disabled'			=> ' disabled',
		));
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		
		parent::init();
	}
	
	
	
}