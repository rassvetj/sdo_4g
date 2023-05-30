<?php
class HM_Form_Ask extends HM_Form
{	
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('form-club-request');
        $this->setAction($this->getView()->url(array('module' => 'payment', 'controller' => 'ask', 'action' => 'question')));

		$this->addElement('textarea', 'question', array(
            'Label' 	=> _('Вопрос:'),
            'Required' 	=> true,	
			'rows'		=> 8,
        ));
		
		$this->addElement('select', 'theme_id', array( 
			'label' 		=> _('Тема'),
			'value' 		=> 0, 			
			'multiOptions' 	=> array('' => '- выберите -') + $this->getService('Payment')->getThemeList(),
			'Required' 		=> true,
		));
		
		
		$this->addElement($this->getDefaultFileElementName(), 'files', array(
            'Label' 			=> _('Файлы'),
            'Validators' 		=> array(
                array('Count', false, 10),           
            ),
            'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'file_size_limit' 	=> 0,
            'file_types' 		=> '*.jpg, *.jpeg, *.png, *.pdf',
            'file_upload_limit' => 10,
			'file_size_limit'	=> 26214400, # 25 Mb			
            'Required' 			=> false
        ));
		
		
		$this->addDisplayGroup(array(
            'theme_id',
            'question',
            'files',
        ),
            'base',
            array('legend' => _('Задать вопрос'))
        );
		
      
        $this->addElement('Submit', 'submit', array('Label' => _('Отправить')));

        parent::init(); // required!
	}

}