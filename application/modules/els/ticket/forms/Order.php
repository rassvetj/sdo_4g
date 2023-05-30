<?php
class HM_Form_Order  extends HM_Form
{
	
    public function init()
	{
		
			
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setName('form_order');
		$this->setAction($this->getView()->url(array('module' => 'ticket', 'controller' => 'order', 'action' => 'save')));
		
		$this->addElement('hidden', 'period',
			array(
				'Required' 	=> false,
				'value'		=> '', 
			)
		);
		
		$this->addElement('hidden', 'postfix', # дополнение к именам полей  во view
			array(
				'Required' 	=> false,
				'value'		=> '', 
			)
		);
		
		$this->addElement('text','sum', array( 
			'label' 	=> _('Сумма'),													
			'Required' 	=> true,				
			'validators'=> array('float'),				
		));	
		
		
		$this->addElement('DatePicker', 'date_payment', array(
			'Label' 	=> _('Дата платежа'),
			'Required' 	=> true,
			'Validators'=> array(
				array(
					'StringLength',
				false,
				array('min' => 10, 'max' => 50)
				)
			),
			'Filters' 		=> array('StripTags'),
			'JQueryParams' 	=> array(
				'showOn'			=> 'button',
				'buttonImage' 		=> "/images/icons/calendar.png",
				'buttonImageOnly' 	=> 'true'
			),			
		));
			
		$this->addElement($this->getDefaultFileElementName(), 'file', array(
			'Label' => _('Чек'),
			'Destination' => Zend_Registry::get('config')->path->upload->temp,
			'Required' => false,
			'Description' => _(''),
			'Filters' => array('StripTags'),
			'file_size_limit' => 2097152,
			'file_types' => '*.jpg;*.png;*.jpeg;*.pdf',
			'file_upload_limit' => 1,
			'user_id' => 0
		));
				
		
		$this->addElement('submit', 'submit', array(
			'Label' 	=> _('Сохранить'),		
		));
				
		parent::init();					
	}
}