<?php
class HM_Form_Entry extends HM_Form
{
    public function init()
	{		
		$this->setAction($this->getView()->url(array('module' => 'entry-event', 'controller' => 'index', 'action' => 'send')));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('entry-event');
		
		$groups = HM_DisabledPeople_DisabledPeopleModel::getFundGroups();
		$listFunds = $this->getService('DisabledPeople')->getListFunds();
		
		 $this->addElement('hidden', 'confirm', array(                
                'Validators'=> array('Int'),
                'Filters' 	=> array('Int'),
				'value'		=> 1,
            )
        );
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Принять'),
        ));
		
		parent::init();
	}
}