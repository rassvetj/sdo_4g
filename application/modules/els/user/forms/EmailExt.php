<?php
/**
 * Форма для создания и редактирования пользователей
 *
 */
class HM_Form_EmailExt extends HM_Form {

    public function init() {

		$userId = $this->getService('User')->getCurrentUserId();
		
        $this->setMethod(Zend_Form::METHOD_POST);
		
		
		
		//$action = array('module' => 'user', 'controller' => 'email-ext', 'action' => 'save');
        
        //$this->setAction($this->getView()->url($action));

        $this->setName('user_email_ext');


        $this->addElement('text',
			'email',
			array('Label' => _('E-mail'),
				'Required' => true,
				'Description' => _('Введитен свою почту'),
				'Validators' => array('EmailAddress'),				
				'Filters' => array('StripTags'),
			)
        );
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Отправить'),
        ));
		
		parent::init(); // required!

	}

}