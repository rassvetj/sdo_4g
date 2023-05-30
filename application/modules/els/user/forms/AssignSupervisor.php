<?php
class HM_Form_AssignSupervisor extends HM_Form{

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign');

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);		
		
        $this->addElement(
            'hidden',
            'cancelUrl',
            array(
                 'Required' => false,
                 'Value' => $this->getView()->url(array(
                                                       'module' => 'assign',
                                                       'controller' => 'supervisor',
                                                       'action' => 'index'
                                                  ))
            ));

        $this->addElement(
            'hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            ));

        $radioGroupMulti = array(0 => _('Не назначена'),
                                 1 => _('Ограничение по курсам'),
                                 2 => _('Ограничение по учебным группам'),
                                 3 => _('Ограничение по учебным программам'),
                                 4 => _('Ограничение по слушателям'),
                                );
        $radioDependence = array(1 => array('subjects'), 2 => array('groups'), 3 => array('programms'), 4 => array('students'));
        /*
        if (count($this->getClassifierElementsNames())) {
            $radioGroupMulti[2] = _('Ограничение по классификаторам');
            $radioDependence[2] = $this->getClassifierElementsNames();
        }
        */
        $this->addElement('RadioGroup', 'limited', array(
                                                                   'Label' => '',
                                                                   'MultiOptions' => $radioGroupMulti,
                                                                   'form' => $this,
                                                                   'dependences' => $radioDependence
                                                              ));

        $subjects = array('-1' => 'Все') + $this->getService('Subject')
                ->fetchAll()
                ->getList('subid', 'name');
				
        $subjects_translations = array('-1' => 'All') + $this->getService('Subject')
                ->fetchAll()
                ->getList('subid', 'name_translation');	


		if($lng == 'eng')
			foreach($subjects as $k => $v) 
				if($subjects_translations[$k] != '')  $subjects[$k] = $subjects_translations[$k]; 	
				
				
        $groups = array('-1' => 'Все') + $this->getService('StudyGroup')
                ->fetchAll()
                ->getList('group_id', 'name');

        $programms = array('-1' => 'Все') + $this->getService('Programm')
            ->fetchAll()
            ->getList('programm_id', 'name');

        $this->addElement(
            'UiMultiSelect',
            'subjects',
            array(
                'Label' => '',
                'Required' => false,
                'multiOptions' => $subjects,
                'class' => 'multiselect'
            ));

        $this->addElement(
            'UiMultiSelect',
            'groups',
            array(
                'Label' => '',
                'Required' => false,
                'multiOptions' => $groups,
                'class' => 'multiselect'
            ));

        $this->addElement(
            'UiMultiSelect',
            'programms',
            array(
                'Label' => '',
                'Required' => false,
                'multiOptions' => $programms,
                'class' => 'multiselect'
            ));

        $this->addElement(new HM_Form_Element_FcbkComplete('students', array(
                'required' => false,
                //'Label' => _('Пользователь'),
                //'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
                'newel' => false,
                //'maxitems' => 1
				'maxitems' => 40
            )
        ));

        $fieldsGroup = array(
            'limited',
            'cancelUrl',
            'user_id',
            'subjects',
            'groups',
            'programms',
            'students',
        );

        $this->addDisplayGroup($fieldsGroup,
            'groupSets',
            array(
            'legend' => _('Доступ к зоне ответственности')
       ));

        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Сохранить')
            ));

        parent::init(); // required!

    }

}