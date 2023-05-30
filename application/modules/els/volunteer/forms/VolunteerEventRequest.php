<?php
class HM_Form_VolunteerEventRequest  extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();
		
		$events = $this->getService('Volunteer')->getListEvents();
				
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('volunteer_event_request');
	
	
		
		$this->addElement('select','event', array(
			'label' => _('Мероприятия, доступные для участия'),
			'value' => '0', 
			'multiOptions' => $events,
			'Required' => true,
			'Validators' => array(),			
		));
					
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить заявку'),
        ));
		
		parent::init();
	}
	
}