<?php
class HM_Form_Group extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('group');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'group_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        $this->addElement('multiCheckbox', 'roles', array(
            'Label' => _('Роли'),
            'Required' => false,
            'MultiOptions' => Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getRoles(),
            'Filters' => array(
                'StripTags'
            )
        ));

		$this->addElement('SubmitButton', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'group_id',
                'name',
                'roles',
                'submit'
            ),
            'projectGroup',
            array('legend' => _('Группа метаданных'))
        );

        parent::init(); // required!
	}

}