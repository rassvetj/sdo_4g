<?php
class HM_Form_Programm extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('programm');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'programm', 'controller' => 'list', 'action' => 'index'), null, true)
        ));

        $this->addElement('hidden', 'programm_id', array(
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
		
        $this->addElement('text', 'name_translation', array(
            'Label' => _('Перевод (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));		

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
                'Label' => _('Описание'),
                'Required' => false,
                'class' => 'wide'
            )
        );
		
        $this->addElement($this->getDefaultWysiwygElementName(), 'description_translation', array(
                'Label' => _('Перевод (en)'),
                'Required' => false,
                'class' => 'wide'
            )
        );		

        $fields = array(
            'cancelUrl',
            'programm_id',
            'name',
            'name_translation',
            'description',
            'description_translation'
        );

        $this->addDisplayGroup(
            $fields,
            'programmGroup',
            array('legend' => _('Общие свойства'))
        );

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}