<?php
class HM_Form_Hostel extends HM_Form{

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('hostel');
		
		$action = $this->getRequest()->getActionName();		
		$this->setAction(
			$this->getView()->url(array(                
                'controller' => 'edit',
                'action' => 'create', 				
            ))
		);
		
		
		$types = HM_Hostel_Claims_ClaimsModel::getTypes();
		
		$user = $this->getService('User')->getCurrentUser();		
        if($user){
			$mid_external = $user->mid_external;	
		}		
		if(!$this->getService('Ticket')->isExsistHostelContract($mid_external)){ //--нет договора на общежитие. Переселяться не может.
			unset($types[HM_Hostel_Claims_ClaimsModel::TYPE_RE_SETTLEMENT]);
		}
		$this->addElement('select', 'type_id', array(
			'Label' => _('Тип'),
			'Required' => false,
			'multiOptions' => $types,
			'Validators' => array('Int'),
			'Filters' => array('Int'),						
		));
		
		
		$addres = $this->getService('Hostel')->getListAddress();
		$this->addElement('select', 'addres_id', array(
			'Label' => _('Выберите общежитие'),
			'Required' => false,
			'multiOptions' => $addres,
			'Validators' => array('Int'),
			'Filters' => array('Int'),	
			'onChange' => 'if (typeof getRooms == \'function\') {	getRooms(); }', //--jQuery ф-ция в tpl шаблоне			
		));
		
		
		$this->addElement('select', 'room_id', array(
			'Label' => _('Комната'),
			'Required' => false,
			'multiOptions' => array('0' => 'Любая'), 
			'Validators' => array('Int'),
			'Filters' => array('Int'),						
		));
	
        
        $this->addElement(
            'Submit',
            'submit',
            array(
				'Label' => _('Отправить'),
				'order' => 99, //--чтобы кнопка была всегда последней				
            ));

    }

}