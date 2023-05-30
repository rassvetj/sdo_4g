<?php
class HM_Form_AssignDean extends HM_Form{

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign');

        $this->addElement(
            'hidden',
            'cancelUrl',
            array(
                 'Required' => false,
                 'Value' => $this->getView()->url(array(
                                                       'module' => 'user',
                                                       'controller' => 'edit',
                                                       'action' => 'card'
                                                  ))
            ));

        $this->addElement(
            'hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            ));

        $this->addElement('RadioGroup', 'unlimited_subjects', array(
                                                                   'Label' => '',
                                                                   'MultiOptions' => array(1 => _('Без ограничений'), 0 => _('Ограничение области ответственности')),
                                                                   'form' => $this,
                                                                   'dependences' => array(0 => array('subjects'))
                                                              ));

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		if($lng == 'eng') 
			$subjects = $this->getService('Subject')->fetchAll()->getList('subid', 'name_translation');			
		else	
			$subjects = $this->getService('Subject')->fetchAll()->getList('subid', 'name');

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
            'checkbox',
            'assign_new_subjects',
            array(
                 'Label' => _('Автоматически включать новые курсы'),
                 'Required' => false,
                 'Validators' => array(),
                 'Filters' => array('StripTags')
            ));

        $this->addDisplayGroup(array(
                                    'unlimited_subjects',
                                    'cancelUrl',
                                    'user_id',
                                    'subjects',
                                    'assign_new_subjects'
                               ),
                               'groupCourses',
                               array(
                                    'legend' => _('Доступ к учебным курсам')
                               ));

        $classifierElementsPeople = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
            $this->getParam('user_id', 0),
            'dean_responsibilities'
        );

        $classifierElements = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
            $this->getParam('user_id', 0),
            'dean_responsibilities'
        );

        $elementsDependency = array_merge($classifierElements, $classifierElementsPeople);


        if (is_array($elementsDependency) && count($elementsDependency)) {
        $this->addElement('RadioGroup', 'unlimited_classifiers', array(
                                                                      'Label' => '',
                                                                      'MultiOptions' => array(1 => _('Без ограничений'), 0 => _('Ограничение области ответственности')),
                                                                      'form' => $this,
                                                                      'dependences' => array(0 => $elementsDependency)
                                                                 ));


            array_unshift($elementsDependency, 'unlimited_classifiers');
        
            $this->addDisplayGroup(
                $elementsDependency,
                'classifiers',
                array('legend' => _('Доступ к учетным записям пользователей'))
            );

        }


        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Сохранить')
            ));

        parent::init(); // required!

    }

}