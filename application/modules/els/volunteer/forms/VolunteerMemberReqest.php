<?php
class HM_Form_VolunteerMemberReqest  extends HM_Form
{
    public function init()
	{	
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('volunteer_member_reqest');
				
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Стать волонтером'),
			'Class' => 'vButton',
        ));
		
		parent::init();
	}
}