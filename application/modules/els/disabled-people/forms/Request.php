<?php
class HM_Form_Request extends HM_Form
{
    public function init()
	{
		
		$this->setAction($this->getView()->url(array('module' => 'disabled-people', 'controller' => 'request', 'action' => 'send')));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('request');
		
		$beginElement = array('' => _('-- Выберите --'));
		
		
		$this->addElement('radio', 'type', array(			
			'multiOptions' => HM_DisabledPeople_DisabledPeopleModel::getRequestTypes(),
			'onChange'	   => 'changeForm($(this).val())',
		));
		
		
		
		#Основная информация
		$this->addElement('select','person', array( 
			'label' 		=> _('Специалист'),
			'value' 		=> '',
			'multiOptions' 	=> $beginElement + array(
				'Психолог'								=> 'Психолог',
				'Тьютор' 								=> 'Тьютор',
				'Тифлопедагог' 							=> 'Тифлопедагог', 
				'Сурдопереводчик' 						=> 'Сурдопереводчик', 
				'Специалист по техническим средствам'	=> 'Специалист по техническим средствам', 
				'Профориентолог' 						=> 'Профориентолог',
			),
		));
		
		$groups = HM_DisabledPeople_DisabledPeopleModel::getFundGroups();
		$listFunds = $this->getService('DisabledPeople')->getListFunds();
		
		foreach($groups as $group_id => $group_name){						
			$this->addElement('multiCheckbox','fund_'.$group_id, array( 
				'label' 		=> $group_name,				
				'multiOptions' 	=> $listFunds[$group_id],
				'separator' 	=> '<br/><br/>',
				'class'			=> 'dp_checkbox',
			));	
		
		}
		
		
		$this->addElement('textarea', 'question', array(
            'Label'		=> _('Ваш вопрос'),            
			'Value'		=> '',		
        ));
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init();
	}
}