<?php
class HM_Form_DebtSubject extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'debt-subject', 'action' => 'get')));
        $this->setName('news');
		
		
		
        $this->addElement('DatePicker', 'date_end', array(
            'Label' 		=> _('Дата продления больше, чем'),
            'Required' 		=> false,
			'Value'		 	=> date('d.m.Y'), 
            'Validators' 	=> array(
                array('StringLength', false, array('min' => 10, 'max' => 50)),
                array('DateGreaterThanFormValue', false, array('name' => 'begin')),
            ),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 			=> 'button',
                'buttonImage' 		=> "/images/icons/calendar.png",
                'buttonImageOnly' 	=> 'true'
            )
        ));
        
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}