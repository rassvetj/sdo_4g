<?php
class HM_Form_Assign extends HM_Form
{
	public function init()
	{
        $front			= Zend_Controller_Front::getInstance();
        $request		= $front->getRequest();
		$language_codes = array('' => _('- выберите -')) + $this->getService('Languages')->getLevels();
		
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'languages_assign');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('languages_assign');

		$this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'languages',
                    'controller' 		=> 'assign',
                    'action' 			=> 'save',                    
                )
            )
        );
		
		$this->addElement('select','language_code', array(
			'label' 		=> _('Уровень языка'),
			'multiOptions'	=> $language_codes,				
		));
		
		$this->addElement('submit', 'save_button', array(
			'Label' 	=> _('Сохранить'),						
		));
		
        parent::init(); // required!
	}

}
